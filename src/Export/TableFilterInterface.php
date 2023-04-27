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

interface TableFilterInterface
{
    /**
     * Apply filter on the select object
     *
     * @param \Zend_Db_Select $select
     */
    public function applyFilter(\Zend_Db_Select $select): void;
}
