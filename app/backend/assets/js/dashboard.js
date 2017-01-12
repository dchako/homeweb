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
    
    $dashboardUpdate = $('#dashboard-update');
    if ($dashboardUpdate.length && $dashboardUpdate.data('checkupdateenabled')) {
        var rand = Math.floor((Math.random()*100)+1);
        if (rand > 30) {
            $.get($dashboardUpdate.data('checkupdateurl'));
        }    
    }

    $("#chatter").slimscroll({
        height: "350px",
        alwaysVisible: false,
        size: "3px"
    }).css("width","100%");
    
    function pickRandomBackground() {
        var backgrounds = ['gray', 'black', 'red', 'yellow', 'aqua', 'blue', 'light-blue', 'green', 'navy', 'teal', 'olive', 'lime', 'fuchsia', 'purple', 'maroon'];
        var rand = Math.floor((Math.random() * backgrounds.length) + 1);
        return 'bg-' + backgrounds[rand];
    }
    
    var _koHandlersInit = function(){
        var self = this;
        
        var glance = function(){
            var self = this;
            self.days    = ko.observableArray([]);
            self.loading = ko.observable(false);
            
            self.customersCount     = ko.observable(0);
            self.listsCount         = ko.observable(0);
            self.subscribersCount   = ko.observable(0);
            self.allSubscribersCount= ko.observable(0);
            self.deliveryServers    = ko.observable(0);
            self.campaignsCount     = ko.observable(0);
            self.segmentsCount      = ko.observable(0);
            
            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#glance-box').data('source'), {}, function(json){
                    self.customersCount(json.customers);
                    self.listsCount(json.lists);
                    self.subscribersCount(json.subscribers);
                    self.allSubscribersCount(json.allSubscribers);
                    self.deliveryServers(json.deliveryServers);
                    self.campaignsCount(json.campaigns);
                    self.segmentsCount(json.segments);
                    self.loading(false);
                });
            };

            self.load();
        };
        
        var chatter = function(){
            var self = this;
            self.days    = ko.observableArray([]);
            self.loading = ko.observable(false);
            
            self.randomIconBg = ko.computed(function(){
                return 'fa fa-comment ' + pickRandomBackground();
            });
            
            self.randomTimeClass = ko.computed(function(){
                return pickRandomBackground();
            });

            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#chatter-box').data('source'), {}, function(json){
                    self.days(json);
                    self.loading(false);
                });
            };
            
            self.remove = function() {
                self.loading(true);
                $.post(this.deleteUrl, ajaxData, function(){
                    self.load(true);
                });
            };
            
            self.removeAll = function() {
                if (!confirm($('#chatter-remove-all').data('confirm'))) {
        			return false;
        		}
                self.loading(true);
                $.post($('#chatter-box').data('deleteall'), ajaxData, function(){
                    self.load(true);
                });
            }
            
            self.load();
        };
        
        var subscribersGrowthChart = function(){
            var self = this, plot = false;
            self.loading    = ko.observable(false);
            self.chartData  = ko.observableArray([]);
            
            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#subscribers-growth-box').data('source'), {}, function(json){
                    self.chartData(json);
                    if (!plot) {
                        _initPlot();
                    }
                    self.loading(false);
                });
            };
            self.load();
            
            function _initPlot() {
                plot = $.plot("#subscribers-growth-chart", [self.chartData()], {
                    grid: {
                        borderWidth: 1,
                        borderColor: "#f3f3f3",
                        tickColor: "#f3f3f3",
                        hoverable: true, 
                        clickable: true
                    },
                    series: {
                        bars: {
                            show: true,
                            barWidth: 0.95,
                            align: "center"
                        }
                    },
                    xaxis: {
                        mode: "categories",
                        tickLength: 0
                    }
                });
    
                $("<div class='tooltip-inner' id='subscribers-growth-chart-tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    opacity: 0.8
                }).appendTo("body");
                
                $("#subscribers-growth-chart").bind("plothover", function(event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0], 
                            y = item.datapoint[1];
                        $("#subscribers-growth-chart-tooltip")
                            .html(y)
                            .css({top: item.pageY + 5, left: item.pageX + 5})
                            .fadeIn(200);
                    } else {
                        $("#subscribers-growth-chart-tooltip").hide();
                    }
                }); 
            }
        };
        
        var listsGrowthChart = function(){
            var self = this, plot = false;
            self.loading    = ko.observable(false);
            self.chartData  = ko.observableArray([]);
            
            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#lists-growth-box').data('source'), {}, function(json){
                    self.chartData(json);
                    if (!plot) {
                        _initPlot();
                    }
                    self.loading(false);
                });
            };
            self.load();
            
            function _initPlot() {
                plot = $.plot("#lists-growth-chart", [self.chartData()], {
                    grid: {
                        borderWidth: 1,
                        borderColor: "#f3f3f3",
                        tickColor: "#f3f3f3",
                        hoverable: true, 
                        clickable: true
                    },
                    series: {
                        bars: {
                            show: true,
                            barWidth: 0.95,
                            align: "center"
                        }
                    },
                    xaxis: {
                        mode: "categories",
                        tickLength: 0
                    }
                });
    
                $("<div class='tooltip-inner' id='lists-growth-chart-tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    opacity: 0.8
                }).appendTo("body");
                
                $("#lists-growth-chart").bind("plothover", function(event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0], 
                            y = item.datapoint[1];
                        $("#lists-growth-chart-tooltip")
                            .html(y)
                            .css({top: item.pageY + 5, left: item.pageX + 5})
                            .fadeIn(200);
                    } else {
                        $("#lists-growth-chart-tooltip").hide();
                    }
                }); 
            }
        };
        
        var campaignsGrowthChart = function(){
            var self = this, plot = false;
            self.loading    = ko.observable(false);
            self.chartData  = ko.observableArray([]);
            
            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#campaigns-growth-box').data('source'), {}, function(json){
                    self.chartData(json);
                    if (!plot) {
                        _initPlot();
                    }
                    self.loading(false);
                });
            };
            self.load();
            
            function _initPlot() {
                plot = $.plot("#campaigns-growth-chart", [self.chartData()], {
                    grid: {
                        borderWidth: 1,
                        borderColor: "#f3f3f3",
                        tickColor: "#f3f3f3",
                        hoverable: true, 
                        clickable: true
                    },
                    series: {
                        bars: {
                            show: true,
                            barWidth: 0.95,
                            align: "center"
                        }
                    },
                    xaxis: {
                        mode: "categories",
                        tickLength: 0
                    }
                });
    
                $("<div class='tooltip-inner' id='campaigns-growth-chart-tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    opacity: 0.8
                }).appendTo("body");
                
                $("#campaigns-growth-chart").bind("plothover", function(event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0], 
                            y = item.datapoint[1];
                        $("#campaigns-growth-chart-tooltip")
                            .html(y)
                            .css({top: item.pageY + 5, left: item.pageX + 5})
                            .fadeIn(200);
                    } else {
                        $("#campaigns-growth-chart-tooltip").hide();
                    }
                }); 
            }
        };
        
        var deliveryBounceGrowthChart = function(){
            var self = this, plot = false;
            self.loading    = ko.observable(false);
            self.chartData  = ko.observableArray([]);
            
            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#deliverybounce-growth-box').data('source'), {}, function(json){
                    self.chartData(json);
                    if (!plot) {
                        _initPlot();
                    }
                    self.loading(false);
                });
            };
            self.load();
            
            var sin = [], cos = [];
                for (var i = 0; i < 14; i += 0.5) {
                    sin.push([i, Math.sin(i)]);
                    cos.push([i, Math.cos(i)]);
                }
                var line_data1 = {
                    data: sin,
                    color: "#3c8dbc"
                };
                var line_data2 = {
                    data: cos,
                    color: "#00c0ef"
                };
                
            function _initPlot() {
                plot = $.plot("#deliverybounce-growth-chart", self.chartData(), {
                    grid: {
                        hoverable: true,
                        borderColor: "#f3f3f3",
                        borderWidth: 1,
                        tickColor: "#f3f3f3"
                    },
                    series: {
                        shadowSize: 0,
                        lines: {
                            show: true
                        },
                        points: {
                            show: true
                        }
                    },
                    lines: {
                        fill: false,
                        color: ["#3c8dbc", "#ff0000"]
                    },
                    yaxis: {
                        show: true,
                    },
                    xaxis: {
                        mode: "categories",
                        tickLength: 0,
                        show:true
                    }
                });
    
                $("<div class='tooltip-inner' id='deliverybounce-growth-chart-tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    opacity: 0.8
                }).appendTo("body");
                
                $("#deliverybounce-growth-chart").bind("plothover", function(event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0], 
                            y = item.datapoint[1];
                        $("#deliverybounce-growth-chart-tooltip")
                            .html(y)
                            .css({top: item.pageY + 5, left: item.pageX + 5})
                            .fadeIn(200);
                    } else {
                        $("#deliverybounce-growth-chart-tooltip").hide();
                    }
                }); 
            }
        };
        
        var unsubscribeGrowthChart = function(){
            var self = this, plot = false;
            self.loading    = ko.observable(false);
            self.chartData  = ko.observableArray([]);
            
            self.load = function(forced){
                if (forced !== true && self.loading()) {
                    return;
                }
                if (!self.loading()) {
                    self.loading(true);
                }
                $.getJSON($('#unsubscribe-growth-box').data('source'), {}, function(json){
                    self.chartData(json);
                    if (!plot) {
                        _initPlot();
                    }
                    self.loading(false);
                });
            };
            self.load();
            
            function _initPlot() {
                plot = $.plot("#unsubscribe-growth-chart", [self.chartData()], {
                    grid: {
                        borderWidth: 1,
                        borderColor: "#f3f3f3",
                        tickColor: "#f3f3f3",
                        hoverable: true, 
                        clickable: true
                    },
                    series: {
                        bars: {
                            show: true,
                            barWidth: 0.95,
                            align: "center"
                        }
                    },
                    xaxis: {
                        mode: "categories",
                        tickLength: 0
                    }
                });
    
                $("<div class='tooltip-inner' id='unsubscribe-growth-chart-tooltip'></div>").css({
                    position: "absolute",
                    display: "none",
                    opacity: 0.8
                }).appendTo("body");
                
                $("#unsubscribe-growth-chart").bind("plothover", function(event, pos, item) {
                    if (item) {
                        var x = item.datapoint[0], 
                            y = item.datapoint[1];
                        $("#unsubscribe-growth-chart-tooltip")
                            .html(y)
                            .css({top: item.pageY + 5, left: item.pageX + 5})
                            .fadeIn(200);
                    } else {
                        $("#unsubscribe-growth-chart-tooltip").hide();
                    }
                }); 
            }
        };
        
        self.glance                     = new glance();
        self.chatter                    = new chatter();
        self.subscribersGrowthChart     = new subscribersGrowthChart();
        self.listsGrowthChart           = new listsGrowthChart();
        self.campaignsGrowthChart       = new campaignsGrowthChart();
        self.deliveryBounceGrowthChart  = new deliveryBounceGrowthChart();
        self.unsubscribeGrowthChart     = new unsubscribeGrowthChart();
    };
    var koHandlers = new _koHandlersInit();
    ko.applyBindings(koHandlers);
});