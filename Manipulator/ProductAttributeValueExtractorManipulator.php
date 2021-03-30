<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Manipulator;

use Magento\Framework\Serialize\Serializer\Json;
use Monolog\Logger;
use Websolute\TransporterImporter\Model\DotConvention;
use Websolute\TransporterBase\Api\ManipulatorInterface;
use Websolute\TransporterBase\Exception\TransporterException;
use Websolute\TransporterEntity\Api\Data\EntityInterface;
use Websolute\TransporterMagentoProduct\Model\Attribute\GetProductAttributeValueBySku;

class ProductAttributeValueExtractorManipulator implements ManipulatorInterface
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
     * @var GetProductAttributeValueBySku
     */
    private $getProductAttributeValueBySku;

    /**
     * @param Logger $logger
     * @param Json $serializer
     * @param DotConvention $dotConvention
     * @param GetProductAttributeValueBySku $getProductAttributeValueBySku
     * @param string $source
     * @param string $destination
     * @param string $attributeCode
     */
    public function __construct(
        Logger $logger,
        Json $serializer,
        DotConvention $dotConvention,
        GetProductAttributeValueBySku $getProductAttributeValueBySku,
        string $source,
        string $destination,
        string $attributeCode
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->dotConvention = $dotConvention;
        $this->getProductAttributeValueBySku = $getProductAttributeValueBySku;
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
        $entity = $entities[$this->source];
        $data = $entity->getDataManipulated();
        $data = $this->serializer->unserialize($data);

        if (!array_key_exists('items', $data)) {
            throw new TransporterException(__('The %1 expect an "items" property exists', self::class));
        }

        $items = &$data['items'];

        foreach ($items as $key => &$item) {
            if (!array_key_exists('sku', $item)) {
                throw new TransporterException(__('The %1 expect a "sku" property exists inside "items"', self::class));
            }
            $sku = $item['sku'];

            $value = $this->getProductAttributeValueBySku->execute($sku, $this->attributeCode);

            if (!is_null($value)) {
                $this->dotConvention->setValue($item, $this->destination, $value);
            }
        }

        $data = $this->serializer->serialize($data);
        $entity->setDataManipulated($data);
    }
}
