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

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    /**
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables
     * via {@CAttributeCollection $collection->controller->data}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false
     * in order to stop rendering the default content.
     * @since 1.3.3.1
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
                <div class="pull-left">
                    <h3 class="box-title"><span class="glyphicon glyphicon-flash"></span> <?php echo $pageHeading;?></h3>
                </div>
                <div class="pull-right">
                    <?php if (!$domain->isNewRecord) { ?>
                    <?php echo CHtml::link(Yii::t('app', 'Create new'), array('tracking_domains/create'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Create new')));?>
                    <?php } ?>
                    <?php echo CHtml::link(Yii::t('app', 'Cancel'), array('tracking_domains/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Cancel')));?>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-body">
                <div class="callout callout-info">
                    <?php echo Yii::t('tracking_domains', 'Please note that because of the way DNS servers work, you need to add a subdomain like tracking.your-domain.com as a DNS CNAME record and point it to {currentDomain}!', array(
                        '{currentDomain}' => $currentDomain
                    ));?>
                </div>
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
                <div class="form-group col-lg-4">
                    <?php echo $form->labelEx($domain, 'name');?>
                    <?php echo $form->textField($domain, 'name', $domain->getHtmlOptions('name')); ?>
                    <?php echo $form->error($domain, 'name');?>
                </div>
                <div class="form-group col-lg-4">
                    <?php echo $form->labelEx($domain, 'customer_id');?>
                    <?php echo $form->hiddenField($domain, 'customer_id', $domain->getHtmlOptions('customer_id')); ?>
                    <?php
                    $this->widget('zii.widgets.jui.CJuiAutoComplete',array(
                        'name'          => 'customer',
                        'value'         => !empty($domain->customer) ? $domain->customer->getFullName() : null,
                        'source'        => $this->createUrl('customers/autocomplete'),
                        'cssFile'       => false,
                        'options'       => array(
                            'minLength' => '2',
                            'select'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($domain, 'customer_id').'").val(ui.item.customer_id);
                            }',
                            'search'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($domain, 'customer_id').'").val("");
                            }',
                            'change'    => 'js:function(event, ui) {
                                if (!ui.item) {
                                    $("#'.CHtml::activeId($domain, 'customer_id').'").val("");
                                }
                            }',
                        ),
                        'htmlOptions'   => $domain->getHtmlOptions('customer_id'),
                    ));
                    ?>
                    <?php echo $form->error($domain, 'customer_id');?>
                </div>
                <div class="form-group col-lg-4">
                    <?php echo $form->labelEx($domain, 'skipValidation');?>
                    <?php echo $form->dropDownList($domain, 'skipValidation', array(0 => Yii::t('app', 'No'), 1 => Yii::t('app', 'Yes')), $domain->getHtmlOptions('skipValidation')); ?>
                    <?php echo $form->error($domain, 'skipValidation');?>
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
     * @since 1.3.3.1
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
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));
