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
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
    var $extraRecipientsTemplate    = $('#extra-recipients-template');
    if ($extraRecipientsTemplate.length) {
        var extraRecipientsCounter = $extraRecipientsTemplate.data('count');
        $('a.btn-add-extra-recipients').on('click', function(){
            var $html = $($extraRecipientsTemplate.html().replace(/__#__/g, extraRecipientsCounter));
            $('#extra-list-segment-container').append($html);
            $html.find('input, select').removeAttr('disabled');
            extraRecipientsCounter++;
            return false;
        });
        
        $(document).on('click', 'a.remove-extra-recipients', function(){
            $(this).closest('.form-group').remove();
            return false;
        });
        
        $(document).on('change', '#extra-list-segment-container .col-list select', function(){
            var list_id = $(this).val();

    		var $segments = $(this).closest('div.form-group').find('.col-segment select');
    		var url = $segments.data('url');
    		$segments.html('');
    		
    		if (!list_id) {
    			$segments.attr('disabled', true);
    			return;
    		}
    		
    		$.get(url, {list_id: list_id}, function(json){
    				
    			if (typeof json.segments == 'object' && json.segments.length > 0) {
    				for (var i in json.segments) {
    					$segments.append($('<option/>').val(json.segments[i].segment_id).html(json.segments[i].name));
    				}	
    			}
    			
    		}, 'json');
    		
    		$segments.removeAttr('disabled'); 
        });
    }

	$('#Campaign_list_id').on('change', function(){
		var list_id = $(this).val();

		var $segments = $('select#Campaign_segment_id');
		var url = $segments.data('url');
		$segments.html('');
		
		if (!list_id) {
			$('#Campaign_segment_id').attr('disabled', true);
			return;
		}
		
		$.get(url, {list_id: list_id}, function(json){
				
			if (typeof json.segments == 'object' && json.segments.length > 0) {
				for (var i in json.segments) {
					$segments.append($('<option/>').val(json.segments[i].segment_id).html(json.segments[i].name));
				}	
			}
			
		}, 'json');
		
		$('#Campaign_segment_id').removeAttr('disabled');
	});
	
	$('a.load-selected').on('click', function(){
		var $select = $('select#CustomerEmailTemplate_template_id');
		
		if ($select.val() == '') {
			alert('Please select a template first!');
			return false;
		}
		$('#selected_template_id').val($select.val());
		$(this).closest('form').submit();
		return false;
	});
	
    var $sendAt = $('#Campaign_send_at'), 
        $displaySendAt = $('#Campaign_sendAt'),
        $fakeSendAt = $('#fake_send_at');
	
    if ($sendAt.length && $displaySendAt.length && $fakeSendAt.length) {

        $fakeSendAt.datetimepicker({
			format: $fakeSendAt.data('date-format') || 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
            language: $fakeSendAt.data('language') || 'en',
            showMeridian: true
		}).on('changeDate', function(e) {
            syncDateTime();
		}).on('blur', function(){
            syncDateTime();
		});
        
        $displaySendAt.on('focus', function(){
            $('#fake_send_at').datetimepicker('show');
        });
        
        function syncDateTime() {
            var date = $fakeSendAt.val();
            if (!date) {
                return;
            }
            $displaySendAt.val('').addClass('spinner');
            $.get($fakeSendAt.data('syncurl'), {date: date}, function(json){
                $displaySendAt.removeClass('spinner');
                $displaySendAt.val(json.localeDateTime);
                $sendAt.val(json.utcDateTime);
            }, 'json');
        }
        syncDateTime();
	}

	$(document).on('click', 'a.pause-sending, a.unpause-sending', function() {
		if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
    $(document).on('click', 'a.copy-campaign', function() {
		$.post($(this).attr('href'), ajaxData, function(json) {
            if (typeof json.next == 'string' && json.next) {
                window.location.href = json.next;
                return;
            }
			window.location.reload();
		}, 'json');
		return false;
	});
    
    $(document).on('click', 'a.check-spam-score', function(){
        var $this = $(this), $parent = $this.closest('td');
        $parent.empty().text($this.data('message'));
        $.post($this.attr('href'), ajaxData, function(json){
            $parent.empty().text(json.message);
        }, 'json');
        return false;
    });
    
    $(document).on('click', 'a.resume-campaign-sending', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
    $(document).on('click', 'a.mark-campaign-as-sent', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
    $('a.btn-remove-attachment').on('click', function(){
        var $this = $(this);
        if (!confirm($this.data('message'))) {
			return false;
		}
        
        $this.closest('.form-group').fadeOut('slow', function(){
            $(this).remove();
        });
        
        $.post($this.attr('href'), ajaxData, function(){
			
		});
        return false;
    });
    
    $('button.btn-plain-text').on('click', function(){
        var $this = $(this), 
            $container = $('.plain-text-version');
        
        if ($('.template-click-actions-container').is(':visible')) {
            $('button.btn-template-click-actions').trigger('click');
        }
        
        if (!$container.is(':visible')){
            $container.slideDown('slow', function(){
                $this.text($this.data('hidetext'));
            });
            $container.find('textarea').eq(0).focus();
        } else {
            $container.slideUp('slow', function(){
                $this.text($this.data('showtext'));
            });
            $this.blur();
        }
        
        return false;
    });
    
    $('button.btn-template-click-actions').on('click', function(){
        var $this = $(this), 
            $container = $('.template-click-actions-container');
        
        if ($('.plain-text-version').is(':visible')) {
            $('button.btn-plain-text').trigger('click');
        }
        
        if (!$container.is(':visible')){
            $container.slideDown('slow');
        } else {
            $container.slideUp('slow');
            $this.blur();
        }
        
        return false;
    });
    
	$(document).on('click', 'a.btn-template-click-actions-remove', function(){
		if ($(this).data('url-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.template-click-actions-row').fadeOut('slow', function() {
            $('button.btn-template-click-actions span.count').text(parseInt($('button.btn-template-click-actions span.count').text()) - 1);
			$(this).remove();
		});
		return false;
	});
	
    $('a.btn-template-click-actions-add').on('click', function(){
		var currentIndex = -1;
		$('.template-click-actions-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#template-click-actions-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.template-click-actions-list').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		$('button.btn-template-click-actions span.count').text(parseInt($('button.btn-template-click-actions span.count').text()) + 1);
		return false;	
	});
    
    //
    $('button.btn-template-click-actions-list-fields').on('click', function(){
        var $this = $(this), 
            $container = $('.template-click-actions-list-fields-container');
        
        if ($('.plain-text-version').is(':visible')) {
            $('button.btn-plain-text').trigger('click');
        }
        
        if (!$container.is(':visible')){
            $container.slideDown('slow');
        } else {
            $container.slideUp('slow');
            $this.blur();
        }
        
        return false;
    });
    
	$(document).on('click', 'a.btn-template-click-actions-list-fields-remove', function(){
		if ($(this).data('url-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.template-click-actions-list-fields-row').fadeOut('slow', function() {
            $('button.btn-template-click-actions-list-fields span.count').text(parseInt($('button.btn-template-click-actions-list-fields span.count').text()) - 1);
			$(this).remove();
		});
		return false;
	});
	
    $('a.btn-template-click-actions-list-fields-add').on('click', function(){
		var currentIndex = -1;
		$('.template-click-actions-list-fields-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#template-click-actions-list-fields-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.template-click-actions-list-fields-list').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		$('button.btn-template-click-actions-list-fields span.count').text(parseInt($('button.btn-template-click-actions-list-fields span.count').text()) + 1);
		return false;	
	});
    //
    
	$(document).on('click', 'a.btn-campaign-open-actions-remove', function(){
		if ($(this).data('action-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.campaign-open-actions-row').fadeOut('slow', function() {
            $(this).remove();
		});
		return false;
	});
	
    $('a.btn-campaign-open-actions-add').on('click', function(){
		var currentIndex = -1;
		$('.campaign-open-actions-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#campaign-open-actions-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.campaign-open-actions-list').append($tpl);
		$tpl.find('.has-help-text').popover();
		return false;	
	});
    
    //
    $(document).on('click', 'a.btn-campaign-open-list-fields-actions-remove', function(){
		if ($(this).data('action-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.campaign-open-list-fields-actions-row').fadeOut('slow', function() {
            $(this).remove();
		});
		return false;
	});
	
    $('a.btn-campaign-open-list-fields-actions-add').on('click', function(){
		var currentIndex = -1;
		$('.campaign-open-list-fields-actions-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#campaign-open-list-fields-actions-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.campaign-open-list-fields-actions-list').append($tpl);
		$tpl.find('.has-help-text').popover();
		return false;	
	});
    //
    
    if ($('#CampaignOption_autoresponder_event').length) {
        $('#CampaignOption_autoresponder_event').on('change', function(){
            var $this = $(this);
            if ($this.val() == 'AFTER-CAMPAIGN-OPEN') {
                $('#CampaignOption_autoresponder_open_campaign_id').closest('div').fadeIn();
            } else {
                $('#CampaignOption_autoresponder_open_campaign_id').closest('div').fadeOut();
            }
        });
    }
	
    $('#CampaignTemplate_only_plain_text').on('change', function(){
        var $this = $(this);
        if ($this.val() == 'yes') {
            $('#CampaignTemplate_auto_plain_text').val('yes').closest('div').hide();
            $('.btn-plain-text').hide();
            $('#CampaignTemplate_content').closest('div').hide();
            $('#CampaignTemplate_plain_text').closest('div').show();
        } else {
            $('#CampaignTemplate_auto_plain_text').val('yes').closest('div').show();
            $('.btn-plain-text').show();
            $('#CampaignTemplate_plain_text').closest('div').hide();
            $('#CampaignTemplate_content').closest('div').show();
        }
    });
    
    if ($('.circliful-graph').length) {
        $('.circliful-graph').circliful();
    }
    
    // since 1.3.5.3
    if ($('#CampaignOption_cronjob').length && $.fn.jqCron != undefined) {
        $('#CampaignOption_cronjob').jqCron({
            enabled_minute: true,
            enabled_hour: true,
            multiple_dom: true,
            multiple_month: true,
            multiple_mins: true,
            multiple_dow: true,
            multiple_time_hours: true,
            multiple_time_minutes: true,
            no_reset_button: false,
            lang: $('#CampaignOption_cronjob').data('lang')
        });
        $('#CampaignOption_cronjob_enabled').on('change', function(){
            var $this = $(this);
            if (!$this.is(':checked')) {
                $('#CampaignOption_cronjob').closest('.jqcron-holder').find('.jqCron').css({visibility:'hidden'});
            } else {
                $('#CampaignOption_cronjob').closest('.jqcron-holder').find('.jqCron').css({visibility:'visible'});
            }
        }).trigger('change');
    }
});