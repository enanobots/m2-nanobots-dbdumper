<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Export;

use Magento\Framework\Exception\FileSystemException;
use Nanobots\DbDumper\Model\TableExport;

interface TableExportInterface
{
    /**
     * @param string $tableName
     * @return int
     */
    public function writeSqlCreateTableQuery(string $tableName): int;

    /**
     * @param string $viewName
     * @return int
     */
    public function writeCreateViewQuery(string $viewName): int;

    /**
     * @param string $tableName
     * @param array|null $tableData
     * @param string|null $relatedColumn
     * @param array|null $relatedIds
     * @param bool $anonymize
     * @param bool $useFilters
     * @return int
     */
    public function prepareTableDataDump(
        string $tableName,
        ?array $tableData = null,
        ?string $relatedColumn = null,
        ?array $relatedIds = null,
        bool $anonymize = false,
        bool $useFilters = false
    ): int;


    /**
     * Write Data from Table to text file
     *
     * @param array $tableRows
     * @param string $tableName
     * @param bool $anonymize
     * @return int
     */
    public function writeTableRows(array $tableRows, string $tableName, bool $anonymize = false): int;

    /**
     * Get All nested tables to determine complete table list
     *
     * @param string $tableName
     * @param null|array $tableData
     * @param array $nestedTables
     * @return array
     */
    public function getAllNestedTables(string $tableName, ?array $tableData, array &$nestedTables): array;

    /**
     * Reset AutoIncrement Field for a $tableName
     *
     * @param string $tableName
     * @return int
     */
    public function resetAutoIncrement(string $tableName): int;

    /**
     * Check if Table has columns that need to be anonymized
     *
     * @param string $tableName
     * @return bool
     */
    public function doesTableHasRestrictedColumns(string $tableName): bool;
}
