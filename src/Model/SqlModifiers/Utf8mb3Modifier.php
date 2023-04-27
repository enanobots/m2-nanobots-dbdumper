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

class Utf8mb3Modifier implements CreateTableInterface
{
    public function modifyCreateTableQuery(string &$sqlCreateTableQuery): void
    {
        $utf8mb4 = 'utf8mb4';
        $sqlCreateTableQuery = str_replace('utf8mb3', $utf8mb4, $sqlCreateTableQuery);
    }
}
