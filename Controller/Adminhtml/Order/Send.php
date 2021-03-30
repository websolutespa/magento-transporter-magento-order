<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoOrder\Controller\Adminhtml\Order;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Controller\Adminhtml\Order;
use Websolute\TransporterMagentoOrder\Model\GetOrderToSendCollection;

class Send extends Order
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Websolute_TransporterMagentoOrder::order_send';

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $order = $this->_initOrder();

        if ($order) {
            try {
                $order->setData(GetOrderToSendCollection::TRANSPORTER_EXPORTED, null);
                $this->orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('This order will be processed by the transporter.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t process the order by the transporter right now.'));
                $this->logger->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getEntityId()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}
