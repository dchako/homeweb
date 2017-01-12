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
 * @since 1.3.4.3
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { ?>
    <div class="box box-primary">
        <div class="box-header" id="chatter-header">
            <h3 class="box-title"><i class="glyphicon glyphicon-list-alt"></i> <?php echo $pageHeading;?></h3>
            <div class="box-tools pull-right">
                <?php echo CHtml::link(Yii::t('lists', 'List overview'), array('lists/overview', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'List overview')));?>
            </div>
        </div>
        <div class="box-body">
            <div class="clearfix"><!-- --></div>
            <?php if (!empty($canImport)) { ?>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3><?php echo Yii::t('list_import', 'Import');?></h3>
                        <p><?php echo Yii::t('app', 'Tools');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-upload"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_import/index", array("list_uid" => $list->list_uid));?>" class="btn bg-orange btn-flat btn-xs"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if (!empty($canExport)) { ?>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3><?php echo Yii::t('list_export', 'Export');?></h3>
                        <p><?php echo Yii::t('app', 'Tools');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-ios7-download"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="<?php echo Yii::app()->createUrl("list_export/index", array("list_uid" => $list->list_uid));?>" class="btn bg-teal btn-flat btn-xs"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if (!empty($canCopy)) { ?>
            <div class="col-lg-2 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo Yii::t('lists', 'Copy');?></h3>
                        <p><?php echo Yii::t('lists', 'Subscribers');?></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-hammer"></i>
                    </div>
                    <div class="small-box-footer">
                        <div class="pull-left"></div>
                        <div class="pull-right">
                            <a href="#copy-list-subscribers-modal" data-ajax="<?php echo $this->createUrl('list_tools/copy_subscribers_ajax', array('list_uid' => $list->list_uid));?>" data-toggle="modal" class="btn bg-red btn-flat btn-xs btn-show-copy-subs-ajax"><span class="glyphicon glyphicon-eye-open"></span> <?php echo Yii::t('app', 'View');?></a>&nbsp;
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="clearfix"><!-- --></div>    
        </div>
    </div>
    
    <?php if (!empty($canCopy)) { ?>
    <div class="modal fade" id="copy-list-subscribers-modal" tabindex="-1" role="dialog" aria-labelledby="copy-list-subscribers-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('lists', 'Copy subscribers from another list');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php echo Yii::t('lists', 'Copy the confirmed subscribers from the selected list/segment below into the current one.')?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('list_tools/copy_subscribers', 'list_uid' => $list->list_uid),
                    'htmlOptions'   => array('id' => 'copy-subscribers-form'),
                ));
                ?>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('lists', 'List'), '');?>
                    <?php echo CHtml::dropDownList('copy_list_id', null, array(), $list->getHtmlOptions('list_id')); ?>
                </div>
                <?php if (!empty($canSegmentLists)) { ?>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('lists', 'Segment'), '');?>
                    <?php echo CHtml::dropDownList('copy_segment_id', null, array(), $list->getHtmlOptions('list_id')); ?>
                </div>
                <?php } ?>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#copy-subscribers-form').submit();"><?php echo Yii::t('app', 'Copy');?></button>
            </div>
          </div>
        </div>
    </div>
    <?php } ?>
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.4.3
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));