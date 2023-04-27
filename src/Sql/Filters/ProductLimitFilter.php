<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Sql\Filters;

use Nanobots\DbDumper\Export\TableFilterInterface;

class ProductLimitFilter implements TableFilterInterface
{
    /** @var int|null  */
    protected ?int $limit;

    /**
     * @param int|null $limit
     */
    public function __construct(
        ?int $limit = 100
    ) {
        $this->limit = $limit;
    }

    /**
     * @param \Zend_Db_Select $select
     * @return void
     */
    public function applyFilter(\Zend_Db_Select $select): void
    {
        $select->limit($this->limit);
    }
}
