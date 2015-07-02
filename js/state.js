$(function() {
	$('.table-sort').click(function(e) {
		e.preventDefault();
		var sort = $(this).attr('data-col');
		$('#index_form').attr('action', document.URL).append('<input type="hidden" name="sort" value="'+sort+'" />').submit();
	});
});

//var sessionState = null;

var ajaxobj;

function cp_ajax(callback, theobject, theevent) {
	ajaxobj = $.ajax({
		url: ajax_host,
		context: document.body,
		accepts: 'text/html',
		data: {
			callback: callback,
			event: theevent,
			object: theobject
		}
	}).done(function(data) {
		$('div[name="'+callback+'"]').html(data);
	});
}

function cp_state(callback, theobject, sender, theevent, data, getresponse, primaryevent) {
	var s = document.createElement("script");
	//s.type = "text/javascript";
	//s.id = "callback_" + callback;
	//s.src = state_host;// + "?callback="+callback+"&object=" + theobject + "&event=" + theevent;
	//if (data) s.src += "&data=" + data;
	//if (sender) s.src += "&sender=" + sender;
	if (getresponse && data !== false) {
		if(ajaxobj) ajaxobj.abort();
		ajaxobj = $.ajax({
			url: state_host,
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
				//var hst = state_host + "?callback="+callback+"&object=" + sessionState + "&event=change";
					if(ajaxobj) ajaxobj.abort();
					ajaxobj = $.ajax({
					url: state_host,
					context: document.body,
					accepts: 'text/plain',
					data: {
						callback: callback,
						object: sessionState,
						event: primaryevent,
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
		if(ajaxobj) ajaxobj.abort();
		ajaxobj = $.ajax({
			url: state_host,
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