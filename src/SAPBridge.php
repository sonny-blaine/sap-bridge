<?php

namespace SonnyBlaine\SAPBridge;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use SonnyBlaine\IntegratorBridge\BridgeInterface;
use SonnyBlaine\IntegratorBridge\RequestInterface;

/**
 * Class SAPBridge
 * @package SonnyBlaine\SAPBridge
 */
class SAPBridge implements BridgeInterface
{
    const URI_ENVIAR_CLIENTE = 'BP';

    /**
     * Client to integrate
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $auth;

    /**
     * SAPBridge constructor.
     * @param string $baseUri
     * @param string $user
     * @param string $password
     */
    public function __construct(string $baseUri, string $user, string $password)
    {
        $this->client = new GuzzleClient(['base_uri' => $baseUri]);
        $this->auth = [$user, $password];
    }

    /**
     * Integrates a requisition
     * @param RequestInterface $request
     * @return void
     * @throws \Exception
     */
    public function integrate(RequestInterface $request): void
    {
        switch ($request->getMethodIdentifier()) {
            case 'EnviarCliente':
                $response = $this->post(self::URI_ENVIAR_CLIENTE, $this->getClienteData($request->getData()));
                $this->checkResponse($response);
                break;

            default:
                throw new \Exception("Error: Method undefined.");
        }
    }

    /**
     * @param $uri
     * @param $data
     * @return ResponseInterface
     */
    private function post($uri, $data)
    {
        return $this->client->post($uri, [
            'json' => $data,
            'auth' => $this->auth,
        ]);
    }

    /**
     * @param ResponseInterface $response
     * @throws \Exception
     */
    private function checkResponse(ResponseInterface $response)
    {
        $responseData = json_decode($response->getBody(), true);

        if ('sucesso' === $responseData['dados']['status']) {
            return;
        }

        $failures = '';

        $detalhes = $responseData['dados']['detalhes'];

        if (false === array_key_exists('0', $detalhes)) {
            $detalhes = [$detalhes];
        }

        foreach ($detalhes as $detalhe) {
            if ('S' === $detalhe['tipo']) {
                continue;
            }

            $failures .= $detalhe['mensagem'] . ';' . PHP_EOL;
        }

        if (false === empty($failures)) {
            throw new \Exception(utf8_decode($failures));
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