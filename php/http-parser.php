<?php

class HtmlParserException extends Exception { }

class HtmlParser {

  const REGEX_GOOGLE_ANALYTICS = '/UA-\d+-\d+/m';

  private $html = null;
  private $doc;

  public function __construct($html) {

    if(empty($html)) {
      throw new HtmlParserException('HTML is empty');
    }

    $this->html = $html;
    $this->doc = $this->parseHtml($html);
  }

  private function parseHtml($html) {

    $doc = new DOMDocument();

    // turning off some errors
    libxml_use_internal_errors(true);

    // it loads the content without adding enclosing html/body tags and also the doctype declaration
    if(function_exists('mb_convert_encoding')) {
      $doc->loadHTML( mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		}

    else {
			$doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		}

    return $doc;
  }

  public function getTitle() {
    $node = $this->doc->getElementsByTagName('title');
		$title = $node->item(0)->nodeValue;
		return (!empty($title)) ? $title : '';
  }

  public function getHead() {
    $node = $this->doc->getElementsByTagName('head');
		$head = $node->item(0);
		return ($head) ? $head : '';
  }

  public function getBody() {
    $node = $this->doc->getElementsByTagName('body');
		$body = $node->item(0);
		return ($body) ? $body : '';
  }

  // <meta name="description" content=""/>
  public function getMetaDescription() {
    $val = $this->parseMetaTag('description');
    return is_array($val) ? $val['description'] : $val;
  }

  // <meta name="keywords" content=""/>
  public function getMetaKeywords() {
    $val = $this->parseMetaTag('keywords');
    return is_array($val) ? $val['keywords'] : $val;
  }

  public function getMetaTags() {
    return $this->parseMetaTag();
  }

  private function parseMetaTag($attrName = null) {

    $metaTags = array();

    $metaTagNodes = $this->doc->getElementsByTagName('meta');
    if($metaTagNodes) {
      foreach($metaTagNodes as $node) {

        // <meta name="description" content="" />
        $name = DOMHelper::getAttributeValue($node, 'name');
  			if (empty($name)) {
          // <meta property="og:image" content="" />
  				$name = DOMHelper::getAttributeValue($node, 'property');
  			}

  			if (empty($name)) {
  				continue;
  			}

  			$name = strtolower($name);
        $content = trim(DOMHelper::getAttributeValue($node, 'content', ''));
        $tag = array($name => $content);

        if(!is_null($attrName)) {

          if($name === $attrName)
            return $tag;

          else if(strpos($name, $attrName) === false)
            continue;

        }

        $metaTags = array_merge($metaTags, $tag);
      }

      return $metaTags;

    }

    return null;
  }

  public function getImageTags() {

    $images = array();

		foreach ($this->doc->getElementsByTagName('img') as $node) {

      $src = $node->getAttribute('src');
      $imageUri = $src;
      if(!empty($src)) {
        // TODO: Need to work out absolute URL of image?????
      }

      $image = array(
        'src' => $imageUri,
        'alt' => DOMHelper::getAttributeValue($node, 'alt'),
        'title' => DOMHelper::getAttributeValue($node, 'title'),
        'height' => DOMHelper::getAttributeValue($node, 'height'),
        'width' => DOMHelper::getAttributeValue($node, 'width')
      );
      $images = array_merge($images, array($image));
		}

    return $images;
  }

  public function getHeadings() {
    $headings = array();

    // Loop through all H1-H6 heading tags
    for($i = 1; $i <= 6; $i++) {
      $headings = array_merge(
        $headings,
        array('h' . $i => $this->getTags('h' . $i))
      );
    }

    return $headings;
  }

  public function getHeading($num) {
    return $this->html;
  }

  private function getTags($tagName, $name, $attrName) {

    $metaTagNodes = $this->doc->getElementsByTagName($tagName);
    foreach($metaTagNodes as $tag) {
      if($tag->hasAttribute($attrName) && $tag->getAttribute($attrName) === $attrName) {
        return $tag->getAttribute('content');
      }
    }

    return null;
  }

  public function getHtml() {
    return $this->html;
  }

  public function getGoogleAnalytics() {
    preg_match_all(self::REGEX_GOOGLE_ANALYTICS, $this->getHtml(), $matches);
    if( is_array ($matches) && count($matches) > 0) {
      $trackingCodes = array_unique($matches[0]);
      foreach ($trackingCodes as $code) {
        if( $code !== 'UA-1422424-2' )
          return $code;
      }
    }
    return null;
  }

  public function getBodyXXXX() {
    preg_match("/<body[^>]*>(.*?)<\/body>/is", $this->html, $matches);
    return count($matches) > 0 ? $matches[0] : null;
  }

  public function getHeadXXXXX() {
    preg_match("/<head[^>]*>(.*?)<\/head>/is", $this->html, $matches);
    return count($matches) > 0 ? $matches[0] : null;
  }

}

?>
