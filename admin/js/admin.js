$(function() {
	$('.table-sort').click(function(e) {
		e.preventDefault();
		var sort = $(this).attr('data-col');
		$('#index_form').attr('action', document.URL).append('<input type="hidden" name="sort" value="'+sort+'" />').submit();
	});
});

//var sessionState = null;

function cp_ajax(callback, theobject, sender, theevent, data, getresponse) {
	var s = document.createElement("script");
	//s.type = "text/javascript";
	//s.id = "callback_" + callback;
	//s.src = ajax_host;// + "?callback="+callback+"&object=" + theobject + "&event=" + theevent;
	//if (data) s.src += "&data=" + data;
	//if (sender) s.src += "&sender=" + sender;
	if (getresponse && data !== false) {
		$.ajax({
			url: ajax_host,
			context: document.body,
			accepts: 'text/javascript',
			data: {
				callback: callback,
				object: theobject,
				event: theevent,
				sender: sender,
				data: data
			},
			type: 'POST'
		}).done(function(resp) {
			// We needed to update our state on the _change event, so now we can call the actual change event.
			if (theevent == 'update_state') {
				//var hst = ajax_host + "?callback="+callback+"&object=" + sessionState + "&event=change";
				$.ajax({
					url: ajax_host,
					context: document.body,
					accepts: 'text/plain',
					data: {
						callback: callback,
						object: sessionState,
						event: 'change',
						sender: sender,
						data: data
					},
					type: 'POST'
				}).done(function(resp) {
					
				});
			}
		});
	} else {
		//$("head").append(s);
		$.ajax({
			url: ajax_host,
			context: document.body,
			accepts: 'text/javascript',
			data: {
				callback: callback,
				object: theobject,
				event: theevent,
				sender: sender,
				data: data
			},
			type: 'POST'
		}).done(function(resp) {
			
		});
	}
}