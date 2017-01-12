<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 */
 
?>

<?php $form = $this->beginWidget('CActiveForm'); ?>
<div class="box box-primary">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <span class="glyphicon glyphicon-plus-sign"></span> <?php echo Yii::t('ext_campaign_activity_map', 'Campaign activity map');?>
            </h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="callout callout-info">
            <?php echo Yii::t('ext_campaign_activity_map', 'Decide whether to show various maps in the campaign overview area.');?><br />
         </div>
         <div class="form-group col-lg-4">
            <?php echo $form->labelEx($model, 'show_opens_map');?>
            <?php echo $form->dropDownList($model, 'show_opens_map', $model->getOptionsDropDown(), $model->getHtmlOptions('show_opens_map')); ?>
            <?php echo $form->error($model, 'show_opens_map');?>
        </div> 
        <div class="form-group col-lg-4">
            <?php echo $form->labelEx($model, 'show_clicks_map');?>
            <?php echo $form->dropDownList($model, 'show_clicks_map', $model->getOptionsDropDown(), $model->getHtmlOptions('show_clicks_map')); ?>
            <?php echo $form->error($model, 'show_clicks_map');?>
        </div> 
        <div class="form-group col-lg-4">
            <?php echo $form->labelEx($model, 'show_unsubscribes_map');?>
            <?php echo $form->dropDownList($model, 'show_unsubscribes_map', $model->getOptionsDropDown(), $model->getHtmlOptions('show_unsubscribes_map')); ?>
            <?php echo $form->error($model, 'show_unsubscribes_map');?>
        </div> 
        <div class="clearfix"><!-- --></div>
        <div class="form-group col-lg-4">
            <?php echo $form->labelEx($model, 'opens_at_once');?>
            <?php echo $form->textField($model, 'opens_at_once', $model->getHtmlOptions('opens_at_once')); ?>
            <?php echo $form->error($model, 'opens_at_once');?>
        </div> 
        <div class="form-group col-lg-4">
            <?php echo $form->labelEx($model, 'clicks_at_once');?>
            <?php echo $form->textField($model, 'clicks_at_once', $model->getHtmlOptions('clicks_at_once')); ?>
            <?php echo $form->error($model, 'clicks_at_once');?>
        </div> 
        <div class="form-group col-lg-4">
            <?php echo $form->labelEx($model, 'unsubscribes_at_once');?>
            <?php echo $form->textField($model, 'unsubscribes_at_once', $model->getHtmlOptions('unsubscribes_at_once')); ?>
            <?php echo $form->error($model, 'unsubscribes_at_once');?>
        </div> 
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-default btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>"><?php echo Yii::t('app', 'Save changes');?></button>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>