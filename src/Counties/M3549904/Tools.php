<?php

namespace NFePHP\NFSe\Counties\M3549904;

/**
 * Classe para a comunicacao com os webservices da
 * Sao Jose dos Campos - SP
 * conforme o modelo Abrasf Simpliss 1.00
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Counties\M3549904\Tools
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Maykon da S. de Siqueira <maykon at multilig dot com dot br>
 * @link      http://github.com/nfephp-org/sped-nfse for the canonical source repository
 */

use NFePHP\NFSe\Models\Abrasf\Tools as ToolsAbrasf;
use NFePHP\NFSe\Models\Abrasf\Factories;

class Tools extends ToolsAbrasf
{
    /**
     * Webservices URL
     * @var array
     */
    protected $url = [
        1 => 'https://notajoseense.sjc.sp.gov.br/notafiscal-abrasfv203-ws/NotaFiscalSoap?wsdl',
        2 => 'https://homol-notajoseense.sjc.sp.gov.br/notafiscal-abrasfv203-ws/NotaFiscalSoap?wsdl'
    ];
    /**
     * County Namespace
     * @var string
     */
    /*nathalia */
    protected $xmlns = 'http://nfse.abrasf.org.br';
    /**
     * Soap Version
     * @var int
     */
    protected $soapversion = SOAP_1_1;
    /**
     * SIAFI County Cod
     * @var int
     */
    protected $codcidade = 3549904;
    /**
     * Indicates when use CDATA string on message
     * @var boolean
     */
    protected $withcdata = false;
    /**
     * Encription signature algorithm
     * @var string
     */
    protected $algorithm = OPENSSL_ALGO_SHA1;
    /**
     * Version of schemas
     * @var int
     */
    protected $versao = 203;
    
    /**
     * Namespaces for soap envelope
     * @var array
     */
    protected $namespaces = ['xmlns:soapenv'=>"http://schemas.xmlsoap.org/soap/envelope/",  'xmlns:nfse'=>"http://nfse.abrasf.org.br"];
    
    protected $params = [];
    
    private $soapAction = 'http://tempuri.org/INFSEGeracao/';
    
    /**
     * Os metodos que realizar operacoes no webservice precisam ser sobrescritos (Override)
     * somente para setar o soapAction espefico de cada operacao (INFSEGeracao, INFSEConsultas, etc.)
     * @param $lote
     * @param $rpss
     * @return string
     */
    public function recepcionarLoteRps($lote, $rpss) {

        $class = "NFePHP\\NFSe\\Counties\\M3549904\\v{$this->versao}\\RecepcionarLoteRps";

        $fact = new $class($this->certificate);
        return $this->recepcionarLoteRpsCommon($fact, $rps);

    }
    
    /**
     * Os metodos que realizar operacoes no webservice precisam ser sobrescritos (Override)
     * somente para setar o soapAction espefico de cada operacao (INFSEGeracao, INFSEConsultas, etc.)
     * @param $protocolo
     * @return string
     */
    public function consultarLoteRps($protocolo) {
        
        $this->soapAction = 'http://tempuri.org/INFSEConsultas/';
        
        return parent::consultarLoteRps($protocolo);
    }

    /**
     * @param $rps
     * @return string
     */
    public function gerarNfse($rps)
    {
        $class = "NFePHP\\NFSe\\Counties\\M3549904\\v{$this->versao}\\GerarNfse";

        $fact = new $class($this->certificate);
        return $this->gerarNfseCommon($fact, $rps);
    }

    /**
     * @param Factories\GerarNfse $fact
     * @param $rps
     * @param string $url
     * @return string
     */
    protected function gerarNfseCommon($fact, $rps, $url = '')
    {
        $this->method = 'GerarNfse';
        $fact->setXmlns($this->xmlns);
        $fact->setSchemeFolder($this->schemeFolder);
        $fact->setCodMun($this->config->cmun);
        $fact->setSignAlgorithm($this->algorithm);
        $fact->setTimezone($this->timezone);

        $message = $fact->render(
            $this->versao,
            $rps
        );

        return $this->sendRequest($url, $message);
    }

    /**
     * Monta o request da mensagem SOAP
     * @param string $url
     * @param string $message
     * @return string
     */
    protected function sendRequest($url, $message)
    {

        $this->xmlRequest = $message;
        
        if (!$url) {
            $url = $this->url[$this->config->tpAmb];
        }
        if (!is_object($this->soap)) {
            $this->soap = new SoapCurl($this->certificate);
        }

        // die($url);
        
        //formata o xml da mensagem para o padrao esperado pelo webservice
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($message);
        $message = str_replace(['<?xml version="1.0" encoding="utf-8"?>', '<?xml version="1.0"?>'], '', $dom->saveXML());

        // $message = str_replace(' xmlns="http://nfse.abrasf.org.br"', '', $message);
        
        if ($this->withcdata) {
            $message = $this->stringTransform($message);
        }

        // header('Content-type: text/xml');
        // die($message);

        $request = $this->makeRequest($message);

        if (!count($this->params)) {
            $this->params = [
                "Content-Type: text/xml;charset=utf-8;",
                // "SOAPAction: {$this->soapAction}{$this->method}"
                // "SOAPAction: {$this->method}"
                // "SOAPAction: "
            ];
        }

        $action = '';
        
        $header = '<soapEnv:Header>' .
                  '</soapEnv:Header>';
                  
                   /* var_dump([$url,
                  $this->method,
                  $action,
                  $this->soapversion == SOAP_1_1 ? 'TRUE' : 'FALSE',
                  $this->params,
                  $this->namespaces,
                  $request,
                  $header]);exit;  */
                  
        $ret = $this->soap->send(
            $url,
            $this->method,
            $action,
            $this->soapversion,
            $this->params,
            $this->namespaces,
            $request,
            $header
        );
        
        //Realiza o request SOAP
        return $ret;
    }
    
    /**
     * Metodo que converto o objeto RPS em XML;
     * @param \NFePHP\NFSe\Models\Abrasf\Rps $rps
     * @return string Retorna o xml serializado
     */
    public function makeXml($rps)
    {

        $class = "NFePHP\\NFSe\\Counties\\M3549904\\v{$this->versao}\\GerarNfse";
        $fact = new $class($this->certificate);
        
        $fact->setXmlns($this->xmlns);
        $fact->setSchemeFolder($this->schemeFolder);
        $fact->setCodMun($this->config->cmun);
        $fact->setSignAlgorithm($this->algorithm);
        $fact->setTimezone($this->timezone);
        $message = $fact->render(
            $this->versao,
            $this->remetenteTipoDoc,
            $this->remetenteCNPJCPF,
            $this->remetenteIM,
            1,
            [$rps]
        );
        
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($message);

        // header('Content-type: text/xml');die($dom->saveXML());
        
        $message = str_replace('<?xml version="1.0"?>', '', $dom->saveXML());
        
        //O atributo xmlns precisa ser removido da tag <EnviarLoteRpsEnvio> pois
        //o web service de Itabira nao o reconhece
        // $messageText = str_replace('<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">', '<EnviarLoteRpsEnvio>', $message);
        
        if ($this->withcdata) {
            $messageText = $this->stringTransform($message);
        }
        
        $request = $this->makeRequest($messageText);
        
        return $request;
    }  
    
    /**
     * @param $message
     * @return string
     */
    protected function makeRequest($message)
    {
        switch ($this->versao) {
            case 100:
                $request = "<{$this->method} xmlns=\"http://www.e-governeapps2.com.br/\">"
                    . $message
                    . "</{$this->method}>";
                break;
            case 201:
                $versao = '2.01';
            case 202:
                $request =
                    "<tem:{$this->method}>"
                    . "<tem:xmlEnvio>"
                    . "<![CDATA["
                    . $message
                    . "]]>"
                    . "</tem:xmlEnvio>"
                    . "</tem:{$this->method}>";        
                break;
            case 203:
                    $versao = '2.03';
                    $request = ""
                        . "<nfse:{$this->method}>"
                        . $message
                        . "</nfse:{$this->method}>";
            break;
            default:
                throw new \LogicException('Versao nao suportada');
        }

        return $request;
    }  
    
    /**
     * Retorna o nome da versao do Layout formatado
     * @return string
     */
    private function getVersionString()
    {
        $return='';
        
        switch ($this->versao) {
            case 100:
                $return = '1.00';
                break;
            case 201:
                $return = '2.01';
                break;
            case 202:
                $return = '2.02';
            case 203:
            default:
                $return = '2.03';
                break;
        }
        
        return $return;
    }
    
    public function cancelarNfse($nfseNumero) {
        
        $this->soapAction = 'http://tempuri.org/INFSEGeracao/';
        
        $class = "NFePHP\\NFSe\\Counties\\M3549904\\v{$this->versao}\\CancelarNfse";
        $fact = new $class($this->certificate);
        
        $this->method = 'cancelarNfse';
        $fact->xmlns = $this->xmlns;
        $fact->schemeFolder = $this->schemeFolder;
        $fact->codMun = $this->config->cmun;
        $fact->algorithm = $this->algorithm;
        //$fact->setTimezone($this->timezone);
        $message = $fact->render(
            $this->versao,
            $this->remetenteTipoDoc,
            $this->remetenteCNPJCPF,
            $this->remetenteIM,
            $nfseNumero
        );

        // @header ("Content-Disposition: attachment; filename=\"NFSe_Lote.xml\"" );
        // echo $message;
        // exit;
        return $this->sendRequest('', $message);
    }
}
