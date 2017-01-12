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
                <span class="glyphicon glyphicon-plus-sign"></span> <?php echo Yii::t('ext_ckeditor', 'CKeditor options');?>
            </h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="form-group col-lg-3">
            <?php echo $form->labelEx($model, 'enable_filemanager_user');?>
            <?php echo $form->dropDownList($model, 'enable_filemanager_user', $model->getOptionsDropDown(), $model->getHtmlOptions('enable_filemanager_user')); ?>
            <?php echo $form->error($model, 'enable_filemanager_user');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($model, 'enable_filemanager_customer');?>
            <?php echo $form->dropDownList($model, 'enable_filemanager_customer', $model->getOptionsDropDown(), $model->getHtmlOptions('enable_filemanager_customer')); ?>
            <?php echo $form->error($model, 'enable_filemanager_customer');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($model, 'default_toolbar');?>
            <?php echo $form->dropDownList($model, 'default_toolbar', $model->getToolbarsDropDown(), $model->getHtmlOptions('default_toolbar')); ?>
            <?php echo $form->error($model, 'default_toolbar');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($model, 'filemanager_theme');?>
            <?php echo $form->dropDownList($model, 'filemanager_theme', $model->getFilemanagerThemesDropDown(), $model->getHtmlOptions('filemanager_theme')); ?>
            <?php echo $form->error($model, 'filemanager_theme');?>
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
