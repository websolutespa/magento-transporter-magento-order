<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Manipulator;

use Magento\Framework\Serialize\Serializer\Json;
use Monolog\Logger;
use Websolute\TransporterBase\Api\ManipulatorInterface;
use Websolute\TransporterBase\Exception\TransporterException;
use Websolute\TransporterEntity\Api\Data\EntityInterface;
use Websolute\TransporterImporter\Model\DotConvention;
use Websolute\TransporterMagentoCustomer\Model\Attribute\GetCustomerAddressAttributeValue;

class CustomerAddressAttributeValueExtractorManipulator implements ManipulatorInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var DotConvention
     */
    private $dotConvention;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var GetCustomerAddressAttributeValue
     */
    private $getCustomerAddressAttributeValue;

    /**
     * @param Logger $logger
     * @param Json $serializer
     * @param DotConvention $dotConvention
     * @param GetCustomerAddressAttributeValue $getCustomerAddressAttributeValue
     * @param string $source
     * @param string $destination
     * @param string $attributeCode
     */
    public function __construct(
        Logger $logger,
        Json $serializer,
        DotConvention $dotConvention,
        GetCustomerAddressAttributeValue $getCustomerAddressAttributeValue,
        string $source,
        string $destination,
        string $attributeCode
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->dotConvention = $dotConvention;
        $this->getCustomerAddressAttributeValue = $getCustomerAddressAttributeValue;
        $this->source = $source;
        $this->destination = $destination;
        $this->attributeCode = $attributeCode;
    }

    /**
     * @param int $activityId
     * @param string $manipulatorType
     * @param string $entityIdentifier
     * @param EntityInterface[] $entities
     * @throws TransporterException
     */
    public function execute(int $activityId, string $manipulatorType, string $entityIdentifier, array $entities): void
    {
        $identifier = $this->dotConvention->getFirst($this->source);
        $entity = $entities[$identifier];
        $data = $entity->getDataManipulated();
        $data = $this->serializer->unserialize($data);

        $source = $this->dotConvention->getFromSecondInDotConvention($this->source);

        $addressId = $this->dotConvention->getValue($data, $source);

        $value = $this->getCustomerAddressAttributeValue->execute($addressId, $this->attributeCode);

        if (!is_null($value)) {
            $this->dotConvention->setValue($data, $this->destination, $value);
        }

        $data = $this->serializer->serialize($data);
        $entity->setDataManipulated($data);
    }
}
