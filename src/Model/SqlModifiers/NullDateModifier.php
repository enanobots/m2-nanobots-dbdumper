<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Model\SqlModifiers;

use Nanobots\DbDumper\Export\SqlModifiers\CreateTableInterface;

class NullDateModifier implements CreateTableInterface
{
    public function modifyCreateTableQuery(string &$sqlCreateTableQuery): void
    {
        $currentDate = sprintf("DEFAULT '%s'" , date('Y-m-d H:i:s'));
        $sqlCreateTableQuery = str_replace("DEFAULT '0000-00-00 00:00:00'", $currentDate, $sqlCreateTableQuery);
    }
}
