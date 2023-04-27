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

class City implements DataFakerInterface
{
    /** @var string[]  */
    protected array $cityPrefix = ['North', 'East', 'West', 'South', 'New', 'Lake', 'Port'];

    /** @var array|string[]  */
    protected array $citySuffix = ['town', 'ton', 'land', 'ville', 'berg', 'burgh', 'borough', 'bury', 'view', 'port', 'mouth', 'stad', 'furt', 'chester', 'mouth', 'fort', 'haven', 'side', 'shire'];

    /** @var \Nanobots\DbDumper\Model\DataFakers\Firstname */
    protected Firstname $firstName;

    /**
     * @param \Nanobots\DbDumper\Model\DataFakers\Firstname $firstname
     */
    public function __construct(
        Firstname $firstname
    ) {
        $this->firstName = $firstname;
    }

    /**
     * @param int $entityId
     * @param null|string $value
     * @return string
     */
    public function decorateData(int $entityId, ?string $value = null): string
    {
        return sprintf(
            '%s %s%s',
            $this->firstName->decorateData($entityId),
            $this->cityPrefix[$entityId % (count($this->cityPrefix) - 1)],
            $this->citySuffix[$entityId % (count($this->citySuffix) - 1)],
        );
    }
}
