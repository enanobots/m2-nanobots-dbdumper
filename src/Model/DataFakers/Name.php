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

class Name implements DataFakerInterface
{
    /** @var Firstname  */
    private Firstname $firstname;

    /** @var Lastname  */
    protected Lastname $lastname;

    /**
     * @param Firstname $firstname
     * @param Lastname $lastname
     */
    public function __construct(
        Firstname $firstname,
        Lastname $lastname,
    ) {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    /**
     * @param int $entityId
     * @param null|string $value
     * @return string
     */
    public function decorateData(int $entityId, ?string $value = null): string
    {
        return sprintf(
            "%s %s",
            $this->firstname->decorateData($entityId),
            $this->lastname->decorateData($entityId)
        );
    }
}
