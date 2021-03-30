<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Downloader;

use Magento\Framework\Exception\AlreadyExistsException;
use Monolog\Logger;
use Websolute\TransporterBase\Api\DownloaderInterface;
use Websolute\TransporterBase\Api\TransporterConfigInterface;
use Websolute\TransporterBase\Exception\TransporterException;
use Websolute\TransporterMagentoOrder\Api\GetOrderToSendCollectionInterface;
use Websolute\TransporterMagentoOrder\Model\Download\DownloadMagentoOrder;

class MagentoOrderDownloader implements DownloaderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TransporterConfigInterface
     */
    protected $config;

    /**
     * @var GetOrderToSendCollectionInterface
     */
    private $getOrderToSendCollection;

    /**
     * @var DownloadMagentoOrder
     */
    private $downloadMagentoOrder;

    /**
     * @param Logger $logger
     * @param TransporterConfigInterface $config
     * @param GetOrderToSendCollectionInterface $getOrderToSendCollection
     * @param DownloadMagentoOrder $downloadMagentoOrder
     */
    public function __construct(
        Logger $logger,
        TransporterConfigInterface $config,
        GetOrderToSendCollectionInterface $getOrderToSendCollection,
        DownloadMagentoOrder $downloadMagentoOrder
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->getOrderToSendCollection = $getOrderToSendCollection;
        $this->downloadMagentoOrder = $downloadMagentoOrder;
    }

    /**
     * @param int $activityId
     * @param string $downloaderType
     * @throws TransporterException
     */
    public function execute(int $activityId, string $downloaderType): void
    {
        $ordersToSend = $this->getOrderToSendCollection->execute($activityId);

        $ok = 0;
        $ko = 0;

        foreach ($ordersToSend as $order) {
            try {
                $orderId = (int)$order->getId();
                $this->downloadMagentoOrder->execute($orderId, $activityId, $downloaderType);
                $ok++;
            } catch (AlreadyExistsException $e) {
                if ($this->config->continueInCaseOfErrors()) {
                    $this->logger->error(__(
                        'activityId:%1 ~ Downloader ~ downloaderType:%2 ~ KO ~ error:%3',
                        $activityId,
                        $downloaderType,
                        $e->getMessage()
                    ));
                    $ko++;
                } else {
                    throw new TransporterException(__(
                        'activityId:%1 ~ Downloader ~ downloaderType:%2 ~ KO ~ error:%3',
                        $activityId,
                        $downloaderType,
                        $e->getMessage()
                    ));
                }
            }
        }

        $this->logger->info(__(
            'activityId:%1 ~ Downloader ~ downloaderType:%2 ~ okCount:%3 koCount:%4',
            $activityId,
            $downloaderType,
            $ok,
            $ko
        ));
    }
}
