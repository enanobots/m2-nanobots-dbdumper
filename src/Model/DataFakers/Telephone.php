<?php

declare(strict_types=1);

namespace Nanobots\DbDumper\Model\DataFakers;

use Nanobots\DbDumper\Export\DataFakerInterface;

class Telephone implements DataFakerInterface
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
            999 % $entityId,
            999 % $entityId,
            9999 % $entityId
        );
    }
}
