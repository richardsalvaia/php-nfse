<?php

namespace NFePHP\NFSe\Counties\M3549904;

/**
 * Classe para a renderizaÃ§Ã£o dos RPS em XML
 * conforme o modelo Abrasf
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Models\Abrasf\RenderRPS
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse for the canonical source repository
 */

use NFePHP\NFSe\Models\Abrasf\Rps;
use NFePHP\NFSe\Models\Abrasf\Factories\v203\RenderRps as RenderRPSBase;
use NFePHP\Common\Certificate;
use NFePHP\NFSe\Models\Abrasf\Factories\Signer;
use NFePHP\NFSe\Models\Abrasf\Factories\SignerRps;

class RenderRps extends RenderRPSBase
{

     /**
     * Monta o xml com base no objeto Rps
     * @param Rps $rps
     * @return string
     */
    protected static function render($rps, &$dom, &$parent)
    {
        self::$dom = $dom;
        $root = self::$dom->createElement('Rps');
        
        $infRPS = self::$dom->createElement("InfDeclaracaoPrestacaoServico");
        $infRPS->setAttribute('Id', "infRPS{$rps->infNumero}");
        
        /** RPS Filha **/
        $rpsInf = self::$dom->createElement('Rps');
        $rpsInf->setAttribute('Id', "rps{$rps->infNumero}");

        //Identificação RPS
        $identificacaoRps = self::$dom->createElement('IdentificacaoRps');

        $rps->infDataEmissao->setTimezone(self::$timezone);

        self::$dom->addChild(
            $identificacaoRps,
            'Numero',
            $rps->infNumero,
            true,
            "Numero do RPS",
            false
        );
        self::$dom->addChild(
            $identificacaoRps,
            'Serie',
            $rps->infSerie,
            true,
            "Serie do RPS",
            false
        );
        self::$dom->addChild(
            $identificacaoRps,
            'Tipo',
            $rps->infTipo,
            true,
            "Tipo do RPS",
            false
        );
        self::$dom->appChild($rpsInf, $identificacaoRps, 'Adicionando tag IdentificacaoRPS');
        //FIM Identificação RPS

        self::$dom->addChild(
            $rpsInf,
            'DataEmissao',
            $rps->infDataEmissao->format('Y-m-d'),
            true,
            'Data de EmissÃ£o do RPS',
            false
        );

        self::$dom->addChild(
            $rpsInf,
            'Status',
            $rps->infStatus,
            true,
            'Status',
            false
        );

        //RPS Substituido
        if (!empty($rps->infRpsSubstituido['numero'])) {
            $rpssubs = self::$dom->createElement('RpsSubstituido');
            self::$dom->addChild(
                $rpssubs,
                'Numero',
                $rps->infRpsSubstituido['numero'],
                true,
                'Numero',
                false
            );
            self::$dom->addChild(
                $rpssubs,
                'Serie',
                $rps->infRpsSubstituido['serie'],
                true,
                'Serie',
                false
            );
            self::$dom->addChild(
                $rpssubs,
                'Tipo',
                $rps->infRpsSubstituido['tipo'],
                true,
                'tipo',
                false
            );
            self::$dom->appChild($rpsInf, $rpssubs, 'Adicionando tag RpsSubstituido em infRps');
        }

        self::$dom->appChild($infRPS, $rpsInf, 'Adicionando tag Rps');
        /** FIM RPS Filha **/

        self::$dom->addChild(
            $infRPS,
            'Competencia',
            $rps->infDataEmissao->format('Y-m-d'),
            true,
            'Competencia EmissÃ£o do RPS',
            false
        );

        /** Serviços **/
        $servico = self::$dom->createElement('Servico');

        //Valores
        $valores = self::$dom->createElement('Valores');
        self::$dom->addChild(
            $valores,
            'ValorServicos',
            \RS_Util::formatValorDB($rps->infValorServicos),
            true,
            'ValorServicos',
            false
        );
        if ($rps->infValorDeducoes ) {
            self::$dom->addChild(
                $valores,
                'ValorDeducoes',
                \RS_Util::formatValorDB($rps->infValorDeducoes),
                false,
                'ValorDeducoes',
                false
            );
        }
        if ($rps->infValorPis) {
            self::$dom->addChild(
                $valores,
                'ValorPis',
                \RS_Util::formatValorDB($rps->infValorPis),
                false,
                'ValorPis',
                false
            );
        }
        if ($rps->infValorCofins) {
            self::$dom->addChild(
                $valores,
                'ValorCofins',
                \RS_Util::formatValorDB($rps->infValorCofins),
                false,
                'ValorCofins',
                false
            );
        }

        if (!empty($rps->infValorInss)) {
            self::$dom->addChild(
                $valores,
                'ValorInss',
                \RS_Util::formatValorDB($rps->infValorInss),
                false,
                'ValorInss',
                false
            );
        }
        if (!empty($rps->infValorIr)) {
            self::$dom->addChild(
                $valores,
                'ValorIr',
                \RS_Util::formatValorDB($rps->infValorIr),
                false,
                'ValorIr',
                false
            );
        }
        if (!empty($rps->infValorCsll)) {
            self::$dom->addChild(
                $valores,
                'ValorCsll',
                \RS_Util::formatValorDB($rps->infValorCsll),
                false,
                'ValorCsll',
                false
            );
        }
        if (!empty($rps->infOutrasRetencoes)) {
            self::$dom->addChild(
                $valores,
                'OutrasRetencoes',
                \RS_Util::formatValorDB($rps->infOutrasRetencoes),
                false,
                'OutrasRetencoes',
                false
            );
        }

        /*self::$dom->addChild(
            $valores,
            'ValTotTributos',
            $rps->infTotTributacao,
            false,
            'ValTotTributos',
            false
        );*/

       /*  if ($rps->infValorIss) {
            self::$dom->addChild(
                $valores,
                'ValorIss',
                \RS_Util::formatValorDB($rps->infValorIss),
                false,
                'ValorIss',
                false
            );
        }
        if ($rps->infAliquota) {
            self::$dom->addChild(
                $valores,
                'Aliquota',
                \RS_Util::formatValorDB($rps->infAliquota),
                false,
                'Aliquota',
                false
            );
        }
        
        if ($rps->infDescontoCondicionado) {
            self::$dom->addChild(
                $valores,
                'DescontoCondicionado',
                \RS_Util::formatValorDB($rps->infDescontoCondicionado),
                false,
                'DescontoCondicionado',
                false
            );
        } */

        /* self::$dom->addChild(
            $valores,
            'DescontoIncondicionado',
            \RS_Util::formatValorDB($rps->infDescontoIncondicionado),
            false,
            'DescontoIncondicionado',
            false
        ); */
        self::$dom->appChild($servico, $valores, 'Adicionando tag Valores em Servico');
        //FIM Valores

        if ($rps->infIssRetido) {
            self::$dom->addChild(
                $servico,
                'IssRetido',
                $rps->infIssRetido,
                true,
                'IssRetido',
                false
            );
        }
        // <======= RESPONSAVEL RETENCAO AQUI =======>
        // self::$dom->addChild(
        //     $servico,
        //     'ResponsavelRetencao',
        //     $rps->infResponsavelRetencao,
        //     false,
        //     'ResponsavelRetencao',
        //     false
        // );
        self::$dom->addChild(
            $servico,
            'ItemListaServico',
            $rps->infItemListaServico,
            true,
            'ItemListaServico',
            false
        );

        self::$dom->addChild(
            $servico,
            'CodigoCnae',
            $rps->infCodigoCnae,
            false,
            'CodigoCnae',
            false
        );

        self::$dom->addChild(
            $servico,
            'CodigoTributacaoMunicipio',
            $rps->infCodigoTributacaoMunicipio,
            false,
            'CodigoTributacaoMunicipio',
            false
        );
        self::$dom->addChild(
            $servico,
            'Discriminacao',
            $rps->infDiscriminacao,
            true,
            'Discriminacao',
            false
        );
        self::$dom->addChild(
            $servico,
            'CodigoMunicipio',
            $rps->infMunicipioPrestacaoServico,
            true,
            'CodigoMunicipio',
            false
        );        
        /* self::$dom->addChild(
             $servico,
             'CodigoPais',
             $rps->infCodigoPais,
             false,
             'CodigoPais',
             false
         );*/
         
        self::$dom->addChild(
            $servico,
            'ExigibilidadeISS',
            1,
            true,
            'ExigibilidadeISS',
            false
        );
        self::$dom->addChild(
            $servico,
            'MunicipioIncidencia',
            $rps->infMunicipioPrestacaoServico,
            false,
            'MunicipioIncidencia',
            false
        );
        // self::$dom->addChild(
        //     $servico,
        //     'NumeroProcesso',
        //     $rps->infNumeroProcesso,
        //     false,
        //     'NumeroProcesso',
        //     false
        // );
        self::$dom->appChild($infRPS, $servico, 'Adicionando tag Servico');
        /** FIM Serviços **/

        /** Prestador **/
        $prestador = self::$dom->createElement('Prestador');

        //Cpf/Cnpj
        if (!empty($rps->infPrestador['cnpjcpf'])) {
            $cpfCnpj = self::$dom->createElement('CpfCnpj');
            if ($rps->infPrestador['tipo'] == 2) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cnpj',
                    $rps->infPrestador['cnpjcpf'],
                    true,
                    'Prestador CNPJ',
                    false
                );
            } else {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cpf',
                    $rps->infPrestador['cnpjcpf'],
                    true,
                    'Prestador CPF',
                    false
                );
            }
            self::$dom->appChild($prestador, $cpfCnpj, 'Adicionando tag CpfCnpj em Prestador');
        }

        //Inscrição Municipal
        self::$dom->addChild(
            $prestador,
            'InscricaoMunicipal',
            $rps->infPrestador['im'],
            false,
            'InscricaoMunicipal',
            false
        );
        self::$dom->appChild($infRPS, $prestador, 'Adicionando tag Prestador em infRPS');
        /** FIM Prestador **/

        /** Tomador **/
        if (!empty($rps->infTomador['razao'])) {
            $tomador = self::$dom->createElement('Tomador');

            //Identificação Tomador
            if (!empty($rps->infTomador['cnpjcpf'])) {
                $identificacaoTomador = self::$dom->createElement('IdentificacaoTomador');
                $cpfCnpjTomador = self::$dom->createElement('CpfCnpj');
                if ($rps->infTomador['tipo'] == 2) {
                    self::$dom->addChild(
                        $cpfCnpjTomador,
                        'Cnpj',
                        $rps->infTomador['cnpjcpf'],
                        true,
                        'Tomador CNPJ',
                        false
                    );
                } else {
                    self::$dom->addChild(
                        $cpfCnpjTomador,
                        'Cpf',
                        $rps->infTomador['cnpjcpf'],
                        true,
                        'Tomador CPF',
                        false
                    );
                }
                self::$dom->appChild($identificacaoTomador, $cpfCnpjTomador,
                    'Adicionando tag CpfCnpj em IdentificacaTomador');


                //Inscrição Municipal
                self::$dom->addChild(
                    $identificacaoTomador,
                    'InscricaoMunicipal',
                    $rps->infTomador['im'],
                    false,
                    'InscricaoMunicipal',
                    false
                );
                self::$dom->appChild($tomador, $identificacaoTomador,
                    'Adicionando tag IdentificacaoTomador em Tomador');

            }

            //Razao Social
            self::$dom->addChild(
                $tomador,
                'RazaoSocial',
                $rps->infTomador['razao'],
                true,
                'RazaoSocial',
                false
            );

            //Endereço
            if (!empty($rps->infTomadorEndereco['end'])) {
                $endereco = self::$dom->createElement('Endereco');
                self::$dom->addChild(
                    $endereco,
                    'Endereco',
                    $rps->infTomadorEndereco['end'],
                    true,
                    'Endereco',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Numero',
                    $rps->infTomadorEndereco['numero'],
                    false,
                    'Numero',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Complemento',
                    $rps->infTomadorEndereco['complemento'],
                    false,
                    'Complemento',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Bairro',
                    $rps->infTomadorEndereco['bairro'],
                    false,
                    'Bairro',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'CodigoMunicipio',
                    $rps->infTomadorEndereco['cmun'],
                    false,
                    'CodigoMunicipio',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Uf',
                    $rps->infTomadorEndereco['uf'],
                    false,
                    'Uf',
                    false
                );
                self::$dom->addChild(
                    $endereco,
                    'Cep',
                    $rps->infTomadorEndereco['cep'],
                    false,
                    'Cep',
                    false
                );
                
                /*self::$dom->addChild(
                    $endereco,
                    'CodigoPais',
                    $rps->infTomadorEndereco['codigoPais'],
                    false,
                    'CodigoPais',
                    false
                );*/
                
                self::$dom->appChild($tomador, $endereco, 'Adicionando tag Endereco em Tomador');

            }

            //Contato
            if ($rps->infTomador['tel'] != '' || $rps->infTomador['email'] != '') {
                $contato = self::$dom->createElement('Contato');
                if ($rps->infTomador['tel'] != '') {
                    self::$dom->addChild(
                        $contato,
                        'Telefone',
                        $rps->infTomador['tel'],
                        false,
                        'Telefone Tomador',
                        false
                    );
                }
                if ($rps->infTomador['email'] != '') {
                    self::$dom->addChild(
                        $contato,
                        'Email',
                        $rps->infTomador['email'],
                        false,
                        'Email Tomador',
                        false
                    );
                }
                self::$dom->appChild($tomador, $contato, 'Adicionando tag Contato em Tomador');
            }
            self::$dom->appChild($infRPS, $tomador, 'Adicionando tag Tomador em infRPS');
        }

        /** FIM Tomador **/

        /** Intermediario **/
        if (!empty($rps->infIntermediario['razao'])) {
            $intermediario = self::$dom->createElement('Intermediario');
            $cpfCnpj = self::$dom->createElement('CpfCnpj');
            if ($rps->infIntermediario['tipo'] == 2) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cnpj',
                    $rps->infIntermediario['cnpjcpf'],
                    true,
                    'CNPJ Intermediario',
                    false
                );
            } elseif ($rps->infIntermediario['tipo'] == 1) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cpf',
                    $rps->infIntermediario['cnpjcpf'],
                    true,
                    'CPF Intermediario',
                    false
                );
            }
            self::$dom->appChild($intermediario, $cpfCnpj, 'Adicionando tag CpfCnpj em Intermediario');
            self::$dom->addChild(
                $intermediario,
                'InscricaoMunicipal',
                $rps->infIntermediario['im'],
                false,
                'IM Intermediario',
                false
            );

            //Razao Social
            self::$dom->addChild(
                $intermediario,
                'RazaoSocial',
                $rps->infIntermediario['razao'],
                true,
                'Razao Intermediario',
                false
            );
            self::$dom->appChild($infRPS, $intermediario, 'Adicionando tag Intermediario em infRPS');
        }
        /** FIM Intermediario **/

        /** Construção Civil **/
        if (!empty($rps->infConstrucaoCivil['obra'])) {
            $construcao = self::$dom->createElement('ContrucaoCivil');
            self::$dom->addChild(
                $construcao,
                'CodigoObra',
                $rps->infConstrucaoCivil['obra'],
                false,
                'Codigo da Obra',
                false
            );
            self::$dom->addChild(
                $construcao,
                'Art',
                $rps->infConstrucaoCivil['art'],
                true,
                'Art da Obra',
                false
            );
            self::$dom->appChild($infRPS, $construcao, 'Adicionando tag Construcao em infRPS');
        }
        /** FIM Construção Civil **/

        /*nathalia self::$dom->addChild(
            $infRPS,
            'RegimeEspecialTributacao',
            $rps->infRegimeEspecialTributacao,
            false,
            'RegimeEspecialTributacao',
            false
        );*/ 
        self::$dom->addChild(
            $infRPS,
            'OptanteSimplesNacional',
            $rps->infOptanteSimplesNacional,
            true,
            'OptanteSimplesNacional',
            false
        ); 
        /*nathalia */self::$dom->addChild(
            $infRPS,
            'IncentivoFiscal',
            $rps->infIncentivadorCultural,
            true,
            'IncentivoFiscal',
            false
        );

        self::$dom->appChild($root, $infRPS, 'Adicionando tag infRPS em RPS');
        self::$dom->appChild($parent, $root, 'Adicionando tag RPS na ListaRps');

        return $root;
    }

    public static function appendRps(
        $data,
        \DateTimeZone $timezone,
        Certificate $certificate,
        $algorithm = OPENSSL_ALGO_SHA1,
        &$dom,
        &$parent
    ) {

        self::$algorithm = $algorithm; //Forçando aqui o XML que é validado no validador https://validar.iti.gov.br/relatorioDeConformidade.html / https://servicos.receita.fazenda.gov.br/servicos/assinadoc/ValidadorAssinaturas.app/valida.aspx
        self::$certificate = $certificate;
        self::$timezone = $timezone;

        if (is_object($data)) {
            //Gera a RPS
            $rootNode = self::render($data, $dom, $parent);
        }

        if (!empty($_SESSION['Admin_Auth']['usuario']) && $_SESSION['Admin_Auth']['usuario'] == 'contato@nsweb.com.br') {
            // header('Content-type: text/xml');die($dom->saveXML());
        } 

        //Gera o nó com a assinatura
        $signatureNode = SignerRps::sign(
            self::$certificate,
            'InfDeclaracaoPrestacaoServico',
            'Id',
            self::$algorithm,
            [true, false, null, null],
            $dom,
            $rootNode
        );

        // header('Content-type: text/xml'); die($dom->saveXML());
    }

}