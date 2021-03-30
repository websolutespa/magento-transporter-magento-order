<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Model;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Websolute\TransporterMagentoOrder\Api\GetOrderToSendCollectionInterface;

class GetOrderToSendCollection implements GetOrderToSendCollectionInterface
{
    /** @var string */
    const TRANSPORTER_EXPORTED = 'transporter_exported';

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $activityId): AbstractCollection
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToSelect([self::TRANSPORTER_EXPORTED]);
        $collection->addFieldToFilter(self::TRANSPORTER_EXPORTED, ['null' => true]);

        return $collection;
    }
}
