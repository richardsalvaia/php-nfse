<?php
namespace NFePHP\NFSe\Counties\M3549904\v203;

use NFePHP\Common\DOMImproved as Dom;
use NFePHP\NFSe\Models\Abrasf\Factories\Header;
use NFePHP\NFSe\Models\Abrasf\Factories\Factory;
use NFePHP\NFSe\Models\Abrasf\Factories\Signer as Signer;
use NFePHP\NFSe\Models\Abrasf\Factories\CancelarNfse as CancelarNfseBase;

class CancelarNfse extends CancelarNfseBase
{

    public $xmlns;
    public $schemeFolder;
    

    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $nfseNumero
    ) {


        $dom = new Dom('1.0', 'utf-8');
        $dom->formatOutput = false;
        //Cria o elemento pai
        $root = $dom->createElement('cancelarNfse');

        //Adiciona as tags ao DOM
        $dom->appendChild($root);

        $loteRps = $dom->createElement('Pedido');

        $dom->appChild($root, $loteRps, 'Adicionando tag Pedido');
        
        $InfPedidoCancelamento = $dom->createElement('InfPedidoCancelamento');

        $dom->appChild(
            $loteRps,
            $InfPedidoCancelamento,
            "Inf Pedido Cancelamento"
        );
        
        $identificacaoNfse = $dom->createElement('IdentificacaoNfse');
        
        $dom->appChild(
            $InfPedidoCancelamento,
            $identificacaoNfse,
            'Identificação da Nfse'
        );
        
        /* Inscrição Municipal */
        $dom->addChild(
            $identificacaoNfse,
            'Numero',
            $nfseNumero,
            false,
            "Numero NFse",
            false
        );

        /* CPF CNPJ */
        $cpfCnpj = $dom->createElement('CpfCnpj');

        if ($remetenteTipoDoc == '2') {
            $tag = 'Cnpj';
        } else {
            $tag = 'Cpf';
        }
        //Adiciona o Cpf/Cnpj na tag CpfCnpj
        $dom->addChild(
            $cpfCnpj,
            $tag,
            $remetenteCNPJCPF,
            true,
            "Cpf / Cnpj",
            true
        );
        $dom->appChild($identificacaoNfse, $cpfCnpj, 'Adicionando tag CpfCnpj ao Prestador');

        /* Inscrição Municipal */
        $dom->addChild(
            $identificacaoNfse,
            'InscricaoMunicipal',
            $inscricaoMunicipal,
            false,
            "Inscricao Municipal",
            false
        );
        
        /* Código do Municipio */
        $dom->addChild(
            $identificacaoNfse,
            'CodigoMunicipio',
            $this->codMun,
            false,
            "Código Municipio",
            false
        );
        
        /* Código do Cancelamento */
        $dom->addChild(
            $InfPedidoCancelamento,
            'CodigoCancelamento',
            2,
            false,
            "Código Municipio",
            false
        );

        //Parse para XML
        $xml  = $dom->saveXML();

 
        //  header('Content-type: text/xml');die($xml);

         $body = Signer::sign(
             $this->certificate,
             $xml,
             "cancelarNfse",
             "",
             $this->algorithm ,
             [true, false, null, null],
             '',
             false,
             0
         );

         $body = trim(str_ireplace('<?xml version="1.0" encoding="UTF-8"?>', '', $body). PHP_EOL);
        
         //  header('Content-type: text/xml');die($body);


        return $body;


    }
   
}
