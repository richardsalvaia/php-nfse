<?php

namespace NFePHP\NFSe\Counties\M3506003;

/**
 * Classe para a comunicação com os webservices da
 * Cidade de Itabira MG
 * conforme o modelo ABRASF
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Counties\M3506003\Tools
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Richard Salvaia <richard at 2rs dot com dot br>
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
        1 => 'http://tbhomologacao.bauru.sp.gov.br/services/Abrasf23?wsdl',
        2 => 'https://tributario.bauru.sp.gov.br/services/Abrasf23?wsdl'
    ];
    /**
     * County Namespace
     * @var string
     */
    protected $xmlns = 'http://www.abrasf.org.br/nfse.xsd';
    /**
     * Soap Version
     * @var int
     */
    protected $soapversion = SOAP_1_1;
    /**
     * SIAFI County Cod
     * @var int
     */
    protected $codcidade = 3506003;
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
     * Os métodos que realizar operações no webservice precisam ser sobrescritos (Override)
     * somente para setar o soapAction espefico de cada operação (INFSEGeracao, INFSEConsultas, etc.)
     * @param $lote
     * @param $rpss
     * @return string
     */
    public function recepcionarLoteRps($lote, $rpss) {
        
        $this->soapAction = 'http://tempuri.org/INFSEGeracao/';
        
        return parent::recepcionarLoteRps($lote, $rpss);
    }
    
    /**
     * Os métodos que realizar operações no webservice precisam ser sobrescritos (Override)
     * somente para setar o soapAction espefico de cada operação (INFSEGeracao, INFSEConsultas, etc.)
     * @param $protocolo
     * @return string
     */
    public function consultarLoteRps($protocolo) {
        
        $this->soapAction = 'http://tempuri.org/INFSEConsultas/';
        
        return parent::consultarLoteRps($protocolo);
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
            $this->soap = new \NFePHP\NFSe\Common\SoapCurl($this->certificate);
        }
        //formata o xml da mensagem para o padão esperado pelo webservice
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($message);

        $message = str_replace('<?xml version="1.0"?>', '', $dom->saveXML());

        //O atributo xmlns precisa ser removido da tag <EnviarLoteRpsEnvio> pois
        //o web service de Itabira não o reconhece
        $messageText = str_replace('xmlns="http://www.abrasf.org.br/nfse.xsd"', '', $message);
        
        if ($this->withcdata) {
            $messageText = $this->stringTransform($message);
        }
        $request = $this->makeRequest($messageText);
        if (!count($this->params)) {
            $this->params = [
                "Content-Type: text/xml;charset=utf-8;",
                "SOAPAction: {$this->soapAction}{$this->method}"
            ];
        }

        $action = '';
        
        $header = '<soapEnv:Header>' .
                  '</soapEnv:Header>';

        //Realiza o request SOAP
        return $this->soap->send(
            $url,
            $this->method,
            $action,
            $this->soapversion,
            $this->params,
            $this->namespaces,
            $request,
            $header
        );
    }
    
    /**
     * Método que converto o objeto RPS em XML;
     * @param \NFePHP\NFSe\Models\Abrasf\Rps $rps
     * @return string Retorna o xml serializado
     */
    public function makeXml($rps)
    {
        $class = "NFePHP\\NFSe\\Models\\Abrasf\\Factories\\v{$this->versao}\\RecepcionarLoteRps";
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
        
        $message = str_replace('<?xml version="1.0"?>', '', $dom->saveXML());
        
        //O atributo xmlns precisa ser removido da tag <EnviarLoteRpsEnvio> pois
        //o web service de Itabira não o reconhece
        $messageText = str_replace('<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">', '<EnviarLoteRpsEnvio>', $message);
        
        if ($this->withcdata) {
            $messageText = $this->stringTransform($message);
        }
        
        $request = $this->makeRequest($messageText);
        
        return $messageText;
    }  
    
    /**
     * @param $message
     * @return string
     */
    protected function makeRequest($message)
    {
        $versao = '2.03';
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
                    $request =
                         "<nfse:{$this->method}>"
                        . "<xml>"
                        . "<![CDATA["
                        . $message
                        . "]]>"
                        . "</xml>"
                        . "</nfse:{$this->method}>";
            break;
            default:
                throw new \LogicException('Versão não suportada');
        }
        return $request;
    }  
    
    /**
     * Retorna o nome da versão do Layout formatado
     * @return string
     */
    private function getVersionString()
    {
        $return;
        
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
        
        $class = "NFePHP\\NFSe\\Models\\Abrasf\\Factories\\v{$this->versao}\\CancelarNfse";
        $fact = new $class($this->certificate);
        
        $this->method = 'CancelarNfse';
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
