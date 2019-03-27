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
     */
    public function __construct(string $baseUri)
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
                $this->client->post(
                    self::URI_ENVIAR_CLIENTE,
                    [
                        'json' => $this->getClienteData($request->getData())
                    ]
                );
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

    /**
     * @param \stdClass $cliente
     * @return array
     */
    private function getClienteData(\stdClass $cliente)
    {
        $dados = [
            'altkn' => $cliente->codigo,
            'dados_gerais' => [
                'bpext' => $cliente->codigo,
                'kunnr' => '',
                'name_org' => $cliente->nome,
                'sort1' => $cliente->documentoNumero,
                'sort2' => $cliente->nomeTratamento,
                'street' => $cliente->enderecoLogradouro,
                'house_num1' => $cliente->enderecoNumero,
                'post_code1' => $cliente->enderecoCep,
                'cod_municipio' => $cliente->enderecoCodigoMunicipio,
                'city2' => $cliente->enderecoBairro,
                'city1' => $cliente->enderecoCidade,
                'country' => $cliente->pais,
                'region' => $cliente->enderecoUf,
                'telf1' => $cliente->telefone,
                'telf2' => $cliente->telefone2,
                'telfx' => '',
                'smtp_addr' => $cliente->email
            ],
            'dados_identificacao' => [
                [
                    'taxtype' => $cliente->documentoTipo,
                    'taxnum' => $cliente->documentoNumero,
                ]
            ]
        ];

        if (false === empty($cliente->inscricaoEstadual)) {
            $dados['dados_identificacao'][] = [
                'taxtype' => 'IE',
                'taxnum' => $cliente->inscricaoEstadual,
            ];
        }

        return ['dados' => [$dados]];
    }
}