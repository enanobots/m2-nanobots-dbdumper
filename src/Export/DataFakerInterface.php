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

interface DataFakerInterface
{
    /**
     * Function needed to modify data after import
     *
     * @param int $entityId
     * @param null|string $value
     * @return mixed
     */
    public function decorateData(int $entityId, ?string $value = null): string;
}
