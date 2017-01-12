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
    <div class="pull-left">
        <?php $this->widget('customer.components.web.widgets.MailListSubNavWidget', array(
            'list' => $list,
        ))?>
    </div>
    <div class="pull-right">
        <a href="javascript:;" class="btn btn-primary toggle-campaigns-filters-form"><?php echo Yii::t('list_subscribers', 'Toggle campaigns filters form');?></a>    
    </div>
    
    <div class="clearfix"><!-- --></div>
    <?php $this->renderPartial('_filters');?>
    <div class="clearfix"><!-- --></div>

    <hr /> 
    
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title"><span class="glyphicon glyphicon-user"><!-- --></span> <?php echo $pageHeading;?></h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(Yii::t('app', 'Create new'), array('list_subscribers/create', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'New')));?>
                <?php echo CHtml::link(Yii::t('app', 'Bulk action from source'), '#bulk-from-source-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-xs', 'title' => Yii::t('list_subscribers', 'Bulk action from source')));?>
                <?php echo CHtml::link(Yii::t('app', 'Refresh'), array('list_subscribers/index', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div id="subscribers-wrapper">
                <?php $this->renderPartial('_list');?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="bulk-from-source-modal" tabindex="-1" role="dialog" aria-labelledby="bulk-from-source-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('list_subscribers', 'Bulk action from source');?></h4>
            </div>
            <div class="modal-body">
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('list_subscribers/bulk_from_source', 'list_uid' => $list->list_uid),
                    'htmlOptions'   => array(
                        'id'        => 'bulk-from-source-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="callout callout-info">
                    <?php echo Yii::t('list_subscribers', 'Match the subscribers added here against the ones existing in the list and make a bulk action against them!');?>
                    <br />
                    <strong><?php echo Yii::t('list_subscribers', 'Please note, this is not the list import ability, for list import go to your list overview, followed by Tools box followed by the Import box.');?></strong>
                </div>
                    
                <div class="form-group">
                    <?php echo $form->labelEx($subBulkFromSource, 'bulk_from_file');?>
                    <?php echo $form->fileField($subBulkFromSource, 'bulk_from_file', $subBulkFromSource->getHtmlOptions('bulk_from_file')); ?>
                    <?php echo $form->error($subBulkFromSource, 'bulk_from_file');?>
                    <div class="callout callout-info">
                        <?php echo $subBulkFromSource->getAttributeHelpText('bulk_from_file');?>
                    </div>
                </div>
                
                <div class="form-group">
                    <?php echo $form->labelEx($subBulkFromSource, 'bulk_from_text');?>
                    <?php echo $form->textArea($subBulkFromSource, 'bulk_from_text', $subBulkFromSource->getHtmlOptions('bulk_from_text', array('rows' => 5))); ?>
                    <?php echo $form->error($subBulkFromSource, 'bulk_from_text');?>
                    <div class="callout callout-info">
                        <?php echo $subBulkFromSource->getAttributeHelpText('bulk_from_text');?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($subBulkFromSource, 'status');?>
                    <?php echo $form->dropDownList($subBulkFromSource, 'status', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $subBulkFromSource->getBulkActionsList()), $subBulkFromSource->getHtmlOptions('status')); ?>
                    <?php echo $form->error($subBulkFromSource, 'status');?>
                    <div class="callout callout-info">
                        <?php echo Yii::t('list_subscribers', 'For all the subscribers found in file/text area take this action!');?>
                    </div>
                </div>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#bulk-from-source-form').submit();"><?php echo Yii::t('app', 'Submit');?></button>
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
