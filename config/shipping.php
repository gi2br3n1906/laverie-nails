<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Flat Shipping Rate
    |--------------------------------------------------------------------------
    |
    | Shipping is intentionally manual: no courier API is called at checkout.
    | Every order is charged a single flat rate in Rupiah, paid by the buyer.
    | The admin fills in the courier and tracking number after dispatch.
    |
    */

    'flat_rate' => max(0, (int) env('SHIPPING_FLAT_RATE', 20000)),

];
