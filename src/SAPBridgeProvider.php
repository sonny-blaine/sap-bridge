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
                getenv('ROVERETI_BASE_URI'),
                getenv('ROVERETI_USER'),
                getenv('ROVERETI_KEY')
            );
        };
    }
}