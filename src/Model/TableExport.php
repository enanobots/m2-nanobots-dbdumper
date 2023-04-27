<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Model;

use Magento\Framework\Exception\FileSystemException;
use Nanobots\DbDumper\Export\TableExportInterface;
use Nanobots\DbDumper\Export\TableFilterInterface;
use Nanobots\DbDumper\Helper\FileWriter;
use Nanobots\DbDumper\Sql\Connection;
use PDO;
use Psr\Log\LoggerInterface;

class TableExport implements TableExportInterface
{
    /** @var array  */
    protected array $writeQueries = [];

    /** @var \Nanobots\DbDumper\Sql\Connection  */
    protected Connection $connection;

    /** @var \Psr\Log\LoggerInterface  */
    protected LoggerInterface $logger;

    /** @var \Nanobots\DbDumper\Helper\FileWriter */
    protected FileWriter $fileWriter;

    /** @var array  */
    protected array $createTableModifiers;

    /** @var int  */
    protected int $batchSize;

    /** @var \Nanobots\DbDumper\Export\DataFakerInterface[]|null  */
    protected ?array $restrictedColumns = null;

    /** @var \Nanobots\DbDumper\Export\TableFilterInterface[]|null */
    protected ?array $tableFilters;

    /**
     * @param \Nanobots\DbDumper\Sql\Connection $connection
     * @param \Nanobots\DbDumper\Helper\FileWriter $fileWriter
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $createTableModifiers
     * @param int $batchSize
     * @param array|null $restrictedColumns
     * @param \Nanobots\DbDumper\Export\TableFilterInterface[]|null $tableFilters
     */
    public function __construct(
        Connection $connection,
        FileWriter $fileWriter,
        LoggerInterface $logger,
        array $createTableModifiers = [],
        int $batchSize = 2500,
        ?array $restrictedColumns = null,
        ?array $tableFilters = null
    ) {
        $this->connection = $connection;
        $this->fileWriter = $fileWriter;
        $this->logger = $logger;
        $this->createTableModifiers = $createTableModifiers;
        $this->batchSize = $batchSize;
        $this->restrictedColumns = $restrictedColumns;
        $this->tableFilters = $tableFilters;
    }

    /**
     * @param string $tableName
     * @return int
     * @throws FileSystemException|\Zend_Db_Statement_Exception
     */
    public function writeSqlCreateTableQuery(string $tableName): int
    {
        $tables = $this->_determineMultipleTables($tableName);
        $bytesWrote = 0;

        foreach ($tables as $table) {
            if ($this->_doesTableExist($table)) {
                if (!isset($this->writeQueries[$table])) {
                    $statement = $this->connection->getCreateTable($table);
                    foreach ($this->createTableModifiers as $createTableModifier) {
                        $createTableModifier->modifyCreateTableQuery($statement);
                    }

                    $fileContent = sprintf(
                        "%s\n%s\n%s;" . PHP_EOL . PHP_EOL,
                        "-- $table",
                        "DROP TABLE IF EXISTS $table;",
                        $statement
                    );

                    $bytesWrote = $this->fileWriter->writeToFile($fileContent, FILE_APPEND);
                    $this->writeQueries[$table] = true;
                }
            }

            $this->logger->warning(__('Table %1 does not exists in the source database. Skipped', $tableName));
        }

        if (!$bytesWrote) {
            $this->logger->error(__('No bytes wrote, some error?'));
        }

        return $bytesWrote;
    }

    /**
     * @param string $tableName
     * @param array|null $tableData
     * @param string|null $relatedColumn
     * @param array|null $relatedIds
     * @param bool $anonymize
     * @param bool $useFilters
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Zend_Db_Statement_Exception
     */
    public function prepareTableDataDump(
        string $tableName,
        ?array $tableData = null,
        ?string $relatedColumn = null,
        ?array $relatedIds = null,
        bool $anonymize = false,
        bool $useFilters = false
    ): int
    {
        $tables = $this->_determineMultipleTables($tableName);

        foreach ($tables as $table) {
            if ($this->_doesTableExist($table)) {
                $selects = [];

                if ($this->connection->isTableAView($table)) {
                    $this->writeCreateViewQuery($table);
                    continue;
                } else {
                    $this->writeSqlCreateTableQuery($table);
                }
                $autoIncrementField = $this->connection->getAutoincrementField($table);
                $select = $this->getMainSelectWithFilters($table, $useFilters);

                if (isset($tableData['parent'])) {
                    $relatedColumn = $tableData['parent'];
                }

                if ($autoIncrementField) {
                    if ($tableData['field'] ?? null === null && $relatedIds === []) {
                        if (!$relatedIds) {
                            $idValueFrom = 0;
                            $batches = $this->getEntityBatches($autoIncrementField, $table);

                            foreach ($batches as $batch) {
                                $batchSelect = clone $select;
                                $batchSelect
                                    ->where("$autoIncrementField >= ?", $idValueFrom)
                                    ->where("$autoIncrementField < ?", $batch['id_value_to']);
                                $idValueFrom = $batch['id_value_to'];

                                $selects[] = $batchSelect;
                            }
                        } else {
                            if (isset($tableData['parent_columns'])) {
                                foreach ($tableData['parent_columns'] as $relatedColumn => $parentColumn) {
                                    $select->where("$relatedColumn in (?)", $relatedIds);
                                }
                            } else {
                                $select->where("$relatedColumn in (?)", $relatedIds);
                            }
                            $selects[] = $select;
                        }
                    } else {
                        if (isset($tableData['parent_columns']) && $relatedIds !== []) {
                            foreach ($tableData['parent_columns'] as $relatedColumn => $parentColumn) {
                                $select->where("$relatedColumn in (?)", $relatedIds);
                            }
                        }
                        $selects[] = $select;
                    }

                } else {
                    if (isset($tableData['parent_columns']) && $relatedIds !== null) {
                        foreach ($tableData['parent_columns'] as $relatedColumn => $parentColumn) {
                            $select->where("$relatedColumn in (?)", $relatedIds);
                        }
                    }
                    $selects[] = $select;
                }

                foreach ($selects as $select) {
                    $tableRows = $this->connection->getConnection()->fetchAll($select);
                    $tableUniqueIds = array_column($tableRows, $tableData['field'] ?? null);

                    if (!empty($tableRows)) {
                        $this->writeTableRows($tableRows, $table, $anonymize);
                    }

                    foreach ($tableData['related_tables'] ?? [] as $relatedTableName => $relatedTableData) {
                        $this->prepareTableDataDump(
                            $relatedTableName,
                            $relatedTableData,
                            $tableData['field'] ?? null,
                            $tableUniqueIds,
                            $anonymize // pass from parent table
                        );
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    private function _determineMultipleTables(string $tableName): array
    {
        $returnArray = [];
        if (str_contains($tableName, '%')) {
            $query = $this->connection->query('SHOW TABLES LIKE "' . $tableName .'"');
            foreach ($query->fetchAll(PDO::FETCH_COLUMN) as $row) {
                if (str_starts_with($tableName, '%')) {
                    $returnArray[] = $row;
                } else {
                    $tableShort = preg_replace('/[%0-9]+/', '', $tableName);
                    $shortName = preg_replace('/[0-9]+/', '', $row);
                    if ($tableShort === $shortName) {
                        $returnArray[] = $row;
                    }
                }
            }
            return $returnArray;
        } else {
            return [$tableName];
        }
    }

    /**
     * @param string $tableName
     * @return bool
     */
    private function _doesTableExist(string $tableName): bool
    {
        return $this->connection->getConnection()->isTableExists($tableName);
    }

    /**
     * @param array $tableRows
     * @param string $tableName
     * @param bool $anonymize
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function writeTableRows(array $tableRows, string $tableName, bool $anonymize = false): int
    {
        $dataToWrite = [];
        $insertString = "LOCK TABLES `$tableName` WRITE;" . PHP_EOL;
        $insertString .= 'INSERT INTO `' . $tableName . '` VALUES ' . PHP_EOL;
        $this->fileWriter->writeToFile($insertString, FILE_APPEND);

        foreach ($tableRows as $tableRow) {
            // DATA ANONYMIZATION TAKES PLACE HERE
            if ($this->doesTableHasRestrictedColumns($tableName) && $anonymize) {
                // TODO - write a method to determine the entity_id value from table Row and table structure
                $entityId = (int)($tableRow['entity_id']
                    ?? $tableRow['parent_id']
                    ?? $tableRow['address_id']
                    ?? $tableRow['customer_id']
                    ?? $tableRow['user_id']?? 1);

                array_walk_recursive($tableRow, function (&$item, $columnName) use ($tableName, $entityId) {
                    if (isset($this->restrictedColumns[$columnName])) {
                        $item = $this->restrictedColumns[$columnName]->decorateData(
                            $entityId, null
                        );
                    }
                });
            }

            array_walk_recursive($tableRow, function (&$item, $columnName) {
                if (is_string($item) && !is_numeric($item)) {
                    // fix for MySQL versions
                    $item = addslashes($item);
                    $item = '"' . $item . '"';
                }

                if ($item === NULL) {
                    $item = 'NULL';
                };
            });

            $dataRow = '(' . implode(',', $tableRow) . ')';
            $dataToWrite[] = $dataRow;
        }

        $bytes = $this->fileWriter->writeToFile(
            implode(',', $dataToWrite) . ';' . PHP_EOL,
            FILE_APPEND
        );

        $this->fileWriter->writeToFile("UNLOCK TABLES;" . PHP_EOL, FILE_APPEND);
        return $bytes;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Db_Statement_Exception|\Magento\Framework\Exception\RuntimeException
     */
    public function resetAutoIncrement(string $tableName): int
    {
        $bytesWritten = 0;
        $tables = $this->_determineMultipleTables($tableName);

        foreach ($tables as $tableName) {
            $autoIncrementField = $this->connection->getAutoincrementField($tableName);

            if ($autoIncrementField) {
                $query = "ALTER TABLE `$tableName` AUTO_INCREMENT = 1;" . PHP_EOL;
                $bytesWritten += $this->fileWriter->writeToFile(
                    $query,
                    FILE_APPEND
                );
            }
        }

        return $bytesWritten;
    }

    /**
     * @param string $viewName
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function writeCreateViewQuery(string $viewName): int
    {
        $query = "SHOW CREATE VIEW $viewName;";
        $string = $this->connection->getConnection()->fetchRow($query);
        $createView = $string['Create View'];
        $regex = "/DEFINER[ ]*=[ ]*`[^`]+`@`[^`]+`/";
        $createViewSql = preg_replace($regex, "DEFINER=`CURRENT_USER`", $createView);

        return $this->fileWriter->writeToFile(
            $createViewSql . ';' . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * @param string $autoIncrementField
     * @param string $table
     * @return array
     */
    public function getEntityBatches(string $autoIncrementField, string $table): array
    {
        return $this->connection->getConnection()->fetchAll(
            sprintf(
                'SELECT CEIL(%s/%s)*%s as id_value_to FROM %s GROUP BY 1 order by id_value_to',
                $autoIncrementField,
                $this->batchSize,
                $this->batchSize,
                $table
            )
        );
    }


    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAllNestedTables(string $tableName, ?array $tableData, array &$nestedTables): array
    {
        $tables = $this->_determineMultipleTables($tableName);
        foreach ($tables as $table) {
            $nestedTables[] = $table;
            foreach ($tableData['related_tables'] ?? [] as $relatedTableName => $relatedTableData) {
                $this->getAllNestedTables($relatedTableName, $relatedTableData, $nestedTables);
            }
        }

        return[];
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function doesTableHasRestrictedColumns(string $tableName): bool
    {
        $table = $this->connection->getTableName($tableName);
        $columns = $this->connection->getConnection()->fetchCol('show columns from `' . $table . '`');
        return !empty(array_intersect(array_keys($this->restrictedColumns), $columns));
    }

    /**
     * @param $tableRow
     * @param string $tableName
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getEntityIdField($tableRow, string $tableName): int
    {
        return (int)$tableRow[$this->connection->getAutoincrementField($tableName)] ?? 1;
    }

    /**
     * @param string $tableName
     * @param bool $useFilters
     * @return \Magento\Framework\DB\Select
     */
    public function getMainSelectWithFilters(string $tableName, bool $useFilters = false): \Magento\Framework\DB\Select
    {
        $select = $this->connection->select();

        if ($useFilters) {
            /** @var TableFilterInterface $tableFilter */
            foreach ($this->tableFilters[$tableName] ?? [] as $tableFilter) {
                $tableFilter->applyFilter($select);
            }
        }

        $select->from($tableName);
        return $select;
    }
}
