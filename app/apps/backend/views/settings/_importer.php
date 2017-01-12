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

?>
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title"><?php echo Yii::t('settings', 'Importer settings')?></h3>
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
            <?php echo $form->labelEx($importModel, 'enabled');?>
            <?php echo $form->dropDownList($importModel, 'enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('enabled')); ?>
            <?php echo $form->error($importModel, 'enabled');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($importModel, 'web_enabled');?>
            <?php echo $form->dropDownList($importModel, 'web_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('web_enabled')); ?>
            <?php echo $form->error($importModel, 'web_enabled');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($importModel, 'file_size_limit');?>
            <?php echo $form->dropDownList($importModel, 'file_size_limit', $importModel->getFileSizeOptions(), $importModel->getHtmlOptions('file_size_limit')); ?>
            <?php echo $form->error($importModel, 'file_size_limit');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($importModel, 'memory_limit');?>
            <?php echo $form->dropDownList($importModel, 'memory_limit', $importModel->getMemoryLimitOptions(), $importModel->getHtmlOptions('memory_limit')); ?>
            <?php echo $form->error($importModel, 'memory_limit');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($importModel, 'import_at_once');?>
            <?php echo $form->textField($importModel, 'import_at_once', $importModel->getHtmlOptions('import_at_once')); ?>
            <?php echo $form->error($importModel, 'import_at_once');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($importModel, 'pause');?>
            <?php echo $form->textField($importModel, 'pause', $importModel->getHtmlOptions('pause')); ?>
            <?php echo $form->error($importModel, 'pause');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($importModel, 'check_mime_type');?>
            <?php echo $form->dropDownList($importModel, 'check_mime_type', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('check_mime_type')); ?>
            <?php echo $form->error($importModel, 'check_mime_type');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <hr />
        <div class="form-group col-lg-2">
            <?php echo $form->labelEx($importModel, 'cli_enabled');?>
            <?php echo $form->dropDownList($importModel, 'cli_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('cli_enabled')); ?>
            <?php echo $form->error($importModel, 'cli_enabled');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <div class="callout callout-info">
            <?php echo Yii::t('settings', 'The command line importer(CLI) is used to queue import files to be processed from the command line instead of having customers wait for the import to finish in the browser.');?><br />
            <?php echo Yii::t('settings', 'Please note that in order for the command line importer to work, after you enable it, you need to add the following cron job, which runs once at 5 minutes:');?><br />
            <span class="badge">*/5 * * * * <?php echo CommonHelper::findPhpCliPath();?> -q <?php echo MW_PATH;?>/apps/console/console.php list-import folder >/dev/null 2>&1 </span>
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
</div>
