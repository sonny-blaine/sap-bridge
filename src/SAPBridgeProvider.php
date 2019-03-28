<?php
namespace SonnyBlaine\SAPBridge;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class SAPBridgeProvider
 * @package SonnyBlaine\SAPBridge
 */
class SAPBridgeProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['sap.bridge'] = function ($app) {
            return new SAPBridge(
                getenv('SAP_BASE_URI'),
                getenv('SAP_AUTH_USER'),
                getenv('SAP_AUTH_PASSWORD')
            );
        };
    }
}