jQuery(document).ready(function($){
	
	ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
	// input/select/textarea fields help text
	$('.has-help-text').popover();
	$(document).on('blur', '.has-help-text', function(e) {
		if ($(this).data('bs.popover')) {
			// this really doesn't want to behave correct unless forced this way!
			$(this).data('bs.popover').destroy();
			$('.popover').remove();
			$(this).popover();
		}
	});
	
	// buttons with loading state
	$('form button.btn-submit').button().on('click', function(){
		$(this).button('loading');
	});
	
});