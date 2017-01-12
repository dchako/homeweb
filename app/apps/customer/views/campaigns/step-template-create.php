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
            'action' => array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'create')
        ));
        echo CHtml::hiddenField('selected_template_id', 0, array('id' => 'selected_template_id'));
        ?>
        <div class="box box-primary">
            <div class="box-header">
                <div class="pull-left">
                    <h3 class="box-title">
                        <span class="glyphicon glyphicon-envelope"></span> <?php echo $pageHeading;?>
                    </h3>
                </div>
                <div class="pull-right">
                    <?php echo CHtml::link(Yii::t('email_templates', 'Import html from url'), '#template-import-modal', array('class' => 'btn btn-primary btn-xs', 'data-toggle' => 'modal', 'title' => Yii::t('email_templates', 'Import html from url')));?>
                    <?php echo CHtml::link(Yii::t('email_templates', 'Upload template'), '#template-upload-modal', array('class' => 'btn btn-primary btn-xs', 'data-toggle' => 'modal', 'title' => Yii::t('email_templates', 'Upload template')));?>
                    <?php echo CHtml::link(Yii::t('campaigns', 'Change/Select template'), array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'select'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('campaigns', 'Change/Select template')));?>
                    <?php if (!empty($template->content)) { ?>
                    <?php echo CHtml::link(Yii::t('campaigns', 'Test template'), '#template-test-email', array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('campaigns', 'Test template'), 'data-toggle' => 'modal'));?>
                    <?php } ?>
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
                    <?php echo $form->labelEx($template, 'inline_css');?>
                    <?php echo $form->dropDownList($template, 'inline_css', $template->getYesNoOptions(), $template->getHtmlOptions('inline_css')); ?>
                    <?php echo $form->error($template, 'inline_css');?>
                </div>
                <div class="form-group col-lg-3">
                    <?php echo $form->labelEx($template, 'minify');?>
                    <?php echo $form->dropDownList($template, 'minify', $template->getYesNoOptions(), $template->getHtmlOptions('minify')); ?>
                    <?php echo $form->error($template, 'minify');?>
                </div>
                <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                <div class="form-group col-lg-3">
                    <?php echo $form->labelEx($template, 'only_plain_text');?>
                    <?php echo $form->dropDownList($template, 'only_plain_text', $template->getYesNoOptions(), $template->getHtmlOptions('only_plain_text')); ?>
                    <?php echo $form->error($template, 'only_plain_text');?>
                </div>
                <div class="form-group col-lg-3" style="display:<?php echo $template->isOnlyPlainText ? 'none':'';?>;">
                    <?php echo $form->labelEx($template, 'auto_plain_text');?>
                    <?php echo $form->dropDownList($template, 'auto_plain_text', $template->getYesNoOptions(), $template->getHtmlOptions('auto_plain_text')); ?>
                    <?php echo $form->error($template, 'auto_plain_text');?>
                </div>
                <?php } ?>
                <div class="clearfix"><!-- --></div>
                <hr />
                <div class="form-group" style="display:<?php echo $template->isOnlyPlainText ? 'none':'';?>;">
                    <div class="pull-left">
                        <?php echo $form->labelEx($template, 'content');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('lists', 'Available tags');?></a>]
                        <?php 
                        // since 1.3.5
                        $hooks->doAction('before_wysiwyg_editor_left_side', array('controller' => $this, 'template' => $template, 'campaign' => $campaign));
                        ?>
                    </div>
                    <div class="pull-right">
                        <?php 
                        // since 1.3.5
                        $hooks->doAction('before_wysiwyg_editor_right_side', array('controller' => $this, 'template' => $template, 'campaign' => $campaign));
                        ?>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <?php echo $form->textArea($template, 'content', $template->getHtmlOptions('content', array('rows' => 30))); ?>
                    <?php echo $form->error($template, 'content');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <div class="form-group plain-text-version" style="display:<?php echo $template->isOnlyPlainText ? 'block':'none';?>;">
                    <?php echo $form->labelEx($template, 'plain_text');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('lists', 'Available tags');?></a>]
                    <?php echo $form->textArea($template, 'plain_text', $template->getHtmlOptions('plain_text', array('rows' => 20))); ?>
                    <?php echo $form->error($template, 'plain_text');?>
                    <?php echo $form->error($template, 'content');?>
                </div>
                <div class="clearfix"><!-- --></div>
                
                <?php if (!empty($templateContentUrls)) { ?>
                <div class="form-group template-click-actions-list-fields-container" style="display: none;">

                    <div class="callout callout-info" style="margin-bottom:0;">
                        <div class="pull-left">
                            <?php echo Yii::t('campaigns', 'When a subscriber clicks one or more links from your email template, do following actions against one of the subscriber custom fields.')?><br />
                            <?php echo Yii::t('campaigns', 'This is useful if you later need to segment your list and find out who clicked on links in this campaign or who did not and based on that to take another action, like sending the campaign again to subscribers that did/did not clicked certain link previously.');?><br />
                            <?php echo Yii::t('campaigns', 'In most of the cases, you will want to keep these fields as hidden fields.')?>
                        </div>
                        <div class="pull-right">
                            <a href="javascript:;" class="btn btn-primary btn-xs btn-template-click-actions-list-fields-add" style="margin-top: 15px;"><?php echo Yii::t('campaigns', 'Add field/value')?></a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <hr />
                    <div class="template-click-actions-list-fields-list">
                    <?php if (!empty($templateUrlActionListFields)) { foreach($templateUrlActionListFields as $index => $templateUrlActionListFieldMdl) { ?>
                    <div class="col-lg-12 template-click-actions-list-fields-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                        <div class="col-lg-4">
                            <?php echo $form->labelEx($templateUrlActionListFieldMdl, 'url');?>
                            <?php echo CHtml::dropDownList($templateUrlActionListFieldMdl->modelName.'['.$index.'][url]', $templateUrlActionListFieldMdl->url, $templateContentUrls, $templateUrlActionListFieldMdl->getHtmlOptions('url')); ?>
                            <?php echo $form->error($templateUrlActionListFieldMdl, 'url');?>
                        </div>
                        <div class="col-lg-3">
                            <?php echo $form->labelEx($templateUrlActionListField, 'field_id');?>
                            <?php echo CHtml::dropDownList($templateUrlActionListField->modelName.'['.$index.'][field_id]', $templateUrlActionListFieldMdl->field_id, CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $templateUrlActionListFieldMdl->getTextFieldsAsDropDownOptions()), $templateUrlActionListFieldMdl->getHtmlOptions('field_id')); ?>
                            <?php echo $form->error($templateUrlActionListField, 'field_id');?>
                        </div>
                        <div class="col-lg-4">
                            <?php echo $form->labelEx($templateUrlActionListFieldMdl, 'field_value');?>
                            <?php echo CHtml::textField($templateUrlActionListFieldMdl->modelName.'['.$index.'][field_value]', $templateUrlActionListFieldMdl->field_value, $templateUrlActionListFieldMdl->getHtmlOptions('field_value')); ?>
                            <?php echo $form->error($templateUrlActionListFieldMdl, 'field_value');?>
                        </div>
                        <div class="col-lg-1">
                            <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-template-click-actions-list-fields-remove" data-url-id="<?php echo $templateUrlActionListFieldMdl->url_id;?>" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                    <?php }} ?>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <hr />
                </div>
                <?php } ?>
                
                <?php if (!empty($templateContentUrls)) { ?>
                <div class="form-group template-click-actions-container" style="display: none;">
                    <div class="callout callout-info" style="margin-bottom:0;">
                        <div class="pull-left">
                            <?php echo Yii::t('campaigns', 'When a subscriber clicks one or more links from your email template, do following actions against the subscriber itself:')?>
                        </div>
                        <div class="pull-right">
                            <a href="javascript:;" class="btn btn-primary btn-xs btn-template-click-actions-add"><?php echo Yii::t('campaigns', 'Add action')?></a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <hr />
                    <div class="template-click-actions-list">
                    <?php if (!empty($templateUrlActionSubscriberModels)) { foreach($templateUrlActionSubscriberModels as $index => $templateUrlActionSub) { ?>
                    <div class="col-lg-12 template-click-actions-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                        <div class="col-lg-6">
                            <?php echo $form->labelEx($templateUrlActionSub, 'url');?>
                            <?php echo CHtml::dropDownList($templateUrlActionSub->modelName.'['.$index.'][url]', $templateUrlActionSub->url, $templateContentUrls, $templateUrlActionSub->getHtmlOptions('url')); ?>
                            <?php echo $form->error($templateUrlActionSub, 'url');?>
                        </div>
                        <div class="col-lg-1">
                            <?php echo $form->labelEx($templateUrlActionSub, 'action');?>
                            <?php echo CHtml::dropDownList($templateUrlActionSub->modelName.'['.$index.'][action]', $templateUrlActionSub->action, $clickAllowedActions, $templateUrlActionSub->getHtmlOptions('action')); ?>
                            <?php echo $form->error($templateUrlActionSub, 'action');?>
                        </div>
                        <div class="col-lg-4">
                            <?php echo $form->labelEx($templateUrlActionSub, 'list_id');?>
                            <?php echo CHtml::dropDownList($templateUrlActionSub->modelName.'['.$index.'][list_id]', $templateUrlActionSub->list_id, $templateListsArray, $templateUrlActionSub->getHtmlOptions('list_id')); ?>
                            <?php echo $form->error($templateUrlActionSub, 'list_id');?>
                        </div>
                        <div class="col-lg-1">
                            <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-template-click-actions-remove" data-url-id="<?php echo $templateUrlActionSub->url_id;?>" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                    <?php }} ?>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <hr />
                </div>
                <?php } ?>
                
                <div class="pull-right">
                    <?php if (!empty($templateContentUrls)) { ?>
                    <button type="button" class="btn btn-primary btn-template-click-actions-list-fields">
                    <?php echo Yii::t('campaigns', 'Change subscriber custom field on link click({count})', array(
                        '{count}' => sprintf('<span class="count">%d</span>', (!empty($templateUrlActionListFields) ? count($templateUrlActionListFields) : 0))
                    ));
                    ?>
                    </button>
                    <?php } ?>
                    <?php if (!empty($templateContentUrls)) { ?>
                    <button type="button" class="btn btn-primary btn-template-click-actions">
                    <?php echo Yii::t('campaigns', 'Actions against subscriber on link click({count})', array(
                        '{count}' => sprintf('<span class="count">%d</span>', (!empty($templateUrlActionSubscriberModels) ? count($templateUrlActionSubscriberModels) : 0))
                    ));
                    ?>
                    </button>
                    <?php } ?>
                    <?php echo CHtml::link(Yii::t('campaigns', 'Google UTM tags'), '#google-utm-tags-modal', array('class' => 'btn btn-primary', 'data-toggle' => 'modal', 'title' => Yii::t('campaigns', 'Google UTM tags')));?>
                    <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                    <button type="button" class="btn btn-primary btn-plain-text" data-showtext="<?php echo Yii::t('campaigns', 'Show plain text version');?>" data-hidetext="<?php echo Yii::t('campaigns', 'Hide plain text version');?>" style="display:<?php echo $template->isOnlyPlainText ? 'none':'';?>;"><?php echo Yii::t('campaigns', 'Show plain text version');?></button>
                    <?php } ?>
                    <button type="submit" id="is_next" name="is_next" value="0" class="btn btn-primary btn-submit btn-go-next" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>"><?php echo Yii::t('campaigns', 'Save template changes');?></button>
                </div>
                <div class="clearfix"><!-- --></div>
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
            </div>
            <div class="box-footer">
                <div class="wizard">
                    <ul class="steps">
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/update', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Details');?></a><span class="chevron"></span></li>
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/setup', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Setup');?></a><span class="chevron"></span></li>
                        <li class="active"><a href="<?php echo $this->createUrl('campaigns/template', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Template');?></a><span class="chevron"></span></li>
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
     * This hook gives a chance to append content after the active form.
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
                    <?php foreach ($template->getAvailableTags() as $tag) { ?>
                    <tr>
                        <td><?php echo CHtml::encode($tag['tag']);?></td>
                        <td><?php echo $tag['required'] ? strtoupper(Yii::t('app', CampaignTemplate::TEXT_YES)) : strtoupper(Yii::t('app', CampaignTemplate::TEXT_NO));?></td>
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
    
    <?php if(!empty($template->content)) { ?>
    <div class="modal fade" id="template-test-email" tabindex="-1" role="dialog" aria-labelledby="template-test-email-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('campaigns', 'Send a test email');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                     <strong><?php echo Yii::t('app', 'Notes');?>: </strong><br />
                     <?php
                     $text = '* if multiple recipients, separate the email addresses by a comma.<br />
                     * the email tags will be parsed and we will pick a random subscriber to impersonate.<br />
                     * the tracking will not be enabled.<br />
                     * make sure you save the template changes before you send the test.';
                     echo Yii::t('campaigns', StringHelper::normalizeTranslationString($text));
                     ?>
                 </div>
                 <?php echo CHtml::form(array('campaigns/test', 'campaign_uid' => $campaign->campaign_uid), 'post', array('id' => 'template-test-form'));?>
                 <div class="form-group">
                     <?php echo CHtml::label(Yii::t('campaigns', 'Recipient(s)'), 'email');?>
                     <?php echo CHtml::textField('email', null, array('class' => 'form-control', 'placeholder' => Yii::t('campaigns', 'i.e: a@domain.com, b@domain.com, c@domain.com')));?>
                 </div>
                 <div class="clearfix"><!-- --></div>
                 <div class="form-group">
                     <?php echo CHtml::label(Yii::t('campaigns', 'From email (optional)'), 'from_email');?>
                     <?php echo CHtml::textField('from_email', null, array('class' => 'form-control', 'placeholder' => Yii::t('campaigns', 'i.e: me@domain.com')));?>
                 </div>
                 <?php echo CHtml::endForm();?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" onclick="$('#template-test-form').submit();" data-loading-text="<?php echo Yii::t('app', 'Please wait, loading...');?>"><?php echo Yii::t('campaigns', 'Send test');?></button>
            </div>
          </div>
        </div>
    </div>
    <?php } ?>
    
    <div class="modal fade" id="template-upload-modal" tabindex="-1" role="dialog" aria-labelledby="template-upload-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('email_templates',  'Upload template archive');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php
                    $text = '
                    Please see <a href="{templateArchiveHref}">this example archive</a> in order to understand how you should format your uploaded archive!
                    Also, please note we only accept zip files.';
                    echo Yii::t('email_templates',  StringHelper::normalizeTranslationString($text), array(
                        '{templateArchiveHref}' => Yii::app()->apps->getAppUrl('customer', 'assets/files/example-template.zip', false, true),
                    ));
                    ?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'upload'),
                    'id'            => $templateUp->modelName.'-upload-form',
                    'htmlOptions'   => array(
                        'id'        => 'upload-template-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($templateUp, 'archive');?>
                    <?php echo $form->fileField($templateUp, 'archive', $templateUp->getHtmlOptions('archive')); ?>
                    <?php echo $form->error($templateUp, 'archive');?>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($templateUp, 'inline_css');?>
                    <?php echo $form->dropDownList($templateUp, 'inline_css', $templateUp->getYesNoOptions(), $templateUp->getHtmlOptions('inline_css')); ?>
                    <div class="help-block"><?php echo $templateUp->getAttributeHelpText('inline_css');?></div>
                    <?php echo $form->error($templateUp, 'inline_css');?>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($templateUp, 'minify');?>
                    <?php echo $form->dropDownList($templateUp, 'minify', $templateUp->getYesNoOptions(), $templateUp->getHtmlOptions('minify')); ?>
                    <div class="help-block"><?php echo $templateUp->getAttributeHelpText('minify');?></div>
                    <?php echo $form->error($templateUp, 'minify');?>
                </div>
                <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                <div class="form-group">
                    <?php echo $form->labelEx($templateUp, 'auto_plain_text');?>
                    <?php echo $form->dropDownList($templateUp, 'auto_plain_text', $templateUp->getYesNoOptions(), $templateUp->getHtmlOptions('auto_plain_text')); ?>
                    <div class="help-block"><?php echo $templateUp->getAttributeHelpText('auto_plain_text');?></div>
                    <?php echo $form->error($templateUp, 'auto_plain_text');?>
                </div>
                <?php } ?>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#upload-template-form').submit();"><?php echo Yii::t('email_templates',  'Upload archive');?></button>
            </div>
          </div>
        </div>
    </div>
    
    <div class="modal fade" id="template-import-modal" tabindex="-1" role="dialog" aria-labelledby="template-import-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('email_templates',  'Import html template from url');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php echo Yii::t('email_templates', 'Please note that your url must contain a valid html email template with absolute paths to resources!');?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'from-url'),
                    'id'            => $template->modelName.'-import-form',
                    'htmlOptions'   => array(
                        'id'        => 'import-template-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($template, 'from_url');?>
                    <?php echo $form->textField($template, 'from_url', $template->getHtmlOptions('from_url')); ?>
                    <?php echo $form->error($template, 'from_url');?>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($template, 'inline_css');?>
                    <?php echo $form->dropDownList($template, 'inline_css', $template->getYesNoOptions(), $template->getHtmlOptions('inline_css')); ?>
                    <div class="help-block"><?php echo $template->getAttributeHelpText('inline_css');?></div>
                    <?php echo $form->error($template, 'inline_css');?>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($template, 'minify');?>
                    <?php echo $form->dropDownList($template, 'minify', $template->getYesNoOptions(), $template->getHtmlOptions('minify')); ?>
                    <div class="help-block"><?php echo $template->getAttributeHelpText('minify');?></div>
                    <?php echo $form->error($template, 'minify');?>
                </div>
                <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                <div class="form-group">
                    <?php echo $form->labelEx($template, 'auto_plain_text');?>
                    <?php echo $form->dropDownList($template, 'auto_plain_text', $template->getYesNoOptions(), $template->getHtmlOptions('auto_plain_text')); ?>
                    <div class="help-block"><?php echo $template->getAttributeHelpText('auto_plain_text');?></div>
                    <?php echo $form->error($template, 'auto_plain_text');?>
                </div>
                <?php } ?>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#import-template-form').submit();"><?php echo Yii::t('email_templates',  'Import');?></button>
            </div>
          </div>
        </div>
    </div>
    
    <?php if (!empty($templateContentUrls)) { ?>
    <div id="template-click-actions-list-fields-template" style="display: none;">
        <div class="col-lg-12 template-click-actions-list-fields-row" data-start-index="{index}" style="margin-bottom: 10px;">
            <div class="col-lg-4">
                <?php echo $form->labelEx($templateUrlActionListField, 'url');?>
                <?php echo CHtml::dropDownList($templateUrlActionListField->modelName.'[{index}][url]', null, $templateContentUrls, $templateUrlActionListField->getHtmlOptions('url')); ?>
                <?php echo $form->error($templateUrlActionListField, 'url');?>
            </div>
            <div class="col-lg-3">
                <?php echo $form->labelEx($templateUrlActionListField, 'field_id');?>
                <?php echo CHtml::dropDownList($templateUrlActionListField->modelName.'[{index}][field_id]', null, CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $templateUrlActionListField->getTextFieldsAsDropDownOptions()), $templateUrlActionListField->getHtmlOptions('field_id')); ?>
                <?php echo $form->error($templateUrlActionListField, 'field_id');?>
            </div>
            <div class="col-lg-4">
                <?php echo $form->labelEx($templateUrlActionListField, 'field_value');?>
                <?php echo CHtml::textField($templateUrlActionListField->modelName.'[{index}][field_value]', null, $templateUrlActionListField->getHtmlOptions('field_value')); ?>
                <?php echo $form->error($templateUrlActionListField, 'field_value');?>
            </div>
            <div class="col-lg-1">
                <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-template-click-actions-list-fields-remove" data-url-id="0" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
    <?php } ?>
    
    <?php if (!empty($templateContentUrls)) { ?>
    <div id="template-click-actions-template" style="display: none;">
        <div class="col-lg-12 template-click-actions-row" data-start-index="{index}" style="margin-bottom: 10px;">
            <div class="col-lg-6">
                <?php echo $form->labelEx($templateUrlActionSubscriber, 'url');?>
                <?php echo CHtml::dropDownList($templateUrlActionSubscriber->modelName.'[{index}][url]', null, $templateContentUrls, $templateUrlActionSubscriber->getHtmlOptions('url')); ?>
                <?php echo $form->error($templateUrlActionSubscriber, 'url');?>
            </div>
            <div class="col-lg-1">
                <?php echo $form->labelEx($templateUrlActionSubscriber, 'action');?>
                <?php echo CHtml::dropDownList($templateUrlActionSubscriber->modelName.'[{index}][action]', null, $clickAllowedActions, $templateUrlActionSubscriber->getHtmlOptions('action')); ?>
                <?php echo $form->error($templateUrlActionSubscriber, 'action');?>
            </div>
            <div class="col-lg-4">
                <?php echo $form->labelEx($templateUrlActionSubscriber, 'list_id');?>
                <?php echo CHtml::dropDownList($templateUrlActionSubscriber->modelName.'[{index}][list_id]', null, $templateListsArray, $templateUrlActionSubscriber->getHtmlOptions('list_id')); ?>
                <?php echo $form->error($templateUrlActionSubscriber, 'list_id');?>
            </div>
            <div class="col-lg-1">
                <a style="margin-top: 30px;" href="javascript:;" class="btn btn-xs btn-danger btn-template-click-actions-remove" data-url-id="0" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo Yii::t('app', 'Remove');?></a>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
    <?php } ?>
    
    <div class="modal fade" id="google-utm-tags-modal" tabindex="-1" role="dialog" aria-labelledby="google-utm-tags-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('campaigns', 'Google UTM tags pattern');?></h4>
            </div>
            <div class="modal-body">
                <div class="callout callout-info">
                    <?php echo Yii::t('campaigns', 'After you insert your UTM tags pattern, each link from your email template will be transformed and this pattern will be appended for tracking. Beside all the regular template tags, following special tags are also recognized:');?>
                    <hr />
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <td><?php echo Yii::t('lists', 'Tag');?></td>
                            <td><?php echo Yii::t('lists', 'Description');?></td>
                        </tr>
                        <?php foreach ($template->getExtraUtmTags() as $tag => $tagDescription) { ?>
                        <tr>
                            <td><?php echo CHtml::encode($tag);?></td>
                            <td><?php echo CHtml::encode($tagDescription);?></td>
                        </tr>
                        <?php } ?>
                    </table>
                    <hr />
                    <strong><?php echo Yii::t('campaigns', 'Example pattern:');?></strong><br />
                    <span>utm_source=mail_from_[CURRENT_DATE]&utm_medium=cpc&utm_term=[EMAIL]&utm_campaign=[CAMPAIGN_NAME]</span>
                </div>
                <?php echo CHtml::form(array('campaigns/google_utm_tags', 'campaign_uid' => $campaign->campaign_uid), 'post', array('id' => 'google-utm-tags-form'));?>
                <div class="form-group">
                    <label><?php echo Yii::t('campaigns', 'Insert your pattern');?>:</label>
                    <?php echo CHtml::textField('google_utm_pattern', '', array('class' => 'form-control'));?>
                </div>
                <?php echo CHtml::endForm();?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary" onclick="$('#google-utm-tags-form').submit(); return false;"><?php echo Yii::t('campaigns', 'Parse links and set pattern');?></button>
            </div>
          </div>
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