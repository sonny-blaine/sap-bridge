<?php

namespace SonnyBlaine\SAPBridge;

use GuzzleHttp\Client as GuzzleClient;
use SonnyBlaine\IntegratorBridge\BridgeInterface;
use SonnyBlaine\IntegratorBridge\RequestInterface;

/**
 * Class SAPBridge
 * @package SonnyBlaine\SAPBridge
 */
class SAPBridge implements BridgeInterface
{
    const URI_ENVIAR_CLIENTE = '';

    /**
     * Client to integrate
     * @var GuzzleClient
     */
    protected $client;

    /**
     * SAPBridge constructor.
     * @param string $baseUri
     * @param string $user
     * @param int $key
     */
    public function __construct(string $baseUri/*, string $user, int $key*/)
    {
        $this->client = new GuzzleClient(['base_uri' => $baseUri]);
    }

    /**
     * Integrates a requisition
     * @param RequestInterface $request
     * @throws \Exception
     * @return void
     */
    public function integrate(RequestInterface $request): void
    {
        switch ($request->getMethodIdentifier()) {
            case 'EnviarCliente':
                //$this->client->post(self::URI_ENVIAR_CLIENTE, $request->getData());
                break;

            default:
                throw new \Exception("Error: Method undefined.");
        }
    }

    /**
     * @param RequestInterface $request
     * @return null
     */
    public function search(RequestInterface $request)
    {
        return null;
    }
}