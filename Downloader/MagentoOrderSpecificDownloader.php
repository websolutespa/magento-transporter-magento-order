<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Downloader;

use Magento\Framework\Exception\NoSuchEntityException;
use Monolog\Logger;
use Websolute\TransporterActivity\Api\ActivityRepositoryInterface;
use Websolute\TransporterBase\Api\DownloaderInterface;
use Websolute\TransporterBase\Api\TransporterConfigInterface;
use Websolute\TransporterBase\Exception\TransporterException;
use Websolute\TransporterMagentoOrder\Api\GetOrderToSendCollectionInterface;
use Websolute\TransporterMagentoOrder\Model\Download\DownloadMagentoOrder;

class MagentoOrderSpecificDownloader implements DownloaderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TransporterConfigInterface
     */
    private $config;

    /**
     * @var GetOrderToSendCollectionInterface
     */
    private $getOrderToSendCollection;

    /**
     * @var DownloadMagentoOrder
     */
    private $downloadMagentoOrder;

    /**
     * @var ActivityRepositoryInterface
     */
    private $activityRepository;

    /**
     * @param Logger $logger
     * @param TransporterConfigInterface $config
     * @param ActivityRepositoryInterface $activityRepository
     * @param GetOrderToSendCollectionInterface $getOrderToSendCollection
     * @param DownloadMagentoOrder $downloadMagentoOrder
     */
    public function __construct(
        Logger $logger,
        TransporterConfigInterface $config,
        ActivityRepositoryInterface $activityRepository,
        GetOrderToSendCollectionInterface $getOrderToSendCollection,
        DownloadMagentoOrder $downloadMagentoOrder
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->getOrderToSendCollection = $getOrderToSendCollection;
        $this->downloadMagentoOrder = $downloadMagentoOrder;
        $this->activityRepository = $activityRepository;
    }

    /**
     * @param int $activityId
     * @param string $downloaderType
     * @throws NoSuchEntityException
     * @throws TransporterException
     */
    public function execute(int $activityId, string $downloaderType): void
    {
        $identifier = $this->getIdentifier($activityId);
        try {
            $this->downloadMagentoOrder->execute($identifier, $activityId, $downloaderType);
        } catch (\Exception $e) {
            if ($this->config->continueInCaseOfErrors()) {
                $this->logger->error(__(
                    'activityId:%1 ~ Downloader ~ downloaderType:%2 ~ KO ~ error:%3',
                    $activityId,
                    $downloaderType,
                    $e->getMessage()
                ));
            } else {
                throw new TransporterException(__(
                    'activityId:%1 ~ Downloader ~ downloaderType:%2 ~ KO ~ error:%3',
                    $activityId,
                    $downloaderType,
                    $e->getMessage()
                ));
            }
        }

        $this->logger->info(__(
            'activityId:%1 ~ Downloader ~ downloaderType:%2 ~ order id %3 sent to gamma',
            $activityId,
            $downloaderType,
            $identifier
        ));
    }

    /**
     * @param int $activityId
     * @return int
     * @throws NoSuchEntityException
     */
    private function getIdentifier(int $activityId): int
    {
        $activity = $this->activityRepository->getById($activityId);
        return (int)$activity->getExtra()->getData('data');
    }
}
