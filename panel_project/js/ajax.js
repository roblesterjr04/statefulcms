$(function() {
	performAsyncGet();
	$('.ajax-save').each(function() {
		$(this).click(function( event ) {
			$('.alerts').remove();
			event.preventDefault();
			var txt = $(this).attr('value');
			$(this).attr('value', 'Please Wait...');
			$(this).prop('disabled', true);
			var button = $(this);
			var this_form = $(this).closest('form');
			var action = $(this_form).attr('action');
			var formid = $(this_form).attr('id');
			var form_vals = $(this_form).serialize();
			$.ajax({
				url: action,
				context: document.body,
				type: 'POST',
				data: form_vals
			}).done(function( data ) {
				var post_form = null;
				if (formid) post_form = $(data).find('form#'+formid);
				else post_form = $(data).find('form');
				//this_form.replaceWith(post_form);
				$(post_form).find('.alerts').each(function() {
					$(this_form).prepend($(this).hide().slideDown('fast'));
				});
				button.prop('disabled', false);
				button.attr('value', txt);
			});
		});
	});
	//$('.ajax-change').each(function() {
		$('.ajax-changed').change(function() {
			var d = $(this).serialize();
			var this_form = $(this).closest('form');
			var formid = $(this_form).attr('id');
			var action = $(this_form).attr('action');
			$.ajax({
				url: action,
				context: document.body,
				type: 'POST',
				data: d
			}).done(function ( data ) {
				var post_form = null;
				if (formid) post_form = $(data).find('form#'+formid);
				else post_form = $(data).find('form');
				this_form.replaceWith(post_form);
				performAsyncGet();
			});
		});
	//});
});

function performAsyncGet() {
	$('.ajax-place').each(function() {
		var place = $(this);
		$.ajax({
			url: "models/ajax.php?func="+place.attr('data-func'),
			context: document.body
		}).done(function( data ) {
			place.html( data ).hide().fadeIn('slow');
		});
	});
}