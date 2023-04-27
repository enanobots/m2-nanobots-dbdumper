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

use Nanobots\DbDumper\Export\TableGroupInterface;
use Nanobots\DbDumper\Helper\FileWriter;

class TableGroup implements TableGroupInterface
{
    /** @var FileWriter  */
    protected FileWriter $fileWriter;

    /** @var array */
    protected array $tableList;

    /** @var string  */
    protected string $groupExportMode;

    /** @var array|\Nanobots\DbDumper\Export\TableFilterInterface[]  */
    protected array $filters;


    /**
     * @param \Nanobots\DbDumper\Helper\FileWriter $fileWriter
     * @param \Nanobots\DbDumper\Export\TableFilterInterface[] $filters
     * @param array $tableList
     * @param string $groupExportMode

     */
    public function __construct(
        FileWriter $fileWriter,
        array $filters = [],
        array $tableList = [],
        string $groupExportMode = TableGroupInterface::GROUP_EXPORT_DEFAULT_MODE,
    ) {
        $this->fileWriter = $fileWriter;
        $this->tableList = $tableList;
        $this->groupExportMode = $groupExportMode;
        $this->filters = $filters;
    }

    /**
     * @return \Nanobots\DbDumper\Export\TableExportInterface[]
     */
    public function getTables(): array
    {
        return $this->tableList;
    }

    /**
     * @return string
     */
    public function getGroupExportMode(): string
    {
        return $this->groupExportMode;
    }

    /**
     * @param string $tableName
     * @return TableGroupInterface
     */
    public function addTable(string $tableName): TableGroupInterface
    {
        $this->tableList[$tableName] = null;

        return $this;
    }

    /**
     * @param string $tableName
     * @return TableGroupInterface
     */
    public function removeTable(string $tableName): TableGroupInterface
    {
        unset($this->tableList[$tableName]);

        return $this;
    }

    /**
     * @param array $tableList
     * @return TableGroupInterface
     */
    public function addTables(array $tableList): TableGroupInterface
    {
        foreach ($tableList as $table) {
            $this->addTable($table);
        }

        return $this;
    }

    /**
     * @param array $tableList
     * @return TableGroupInterface
     */
    public function removeTables(array $tableList): TableGroupInterface
    {
        foreach ($tableList as $table) {
            $this->removeTable($table);
        }

        return $this;
    }
}
