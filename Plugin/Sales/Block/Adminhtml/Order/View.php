<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

namespace Websolute\TransporterMagentoOrder\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    public function beforeSetLayout(OrderView $subject)
    {
        $confirmationMessage = __('Are you sure you want to send this order to the webservice?');

        $subject->addButton(
            'transporter_send_order',
            [
                'label' => __('Send Order'),
                'class' => __(''),
                'id' => 'transporter-send-order',
                'onclick' => 'confirmSetLocation(\''.$confirmationMessage.'\',\'' . $subject->getUrl('transporter_magentoorder/order/send') . $subject->getOrderId() . '\')'
            ]
        );
    }
}
