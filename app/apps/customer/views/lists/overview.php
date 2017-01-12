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
if ($viewCollection->renderContent) { ?>
    <div class="box box-primary">
        <div class="box-header" id="chatter-header">
            <h3 class="box-title"><i class="glyphicon glyphicon-list-alt"></i> <?php echo Yii::t('lists', 'Overview');?></h3>
            <div class="box-tools pull-right">
                <?php echo CHtml::link(Yii::t('app', 'Create new'), array('lists/create'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'New')));?>
                <?php echo CHtml::link(Yii::t('app', 'Update'), array('lists/update', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Update')));?>
            </div>
        </div>
        <div class="box-body">
            <div class="clearfix"><!-- --></div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo Yii::app()->format->formatNumber($subscribersCount);?></h3>
                        <p><?php echo Yii::t('list_subscribers', 'Subscribers');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-people"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left">
                            &nbsp;<a href="<?php echo Yii::app()->createUrl("list_subscribers/create", array("list_uid" => $list->list_uid));?>" class="btn bg-red btn-flat btn-xs"><span class="glyphicon glyphicon-plus-sign"></span> <?php echo Yii::t('app', 'Add');?></a>
                        </div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_subscribers/index", array("list_uid" => $list->list_uid));?>" class="btn bg-red btn-flat btn-xs"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                    
                </div>
            </div>
            <?php if (!empty($canSegmentLists)) { ?>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3><?php echo Yii::app()->format->formatNumber($segmentsCount);?></h3>
                        <p><?php echo Yii::t('list_segments', 'Segments');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-gear-b"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left">
                            &nbsp;<a href="<?php echo Yii::app()->createUrl("list_segments/create", array("list_uid" => $list->list_uid));?>" class="btn bg-purple btn-flat btn-xs"><span class="glyphicon glyphicon-plus-sign"></span> <?php echo Yii::t('app', 'Add');?></a>
                        </div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_segments/index", array("list_uid" => $list->list_uid));?>" class="btn bg-purple btn-flat btn-xs"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo Yii::app()->format->formatNumber($customFieldsCount);?></h3>
                        <p><?php echo Yii::t('list_fields', 'Custom fields');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-android-storage"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_fields/index", array("list_uid" => $list->list_uid));?>" class="btn bg-yellow btn-flat btn-xs"><span class="glyphicon glyphicon-cog"></span> <?php echo Yii::t('app', 'Manage');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo Yii::app()->format->formatNumber($pagesCount);?></h3>
                        <p><?php echo Yii::t('list_pages', 'Pages');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-folder"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_page/index", array("list_uid" => $list->list_uid, 'type' => 'subscribe-form'));?>" class="btn bg-aqua btn-flat btn-xs"><span class="glyphicon glyphicon-cog"></span> <?php echo Yii::t('app', 'Manage');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-olive">
                    <div class="inner">
                        <h3><?php echo Yii::t('list_forms', 'Forms');?></h3>
                        <p><?php echo Yii::t('app', 'Tools');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-photos"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_forms/index", array("list_uid" => $list->list_uid));?>" class="btn bg-olive btn-flat btn-xs"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3><?php echo Yii::t('lists', 'Tools');?></h3>
                        <p><?php echo Yii::t('lists', 'List tools');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-hammer"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_tools/index", array("list_uid" => $list->list_uid));?>" class="btn bg-teal btn-flat btn-xs"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <div class="clearfix"><!-- --></div>    
        </div>
    </div>
    <section class="no-margin-left col-lg-6" id="subscribers-growth-box" data-source="<?php echo $this->createUrl('lists/subscribers_growth', array('list_uid' => $list->list_uid));?>">
        <div class="box box-primary">
            <div class="box-header" id="subscribers-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('lists', 'Subscribers growth');?></h3>
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
    <section class="col-lg-6" id="campaigns-growth-box" data-source="<?php echo $this->createUrl('lists/campaigns_growth', array('list_uid' => $list->list_uid));?>">
        <div class="box box-primary">
            <div class="box-header" id="campaigns-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('lists', 'Campaigns growth');?></h3>
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
    <section class="col-lg-6 no-margin-left" id="deliverybounce-growth-box" data-source="<?php echo $this->createUrl('lists/delivery_bounce_growth', array('list_uid' => $list->list_uid));?>">
        <div class="box box-primary">
            <div class="box-header" id="deliverybounce-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('lists', 'Delivery vs Bounces');?></h3>
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
    <section class="col-lg-6" id="unsubscribe-growth-box" data-source="<?php echo $this->createUrl('lists/unsubscribe_growth', array('list_uid' => $list->list_uid));?>">
        <div class="box box-primary">
            <div class="box-header" id="unsubscribe-growth-header">
                <h3 class="box-title"><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('lists', 'Unsubscribe growth');?></h3>
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