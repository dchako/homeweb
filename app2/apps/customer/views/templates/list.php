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
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('glyphicon-text-width') .  $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('upload') . Yii::t('email_templates', 'Upload template'), '#template-upload-modal', array('class' => 'btn btn-primary btn-flat', 'data-toggle' => 'modal', 'title' => Yii::t('email_templates', 'Upload template')));?>
                <?php echo CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('templates/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new')));?>
                <?php echo CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('templates/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body sortable-box">
            <?php foreach ($templates as $model) { ?>
            <div class="box box-primary borderless panel-template-box" style="height: 270px;" data-id="<?php echo $model->template_id;?>" data-url="<?php echo $this->createUrl('templates/update_sort_order');?>">
                <div class="box-header"><h3 class="box-title"><?php echo $model->shortName;?></h3></div>
                <div class="box-body">
                    <a title="<?php echo Yii::t('email_templates',  'Preview');?> <?php echo CHtml::encode($model->name);?>" href="javascript:;" onclick="window.open('<?php echo $this->createUrl('templates/preview', array('template_uid' => $model->template_uid));?>','<?php echo Yii::t('email_templates',  'Preview') . ' '.CHtml::encode($model->name);?>', 'scrollbars=1, resizable=1, height=600, width=600'); return false;">
                        <img class="img-rounded" src="<?php echo $model->screenshotSrc;?>" />
                    </a>
                </div>
                <div class="box-footer">
                    <a href="<?php echo Yii::app()->createUrl("templates/delete", array("template_uid" => $model->template_uid));?>" class="btn btn-danger btn-flat btn-delete-template" data-confirm-text="<?php echo Yii::t('app', 'Are you sure you want to remove this item?')?>" title="<?php echo Yii::t('app', 'Delete');?>"><?php echo IconHelper::make('delete');?></a><a href="<?php echo Yii::app()->createUrl("templates/copy", array("template_uid" => $model->template_uid));?>" class="btn btn-primary btn-flat" title="<?php echo Yii::t('app', 'Copy');?>"><?php echo IconHelper::make('copy');?></a><a href="<?php echo Yii::app()->createUrl("templates/update", array("template_uid" => $model->template_uid));?>" class="btn btn-primary btn-flat" title="<?php echo Yii::t('app', 'Update');?>"><?php echo IconHelper::make('update');?></a>
                </div>
            </div>
            <?php } ?>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
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
                    'action'        => array('templates/upload'),
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
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#upload-template-form').submit();"><?php echo IconHelper::make('upload') . '&nbsp;' . Yii::t('email_templates',  'Upload archive');?></button>
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