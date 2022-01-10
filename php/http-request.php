<?php

class InvalidUriException extends HttpRequest { }
class HttpRequestException extends Exception { }

class HttpRequest {

  const HTTP_CODE_REGEX = '/(http\/\d+\.\d+)\s+(\d+)/i';
  const HTTP_CONTENT_TYPE_REGEX = '/([\w\/]+)(;\s*charset=([^\s\"]+))?/ix';

  private $httpHeaders;
  private $httpCode;
  private $content = null;

  public function __construct() {

  }

  public function fetch($url) {

    if (filter_var($url, FILTER_VALIDATE_URL) === false ) {
        throw new InvalidUriException($url . ' is a valid URL');
    }

    if (!preg_match('/https?:\/\//', $url)) {
        throw new InvalidUriException($url . ' must be HTTP or HTTPS');
    }

    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 5,
            'follow_location' => true,
            'method'  => 'GET'
          )
        )
    );

    // See http://php.net/manual/en/ref.curl.php
    if (function_exists('curl_init') && function_exists('curl_exec') && function_exists('curl_setopt') && function_exists('curl_close')) {

      if($conn = @curl_init($url)) {
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
        curl_setopt($conn, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_CONNECTTIMEOUT , 5);
        if(($content = @curl_exec($conn)) !== false) {
          $this->content = $content;
          $this->httpHeaders = curl_getinfo($conn);

          // Gets the HTTP Status Code
          if(array_key_exists('http_code', $this->httpHeaders) && !empty($this->httpHeaders['http_code']))
            $this->httpCode = $this->httpHeaders['http_code'];

        }

        if($errno = curl_errno($conn)) {
          throw new HttpRequestException('CURL error (' . $errno . '): ' . curl_strerror($errno));
        }

        @curl_close($conn);
      }

    }

    // See http://php.net/manual/en/function.file-get-contents.php
    else if(function_exists('file_get_contents')) {

      if($url_content = @file_get_contents($url, false, $context)) {
        $this->content = $url_content;

        // See http://php.net/manual/en/reserved.variables.httpresponseheader.php
        if(isset($http_response_header) && is_array($http_response_header)) {
          $this->setHttpHeaders($http_response_header);
        }

      }
      else {
        // if (strpos($http_response_header[0], '200')) {
        throw new HttpRequestException('Cannot access ' . $url . ' to read contents.');
      }

    }

    // See http://php.net/manual/en/function.fopen.php
    else if(function_exists('fopen') && function_exists('stream_get_contents')) {

      if($handle = @fopen($url, 'r', false, $context)) {
        $this->content = stream_get_contents($handle);

        // See http://php.net/manual/en/reserved.variables.httpresponseheader.php
        if(isset($http_response_header) && is_array($http_response_header)) {
          $this->setHttpHeaders($http_response_header);
        }

      }

      else {
        // if (strpos($http_response_header[0], '200')) {
        throw new HttpRequestException('Cannot access ' . $url . ' to read contents.');
      }

    }

  }

  public function getHttpCode() {
    return is_null($this->httpCode) ? $this->httpCode : intval($this->httpCode);
  }

  public function getHeaders() {
    return $this->httpHeaders;
  }

  public function getContent() {
    return $this->content;
  }

  public function getContentType() {
    if(array_key_exists('content_type', $this->httpHeaders))
      return self::parseContentType($this->httpHeaders['content_type']);
    if(array_key_exists('Content-Type', $this->httpHeaders))
      return self::parseContentType($this->httpHeaders['Content-Type']);;
    return null;
  }

  public static function parseHttpCode($httpStatus) {
    preg_match(self::HTTP_CODE_REGEX, $httpStatus, $matches);
    if(count($matches) === 3) // Should always find three items
      return $matches[2];
    return null;
  }

  public static function parseContentType($contentType) {
    preg_match(self::HTTP_CONTENT_TYPE_REGEX, $contentType, $matches);
    if(count($matches) === 4) // Content-Type: text/html;charset=UTF-8
      return $matches[1];
    else if(count($matches) === 2) // content_type: text/html
      return $matches[1];
    return null;
  }

  private function setHttpHeaders($httpHeaders) {
    $headers = array();
    for($i = 0; $i < count($httpHeaders); $i++) {

      if(strrpos($httpHeaders[$i], ':') !== false) {
        list($key, $value) = explode(':', $httpHeaders[$i]);
        $headers = array_merge(
          $headers,
          array(trim($key) => trim($value))
        );
      }

      else {
        $headers = array_merge(
          $headers,
          array(0 => trim($httpHeaders[$i]))
        );
      }

    }

    if(array_key_exists(0, $headers) ) {
      $code = self::parseHttpCode($headers[0]);
      if(!is_null($code))
        $headers = array_merge($headers, array('Http-Code' => $code));
    }

    $this->httpHeaders = $headers;
  }

}

?>
