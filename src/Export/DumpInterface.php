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

use Symfony\Component\Console\Output\OutputInterface;

interface DumpInterface
{
    /**
     * @param null|string $exportModel
     * @return null|\Nanobots\DbDumper\Export\ExportModelInterface
     */
    public function getExportModel(?string $exportModel = "default"): ?ExportModelInterface;

    /**
     * @return \Nanobots\DbDumper\Export\ExportModelInterface[]|null
     */
    public function getExportModels(): array;

    /**
     * @return array
     */
    public function getTableGroups(): array;

    /**
     * Set Table Groups
     *
     * @param \Nanobots\DbDumper\Export\TableGroupInterface[] $tableGroups
     * @return mixed
     */
    public function setTableGroups(array $tableGroups): DumpInterface;

    /**
     * @param \Nanobots\DbDumper\Export\ExportModelInterface $exportModel
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @return bool
     */
    public function initializeDatabaseDump(ExportModelInterface $exportModel, OutputInterface $output = null): bool;

    /**
     * @param \Nanobots\DbDumper\Export\TableGroupInterface[] $tableGroups
     * @return array
     */
    public function getTablesFromTableGroups(array $tableGroups): array;

    /**
     * @param bool $filterTableGroups
     * @return array
     */
    public function getTablesWithoutGroups(bool $filterTableGroups = true): array;

    /**
     * Method to add new tables to a Table Group, needed for undefined tables
     *
     * @param string $tableGroup
     * @param array $tableList
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Nanobots\DbDumper\Export\DumpInterface
     */
    public function addTablesToTableGroup(string $tableGroup, array $tableList): DumpInterface;
}
