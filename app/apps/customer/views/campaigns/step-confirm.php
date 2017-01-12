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
    if ($campaign->hasErrors()) { ?>
    <div class="alert alert-block alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo CHtml::errorSummary($campaign);?>
    </div>
    <?php 
    }
    
    /**
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false 
     * in order to stop rendering the default content.
     * @since 1.3.3.1
     */    
    $hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
        'controller'    => $this,
        'renderForm'    => true,
    )));
    
    // and render if allowed
    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary">
            <div class="box-header">
                <div class="pull-left">
                    <h3 class="box-title">
                        <span class="glyphicon glyphicon-envelope"></span> <?php echo $pageHeading;?>
                    </h3>
                </div>
                <div class="pull-right">
                    <?php echo CHtml::link(Yii::t('app', 'Cancel'), array('campaigns/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Cancel')));?>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-body">
                <?php 
                /**
                 * This hook gives a chance to prepend content before the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                $hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
                <div class="clearfix"><!-- --></div>
                <div class="form-group col-lg-3">
                    <?php echo $form->labelEx($campaign, 'send_at');?>
                    <?php echo $form->hiddenField($campaign, 'send_at', $campaign->getHtmlOptions('send_at')); ?>
                    <?php echo $form->textField($campaign, 'sendAt', $campaign->getHtmlOptions('send_at')); ?>
                    <?php echo CHtml::textField('fake_send_at', $campaign->dateTimeFormatter->formatDateTime($campaign->send_at), array(
                        'data-date-format'  => 'yyyy-mm-dd hh:ii:ss', 
                        'data-autoclose'    => true, 
                        'data-language'     => LanguageHelper::getAppLanguageCode(),
                        'data-syncurl'      => $this->createUrl('campaigns/sync_datetime'),
                        'class'             => 'form-control',
                        'style'             => 'visibility:hidden; height:1px; margin:0; padding:0;',
                    )); ?>
                    <?php echo $form->error($campaign, 'send_at');?>
                </div>
                <?php if (MW_COMPOSER_SUPPORT && $campaign->isRegular) { ?>
                <div class="form-group col-lg-9 jqcron-holder">
                    <?php echo $form->checkbox($campaign->option, 'cronjob_enabled', $campaign->option->getHtmlOptions('cronjob_enabled', array('uncheckValue' => 0, 'class' => '', 'style' => 'padding-top:3px')));?><?php echo $form->labelEx($campaign->option, 'cronjob');?>
                    <div class="col-lg-12 jqcron-wrapper">
                        <?php echo $form->hiddenField($campaign->option, 'cronjob', $campaign->option->getHtmlOptions('cronjob', array('data-lang' => $jqCronLanguage))); ?>
                    </div>
                    <?php echo $form->error($campaign->option, 'cronjob');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <?php } ?>
                <?php if ($campaign->isAutoresponder) { ?>
                <div class="form-group col-lg-2">
                    <?php echo $form->labelEx($campaign->option, 'autoresponder_event');?>
                    <?php echo $form->dropDownList($campaign->option, 'autoresponder_event', $campaign->option->getAutoresponderEvents(), $campaign->option->getHtmlOptions('autoresponder_event')); ?>
                    <?php echo $form->error($campaign->option, 'autoresponder_event');?>
                </div>
                <div class="form-group col-lg-2">
                    <?php echo $form->labelEx($campaign->option, 'autoresponder_time_value');?>
                    <?php echo $form->textField($campaign->option, 'autoresponder_time_value', $campaign->option->getHtmlOptions('autoresponder_time_value')); ?>
                    <?php echo $form->error($campaign->option, 'autoresponder_time_value');?>
                </div>
                <div class="form-group col-lg-2">
                    <?php echo $form->labelEx($campaign->option, 'autoresponder_time_unit');?>
                    <?php echo $form->dropDownList($campaign->option, 'autoresponder_time_unit', $campaign->option->getAutoresponderTimeUnits(), $campaign->option->getHtmlOptions('autoresponder_time_unit')); ?>
                    <?php echo $form->error($campaign->option, 'autoresponder_time_unit');?>
                </div>
                <div class="form-group col-lg-2">
                    <?php echo $form->labelEx($campaign->option, 'autoresponder_include_imported');?>
                    <?php echo $form->dropDownList($campaign->option, 'autoresponder_include_imported', $campaign->option->getYesNoOptions(), $campaign->option->getHtmlOptions('autoresponder_include_imported')); ?>
                    <?php echo $form->error($campaign->option, 'autoresponder_include_imported');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <div class="form-group col-lg-3" style="display: <?php echo !empty($campaign->option->autoresponder_open_campaign_id) || $campaign->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN ? 'block' : 'none';?>;">
                    <?php echo $form->labelEx($campaign->option, 'autoresponder_open_campaign_id');?>
                    <?php echo $form->dropDownList($campaign->option, 'autoresponder_open_campaign_id', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->option->getAutoresponderOpenRelatedCampaigns()), $campaign->option->getHtmlOptions('autoresponder_open_campaign_id')); ?>
                    <?php echo $form->error($campaign->option, 'autoresponder_open_campaign_id');?>
                </div>
                <?php } ?>
                <div class="clearfix"><!-- --></div>
                
                <?php if ($campaign->isRegular && count($campaign->option->getRelatedCampaignsAsOptions())) { ?>
                <hr />
                <div class="col-lg-12">
                    <div class="callout callout-info">
                        <?php echo Yii::t('campaigns', 'Additionally, send this campaign only to subscribers that did opened or did not opened a certain campaign, as follows:');?>
                    </div>
                    <div class="col-lg-4">
                        <?php echo $form->labelEx($campaign->option, 'regular_open_unopen_action');?>
                        <?php echo $form->dropDownList($campaign->option, 'regular_open_unopen_action', CMap::mergeArray(array('' => ''), $campaign->option->getRegularOpenUnopenActions()), $campaign->option->getHtmlOptions('regular_open_unopen_action')); ?>
                        <?php echo $form->error($campaign->option, 'regular_open_unopen_action');?>
                    </div>
                    <div class="col-lg-4">
                        <?php echo $form->labelEx($campaign->option, 'regular_open_unopen_campaign_id');?>
                        <?php echo $form->dropDownList($campaign->option, 'regular_open_unopen_campaign_id', CMap::mergeArray(array('' => ''), $campaign->option->getRelatedCampaignsAsOptions()), $campaign->option->getHtmlOptions('regular_open_unopen_campaign_id')); ?>
                        <?php echo $form->error($campaign->option, 'regular_open_unopen_campaign_id');?>
                    </div>
                </div>
                <div class="clearfix"><!-- --></div>
                <?php } ?>
                
                <?php 
                /**
                 * This hook gives a chance to append content after the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
                <div class="clearfix"><!-- --></div>
                <hr />
                <div class="table-responsive">
                    <?php
                    $this->widget('zii.widgets.CDetailView', array(
                        'data'          => $campaign,
                        'cssFile'       => false,
                        'htmlOptions'   => array('class' => 'table table-striped table-bordered table-hover table-condensed'),
                        'attributes'    => array(
                            'name',
                            array(
                                'label' => Yii::t('campaigns', 'List/Segment'),
                                'value' => $campaign->getListSegmentName(),
                            ),
                            'from_name', 'reply_to', 'to_name', 'subject',
                            array(
                                'label' => $campaign->getAttributeLabel('date_added'),
                                'value' => $campaign->dateAdded,
                            ),
                            array(
                                'label' => $campaign->getAttributeLabel('last_updated'),
                                'value' => $campaign->lastUpdated,
                            ),
                            array(
                                'label' => Yii::t('campaigns', 'Spam score'),
                                'value' => CHtml::link(Yii::t('campaigns', 'Click to check spam score (please note, score is approximate)'), array('campaigns/spamcheck', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'check-spam-score', 'data-message' => Yii::t('campaigns', 'Please wait while running the spam checks...'))),
                                'type'  => 'raw',
                            )
                        ),
                    ));
                    ?>
                </div>
                <div class="clearfix"><!-- --></div>    
            </div>
            <div class="box-footer">
                <div class="wizard">
                    <ul class="steps">
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/update', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Details');?></a><span class="chevron"></span></li>
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/setup', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Setup');?></a><span class="chevron"></span></li>
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/template', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Template');?></a><span class="chevron"></span></li>
                        <li class="active"><a href="<?php echo $this->createUrl('campaigns/confirm', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Confirmation');?></a><span class="chevron"></span></li>
                        <li><a href="javascript:;"><?php echo Yii::t('app', 'Done');?></a><span class="chevron"></span></li>
                    </ul>
                    <div class="actions">
                        <button type="submit" id="is_next" name="is_next" value="1" class="btn btn-primary btn-submit btn-go-next" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>">
                            <?php echo $campaign->isAutoresponder ? Yii::t('campaigns', 'Save and activate') : Yii::t('campaigns', 'Send campaign');?>
                        </button>
                    </div>
                </div>
            </div>
        </div>    
        <?php 
        $this->endWidget(); 
    } 
    /**
     * This hook gives a chance to append content after the active form.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.3.1
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));

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