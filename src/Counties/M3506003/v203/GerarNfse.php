<?php
namespace NFePHP\NFSe\Counties\M3506003\v203;

use NFePHP\NFSe\Models\Abrasf\Factories;

use NFePHP\NFSe\Common\Factory as FactoryBase;

use NFePHP\Common\DOMImproved as Dom;

use NFePHP\NFSe\Counties\M3506003\RenderRps as RenderRps;

use NFePHP\Common\Certificate;

use NFePHP\NFSe\Models\Abrasf\Factories\Signer as Signer;

class GerarNfse  extends FactoryBase
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
     * Método usado para gerar o XML do Soap Request
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
        $root = $dom->createElement('GerarNfseEnvio');
        // $root->setAttribute('xmlns', $this->xmlns); // Colocando o namespace aqui da erro na assinatura

        //Adiciona as tags ao DOM
        $dom->appendChild($root);

        RenderRps::render($rps, $this->timezone, $dom, $root);

         //Parse para XML
        $xml = $dom->saveXml();

        // $this->validar($versao, $xml, $this->schemeFolder, $xsd, ''); // Sem o namespace acima não adianta validar
        // header('Content-type: text/xml');die($xml);

        //Gera o nó com a assinatura (Assinar a tag RPS não funciona, precisa ser a root GerarNfseEnvio)
        $body = Signer::sign(
            $this->certificate,
            $xml,
            "GerarNfseEnvio",
            "",
            $this->algorithm ,
            [true, false, null, null],
            '',
            false,
            0
        );

        return $body;
        
    }

}
