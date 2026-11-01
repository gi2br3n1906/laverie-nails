<?php

declare(strict_types=1);

return [
    'title' => 'Shipping Policy',
    'eyebrow' => 'CUSTOMER CARE',
    'updated' => 'Last updated: 1 November 2026',
    'intro' => 'At Laverie, we want to make sure your favorite nails arrive safely at your doorstep. Please take a moment to read our shipping policy before placing your order.',
    'sections' => [
        [
            'icon' => '🚚',
            'heading' => 'SHIPPING COVERAGE',
            'paragraphs' => [
                'Laverie ships orders throughout Indonesia, including Java and outside Java. All deliveries are handled by third-party courier services.',
            ],
        ],
        [
            'icon' => '⏳',
            'heading' => 'SHIPPING METHOD & DELIVERY TIME',
            'paragraphs' => [
                'Laverie partners with JNE and J&T to deliver your orders. The estimated production and shipping time is 7–15 days. Delivery times may vary depending on your order type, location, courier service, and other circumstances.',
                'Please note that this is an estimated timeframe and may vary depending on order volume and delivery conditions.',
            ],
        ],
        [
            'icon' => '💰',
            'heading' => 'SHIPPING FEES',
            'paragraphs' => [
                'Shipping fees are currently based on two delivery zones:',
                'These rates are temporary estimates and may change depending on shipping costs and courier rates. Any updated rates will be reflected on the website.',
                'The shipping fee will be automatically added to your order total at checkout based on your selected delivery address.',
            ],
            'items' => [
                ['label' => 'Java', 'text' => ':java'],
                ['label' => 'Outside Java', 'text' => ':outside'],
            ],
            'items_at' => 1,
        ],
        [
            'icon' => '🛍️',
            'heading' => 'ORDER PROCESSING & SHIPMENT',
            'paragraphs' => [
                'Orders will be processed after payment has been successfully confirmed. For custom or made-to-order products, production will begin after the order details have been confirmed. Once your order is ready, it will be packed and handed over to our courier partner for delivery.',
            ],
        ],
        [
            'icon' => '🧾',
            'heading' => 'ORDER STATUS & PACKAGE TRACKING',
            'paragraphs' => [
                'You can monitor your order progress directly through the Laverie website. The order status will be updated through the following stages:',
                'Please note that Laverie’s website only provides order status updates, not real-time package location tracking.',
                'To track your package location and delivery progress, you can visit the official JNE or J&T website using the tracking number (receipt number) provided by our team.',
            ],
            'items' => [
                ['label' => 'Pending', 'text' => 'Your order has been received and is waiting for payment confirmation.'],
                ['label' => 'Processing', 'text' => 'Your order is being prepared or produced.'],
                ['label' => 'Shipped', 'text' => 'Your package has been handed over to the courier.'],
                ['label' => 'Completed', 'text' => 'Your order has been marked as completed.'],
            ],
            'items_at' => 1,
        ],
        [
            'icon' => '📦',
            'heading' => 'SHIPPING DELAYS',
            'paragraphs' => [
                'While we do our best to ensure timely delivery, delays may occur due to circumstances beyond our control, such as weather conditions, high shipping volumes, public holidays, or courier operational issues.',
                'As deliveries are handled by third-party courier services, Laverie cannot guarantee exact delivery dates. However, our team will do our best to assist you if any shipping issues arise.',
            ],
        ],
        [
            'icon' => '💌',
            'heading' => 'REFUND & PRODUCT ISSUES',
            'paragraphs' => [
                'Please note that Laverie currently does not offer refunds for confirmed orders. However, if you experience any issues related to the product you receive, such as incorrect items or product defects, please contact our admin team via WhatsApp. We will be happy to assist you and review the issue further.',
            ],
        ],
        [
            'icon' => '📍',
            'heading' => 'INCORRECT ADDRESS & DELIVERY ISSUES',
            'paragraphs' => [
                'Please double-check your shipping details before completing your order. Laverie is not responsible for delivery issues caused by incorrect or incomplete addresses provided by customers. If your package has not arrived within the estimated delivery time or you experience any shipping issues, please contact our team via WhatsApp for assistance.',
            ],
        ],
    ],
];
