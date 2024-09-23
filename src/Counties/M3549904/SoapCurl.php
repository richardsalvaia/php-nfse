<?php

namespace NFePHP\NFSe\Counties\M3549904;

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

    public $requestHead = '';
    public $requestBody = '';

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

        //check or create key files
        //before send request
        $response = '';
        $envelope = $this->makeEnvelopeSoap(
            $request,
            $namespaces,
            $soapver,
            $soapheader
        );

        // \RS_Env::dump($namespaces);exit;

        $msgSize = strlen($envelope);
        $parameters = [
            "Content-Type: application/soap+xml;charset=utf-8;",
            "Content-length: $msgSize"
        ];
        if (!empty($action)) {
            $parameters[0] .= "action=$action";
        }

        // header('Content-type: text/xml');die(self::clear($envelope)); 
        /* if (!empty($_SESSION['Admin_Auth']['usuario']) && $_SESSION['Admin_Auth']['usuario'] == 'contato@nsweb.com.br') {
        } */

        $this->requestHead = implode("\n", $parameters);
        $this->requestBody = $envelope;
        try {
            $oCurl = curl_init();
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
            /* curl_setopt($oCurl, CURLOPT_SSLVERSION, $this->soapprotocol);
            curl_setopt($oCurl, CURLOPT_SSLCERT,APPLICATION_PATH. '/certs/production/A1/cert_1.crt');
            curl_setopt($oCurl, CURLOPT_SSLKEY, APPLICATION_PATH. '/certs/production/A1/cert_1.key');
            // if (!empty($this->temppass)) {
                curl_setopt($oCurl, CURLOPT_KEYPASSWD, trim(file_get_contents(APPLICATION_PATH. '/certs/production/A1/cert_1.pass')));
            // }  */

            /* \RS_Env::dump(APPLICATION_PATH. '/certs/production/A1/cert_1.crt');
            \RS_Env::dump(APPLICATION_PATH. '/certs/production/A1/cert_1.key');
            \RS_Env::dump(trim(file_get_contents(APPLICATION_PATH. '/certs/production/A1/cert_1.pass')));
            exit; */
            
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            if (!empty($envelope)) {
                curl_setopt($oCurl, CURLOPT_POST, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $envelope);
                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parameters);
            }
            $response = curl_exec($oCurl);
            $this->soaperror = curl_error($oCurl);
            $this->soaperror_code = curl_errno($oCurl);

            $is_post = curl_getinfo($oCurl, CURLINFO_HTTP_CODE) && curl_getinfo($oCurl, CURLINFO_SIZE_UPLOAD) > 0;
            /* echo "<h2>Request".($is_post ? ' POST' : '')."</h2>";
            echo "<pre>".htmlentities($envelope)."</pre>";
            echo "<h2>Response</h2>";
            echo "<pre>".htmlentities($response)."</pre>";
            echo "<h2>Error</h2>";
            echo "<pre>".htmlentities($this->soaperror.' '.$this->soaperror_code)."</pre>";
            echo "<h2>Curl Info</h2>";
            echo "<pre>".print_r(curl_getinfo($oCurl), true)."</pre>";
            exit; */
            
            $ainfo = curl_getinfo($oCurl);
            if (is_array($ainfo)) {
                $this->soapinfo = $ainfo;
            }
            $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
            $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
            curl_close($oCurl);
            $this->responseHead = trim(substr($response, 0, $headsize));
            $this->responseBody = trim(substr($response, $headsize));
           /*  $this->saveDebugFiles(
                $operation,
                $this->requestHead . "\n" . $this->requestBody,
                $this->responseHead . "\n" . $this->responseBody
            ); */
        } catch (\Exception $e) {
            throw SoapException::unableToLoadCurl($e->getMessage());
        }
        if ($this->soaperror != '') {
            if (intval($this->soaperror_code) == 0) {
                $this->soaperror_code = 7;
            }
            throw SoapException::soapFault($this->soaperror . " [$url]", $this->soaperror_code);
        }
        if ($httpcode != 200) {
            if (intval($httpcode) == 0) {
                $httpcode = 52;
            } elseif ($httpcode == 500) {
                $httpcode = 89;
            }
            throw SoapException::soapFault($response, $httpcode);
        }
        return $this->responseBody;
    }

    public static function clear($body)
    {
        $body = str_replace('<?xml version="1.0"?>', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $body);
        return $body;
    }

}
