<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.4
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
if ($viewCollection->renderContent) {
    $this->renderPartial('_customers_tabs');
    /**
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false 
     * in order to stop rendering the default content.
     * @since 1.3.4.3
     */
    $hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
        'controller'    => $this,
        'renderForm'    => true,
    )));
    
    // and render if allowed
    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm'); 
        ?>
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Customer sending')?></h3>
            </div>
            <div class="box-body">
                <div class="callout callout-info">
                    <?php echo Yii::t('settings', 'A sending quota of 1000 with a time value of 1 and a time unit of Day means the customer is able to send 1000 emails during 1 day.');?>
                    <br />
                    <?php echo Yii::t('settings', 'If waiting is enabled and the customer sends all emails in an hour, he will wait 23 more hours until the specified action is taken.');?>
                    <br />
                    <?php echo Yii::t('settings', 'However, if the waiting is disabled, the action will be taken immediatly.');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <?php 
                /**
                 * This hook gives a chance to prepend content before the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.4.3
                 */
                $hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
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
                <?php 
                /**
                 * This hook gives a chance to append content after the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.4.3
                 */
                $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>"><?php echo Yii::t('app', 'Save changes');?></button>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <?php 
        $this->endWidget(); 
    }
    /**
     * This hook gives a chance to append content after the active form.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.4.3
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));
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