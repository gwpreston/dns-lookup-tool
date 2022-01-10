<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once ('dom-helper.php');
include_once ('http-request.php');
include_once ('http-parser.php');
include_once ('seo-analyzer.php');

try {

  $httpRequest = new HttpRequest();
  $httpRequest->fetch('http://www.michael-chandler.co.uk');
  $html = $httpRequest->getContent();

  if(!is_null($html)) {
    //print_r($httpRequest->getHeaders());
    //print_r($httpRequest->getContentType());
    //print_r($html);

    $htmlParser = new HtmlParser($html);
    var_dump($htmlParser->getImageTags());

  }
  else {
    echo 'Error getting HTML from ' . $url;
  }
}

catch (HttpRequestException $e) {
  echo $e->getMessage();
}

?>
