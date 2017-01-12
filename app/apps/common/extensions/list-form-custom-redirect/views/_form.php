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
<hr />
<div class="col-lg-12">
    <div class="col-lg-9">
        <label><?php echo Yii::t('lists', 'Instead of the above message, redirect the subscriber to the following url:')?></label>
        <?php echo $form->textField($model, 'url', $model->getHtmlOptions('url'));?>
        <?php echo $form->error($model, 'url');?>
    </div>
    <div class="col-lg-3">
        <label><?php echo Yii::t('lists', 'After this number of seconds:');?></label>
        <?php echo $form->textField($model, 'timeout', $model->getHtmlOptions('timeout'));?>
        <?php echo $form->error($model, 'timeout');?>
    </div>
</div>
<div class="clearfix"><!-- --></div>