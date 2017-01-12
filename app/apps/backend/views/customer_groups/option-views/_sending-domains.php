<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
 ?>
 <div class="col-lg-12 row-group-category">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title"><?php echo Yii::t('settings', 'Sending domains')?></h3>
        </div>
        <div class="box-body">
            <div class="clearfix"><!-- --></div>
            <div class="form-group col-lg-6">
                <?php echo $form->labelEx($model, 'can_manage_sending_domains');?>
                <?php echo $form->dropDownList($model, 'can_manage_sending_domains', $model->getYesNoOptions(), $model->getHtmlOptions('can_manage_sending_domains')); ?>
                <?php echo $form->error($model, 'can_manage_sending_domains');?>
            </div>
            <div class="form-group col-lg-6">
                <?php echo $form->labelEx($model, 'max_sending_domains');?>
                <?php echo $form->textField($model, 'max_sending_domains', $model->getHtmlOptions('max_sending_domains')); ?>
                <?php echo $form->error($model, 'max_sending_domains');?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>