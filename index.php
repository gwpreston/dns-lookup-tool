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
		// http://www.phpliveregex.com/p/hwF
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
        $json->ipaddress[] = $record['ip'];

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

  }
  else if($type === 'whois') {

		require_once('whois.class.php');

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
<!doctype html>
<html lang="en" class="no-js">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<style type="text/css">

.DnsTools { word-break: break-word; }
.DnsTools-form,
.DnsTools-centerText { text-align:center; }
.DnsTools pre { min-height: 60px; white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; }
.DnsTools .panel-heading span { margin-top: -20px; font-size: 15px; }
.DnsTools-form form { margin:0 auto; width:400px; }
.DnsTools-form form label { display:none; }
.DnsTools-form form .btn { margin-top:1em; }
.DnsTools-whois pre { min-height: 400px; font: 10px/1.2 "Helvetica Neue",Helvetica,Arial,sans-serif; }
.DnsTools-preview iframe { width: 100%; min-height: 400px; }
.DnsTools-userInfo { text-align: center; font-size: 0.9em; padding-top: 15px; }
.DnsTools-userInfo h2 { text-align: left; }
.DnsTools-userInfo p span { }
.DnsTools-contentTitle { padding-bottom: 15px; }
.DnsTools-contentTitle span { font-weight: normal; font-size: 0.8em;}
</style>
</head>

<!--[if lt IE 7]>      <body class="DnsTools lt-ie10 lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <body class="DnsTools lt-ie10 lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <body class="DnsTools lt-ie10 lt-ie9 "> <![endif]-->
<!--[if IE 9]>         <body class="DnsTools lt-ie10"> <![endif]-->
<!--[if gt IE 8]><!--> <body class="DnsTools"> <!--<![endif]-->

<div class="container">
  <div class="row">
    <div class="col-sm-12 DnsTools-form">
      <h2>DNS Lookup Tool</h2>
      <p>This tools will allow you to work out some information about a domain name to confirm that we are still hosting it.</p>
      <form action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <label for="domain">Domain name</label>
        <div class="input-group">
          <span class="input-group-addon" id="basic-addon3">http://</span>
          <input type="text" name="domain" size="30" id="domain" value="<?php echo $domain; ?>" placeholder="Enter domain name" class="form-control" aria-describedby="basic-addon3">
        </div>
        <button type="submit" class="btn btn-primary btn-lg">Submit</button>
      </form>
    </div>
  </div>

	<div class="row">
		<div class="col-sm-12 DnsTools-userInfo">
			<p class="DnsTools-userIp"><strong>Your IP Address:</strong> <span><?php echo $userIp; ?></span> <span>(<?php echo gethostbyaddr($userIp); ?>)</span></p>
			<p class="DnsTools-userAgent"><strong>Your Useragent:</strong> <span><?php echo $_SERVER['HTTP_USER_AGENT']; ?></span></p>
			<p class="DnsTools-userViewport"><strong>Your viewport:</strong> <span></span></p>
		</div>
	</div>

	<hr>

  <!--
  <div class="alert alert-danger" role="alert">...</div>
  <div class="alert alert-info" role="alert">loading...</div>
	http://mxtoolbox.com/SuperTool.aspx?action=blacklist%3abbc.co.uk
  -->
  <div class="DnsTools-content hidden">

		<h3 class="DnsTools-contentTitle DnsTools-centerText">Results for: <span></span></h3>

    <div class="row">
      <div class="col-sm-6">
        <div class="row">
          <div class="col-sm-12 col-lg-6 DnsTools-nameserver">
						<div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">Nameservers</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
							<div class="table-responsive">
								<table class="table table-bordered table-striped">
									<tbody>
										<tr>
											<td></td>
										</tr>
									</tbody>
							  </table>
							</div>
						</div>
          </div>
          <div class="col-sm-12 col-lg-6 DnsTools-ipAddress">
            <div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">IP Addresses IPv4</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
							<div class="table-responsive">
								<table class="table table-bordered table-striped">
									<tbody>
										<tr>
											<td></td>
										</tr>
									</tbody>
							  </table>
							</div>
						</div>
          </div>
					<div class="col-sm-12 col-lg-12 DnsTools-mxRecords">
						<div class="panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">MX Records</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped">
								    <thead>
											<tr>
												<th>Host</th>
												<th>Priority</th>
												<th>IP</th>
												<th>Blacklist</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Host</td>
												<td>Priority</td>
												<td>IP</td>
												<td>Check</td>
											</tr>
										</tbody>
								  </table>
								</div>
							</div>
						</div>
          </div>
					<div class="col-sm-12 col-lg-12 DnsTools-txtRecords">
						<div class="panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">TXT Record</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
								<div class="table-responsive">
									<table class="table table-bordered table-striped">
										<tbody>
											<tr>
												<td></td>
											</tr>
										</tbody>
								  </table>
								</div>
							</div>
						</div>
						<div class="col-sm-12 DnsTools-preview">
			        <h3>Preview</h3>
			        <iframe></iframe>
			      </div>
        </div>
      </div>

      <div class="col-sm-6">
        <div class="row">
          <div class="col-sm-12 col-lg-6 DnsTools-expiryDate">
						<div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">Expiry Date</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
							<div class="panel-body"></div>
						</div>
          </div>
          <div class="col-sm-12 col-lg-6 DnsTools-ipstag hidden">
						<div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">IPS TAG</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
							<div class="panel-body"></div>
						</div>
          </div>
          <div class="col-sm-12 col-lg-6 DnsTools-registrar">
						<div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">Domain Registrar</h3>
								<span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
						  </div>
							<div class="panel-body"></div>
						</div>
          </div>
					<div class="col-sm-12 DnsTools-whois">
		        <h3>Whois</h3>
		        <pre></pre>
		      </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

	function setViewport() {
		$('.DnsTools-userViewport span').html($(window).width() + 'x' + $(window).height());
	}
	setViewport();
	$(window).resize(setViewport);

	$(document).on('click', '.panel-heading span.clickable', function(e) {
	  var $this = $(this);
		if(!$this.hasClass('panel-collapsed')) {
			var $contentElm = $this.parents('.panel').find('.panel-body');
			if($contentElm.length == 0)
				$contentElm = $this.parents('.panel').find('.table-responsive');
			$contentElm.slideUp();
			$this.addClass('panel-collapsed');
			$this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
		}
		else {
			var $contentElm = $this.parents('.panel').find('.panel-body');
			if($contentElm.length == 0)
				$contentElm = $this.parents('.panel').find('.table-responsive');
			$contentElm.slideDown();
			$this.removeClass('panel-collapsed');
			$this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
		}
	});

  $('form').submit(function() {

		var $form = $(this);
    $('.DnsTools-content, .DnsTools-ipstag').addClass('hidden');
    $('.input-group', this).removeClass('has-error');
		$('.DnsTools pre').html('Not Found...');

    var domainName = $("input[name='domain']", this).val().toLowerCase();
    if(domainName != '') {

			$('.DnsTools-contentTitle span').html(domainName);

      $.get($form.attr('action'), { domain: domainName, type: 'dns' }, function( data ) {
        console.log(data);

        $('iframe').attr('src', 'http://www.' + data.domain);
				$('.DnsTools-contentTitle span').html(data.domain);

				if(data.nservers !== undefined) {
	        $('.DnsTools-nameserver tbody').empty();
	        for (var i = 0; i < data.nservers.length; i++ ) {
						$tr = $('<tr />');
						$tr.append($('<td />').html(data.nservers[i]));
						$('.DnsTools-nameserver tbody').append($tr);
					}
				}

				if(data.ipaddress !== undefined) {
	        $('.DnsTools-ipAddress tbody').empty();
	        for (var i = 0; i < data.ipaddress.length; i++ ) {
						var $tr = $('<tr />');
						var $link = $('<a />').attr({
							href: 'http://ip-lookup.net/index.php?ip=' + data.ipaddress[i],
							target : '_blank'
						}).html(data.ipaddress[i]);
						$tr.append($('<td />').append($link));
						$('.DnsTools-ipAddress tbody').append($tr);
					}
				}

				if(data.mxrecord !== undefined) {
	        $('.DnsTools-mxRecords tbody').empty();
	        for (var i = 0; i < data.mxrecord.length; i++ ) {
						var $tr = $('<tr />');

						var $ipLookupLink = $('<a />').attr({
							href: 'http://ip-lookup.net/index.php?ip=' + data.mxrecord[i].ip,
							target : '_blank'
						}).html(data.mxrecord[i].ip);

						var $blacklistLink = $('<a />').attr({
							href: 'http://mxtoolbox.com/SuperTool.aspx?action=' + encodeURIComponent('blacklist:' + data.mxrecord[i].ip),
							target : '_blank'
						}).html('Check');

						$tr.append($('<td />').html(data.mxrecord[i].name));
						$tr.append($('<td />').addClass('DnsTools-centerText').html(data.mxrecord[i].priority));
						$tr.append($('<td />').append($ipLookupLink));
						$tr.append($('<td />').addClass('DnsTools-centerText').append($blacklistLink));
						$('.DnsTools-mxRecords tbody').append($tr);
					}
				}

				if(data.txt !== undefined) {
	        $('.DnsTools-txtRecords tbody').empty();
	        for (var i = 0; i < data.txt.length; i++ ) {
						$tr = $('<tr />');
						$tr.append($('<td />').html(data.txt[i]));
						$('.DnsTools-txtRecords tbody').append($tr);
					}
				}

      });

      $('.DnsTools-whois pre, .DnsTools-expiryDate pre, .DnsTools-registrar pre, .DnsTools-ipstag pre').empty().html('Please wait....');
      $.get($form.attr('action'), { domain: domainName, type: 'whois' }, function( data ) {
        console.log(data);

        $('.DnsTools-whois pre').empty().html(data.output);
        $('.DnsTools-expiryDate .panel-body').empty().html(new Date(Date.parse(data.expiryDate)).toDateString());
        $('.DnsTools-registrar .panel-body').empty().html(data.registrar);

				$('.DnsTools-ipstag').addClass('hidden');
        $('.DnsTools-ipstag .panel-body').empty();
        if(data.ipstag !== undefined) {
					$('.DnsTools-ipstag').removeClass('hidden');
          $('.DnsTools-ipstag  .panel-body').empty().html(data.ipstag);
				}

      });

      $('.DnsTools-content').removeClass('hidden');
    }
    else {
      $('.input-group', this).addClass('has-error');
    }
    return false;
  });
});
</script>
</body>
</html>
