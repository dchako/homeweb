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

<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title"><?php echo Yii::t('settings', 'Tracking domains')?></h3>
    </div>
    <div class="box-body">
        <div class="callout callout-info">
            <?php echo Yii::t('settings', 'Please note, in order for this feature to work this domain needs a dedicated IP address, otherwise all defined CNAMES for it will point to the default domain on this server.');?>
            <br />
            <strong><?php echo Yii::t('settings', 'If you do not use a dedicated IP address for this domain only or you are not sure you do so, do not enable this feature!');?></strong>
        </div>
        <div class="clearfix"><!-- --></div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($model, 'can_manage_tracking_domains');?>
            <?php echo $form->dropDownList($model, 'can_manage_tracking_domains', $model->getYesNoOptions(), $model->getHtmlOptions('can_manage_tracking_domains')); ?>
            <?php echo $form->error($model, 'can_manage_tracking_domains');?>
        </div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($model, 'can_select_for_delivery_servers');?>
            <?php echo $form->dropDownList($model, 'can_select_for_delivery_servers', $model->getYesNoOptions(), $model->getHtmlOptions('can_select_for_delivery_servers')); ?>
            <?php echo $form->error($model, 'can_select_for_delivery_servers');?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>