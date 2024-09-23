<?php

namespace NFePHP\NFSe\Counties\M3549904\v203;

use NFePHP\Common\DOMImproved as Dom;
use  NFePHP\NFSe\Counties\M3549904\RenderRps;
use  NFePHP\NFSe\Models\Abrasf\Factories\Factory;

use NFePHP\NFSe\Models\Abrasf\Factories\Signer as Signer;

class GerarNfse extends Factory
{

    protected $xmlns;
    protected $schemeFolder;
    protected $cmun;

    /**
     * @param $xmlns
     */
    public function setXmlns($xmlns)
    {
        $this->xmlns = $xmlns;
    }

    /**
     * @param $schemeFolder
     */
    public function setSchemeFolder($schemeFolder)
    {
        $this->schemeFolder = $schemeFolder;
    }

    /**
     * @param $cmun
     */
    public function setCodMun($cmun)
    {
        $this->cmun = $cmun;
    }

    /**
     * Metodo usado para gerar o XML do Soap Request
     * @param $versao
     * @param $rps
     * @return bool|string
     */
    public function render(
        $versao,
        $rps
    ) {
        $xsd = "nfse_v{$versao}";

        $dom = new Dom('1.0', 'utf-8');
        $dom->formatOutput = false;
        //Cria o elemento pai
        // $root = $dom->createElement('GerarNfse');
        $root = $dom->createElement('GerarNfseEnvio');
        // $root->setAttribute('xmlns', $this->xmlns);

        //Adiciona as tags ao DOM
        /*$root0->appendChild($root);
        $dom->appendChild($root0);*/
        /*nathalia*/ 
        
        $dom->appendChild($root);

        RenderRps::appendRps($rps, $this->timezone, $this->certificate, $this->algorithm, $dom, $root);

        //Parse para XML
        $xml = trim($this->clear($dom->saveXML()), PHP_EOL);
        
        // $this->validar($versao, $xml, $this->schemeFolder, $xsd, ''); //O codigo CNAE de SJC usa mais de 7 caracteres, por isso nao valida

        // header('Content-Type: text/xml');die($xml);
        return $xml;

    }
}