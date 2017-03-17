<?php
include_once('main.php');
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="css/main.min.css" type="text/css">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
	<script src="js/html5shiv.min.js"></script>
	<script src="js/respond.min.js"></script>
<![endif]-->
</head>

<!--[if lt IE 7]>      <body class="DnsTools lt-ie10 lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <body class="DnsTools lt-ie10 lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <body class="DnsTools lt-ie10 lt-ie9 "> <![endif]-->
<!--[if IE 9]>         <body class="DnsTools lt-ie10"> <![endif]-->
<!--[if gt IE 8]><!--> <body class="DnsTools"> <!--<![endif]-->

<?php if (version_compare(phpversion(), '5.0.0', '<')) { ?>
<div class="alert alert-danger DnsTools-phpVersion">
  <h5>Error: It appears you have not the correct PHP required. It must be PHP 5 or greater. You currently have <?php echo phpversion(); ?>.</h5>
</div>
<?php } ?>

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
			<p class="DnsTools-userIp"><strong>Your IP Address:</strong> <span><?php echo ($userIp === '::1')?'127.0.0.1':$userIp; ?></span> <span>(<?php echo gethostbyaddr($userIp); ?>)</span></p>
			<p class="DnsTools-userAgent"><strong>Your Useragent:</strong> <span><?php echo $_SERVER['HTTP_USER_AGENT']; ?></span></p>
			<p class="DnsTools-userViewport"><strong>Your viewport:</strong> <span></span></p>
		</div>
	</div>

	<hr>

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
          <div class="col-sm-12 col-lg-6 DnsTools-ipAddress DnsTools-ipAddressV4">
            <div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">IP Address IPv4</h3>
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
					<div class="col-sm-12 col-lg-6 DnsTools-ipAddress DnsTools-ipAddressV6">
            <div class="panel panel-default">
							<div class="panel-heading">
						    <h3 class="panel-title">IP Address IPv6</h3>
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
            <?php if(defined('GOOGLE_MAP_KEY') && !empty(GOOGLE_MAP_KEY)) { ?>
            <div class="col-sm-12 DnsTools-server">
              <h3>Server</h3>
              <p>Location: <span></span></p>
              <div id="Map" class="DnsTools-map" data-mapkey="<?php echo GOOGLE_MAP_KEY; ?>"></div>
            </div>
            <?php } ?>
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

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/main.min.js"></script>

</body>
</html>
