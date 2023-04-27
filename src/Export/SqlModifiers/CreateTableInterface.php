<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Export\SqlModifiers;

interface CreateTableInterface
{
    /**
     * @param string $sqlCreateTableQuery
     */
    public function modifyCreateTableQuery(string &$sqlCreateTableQuery): void;
}
