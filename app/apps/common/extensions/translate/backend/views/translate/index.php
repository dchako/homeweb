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
                <span class="glyphicon glyphicon-plus-sign"></span> <?php echo Yii::t('ext_translate', 'Translation extension');?>
            </h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="callout callout-info">
            <?php echo Yii::t('ext_translate', 'Once enabled, the translate extension will start collecting messages from the application and write them in files if the message is missing from file and the application language is other than english.');?><br />
         </div>
         <div class="form-group col-lg-6">
            <?php echo $form->labelEx($model, 'enabled');?>
            <?php echo $form->dropDownList($model, 'enabled', $model->getOptionsDropDown(), $model->getHtmlOptions('enable')); ?>
            <?php echo $form->error($model, 'enabled');?>
        </div> 
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($model, 'translate_extensions');?>
            <?php echo $form->dropDownList($model, 'translate_extensions', $model->getOptionsDropDown(), $model->getHtmlOptions('translate_extensions')); ?>
            <?php echo $form->error($model, 'translate_extensions');?>
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