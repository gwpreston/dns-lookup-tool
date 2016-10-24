<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userIp = null;
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$userIp = $_SERVER['HTTP_CLIENT_IP'];
} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
		$userIp = $_SERVER['REMOTE_ADDR'];
}

$type = @$_REQUEST['type'];
$domain = @$_REQUEST['domain'];

if(isset($domain)) {

  # remove white space from start and end of domain
  $domain = strtolower(trim($domain));

	if(function_exists('preg_replace')) {
		# http://www.phpliveregex.com/p/hwF
		# Removes www. or http:// or https://
		$domain = preg_replace("/(https?:\/\/)?www\.([^\/]+)\/?/", "$2", $domain);
	}
	else {
	  # remove http:// if included
	  if (substr(strtolower($domain) , 0, 7) == 'http://')
	    $domain = substr($domain, 7);

	  # remove http:// if included
	  if (substr(strtolower($domain) , 0, 8) == 'https://')
	    $domain = substr($domain, 8);

	  # remove www from domain
	  if (substr(strtolower($domain) , 0, 4) == 'www.')
	    $domain = substr($domain, 4);
	}

  $json = new StdClass;
	$json->domain = $domain;

  if($type === 'dns') {
    $dnsr = dns_get_record($domain, DNS_ALL);
    $json->debug = $dnsr;

    foreach($dnsr as $record) {

      if($record['type'] === 'A')
        $json->ipaddressV4[] = $record['ip'];

			if($record['type'] === 'AAAA')
        $json->ipaddressV6[] = $record['ipv6'];

			if($record['type'] === 'TXT')
        $json->txt[] = $record['txt'];

      if($record['type'] === 'NS')
        $json->nservers[] = $record['target'];

      if($record['type'] === 'MX')
        $json->mxrecord[] = array(
          'name' => $record['target'],
          'ip' => gethostbyname($record['target']),
          'priority' => $record['pri']
        );
    }

		// Sort MX Records
		function mxPriorityAscSort($item1, $item2)
		{
		    if ($item1['priority'] === $item2['priority']) return 0;
		    return ($item1['priority'] > $item2['priority']) ? 1 : -1;
		}
		usort($json->mxrecord, 'mxPriorityAscSort');

  }
  else if($type === 'whois') {

		include_once('whois.class.php');

    $whois = new Whois();
    $whoisOutput = $whois->whoislookup($domain);
    $json->output = $whoisOutput;
    $json->expiryDate = Whois::GetExpirationDate($whoisOutput);
    $json->updateDate = Whois::GetUpdatedDate($whoisOutput);
    $json->createdDate = Whois::GetCreationDate($whoisOutput);
    $json->registrar = Whois::GetRegistrar($whoisOutput);
    $json->whoisServer = Whois::GetWhoisServer($whoisOutput);

		if(Whois::isNominet($whoisOutput))
    	$json->ipstag = Whois::GetIpStag($whoisOutput);
  }

  header('Content-Type: application/json');
  echo json_encode($json);
  exit;
}

?>
