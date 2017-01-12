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
        $form = $this->beginWidget('CActiveForm', array(
            'htmlOptions' => array(
                'enctype' => 'multipart/form-data',
            ),
        ));
        ?>
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
                    <?php echo $form->labelEx($campaign, 'from_name');?>
                    <?php echo $form->textField($campaign, 'from_name', $campaign->getHtmlOptions('from_name')); ?>
                    <?php echo $form->error($campaign, 'from_name');?>
                </div>
                <div class="form-group col-lg-3">
                    <?php echo $form->labelEx($campaign, 'from_email');?>
                    <?php echo $form->textField($campaign, 'from_email', $campaign->getHtmlOptions('from_email')); ?>
                    <?php echo $form->error($campaign, 'from_email');?>
                </div>
                <div class="form-group col-lg-3">
                    <?php echo $form->labelEx($campaign, 'reply_to');?>
                    <?php echo $form->textField($campaign, 'reply_to', $campaign->getHtmlOptions('reply_to')); ?>
                    <?php echo $form->error($campaign, 'reply_to');?>
                </div>
                <div class="form-group col-lg-3">
                    <?php echo $form->labelEx($campaign, 'to_name');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('campaigns', 'Available tags');?></a>]
                    <?php echo $form->textField($campaign, 'to_name', $campaign->getHtmlOptions('to_name')); ?>
                    <?php echo $form->error($campaign, 'to_name');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <div class="form-group col-lg-12">
                    <?php echo $form->labelEx($campaign, 'subject');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('campaigns', 'Available tags');?></a>]
                    <?php echo $form->textField($campaign, 'subject', $campaign->getHtmlOptions('subject')); ?>
                    <?php echo $form->error($campaign, 'subject');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <div class="col-lg-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <div class="pull-left">
                                <h3 class="box-title">
                                    <span class="glyphicon glyphicon-cog"></span> <?php echo Yii::t('campaigns', 'Campaign options');?>
                                </h3>
                            </div>
                            <div class="pull-right"></div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                        <div class="box-body">
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'open_tracking');?>
                                <?php echo $form->dropDownList($campaign->option, 'open_tracking', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('open_tracking')); ?>
                                <?php echo $form->error($campaign->option, 'open_tracking');?>
                            </div>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'url_tracking');?>
                                <?php echo $form->dropDownList($campaign->option, 'url_tracking', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('url_tracking')); ?>
                                <?php echo $form->error($campaign->option, 'url_tracking');?>
                            </div>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'json_feed');?>
                                <?php echo $form->dropDownList($campaign->option, 'json_feed', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('json_feed')); ?>
                                <?php echo $form->error($campaign->option, 'json_feed');?>
                            </div>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'xml_feed');?>
                                <?php echo $form->dropDownList($campaign->option, 'xml_feed', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('xml_feed')); ?>
                                <?php echo $form->error($campaign->option, 'xml_feed');?>
                            </div>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'embed_images');?>
                                <?php echo $form->dropDownList($campaign->option, 'embed_images', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('embed_images')); ?>
                                <?php echo $form->error($campaign->option, 'embed_images');?>
                            </div>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'plain_text_email');?>
                                <?php echo $form->dropDownList($campaign->option, 'plain_text_email', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('plain_text_email')); ?>
                                <?php echo $form->error($campaign->option, 'plain_text_email');?>
                            </div>
                            <div class="clearfix"><!-- --></div>
                            <?php if (!$campaign->isAutoresponder) {?>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'max_send_count');?>
                                <?php echo $form->textField($campaign->option, 'max_send_count', $campaign->option->getHtmlOptions('max_send_count')); ?>
                                <?php echo $form->error($campaign->option, 'max_send_count');?>
                            </div>
                            <div class="form-group col-lg-2">
                                <?php echo $form->labelEx($campaign->option, 'max_send_count_random');?>
                                <?php echo $form->dropDownList($campaign->option, 'max_send_count_random', $campaign->option->getYesNoOptionsArray(), $campaign->option->getHtmlOptions('max_send_count_random')); ?>
                                <?php echo $form->error($campaign->option, 'max_send_count_random');?>
                            </div>
                            <div class="form-group col-lg-8">
                                <?php echo $form->labelEx($campaign->option, 'email_stats');?>
                                <?php echo $form->textField($campaign->option, 'email_stats', $campaign->option->getHtmlOptions('email_stats')); ?>
                                <?php echo $form->error($campaign->option, 'email_stats');?>
                            </div>
                            <?php } ?>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"><!-- --></div>

                <?php if (!empty($canShowOpenListFieldActions)) { ?>
                <div class="col-lg-12">
                    <div class="box box-primary panel-campaign-open-list-fields-actions">
                        <div class="box-header">
                            <div class="pull-left">
                                <h3 class="box-title">
                                    <span class="glyphicon glyphicon-tasks"></span> <?php echo Yii::t('campaigns', 'Change subscriber custom field value upon campaign open');?>
                                </h3>
                            </div>
                            <div class="pull-right">
                                <a href="javascript:;" class="btn btn-primary btn-xs btn-campaign-open-list-fields-actions-add"><?php echo Yii::t('campaigns', 'Add field/value')?></a>
                            </div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                        <div class="box-body">
                            <div class="callout callout-info">
                                <?php echo Yii::t('campaigns', 'This is useful if you later need to segment your list and find out who opened this campaign or who did not and based on that to take another action, like sending the campaign again to subscribers that did not open it previously.');?><br />
                                <?php echo Yii::t('campaigns', 'In most of the cases, you will want to keep these fields as hidden fields.')?>
                            </div>
                            <div class="campaign-open-list-fields-actions-list">
                            <?php if (!empty($openListFieldActions)) { foreach($openListFieldActions as $index => $openListFieldAct) { ?>
                            <div class="col-lg-6 campaign-open-list-fields-actions-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                                <div class="col-lg-4">
                                    <?php echo $form->labelEx($openListFieldAct, 'field_id');?>
                                    <?php echo CHtml::dropDownList($openListFieldAct->modelName.'['.$index.'][field_id]', $openListFieldAct->field_id, $openListFieldActionOptions, $openListFieldAct->getHtmlOptions('field_id')); ?>
                                    <?php echo $form->error($openListFieldAct, 'field_id');?>
                                </div>
                                <div class="col-lg-7">
                                    <?php echo $form->labelEx($openListFieldAct, 'field_value');?>
                                    <?php echo CHtml::textField($openListFieldAct->modelName.'['.$index.'][field_value]', $openListFieldAct->field_value, $openListFieldAct->getHtmlOptions('field_value')); ?>
                                    <?php echo $form->error($openListFieldAct, 'field_value');?>
                                </div>
                                <div class="col-lg-1">
                                    <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-campaign-open-list-fields-actions-remove" data-action-id="<?php echo $openListFieldAct->action_id;?>" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
                                </div>
                                <div class="clearfix"><!-- --></div>
                            </div>
                            <?php }} ?>
                            </div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if (!empty($canShowOpenActions)) { ?>
                <div class="col-lg-12">
                    <div class="box box-primary panel-campaign-open-actions">
                        <div class="box-header">
                            <div class="pull-left">
                                <h3 class="box-title">
                                    <span class="glyphicon glyphicon-new-window"></span> <?php echo Yii::t('campaigns', 'Actions against subscribers upon campaign open');?>
                                </h3>
                            </div>
                            <div class="pull-right">
                                <a href="javascript:;" class="btn btn-primary btn-xs btn-campaign-open-actions-add"><?php echo Yii::t('campaigns', 'Add action')?></a>
                            </div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                        <div class="box-body">
                            <div class="callout callout-info">
                                <?php echo Yii::t('campaigns', 'When a subscriber opens your campaign, do following actions against the subscriber itself:')?>
                            </div>
                            <div class="campaign-open-actions-list">
                            <?php if (!empty($openActions)) { foreach($openActions as $index => $openAct) { ?>
                            <div class="col-lg-6 campaign-open-actions-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                                <div class="col-lg-4">
                                    <?php echo $form->labelEx($openAct, 'action');?>
                                    <?php echo CHtml::dropDownList($openAct->modelName.'['.$index.'][action]', $openAct->action, $openAllowedActions, $openAct->getHtmlOptions('action')); ?>
                                    <?php echo $form->error($openAct, 'action');?>
                                </div>
                                <div class="col-lg-7">
                                    <?php echo $form->labelEx($openAct, 'list_id');?>
                                    <?php echo CHtml::dropDownList($openAct->modelName.'['.$index.'][list_id]', $openAct->list_id, $openActionLists, $openAct->getHtmlOptions('list_id')); ?>
                                    <?php echo $form->error($openAct, 'list_id');?>
                                </div>
                                <div class="col-lg-1">
                                    <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-campaign-open-actions-remove" data-action-id="<?php echo $openAct->action_id;?>" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
                                </div>
                                <div class="clearfix"><!-- --></div>
                            </div>
                            <?php }} ?>
                            </div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php if ($canSelectDeliveryServers && !empty($deliveryServers)) { ?>
                <div class="col-lg-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <div class="pull-left">
                                <h3 class="box-title">
                                    <span class="glyphicon glyphicon-send"></span> <?php echo Yii::t('campaigns', 'Campaign delivery servers');?>
                                </h3>
                            </div>
                            <div class="pull-right"></div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                        <div class="box-body panel-delivery-servers-pool">
                            <div class="callout callout-info">
                                <?php echo Yii::t('campaigns', 'Select which delivery servers are used for this campaign, if no option is selected, all the available servers will be used.');?>
                            </div>
                            <?php foreach ($deliveryServers as $server) { ?>
                                <div class="form-group col-lg-4">
                                    <div class="item">
                                        <?php echo CHtml::checkBox($campaignToDeliveryServers->modelName.'[]', in_array($server->server_id, $campaignDeliveryServersArray), array('value' => $server->server_id));?>
                                        <?php echo $server->displayName;?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"><!-- --></div>
                <?php } ?>

                <?php if ($canAddAttachments) { ?>
                <div class="col-lg-12">
                    <div class="box box-primary panel-campaign-attachments">
                        <div class="box-header">
                            <div class="pull-left">
                                <h3 class="box-title">
                                    <span class="glyphicon glyphicon-upload"></span> <?php echo Yii::t('campaigns', 'Campaign attachments');?>
                                </h3>
                            </div>
                            <div class="pull-right"></div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                        <div class="box-body">
                            <div class="callout callout-info">
                                <?php echo Yii::t('campaigns', 'You are allowed to upload up to {maxCount} attachments. Each attachment size must be lower than {maxSize}.', array(
                                    '{maxCount}' => $attachment->getAllowedFilesCount(),
                                    '{maxSize}'  => ($attachment->getAllowedFileSize() / 1024 / 1024) . ' mb'
                                )); ?>
                                <?php if (count($allowedExtensions = $attachment->getAllowedExtensions()) > 0) { ?>
                                    <br />
                                    <?php echo Yii::t('campaigns', 'Following file types are allowed for upload: {types}', array(
                                        '{types}' => implode(', ', $allowedExtensions),
                                    ));?>
                                <?php } ?>
                            </div>
                            <?php
                              $this->widget('CMultiFileUpload', array(
                                 'model'        => $attachment,
                                 'attribute'    => 'file',
                                 'max'          => $attachment->getAllowedFilesCount(),
                              ));
                            ?>
                            <?php if (!empty($campaign->attachments)) { ?>
                            <h5><?php echo Yii::t('campaigns', 'Uploaded files for this campaign:');?></h5>
                            <?php foreach ($campaign->attachments as $file) { ?>
                                <div class="form-group col-lg-4">
                                    <div class="item">
                                        <a href="<?php echo $this->createUrl('campaigns/remove_attachment', array('campaign_uid' => $campaign->campaign_uid, 'attachment_id' => $file->attachment_id));?>" class="btn btn-xs btn-danger btn-remove-attachment" data-message="<?php echo Yii::t('campaigns', 'Are you sure you want to remove this attachment?');?>">x</a>
                                        <?php echo $file->name;?>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php } ?>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"><!-- --></div>
                <?php
                }
                /**
                 * This hook gives a chance to append content after the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables
                 * via {@CAttributeCollection $collection->controller->data}
                 *
                 * @since 1.3.3.1
                 */
                $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form
                )));
                ?>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-footer">
                <div class="wizard">
                    <ul class="steps">
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/update', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Details');?></a><span class="chevron"></span></li>
                        <li class="active"><a href="<?php echo $this->createUrl('campaigns/setup', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Setup');?></a><span class="chevron"></span></li>
                        <li><a href="<?php echo $this->createUrl('campaigns/template', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Template');?></a><span class="chevron"></span></li>
                        <li><a href="<?php echo $this->createUrl('campaigns/confirm', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Confirmation');?></a><span class="chevron"></span></li>
                        <li><a href="javascript:;"><?php echo Yii::t('app', 'Done');?></a><span class="chevron"></span></li>
                    </ul>
                    <div class="actions">
                        <button type="submit" id="is_next" name="is_next" value="1" class="btn btn-primary btn-submit btn-go-next" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>"><?php echo Yii::t('campaigns', 'Save and next');?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $this->endWidget();
    }
    /**
     * This hook gives a chance to append content after the active form fields.
     * Please note that from inside the action callback you can access all the controller view variables
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.3.1
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));
    ?>
    <div class="modal fade" id="available-tags-modal" tabindex="-1" role="dialog" aria-labelledby="available-tags-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('lists', 'Available tags');?></h4>
            </div>
            <div class="modal-body" style="max-height: 300px; overflow-y:scroll;">
                <table class="table table-bordered table-hover table-striped">
                    <tr>
                        <td><?php echo Yii::t('lists', 'Tag');?></td>
                        <td><?php echo Yii::t('lists', 'Required');?></td>
                    </tr>
                    <?php foreach ($campaign->getSubjectToNameAvailableTags() as $tag) { ?>
                    <tr>
                        <td><?php echo CHtml::encode($tag['tag']);?></td>
                        <td><?php echo $tag['required'] ? strtoupper(Yii::t('app', Campaign::TEXT_YES)) : strtoupper(Yii::t('app', Campaign::TEXT_NO));?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
            </div>
          </div>
        </div>
    </div>

    <div id="campaign-open-actions-template" style="display: none;">
        <div class="col-lg-6 campaign-open-actions-row" data-start-index="{index}" style="margin-bottom: 10px;">
            <div class="col-lg-4">
                <?php echo $form->labelEx($openAction, 'action');?>
                <?php echo CHtml::dropDownList($openAction->modelName.'[{index}][action]', null, $openAllowedActions, $openAction->getHtmlOptions('action')); ?>
                <?php echo $form->error($openAction, 'action');?>
            </div>
            <div class="col-lg-7">
                <?php echo $form->labelEx($openAction, 'list_id');?>
                <?php echo CHtml::dropDownList($openAction->modelName.'[{index}][list_id]', null, $openActionLists, $openAction->getHtmlOptions('list_id')); ?>
                <?php echo $form->error($openAction, 'list_id');?>
            </div>
            <div class="col-lg-1">
                <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-campaign-open-actions-remove" data-action-id="0" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>

    <div id="campaign-open-list-fields-actions-template" style="display: none;">
        <div class="col-lg-6 campaign-open-list-fields-actions-row" data-start-index="{index}" style="margin-bottom: 10px;">
            <div class="col-lg-4">
                <?php echo $form->labelEx($openListFieldAction, 'field_id');?>
                <?php echo CHtml::dropDownList($openListFieldAction->modelName.'[{index}][field_id]', null, $openListFieldActionOptions, $openListFieldAction->getHtmlOptions('field_id')); ?>
                <?php echo $form->error($openListFieldAction, 'field_id');?>
            </div>
            <div class="col-lg-7">
                <?php echo $form->labelEx($openListFieldAction, 'field_value');?>
                <?php echo CHtml::textField($openListFieldAction->modelName.'[{index}][field_value]', null, $openListFieldAction->getHtmlOptions('field_value')); ?>
                <?php echo $form->error($openListFieldAction, 'field_value');?>
            </div>
            <div class="col-lg-1">
                <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-campaign-open-list-fields-actions-remove" data-action-id="0" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
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
