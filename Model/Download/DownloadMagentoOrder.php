<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Model\Download;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Websolute\TransporterEntity\Api\Data\EntityInterface;
use Websolute\TransporterEntity\Model\EntityModelFactory;
use Websolute\TransporterEntity\Model\EntityRepository;
use Websolute\TransporterMagentoOrder\Api\GetOrderItemCollectionInterface;

class DownloadMagentoOrder
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityModelFactory
     */
    private $entityModelFactory;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var GetOrderItemCollectionInterface
     */
    private $getOrderItemCollection;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderResourceInterface
     */
    private $orderResource;

    /**
     * @param SerializerInterface $serializer
     * @param EntityModelFactory $entityModelFactory
     * @param EntityRepository $entityRepository
     * @param GetOrderItemCollectionInterface $getOrderItemCollection
     * @param OrderFactory $orderFactory
     * @param OrderResourceInterface $orderResource
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityModelFactory $entityModelFactory,
        EntityRepository $entityRepository,
        GetOrderItemCollectionInterface $getOrderItemCollection,
        OrderFactory $orderFactory,
        OrderResourceInterface $orderResource
    ) {
        $this->serializer = $serializer;
        $this->entityModelFactory = $entityModelFactory;
        $this->entityRepository = $entityRepository;
        $this->getOrderItemCollection = $getOrderItemCollection;
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
    }

    /**
     * @param int $orderId
     * @param int $activityId
     * @param string $downloaderType
     */
    public function execute(int $orderId, int $activityId, string $downloaderType)
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);

        $data = [];

        $this->populateOrderData($data, $order);

        $orderItemCollection = $this->getOrderItemCollection->execute($orderId);

        foreach ($orderItemCollection as $orderItem) {
            $this->populateOrderItemData($data, $orderItem);
        }

        $dataOriginal = $this->serializer->serialize($data);

        /** @var EntityInterface $entity */
        $entity = $this->entityModelFactory->create();
        $entity->setActivityId($activityId);
        $entity->setType($downloaderType);
        $entity->setIdentifier((string)$orderId);
        $entity->setDataOriginal($dataOriginal);

        $this->entityRepository->save($entity);
    }

    /**
     * @param array $data
     * @param Order $order
     */
    protected function populateOrderData(array &$data, Order $order)
    {
        foreach ($order->getData() as $key => $value) {
            $data[$key] = $value;
        }
    }

    /**
     * @param array $data
     * @param Order\Item $orderItem
     */
    protected function populateOrderItemData(array &$data, Order\Item $orderItem)
    {
        if (!array_key_exists('items', $data)) {
            $data['items'] = [];
        }
        $data['items'][] = $orderItem->getData();
    }
}
