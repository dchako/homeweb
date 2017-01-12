<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4
 */
 
 ?>
 
<div class="col-lg-12 row-group-category">
    <div class="box box-primary">
        <div class="box-body">
            <div class="callout callout-info">
                <?php echo Yii::t('settings', 'A sending quota of 1000 with a time value of 1 and a time unit of Day means the customer is able to send 1000 emails during 1 day.');?>
                <br />
                <?php echo Yii::t('settings', 'If waiting is enabled and the customer sends all emails in an hour, he will wait 23 more hours until the specified action is taken.');?>
                <br />
                <?php echo Yii::t('settings', 'However, if the waiting is disabled, the action will be taken immediatly.');?>
            </div>
            <br />
            <div class="clearfix"><!-- --></div>
            <div class="form-group col-lg-4">
                <?php echo $form->labelEx($model, 'quota');?>
                <?php echo $form->textField($model, 'quota', $model->getHtmlOptions('quota')); ?>
                <?php echo $form->error($model, 'quota');?>
            </div>
            <div class="form-group col-lg-4">
                <?php echo $form->labelEx($model, 'quota_time_value');?>
                <?php echo $form->textField($model, 'quota_time_value', $model->getHtmlOptions('quota_time_value')); ?>
                <?php echo $form->error($model, 'quota_time_value');?>
            </div>
            <div class="form-group col-lg-4">
                <?php echo $form->labelEx($model, 'quota_time_unit');?>
                <?php echo $form->dropDownList($model, 'quota_time_unit', $model->getTimeUnits(), $model->getHtmlOptions('quota_time_unit')); ?>
                <?php echo $form->error($model, 'quota_time_unit');?>
            </div>
            <div class="clearfix"><!-- --></div>
            <div class="form-group col-lg-4">
                <?php echo $form->labelEx($model, 'quota_wait_expire');?>
                <?php echo $form->dropDownList($model, 'quota_wait_expire', $model->getYesNoOptions(), $model->getHtmlOptions('quota_wait_expire')); ?>
                <?php echo $form->error($model, 'quota_wait_expire');?>
            </div>
            <div class="col-lg-6">
                <div class="form-group col-lg-8">
                    <?php echo $form->labelEx($model, 'action_quota_reached');?>
                    <?php echo $form->dropDownList($model, 'action_quota_reached', $model->getActionsQuotaReached(), $model->getHtmlOptions('action_quota_reached')); ?>
                    <?php echo $form->error($model, 'action_quota_reached');?>
                </div>
                <div class="form-group col-lg-4" style="display: <?php echo $model->action_quota_reached == 'move-in-group' ? 'block' : 'none';?>;">
                    <?php echo $form->labelEx($model, 'move_to_group_id');?>
                    <?php echo $form->dropDownList($model, 'move_to_group_id', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $model->getGroupsList()), $model->getHtmlOptions('move_to_group_id')); ?>
                    <?php echo $form->error($model, 'move_to_group_id');?>
                </div>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>