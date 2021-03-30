<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Model;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Websolute\TransporterMagentoOrder\Api\GetOrderItemCollectionInterface;

class GetOrderItemCollection implements GetOrderItemCollectionInterface
{
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
    public function execute(int $orderId): AbstractCollection
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->addFieldToSelect('*');

        return $collection;
    }
}
