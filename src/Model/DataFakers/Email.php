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

use Magento\Framework\Filter\TranslitUrl;
use Nanobots\DbDumper\Export\DataFakerInterface;

class Email implements DataFakerInterface
{
    /** @var Firstname  */
    private Firstname $firstname;

    /** @var Lastname  */
    protected Lastname $lastname;

    /** @var string|null  */
    protected ?string $emailSchema;

    /** @var string|null  */
    protected ?string $emailDomain;

    /** @var TranslitUrl  */
    protected TranslitUrl $translitUrl;

    /**
     * @param \Magento\Framework\Filter\TranslitUrl $translitUrl
     * @param \Nanobots\DbDumper\Model\DataFakers\Firstname $firstname
     * @param \Nanobots\DbDumper\Model\DataFakers\Lastname $lastname
     * @param string|null $emailSchema
     * @param string|null $emailDomain
     */
    public function __construct(
        TranslitUrl $translitUrl,
        Firstname $firstname,
        Lastname $lastname,
        ?string $emailSchema = null,
        ?string $emailDomain = null
    ) {
        $this->translitUrl = $translitUrl;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->emailSchema = $emailSchema;
        $this->emailDomain = $emailDomain;
    }

    /**
     * @param int $entityId
     * @param null|string $value
     * @return string
     */
    public function decorateData(int $entityId, ?string $value = null): string
    {
        return sprintf("%s@%s", $this->_getEmailFromSchema($entityId), $this->emailDomain);
    }

    /**
     * Prepare Fake Email Address from Customer data
     *
     * @param int $entityId
     * @return string
     */
    private function _getEmailFromSchema(int $entityId): string
    {
        return str_replace(
            [
                '{{firstname}}',
                '{{lastname}}'
            ],
            [
                $this->translitUrl->filter($this->firstname->decorateData($entityId)),
                $this->translitUrl->filter($this->lastname->decorateData($entityId))
            ],
            $this->emailSchema
        );
    }
}
