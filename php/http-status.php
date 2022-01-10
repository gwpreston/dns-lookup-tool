<?php

/**
 * Check if a resource is accessible by checking the HTTP status code
 *
 * @param The url to check
 * @return Returns true if status code isn't a 404 else false
*/
function isResourceAccessible($url) {
  $headers = get_headers($url, 1);
  print_r($headers);
  return $headers !== false && count($headers) > 0 && strpos($headers[0], '404') !== false ? false : true;
}
$url = 'https://www.propertypal.com';
//print_r(isResourceAccessible($url));

preg_match('/(http\/\d+\.\d+)\s+(\d+)/i', 'HTTP/1.1 200 OK', $matches);
if(count($matches) === 3) // Should always find three items
  echo 'Status code::: ' . $matches[2];

 ?>
