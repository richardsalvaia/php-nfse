<?php

namespace NFePHP\NFSe\Counties\M3506003;

/**
 * SoapClient based in cURL class
 *
 * @category  NFePHP
 * @package   NFePHP\Common\Soap\SoapCurl
 * @copyright NFePHP Copyright (c) 2016-2019
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-common for the canonical source repository
 */

use NFePHP\Common\Exception\SoapException;
use NFePHP\Common\Soap\SoapCurl as SoapCurlBase;

class SoapCurl extends SoapCurlBase
{
    /**
     * Send soap message to url
     * @param string $url
     * @param string $operation
     * @param string $action
     * @param int $soapver
     * @param array $parameters
     * @param array $namespaces
     * @param string $request
     * @param \SoapHeader $soapheader
     * @return string
     * @throws \NFePHP\Common\Exception\SoapException
     */
    public function send(
        $url,
        $operation = '',
        $action = '',
        $soapver = SOAP_1_1,
        $parameters = [],
        $namespaces = [],
        $request = '',
        $soapheader = null
    ) {

        // header('Content-type: text/xml');die($request);

        //check or create key files
        //before send request
        $response = '';
        $envelope = $this->makeEnvelopeSoap(
            $request,
            $namespaces,
            $soapver,
            $soapheader
        );

        if (!empty($action)) {
            $parameters[0] .= "action=$action";
        }


        
        /* preg_match('~<Signature(.*?)</Signature>~Usi', $envelope, $match);
        if (count($match)) {
            $envelope = str_replace($match[0], "", $envelope); //Removendo toda a tag Signature e colocando ela fora do RPS
            $envelope = str_replace('</GerarNfseEnvio', trim($match[0]).'</GerarNfseEnvio', $envelope);
        } */

        // header('Content-type: text/xml');die($envelope);
       /*  preg_match('/\<\!\[CDATA\[(.*)\]\]\>/ms', $envelope, $match);
        if (count($match)) {
            $envelope = str_replace($match[1], trim($match[1]), $envelope); //REMOVENDO O ESPACO DO CDATA
        } */

        
        // header('Content-type: text/xml');die($envelope);

        $this->requestHead = implode("\n", $parameters);
        $this->requestBody = $envelope;
        

        try {
            $oCurl = curl_init();
            #$this->setCurlProxy($oCurl);
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soaptimeout);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->soaptimeout + 20);
            curl_setopt($oCurl, CURLOPT_HEADER, 1);
            curl_setopt($oCurl, CURLOPT_HTTP_VERSION, $this->httpver);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
            if (!$this->disablesec) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
                if (is_file($this->casefaz)) {
                    curl_setopt($oCurl, CURLOPT_CAINFO, $this->casefaz);
                }
            }
            curl_setopt($oCurl, CURLOPT_SSLVERSION, $this->soapprotocol);
            curl_setopt($oCurl, CURLOPT_SSLCERT, $this->tempdir . $this->certfile);
            curl_setopt($oCurl, CURLOPT_SSLKEY, $this->tempdir . $this->prifile);
            if (!empty($this->temppass)) {
                curl_setopt($oCurl, CURLOPT_KEYPASSWD, $this->temppass);
            }
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            if (!empty($envelope)) {
                //  echo '<pre>'.print_r($envelope, true).'</pre>';die;
                curl_setopt($oCurl, CURLOPT_POST, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $envelope);
                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parameters);
            }
            $response = curl_exec($oCurl);
            $this->soaperror = curl_error($oCurl);
            $ainfo = curl_getinfo($oCurl);
            if (is_array($ainfo)) {
                $this->soapinfo = $ainfo;
            }
            $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
            $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
            curl_close($oCurl);
            $this->responseHead = trim(substr($response, 0, $headsize));
            $this->responseBody = trim(substr($response, $headsize));
            $this->saveDebugFiles(
                $operation,
                $this->requestHead . "\n" . $this->requestBody,
                $this->responseHead . "\n" . $this->responseBody
            );
        } catch (\Exception $e) {
            throw SoapException::unableToLoadCurl($e->getMessage(), '00');
        }
        if ($this->soaperror != '') {
            throw SoapException::soapFault($this->soaperror . " [$url]", '00');
        }
        if ($httpcode != 200) {
            throw SoapException::soapFault(" [$url]" . $this->responseHead, '00');
        }
        return $this->responseBody;
    }
}
