<?php

use Tamara\Wp\Plugin\Services\TamaraNotificationService;
use Tamara\Wp\Plugin\Services\ViewService;
use Tamara\Wp\Plugin\Services\WCTamaraGateway;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayNextMonth;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayNow;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayCheckout;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayByInstalments;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn10;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn11;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn12;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn2;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn3;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn4;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn5;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn6;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn7;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn8;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn9;

$textDomain = 'tamara';

$config = [
    'version' => TAMARA_CHECKOUT_VERSION,
    'basePath' => __DIR__,
    'baseUrl' => plugins_url(null, __FILE__),
    'textDomain' => $textDomain,
    'services' => [
        ViewService::class => [
        ],
        WCTamaraGateway::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayByInstalments::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn2::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn3::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn4::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn5::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn6::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn7::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn8::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn9::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn10::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn11::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayIn12::class => [
            'textDomain' => $textDomain,
        ],
        TamaraNotificationService::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayCheckout::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayNow::class => [
            'textDomain' => $textDomain,
        ],
        WCTamaraGatewayPayNextMonth::class => [
            'textDomain' => $textDomain,
        ]
    ],
];

return $config;
