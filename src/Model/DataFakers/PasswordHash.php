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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Math\Random;
use Nanobots\DbDumper\Export\DataFakerInterface;

class PasswordHash implements DataFakerInterface
{
    /** @var \Magento\Customer\Api\AccountManagementInterface  */
    protected AccountManagementInterface $accountManagement;

    /** @var \Magento\Framework\Math\Random  */
    protected Random $random;

    /**
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     */
    public function __construct(
        Random $random,
        AccountManagementInterface $accountManagement
    ) {
        $this->random = $random;
        $this->accountManagement = $accountManagement;
    }

    /**
     * @param int $entityId
     * @param null|string $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function decorateData(int $entityId, ?string $value = null): string
    {
        return $this->accountManagement->getPasswordHash(
            $this->random->getRandomString(24)
        );
    }
}
