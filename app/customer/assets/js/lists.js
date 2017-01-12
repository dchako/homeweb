/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */
jQuery(document).ready(function($){

	// company start
	$('select#ListCompany_country_id').on('change', function() {
		var url = $(this).data('zones-by-country-url'), 
			countryId = $(this).val(),
			$zones = $('select#ListCompany_zone_id');
		
		if (url) {
			var formData = {
				country_id: countryId
			}

			$.get(url, formData, function(json){
				$zones.html('');
				if (typeof json.zones == 'object' && json.zones.length > 0) {
					for (var i in json.zones) {
						$zones.append($('<option/>').val(json.zones[i].zone_id).html(json.zones[i].name));
					}	
				}
			}, 'json');
			
		}
	});
	// company end
	
    $(document).on('click', 'a.copy-list', function() {
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
	
	$('.list-subscriber-actions-scrollbox input[type=checkbox]').on('click', function(){
		var $this = $(this), $wrapper = $this.closest('.list-subscriber-actions-scrollbox');
		if ($this.val() == 0) {
            if ($this.is(':checked')) {
                $('input[type=checkbox]', $wrapper).not($this).each(function() {
                    if (!$(this).is(':checked')) {
                        $(this).click();
                    }
                });
            } else {
                $('input[type=checkbox]', $wrapper).not($this).each(function() {
                    if ($(this).is(':checked')) {
                        $(this).click();
                    }
                });
            }
        }
	});
    
});