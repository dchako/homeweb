<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
 ?>
 <div class="col-lg-12 row-group-category">
    <div class="box box-primary">
        <div class="box-body">
            <div class="clearfix"><!-- --></div>
            <div class="form-group col-lg-12">
                <?php echo $form->labelEx($model, 'notification_message');?>
                <?php echo $form->textArea($model, 'notification_message', $model->getHtmlOptions('notification_message')); ?>
                <?php echo $form->error($model, 'notification_message');?>
            </div>
            <div class="clearfix"><!-- --></div> 
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>