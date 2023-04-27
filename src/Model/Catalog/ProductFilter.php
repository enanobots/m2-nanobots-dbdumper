<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Model\Catalog;

use Nanobots\DbDumper\Export\TableFilterInterface;

class ProductFilter implements TableFilterInterface
{
    public const PRODUCT_ID_COLUMNS = ['entity_id', 'product_id', 'parent_id', 'child_id',
        'parent_product_id', 'linked_product_id'];

    /**
     * @return array
     */
    public function getFilters(): array
    {
        // TODO: Implement getFilters() method.
    }
}
