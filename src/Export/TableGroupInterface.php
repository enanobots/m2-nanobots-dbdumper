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

interface TableGroupInterface
{
    /** @var string  */
    public const GROUP_EXPORT_DEFAULT_MODE = 'default';

    /** @var string  */
    public const GROUP_EXPORT_MODE_REDUNDANT = 'redundant';

    /** @var string  */
    public const GROUP_EXPORT_MODE_ANONYMIZE = 'anonymize';

    /**
     * @return array
     */
    public function getTables(): array;

    /**
     * @return string
     */
    public function getGroupExportMode(): string;

    /**
     * @param string $tableName
     * @return TableGroupInterface
     */
    public function addTable(string $tableName): TableGroupInterface;

    /**
     * @param array $tableList
     * @return TableGroupInterface
     */
    public function addTables(array $tableList): TableGroupInterface;

    /**
     * @param string $tableName
     * @return TableGroupInterface
     */
    public function removeTable(string $tableName): TableGroupInterface;

    /**
     * @param array $tableList
     * @return TableGroupInterface
     */
    public function removeTables(array $tableList): TableGroupInterface;
}
