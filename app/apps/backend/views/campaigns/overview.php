<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5.5
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
    <div class="box box-primary campaign-overview-pnl">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <span class="glyphicon glyphicon-envelope"></span> <?php echo $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right"></div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="callout callout-info">
                <?php echo Yii::t('campaigns', 'Please note that the stats are based only on your list confirmed subscribers count.');?> <br />
                <?php echo Yii::t('campaigns', 'The number of confirmed subscribers can change during a sendout, subscribers can unsubscribe, get blacklisted or report the email as spam, case in which actions are taken and those subscribers are not confirmed anymore.');?>
            </div>
            <div class="table-responsive">
            <?php
            $this->widget('zii.widgets.CDetailView', array(
                'data'      => $campaign,
                'cssFile'   => false,
                'htmlOptions' => array(
                    'class' => 'table table-striped table-bordered table-hover table-condensed'
                ),
                'attributes' => array(
                    'name',
                    array(
                        'label' => Yii::t('campaigns', 'Type'),
                        'value' => ucfirst(Yii::t('campaigns', $campaign->type)),
                    ),
                    array(
                        'label' => Yii::t('campaigns', 'List/Segment'),
                        'value' => $campaign->getListSegmentName(),
                    ),
                    'from_name', 'from_email', 'reply_to', 'to_name', 'subject',
                    array(
                        'label' => $campaign->getAttributeLabel('date_added'),
                        'value' => $campaign->dateAdded,
                    ),
                    array(
                        'label'     => $campaign->getAttributeLabel('send_at'),
                        'value'     => $campaign->sendAt,
                    ),
                    array(
                        'label'     => $campaign->getAttributeLabel('lastOpen'),
                        'value'     => $campaign->lastOpen,
                        'visible'   => $campaign->isRegular,
                    ),
                    array(
                        'label'     => $campaign->getAttributeLabel('started_at'),
                        'value'     => $campaign->startedAt,
                        'visible'   => $campaign->isRegular,
                    ),
                    array(
                        'label'     => $campaign->getAttributeLabel('finished_at'),
                        'value'     => $campaign->finishedAt,
                        'visible'   => $campaign->isRegular,
                    ),
                    array(
                        'label'     => $campaign->getAttributeLabel('totalDeliveryTime'),
                        'value'     => $campaign->totalDeliveryTime,
                        'visible'   => $campaign->isRegular,
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Filtered sent to'),
                        'value'     => $campaign->option->getRegularOpenUnopenDisplayText(),
                        'visible'   => $campaign->option->getRegularOpenUnopenDisplayText(),
                        'type'      => 'raw',
                    ),
                    array(
                        'label' => Yii::t('campaigns', 'Web version'),
                        'value' => CHtml::link(Yii::t('app', 'View'), $webVersionUrl, array('target' => '_blank')),
                        'type'  => 'raw',
                    ),
                    array(
                        'label' => Yii::t('campaigns', 'Forwards'),
                        'value' => $campaign->countForwards(),
                        'type'  => 'raw',
                    ),
                    array(
                        'label' => Yii::t('campaigns', 'Abuse reports'),
                        'value' => $campaign->countAbuseReports(),
                        'type'  => 'raw',
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Recurring'),
                        'value'     => !empty($recurringInfo) ? $recurringInfo : Yii::t('app', 'No'),
                        'visible'   => !empty($recurringInfo),
                    ),
                    array(
                        'label' => Yii::t('campaigns', 'Estimated completition rate'),
                        'value' => Yii::t('campaigns', '{percentage}% sent so far, that is {processed} out of {count}', array(
                            '{percentage}'  => $campaign->stats->getCompletitionRate(true),
                            '{processed}'   => $campaign->stats->getProcessedCount(true),
                            '{count}'       => $campaign->stats->getSubscribersCount(true),
                        )),
                        'visible'   => $campaign->isRegular && !empty($campaign->stats->completitionRate),
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Autoresponder event'),
                        'value'     => Yii::t('campaigns', $campaign->option->autoresponder_event),
                        'visible'   => $campaign->isAutoresponder,
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Autoresponder time unit'),
                        'value'     => ucfirst(Yii::t('app', $campaign->option->autoresponder_time_unit)),
                        'visible'   => $campaign->isAutoresponder,
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Autoresponder time value'),
                        'value'     => $campaign->option->autoresponder_time_value,
                        'visible'   => $campaign->isAutoresponder,
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Campaign to send for'),
                        'value'     => !empty($campaign->option->autoresponder_open_campaign_id) ? $campaign->option->autoresponderOpenCampaign->name : null,
                        'visible'   => $campaign->isAutoresponder && !empty($campaign->option->autoresponder_open_campaign_id),
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Current opens count for target campaign'),
                        'value'     => !empty($campaign->option->autoresponder_open_campaign_id) ? (int)$campaign->option->autoresponderOpenCampaign->getUniqueOpensCount(true) : null,
                        'visible'   => $campaign->isAutoresponder && !empty($campaign->option->autoresponder_open_campaign_id),
                    ),
                    array(
                        'label'     => Yii::t('campaigns', 'Include imported subscribers'),
                        'value'     => ucfirst(Yii::t('app', $campaign->option->autoresponder_include_imported)),
                        'visible'   => $campaign->isAutoresponder,
                    ),
                ),
            ));
            ?>
            </div>
            <hr />
            <div class="pull-left">
                <h4><?php echo Yii::t('campaigns', 'Tracking stats');?></h4>
            </div>
            <div class="pull-right"></div>
            <div class="clearfix"><!-- --></div>
            <?php 
            $this->renderPartial('stats/delivery');
            $this->renderPartial('stats/opens');
            
            if ($campaign->option->url_tracking == CampaignOption::TEXT_YES) {
                $this->renderPartial('stats/clicks');
            }
            
            $this->renderPartial('stats/bounces');
            $this->renderPartial('stats/unsubscribes');

            $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingLatestClickedLinksWidget', array(
                'campaign'        => $campaign,
                'showDetailLinks' => false,
            ));
            
            $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingLatestOpensWidget', array(
                'campaign'        => $campaign,
                'showDetailLinks' => false,
            ));

            $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingTopClickedLinksWidget', array(
                'campaign'        => $campaign,
                'showDetailLinks' => false,
            ));
            
            $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingSubstribersWithMostOpensWidget', array(
                'campaign'        => $campaign,
                'showDetailLinks' => false,
            ));
            ?>
            <div class="clearfix"><!-- --></div>
            <?php 
                // hook available since version 1.2
                $hooks->doAction('customer_campaigns_overview_after_tracking_stats', new CAttributeCollection(array(
                    'controller' => $this,
                )));
            ?>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
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