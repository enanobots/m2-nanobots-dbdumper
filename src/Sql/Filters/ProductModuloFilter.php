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

class ProductModuloFilter implements TableFilterInterface
{

    /** @var int */
    protected int $modParam;

    /** @var string|null  */
    protected ?string $entityIdColumn;

    /**
     * @param string|null $entityIdColumn
     * @param int|null $modParam
     */
    public function __construct(
        ?string $entityIdColumn = 'entity_id',
        ?int $modParam = 2
    ) {
        $this->modParam = $modParam;
        $this->entityIdColumn = $entityIdColumn;
    }

    /**
     *
     *
     * @param \Zend_Db_Select $select
     */
    public function applyFilter(\Zend_Db_Select $select): void
    {
        $select->where('`' . $this->entityIdColumn . '` MOD ' . $this->modParam . ' = 0');
    }
}
