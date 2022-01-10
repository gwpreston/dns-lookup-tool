<?php
// https://www.chrishair.co.uk/php-uk-domain-bulk-whois-web-script/

if (isset($_REQUEST['domains'])){
	// set error to nothing for future use.
	$error = '';

	// grab inputted data from textarea for later use.
	$domains = trim(stripslashes($_REQUEST['domains']));

	// check if the domains text area is empty and if so flag up an error.
	if ($domains == '') {
		$error .= "<p><strong>No Domains!.</strong></p>\n";
	}

	// if error is still nothing by this point continue on with the WHOIS lookups.
	if ($error == '') {

		// explode the domains into an array based on new lines.
		$domain = explode("\r\n", $domains);

		// start of formatting tables.
		echo "<table>\n";
		echo "<tbody>\n\n";
		echo "<tr>\n";
		echo "<th width='10%'>Domain Name</th>\n";
		echo "<th width='10%'>Registrant</th>\n";
		echo "<th width='40%'>Registrar</th>\n";
		echo "<th width='40%'>Nameservers</th>\n";
		echo "</tr>\n\n";

		// start of master loop as we go trhough each domain.
		foreach ($domain as $item) {

			// open a socket connection to Nominets WHOIS server.
			$fp = fsockopen("whois.nic.uk", 43, $errno, $errstr);

			// If theres any problems flag an error.
			if (!$fp) echo "ERROR: $errno - $errstr<br />\n";

			// Write our domain to Nominets WHOIS server.
			fwrite($fp, $item . "\r\n");

      $lookup = '';

			// Read the response from Nominet.
			while (!feof($fp)) {
			     $lookup .= fread($fp, 8192);
			}

			// Explode the response into an array based on new lines, sloppy.
			$value = explode("\r\n\r\n", $lookup);

			// Make a blank array for later use.
			$whois_data = array();

			// Loop through our previous array, separating "field" and value and insering these into the previously blank array.
			foreach ($value as $values) {
				$details = explode(":\r\n", $values, 2);
				@$whois_data[trim($details[0])] = $details[1];
			}

			// Begin output of the results.
			echo "<tr>\n";
			echo "<td>" . $whois_data['Domain name'] . "</td>";
			echo "<td>" . $whois_data['Registrant'] . "</td>";
			echo "<td>" . $whois_data['Registrar'] . "</td>";
			echo "<td>" . $whois_data['Name servers'] . "</td>";
			echo "</tr>\n\n";

			// Close the connection to Nominets server.
			// We have to restablish a connection for every query!
			fclose($fp);
		}

		// Final endings of the formatting.
		echo "</tbody>\n";
		echo "</table>\n";
	} else {
		// Incase theres an error very sloppily show it.
		echo $error;
	}
  exit;
}
?>
