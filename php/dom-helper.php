<?php

class DOMHelper {

  public static function getAttributeValue($node, $name, $defaultValue = null) {
    return $node->hasAttribute($name) ? $node->getAttribute($name) : $defaultValue;
  }

}

?>
