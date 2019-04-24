<?php

namespace SonnyBlaine\SAPBridge;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use SonnyBlaine\IntegratorBridge\BridgeInterface;
use SonnyBlaine\IntegratorBridge\IntegrateRequestInterface;
use SonnyBlaine\IntegratorBridge\SearchRequestInterface;

/**
 * Class SAPBridge
 * @package SonnyBlaine\SAPBridge
 */
class SAPBridge implements BridgeInterface
{
    const URI_SEND_BUSINESS_PARTNER = 'BP001_CadastraBP';

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
     * @param IntegrateRequestInterface $request
     * @return void
     * @throws \Exception
     */
    public function integrate(IntegrateRequestInterface $request): void
    {
        switch ($request->getMethodIdentifier()) {
            case 'EnviarBusinessPartner':
                $response = $this->post(self::URI_SEND_BUSINESS_PARTNER, $this->getBusinessPartnerData($request->getData()));
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
     * @param SearchRequestInterface $request
     * @return null
     */
    public function search(SearchRequestInterface $request)
    {
        return null;
    }

    /**
     * @param \stdClass $businessPartner
     * @return array
     */
    private function getBusinessPartnerData(\stdClass $businessPartner)
    {
        $dados = [
            'altkn' => $businessPartner->codigo,
            'dados_gerais' => [
                'bpext' => $businessPartner->codigo,
                'kunnr' => '',
                'name_org' => $businessPartner->nome,
                'name_org4' => $this->isFornecedor($businessPartner->documentoTipo) ? $businessPartner->nomeTratamento : '',
                'sort1' => $businessPartner->documentoNumero,
                'sort2' => $businessPartner->nomeTratamento,
                'street' => $businessPartner->enderecoLogradouro,
                'house_num1' => $businessPartner->enderecoNumero,
                'post_code1' => $businessPartner->enderecoCep,
                'cod_municipio' => $businessPartner->enderecoCodigoMunicipio,
                'city2' => $businessPartner->enderecoBairro,
                'city1' => $businessPartner->enderecoCidade,
                'country' => $businessPartner->pais,
                'region' => $businessPartner->enderecoUf,
                'telf1' => $businessPartner->telefone,
                'telf2' => $businessPartner->telefone2,
                'telfx' => '',
                'smtp_addr' => $businessPartner->email
            ],
            'dados_identificacao' => [
                [
                    'taxtype' => $businessPartner->documentoTipo,
                    'taxnum' => $businessPartner->documentoNumero,
                ]
            ],
            'dados_cliente' => [],
            'dados_fornecedor' => [],
        ];

        if (false === empty($businessPartner->inscricaoEstadual)) {
            $dados['dados_identificacao'][] = [
                'taxtype' => 'IE',
                'taxnum' => $businessPartner->inscricaoEstadual,
            ];
        }

        if ($this->isFornecedor($businessPartner->documentoTipo)) {
            $dados['dados_fornecedor']['fornecedor'] = 'true';
        } else {
            $dados['dados_cliente']['cliente'] = 'true';
        }

        return [
            'header' => ['id_sistema' => $businessPartner->origem],
            'dados' => [$dados]
        ];
    }

    /**
     * @param string $documentoTipo
     * @return bool
     */
    private function isFornecedor(string $documentoTipo)
    {
        return 'CNPJ' === $documentoTipo;
    }
}