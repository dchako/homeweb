<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.8
 */
 
?>

<div class="box box-primary">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><span class="glyphicon glyphicon-user"></span> <?php echo Yii::t('sending_domains', 'Customer');?></h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="form-group col-lg-3">
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
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($domain, 'locked');?>
            <?php echo $form->dropDownList($domain, 'locked', $domain->getYesNoOptions(), $domain->getHtmlOptions('locked')); ?>
            <?php echo $form->error($domain, 'locked');?>
        </div>
        <div class="clearfix"><!-- --></div>           
    </div>
</div>
