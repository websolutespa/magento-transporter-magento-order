<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Api;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;

interface GetOrderToSendCollectionInterface
{
    /**
     * @param int $activityId
     * @return AbstractCollection
     */
    public function execute(int $activityId): AbstractCollection;
}
