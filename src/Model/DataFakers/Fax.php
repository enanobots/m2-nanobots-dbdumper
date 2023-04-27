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

class Fax implements DataFakerInterface
{
    /**
     * @param int $entityId
     * @param null|string $value
     * @return string
     */
    public function decorateData(int $entityId, ?string $value = null): string
    {
        return sprintf(
            '%03d-%03d-%04d',
            777 % $entityId,
            777 % $entityId,
            777 % $entityId
        );
    }
}
