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

use Magento\Framework\Exception\LocalizedException;
use Nanobots\DbDumper\Export\DumpInterface;
use Nanobots\DbDumper\Export\ExportModelInterface;
use Nanobots\DbDumper\Export\TableExportInterface;
use Nanobots\DbDumper\Export\TableGroupInterface;
use Nanobots\DbDumper\Helper\FileWriter;
use Nanobots\DbDumper\Sql\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Dump implements DumpInterface
{
    /** @var ProgressBar  */
    protected ProgressBar $progressBar;

    /** @var array|\Nanobots\DbDumper\Export\ExportModelInterface[]  */
    protected array $exportModels;

    /** @var Connection  */
    protected Connection $connection;

    /** @var array|TableGroupInterface[]  */
    protected array $tableGroups;

    /** @var TableExportInterface  */
    protected TableExportInterface $tableExport;

    /** @var LoggerInterface  */
    protected LoggerInterface $logger;

    /** @var FileWriter  */
    protected FileWriter $fileWriter;

    /** @var array  */
    protected array $initStatements;

    /** @var array  */
    protected array $finalStatements;

    /**
     * @param \Nanobots\DbDumper\Sql\Connection $connection
     * @param \Nanobots\DbDumper\Export\TableGroupInterface[] $tableGroups
     * @param \Nanobots\DbDumper\Export\TableExportInterface $tableExport
     * @param \Nanobots\DbDumper\Helper\FileWriter $fileWriter
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Nanobots\DbDumper\Export\ExportModelInterface[] $exportModels
     * @param array $initStatements
     * @param array $finalStatements
     */
    public function __construct(
        Connection $connection,
        array $tableGroups,
        TableExportInterface $tableExport,
        FileWriter $fileWriter,
        LoggerInterface $logger,
        array $exportModels = [],
        array $initStatements = [],
        array $finalStatements = []
    ) {
        $this->exportModels = $exportModels;
        $this->connection = $connection;
        $this->tableGroups = $tableGroups;
        $this->tableExport = $tableExport;
        $this->logger = $logger;
        $this->fileWriter = $fileWriter;
        $this->initStatements = $initStatements;
        $this->finalStatements = $finalStatements;
    }

    /**
     * @return \Nanobots\DbDumper\Export\TableGroupInterface[]
     */
    public function getTableGroups(): array
    {
        return $this->tableGroups;
    }

    /**
     * @param \Nanobots\DbDumper\Export\ExportModelInterface $exportModel
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function initializeDatabaseDump(ExportModelInterface $exportModel, OutputInterface $output = null): bool
    {
        $timeStamp = 777; // for debugging
        $this->fileWriter->setFile('dump.sql')->setTimeStamp($timeStamp);
        $this->fileWriter->writeToFile(); //initialize file

        /** overwrite table groups specified in export mode */
        if (!empty($exportModel->getTableGroups())) {
            $this->setTableGroups($exportModel->getTableGroups());
        }

        foreach ($this->initStatements as $initStatement) {
            $this->fileWriter->writeToFile(
                $initStatement . PHP_EOL,
                FILE_APPEND
            );
        }

        $this->_initiateProgressBar($output);
        foreach ($this->tableGroups as $tableGroup) {
            foreach ($tableGroup->getTables() as $tableName => $tableData) {
                $this->progressBar->advance(1);

                switch ($tableGroup->getGroupExportMode()) {
                    //TODO - allow production export with data anonymization
                    case TableGroupInterface::GROUP_EXPORT_MODE_REDUNDANT: {
                        $this->tableExport->writeSqlCreateTableQuery($tableName);
                        $this->tableExport->resetAutoIncrement($tableName);
                        break;
                    }
                    default: {
                        $this->tableExport->prepareTableDataDump(
                            $tableName,
                            $tableData,
                            null,
                            null,
                            $tableGroup->getGroupExportMode() === TableGroupInterface::GROUP_EXPORT_MODE_ANONYMIZE,
                            $exportModel->applyFilters()
                        );
                        break;
                    }
                }
            }
        }

        foreach ($this->finalStatements as $table => $finalStatement) {
            $finalStatement = str_replace(
                '{table}',
                $this->connection->getTableName($table),
                $finalStatement
            );

            $this->fileWriter->writeToFile($finalStatement . PHP_EOL, FILE_APPEND);
        }

        $this->progressBar->finish();
        $output->writeln('');

        return false;
    }

    /**
     * @param array $tableGroups
     * @return array
     */
    public function getTablesFromTableGroups(array $tableGroups): array
    {
        $nestedTables = [];
        foreach ($tableGroups as $tableGroup) {
            foreach ($tableGroup->getTables() as $tableName => $tableData) {
                $this->tableExport->getAllNestedTables($tableName, $tableData, $nestedTables);
            }
        }

        return $nestedTables;
    }

    /**
     * @param bool $filterTableGroups
     * @return array
     */
    public function getTablesWithoutGroups(bool $filterTableGroups = true): array
    {
        if ($filterTableGroups) {
            return array_diff(
                $this->connection->getAllTables(),
                $this->getTablesFromTableGroups($this->tableGroups)
            );
        }

        return $this->connection->getAllTables();
    }

    /**
     * @param string $tableGroup
     * @param array $tableList
     * @return \Nanobots\DbDumper\Export\DumpInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addTablesToTableGroup(string $tableGroup, array $tableList): DumpInterface
    {
        if (isset($this->tableGroups[$tableGroup])) {
            $this->tableGroups[$tableGroup]->addTables($tableList);
        } else {
            throw new LocalizedException(__('Table group %1 is not defined, please check your di.xml'));
        }

        return $this;
    }

    /**
     * @param null|string $exportModel
     * @return \Nanobots\DbDumper\Export\ExportModelInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExportModel(?string $exportModel = "default"): ?ExportModelInterface
    {
        if (isset($this->exportModels[$exportModel])) {
            return $this->exportModels[$exportModel];
        }

        if (!isset($this->exportModels['default'])) {
            throw new LocalizedException(__('Default Export Mode is not configured'));
        }

        return null;
    }

    /**
     * @param array $tableGroups
     * @return DumpInterface
     */
    public function setTableGroups(array $tableGroups): DumpInterface
    {
        $this->tableGroups = $tableGroups;

        return $this;
    }

    /**
     * @return array|ExportModelInterface[]
     */
    public function getExportModels(): array
    {
        return $this->exportModels;
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    private function _initiateProgressBar(OutputInterface $output): void
    {
        $count = count($this->getTablesFromTableGroups($this->tableGroups));
        $this->progressBar = new ProgressBar($output, $count);
        $this->progressBar->setBarCharacter('<fg=magenta>=</>');
    }
}
