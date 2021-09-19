<?php

namespace Rialto\Legacy;

use Rialto\NetworkException;

/**
 * @author Tyler Jones <tyler@gumstix.com>
 * @deprecated use Guzzle instead
 */
class CurlHelper
{
    const DEFAULT_TIMEOUT = 5; /* seconds */

    private $errorNo = 0;
    private $errorMsg = null;
    private $options = [];

    public function __construct()
    {
        $this->options[CURLOPT_USERAGENT] = 'Rialto HTTP agent';
        $this->setTimeout( self::DEFAULT_TIMEOUT );
    }

    public function disableCertificateCheck($disable)
    {
        $this->options[CURLOPT_SSL_VERIFYPEER] = ! $disable;
    }

    public function setBinaryTransfer($isBinary)
    {
        $this->options[CURLOPT_BINARYTRANSFER] = $isBinary;
        return $this;
    }

    public function setCredentials($username, $password)
    {
        $this->options[CURLOPT_USERPWD] = $username . ':'. $password;
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_ANY;
        return $this;
    }

    public function useSSLv3()
    {
        $this->options[CURLOPT_SSLVERSION] = 3;
        $this->options[CURLOPT_SSL_CIPHER_LIST] = 'SSLv3';
    }

    /**
     * @param string $postData
     *  The raw post data.
     */
    public function setPost($postData)
    {
        $this->options[CURLOPT_POST] = true;
        $this->options[CURLOPT_POSTFIELDS] = $postData;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->options[CURLOPT_CONNECTTIMEOUT] = $timeout;
        return $this;
    }

    /**
     * Returns the content at the given URI.
     *
     * @param string $uri
     * @return string
     */
    public function fetch($uri)
    {
        $response = $this->core($uri);
        if ( $this->errorNo ) throw new NetworkException(
            $uri,
            $this->errorMsg
        );
        return $response;
    }

    /** @return string */
    public function fetchGet($uri, array $get = [])
    {
        $uri .= (strpos($uri, '?') === false ? '?' : '&').http_build_query($get);
        return $this->fetch($uri);
    }

    /** @return string */
    public function fetchPost($uri, array $post = [])
    {
        $this->options[CURLOPT_POST] = count($post);
        $this->options[CURLOPT_POSTFIELDS] = http_build_query($post);
        return $this->fetch($uri);
    }

    /**
     * @param string $uri
     * @return bool
     *  Returns true if the given URI is accessible, false otherwise.
     */
    public function test($uri)
    {
        $this->core($uri);
        return ( 0 == $this->errorNo );
    }

    /**
     * @param string $uri
     * @return string
     *  The server response.
     */
    private function core($uri)
    {
        $handle = curl_init($uri);
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);
        curl_setopt_array($handle, $this->options);

        $response = curl_exec($handle);
        $this->errorNo = curl_errno($handle);
        $this->errorMsg = curl_error($handle);
        curl_close($handle);
        return $response;
    }

    public function getErrorNumber()
    {
        return $this->errorNo;
    }

    public function getErrorMessage()
    {
        return $this->errorMsg;
    }
}
