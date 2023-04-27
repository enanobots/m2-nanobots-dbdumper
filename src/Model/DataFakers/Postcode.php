<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Model\DataFakers;

use Nanobots\DbDumper\Export\DataFakerInterface;

class Postcode implements DataFakerInterface
{
    /**
     * @param int $entityId
     * @param null|string $value
     * @return string
     */
    public function decorateData(int $entityId, ?string $value = null): string
    {
        return sprintf('%02d-%03d', rand(10,99), rand(100, 999));
    }
}
