
$(document).ready(function() {

	$('html').removeClass('no-js');

	if($('.DnsTools-server .DnsTools-map').length > 0) {
		var script = document.createElement( 'script' );
		script.type = 'text/javascript';
		script.src = 'https://maps.googleapis.com/maps/api/js?key=' + $('.DnsTools-server .DnsTools-map').data('mapkey');
		$('head').append( script );
	}

	var map = null;
	var marker = null;

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

				$('.DnsTools-nameserver tbody, .DnsTools-ipAddressV4 tbody, .DnsTools-ipAddressV6 tbody, .DnsTools-txtRecords tbody').empty().append($('<tr />').append($('<td />').html('Please wait....')));
				if($('.DnsTools-mxRecords tbody .panel-body').length == 0)
					$('.DnsTools-mxRecords tbody .panel-body').append($('<p />').html('Please wait....'));
				$('.DnsTools-mxRecords tbody .panel-body p').removeClass('hidden');
				$('.DnsTools-mxRecords tbody .panel-body .table-responsive').addClass('hidden');

				if(data.nservers !== undefined) {
	        $('.DnsTools-nameserver tbody').empty();
	        for (var i = 0; i < data.nservers.length; i++ ) {

						var $link = $('<a />').attr({
							href: 'http://ip-lookup.net/index.php?ip=' + data.nservers[i].ip,
							target : '_blank'
						}).html('(' + data.nservers[i].ip + ')');

						$tr = $('<tr />');
						$tr.append($('<td />').html(data.nservers[i].name).append($link));
						$('.DnsTools-nameserver tbody').append($tr);
					}
				}

				if(data.ipaddressV4 !== undefined) {
					$('.DnsTools-ipAddressV4').removeClass('hidden');
					$('.DnsTools-ipAddressV4 tbody').empty();
	        for (var i = 0; i < data.ipaddressV4.length; i++ ) {
						var $tr = $('<tr />');
						var $link = $('<a />').attr({
							href: 'http://ip-lookup.net/index.php?ip=' + data.ipaddressV4[i],
							target : '_blank'
						}).html(data.ipaddressV4[i]);
						$tr.append($('<td />').append($link));
						$('.DnsTools-ipAddressV4 tbody').append($tr);
					}
				}
				else {
					$('.DnsTools-ipAddressV4').addClass('hidden');
				}

				if(data.ipaddressV6 !== undefined) {
					$('.DnsTools-ipAddressV6').removeClass('hidden');
					$('.DnsTools-ipAddressV6 tbody').empty();
	        for (var i = 0; i < data.ipaddressV6.length; i++ ) {
						var $tr = $('<tr />');
						var $link = $('<a />').attr({
							href: 'http://ip-lookup.net/index.php?ip=' + data.ipaddressV6[i],
							target : '_blank'
						}).html(data.ipaddressV6[i]);
						$tr.append($('<td />').append($link));
						$('.DnsTools-ipAddressV6 tbody').append($tr);
					}
				}
				else {
					$('.DnsTools-ipAddressV6').addClass('hidden');
				}

				if(data.mxrecord !== undefined) {
					$('.DnsTools-mxRecords tbody .panel-body p').addClass('hidden');
					$('.DnsTools-mxRecords tbody .panel-body .table-responsive').removeClass('hidden');
	        $('.DnsTools-mxRecords tbody').empty();
	        for (var i = 0; i < data.mxrecord.length; i++ ) {
						var $tr = $('<tr />');

						var $ipLookupLink = $('<a />').attr({
							href: 'http://ip-lookup.net/index.php?ip=' + data.mxrecord[i].ip,
							target : '_blank'
						}).html(data.mxrecord[i].ip);

						var $blacklistLink = $('<a />').attr({
							href: 'https://mxtoolbox.com/SuperTool.aspx?run=toolpage&action=' + encodeURIComponent('blacklist:' + data.mxrecord[i].ip),
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

				if(data.geo !== undefined) {
					$('.DnsTools-server p span').html([data.geo.city, data.geo.country].join(', '));

					if($('.DnsTools-server .DnsTools-map').length > 0) {
						var latlng = {lat: data.geo.lat, lng: data.geo.lng};

						if(!$('.DnsTools-server').hasClass('DnsTools--hasMap')) {
							$('.DnsTools-server').addClass('DnsTools--hasMap');

			        map = new google.maps.Map(document.getElementById('Map'), {
			          zoom: 4,
			          center: latlng
			        });

			        marker = new google.maps.Marker({
			          position: latlng,
			          map: map
			        });

						}
						else {

							map.setCenter(latlng);
							marker.setPosition(latlng);

						}

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
