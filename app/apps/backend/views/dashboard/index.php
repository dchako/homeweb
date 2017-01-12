<?php defined('MW_PATH') || exit('No direct script access allowed');

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

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    ?>
    <div class="box box-primary" id="glance-box" data-source="<?php echo $this->createUrl('dashboard/glance');?>">
        <div class="box-header" id="chatter-header">
            <h3 class="box-title"><i class="ion ion-information-circled"></i> <?php echo Yii::t('dashboard', 'At a glance');?></h3>
            <div class="box-tools pull-right"></div>
        </div>
        <div class="box-body">
            <div class="clearfix"><!-- --></div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 data-bind="text: glance.customersCount"></h3>
                        <p><?php echo Yii::t('dashboard', 'Customers');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="<?php echo $this->createUrl('customers/index');?>" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'More info');?> <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 data-bind="text: glance.campaignsCount"></h3>
                        <p><?php echo Yii::t('dashboard', 'Campaigns');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-email-outline"></i>
                    </div>
                    <a href="<?php echo $this->createUrl('campaigns/index');?>" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'More info');?> <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 data-bind="text: glance.listsCount"></h3>
                        <p><?php echo Yii::t('dashboard', 'Lists');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-clipboard"></i>
                    </div>
                    <a href="javascript:;" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'No detailed info');?> <i class="fa fa-exclamation-circle"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3 data-bind="text: glance.segmentsCount"></h3>
                        <p><?php echo Yii::t('dashboard', 'Segments');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-gear-b"></i>
                    </div>
                    <a href="javascript:;" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'No detailed info');?> <i class="fa fa-exclamation-circle"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3 data-bind="text: glance.subscribersCount"></h3>
                        <p><?php echo Yii::t('dashboard', 'Unique subscribers');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-people"></i>
                    </div>
                    <a href="javascript:;" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'No detailed info');?> <i class="fa fa-exclamation-circle"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3 data-bind="text: glance.allSubscribersCount"></h3>
                        <p><?php echo Yii::t('dashboard', 'Total subscribers');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-people"></i>
                    </div>
                    <a href="javascript:;" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'No detailed info');?> <i class="fa fa-exclamation-circle"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 data-bind="text: glance.deliveryServers"></h3>
                        <p><?php echo Yii::t('dashboard', 'Delivery servers');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-paper-airplane"></i>
                    </div>
                    <a href="<?php echo $this->createUrl('delivery_servers/index');?>" class="small-box-footer">
                        <?php echo Yii::t('dashboard', 'More info');?> <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="clearfix"><!-- --></div>    
        </div>
        <div class="overlay" data-bind="visible: glance.loading"></div>
        <div class="loading-img" data-bind="visible: glance.loading"></div>
    </div>
    <section class="col-lg-6 no-margin-left" id="chatter-box" data-source="<?php echo $this->createUrl('dashboard/chatter');?>" data-deleteall="<?php echo $this->createUrl('dashboard/delete_logs');?>">
        <div class="box box-primary">
            <div class="box-header" id="chatter-header">
                <h3 class="box-title"><i class="fa fa-bullhorn"></i> <?php echo Yii::t('dashboard', 'Recent customer activity');?></h3>
                <div class="box-tools pull-right">
                    <a class="btn btn-primary btn-xs" data-bind="click: chatter.load"><i class="fa fa-refresh"></i> <?php echo Yii::t('app', 'Refresh');?></a>
                    <a class="btn btn-danger btn-xs" data-bind="click: chatter.removeAll" id="chatter-remove-all" data-confirm="<?php echo Yii::t('dashboard', 'Are you sure you want to remove all activity messages?');?>"><i class="fa fa-times-circle-o"></i> <?php echo Yii::t('app', 'Delete all');?></a>
                </div>
            </div>
            <div class="box-body" style="height: 390px;">
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12" id="chatter">
                    <ul class="timeline" data-bind="foreach: { data: chatter.days, as: 'day' }">
                        <li class="time-label"><span data-bind="css: $root.chatter.randomTimeClass, text: day.date"></span></li>
                        <!-- ko foreach: { data: items, as: 'item' } -->
                        <li>
                            <i data-bind="css: $root.chatter.randomIconBg"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> <span data-bind="text: time"></span> <a data-bind="click: $root.chatter.remove" class="btn btn-danger btn-xs"><?php echo Yii::t('app', 'Delete')?></a></span>
                                <h3 class="timeline-header"><a data-bind="text: customerName, attr: {href: customerUrl}"></a></h3>
                                <div class="timeline-body" data-bind="html: message"></div>
                            </div>
                        </li>
                        <!-- /ko -->
                    </ul>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="overlay" data-bind="visible: chatter.loading"></div>
            <div class="loading-img" data-bind="visible: chatter.loading"></div>
            <div class="clearfix"><!-- --></div>
        </div>
    </section>
    <section class="col-lg-6" id="subscribers-growth-box" data-source="<?php echo $this->createUrl('dashboard/subscribers_growth');?>">
        <div class="box box-primary">
            <div class="box-header" id="subscribers-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('dashboard', 'Subscribers growth');?></h3>
                <div class="box-tools pull-right">
                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="left" title="<?php echo Yii::t('app', 'The information is refreshed once at {n} minutes.', 10);?>"><span class="fa fa-info-circle"></span></a>
                </div>
            </div>
            <div class="box-body" style="height: 390px;">
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12" id="subscribers">
                    <div id="subscribers-growth-chart" style="height: 350px;"></div>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="overlay" data-bind="visible: subscribersGrowthChart.loading"></div>
            <div class="loading-img" data-bind="visible: subscribersGrowthChart.loading"></div>
            <div class="clearfix"><!-- --></div>
        </div>
    </section>
    <div class="clearfix"><!-- --></div>
    <section class="col-lg-6 no-margin-left" id="lists-growth-box" data-source="<?php echo $this->createUrl('dashboard/lists_growth');?>">
        <div class="box box-primary">
            <div class="box-header" id="lists-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('dashboard', 'Lists growth');?></h3>
                <div class="box-tools pull-right">
                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="left" title="<?php echo Yii::t('app', 'The information is refreshed once at {n} minutes.', 10);?>"><span class="fa fa-info-circle"></span></a>
                </div>
            </div>
            <div class="box-body" style="height: 390px;">
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12" id="lists">
                    <div id="lists-growth-chart" style="height: 350px;"></div>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="overlay" data-bind="visible: listsGrowthChart.loading"></div>
            <div class="loading-img" data-bind="visible: listsGrowthChart.loading"></div>
            <div class="clearfix"><!-- --></div>
        </div>
    </section>
    <section class="col-lg-6" id="campaigns-growth-box" data-source="<?php echo $this->createUrl('dashboard/campaigns_growth');?>">
        <div class="box box-primary">
            <div class="box-header" id="campaigns-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('dashboard', 'Campaigns growth');?></h3>
                <div class="box-tools pull-right">
                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="left" title="<?php echo Yii::t('app', 'The information is refreshed once at {n} minutes.', 10);?>"><span class="fa fa-info-circle"></span></a>
                </div>
            </div>
            <div class="box-body" style="height: 390px;">
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12" id="campaigns">
                    <div id="campaigns-growth-chart" style="height: 350px;"></div>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="overlay" data-bind="visible: campaignsGrowthChart.loading"></div>
            <div class="loading-img" data-bind="visible: campaignsGrowthChart.loading"></div>
            <div class="clearfix"><!-- --></div>
        </div>
    </section>
    <div class="clearfix"><!-- --></div>
    <section class="col-lg-6 no-margin-left" id="deliverybounce-growth-box" data-source="<?php echo $this->createUrl('dashboard/delivery_bounce_growth');?>">
        <div class="box box-primary">
            <div class="box-header" id="deliverybounce-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('dashboard', 'Delivery vs Bounces');?></h3>
                <div class="box-tools pull-right">
                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="left" title="<?php echo Yii::t('app', 'The information is refreshed once at {n} minutes.', 10);?>"><span class="fa fa-info-circle"></span></a>
                </div>
            </div>
            <div class="box-body" style="height: 390px;">
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12" id="deliverybounce">
                    <div id="deliverybounce-growth-chart" style="height: 350px;"></div>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="overlay" data-bind="visible: deliveryBounceGrowthChart.loading"></div>
            <div class="loading-img" data-bind="visible: deliveryBounceGrowthChart.loading"></div>
            <div class="clearfix"><!-- --></div>
        </div>
    </section>
    <section class="col-lg-6" id="unsubscribe-growth-box" data-source="<?php echo $this->createUrl('dashboard/unsubscribe_growth');?>">
        <div class="box box-primary">
            <div class="box-header" id="unsubscribe-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('dashboard', 'Unsubscribe growth');?></h3>
                <div class="box-tools pull-right">
                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="left" title="<?php echo Yii::t('app', 'The information is refreshed once at {n} minutes.', 10);?>"><span class="fa fa-info-circle"></span></a>
                </div>
            </div>
            <div class="box-body" style="height: 390px;">
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12" id="unsubscribe">
                    <div id="unsubscribe-growth-chart" style="height: 350px;"></div>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="overlay" data-bind="visible: unsubscribeGrowthChart.loading"></div>
            <div class="loading-img" data-bind="visible: unsubscribeGrowthChart.loading"></div>
            <div class="clearfix"><!-- --></div>
        </div>
    </section>
    <div class="clearfix" id="dashboard-update" data-checkupdateenabled="<?php echo (int)$checkVersionUpdate;?>" data-checkupdateurl="<?php echo $this->createUrl('dashboard/check_update');?>"><!-- --></div>
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));