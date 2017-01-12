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
        <h3 class="box-title"><?php echo Yii::t('settings', 'Common settings')?></h3>
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
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'site_name');?>
            <?php echo $form->textField($commonModel, 'site_name', $commonModel->getHtmlOptions('site_name')); ?>
            <?php echo $form->error($commonModel, 'site_name');?>
        </div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'site_tagline');?>
            <?php echo $form->textField($commonModel, 'site_tagline', $commonModel->getHtmlOptions('site_tagline')); ?>
            <?php echo $form->error($commonModel, 'site_tagline');?>
        </div>    
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'site_description');?>
            <?php echo $form->textField($commonModel, 'site_description', $commonModel->getHtmlOptions('site_description')); ?>
            <?php echo $form->error($commonModel, 'site_description');?>
        </div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'site_keywords');?>
            <?php echo $form->textField($commonModel, 'site_keywords', $commonModel->getHtmlOptions('site_keywords')); ?>
            <?php echo $form->error($commonModel, 'site_keywords');?>
        </div>    
        <hr />
        <div class="col-lg-6">
            <div class="form-group<?php if ($commonModel->clean_urls == 1){?> col-lg-8<?php }?>">
                <?php echo $form->labelEx($commonModel, 'clean_urls');?>
                <?php echo $form->dropDownList($commonModel, 'clean_urls', array(0 => Yii::t('app', 'No, do not use clean urls'), 1 => Yii::t('app', 'Yes, use clean urls')), $commonModel->getHtmlOptions('clean_urls')); ?>
                <?php echo $form->error($commonModel, 'clean_urls');?>
            </div>    
            <div class="form-group col-lg-2" style="<?php if ($commonModel->clean_urls != 1){?>display:none<?php }?>">
                <label><?php echo Yii::t('app', 'Action');?></label>
                <a data-toggle="modal" data-remote="<?php echo $this->createUrl('settings/htaccess_modal');?>" href="#writeHtaccessModal" class="btn btn-default"><?php echo Yii::t('settings', 'Generate htaccess')?></a>
            </div>
        </div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'default_mailer');?>
            <?php echo $form->dropDownList($commonModel, 'default_mailer', $commonModel->getSystemMailers(), $commonModel->getHtmlOptions('default_mailer')); ?>
            <?php echo $form->error($commonModel, 'default_mailer');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($commonModel, 'api_status');?>
            <?php echo $form->dropDownList($commonModel, 'api_status', $commonModel->getSiteStatusOptions(), $commonModel->getHtmlOptions('api_status')); ?>
            <?php echo $form->error($commonModel, 'api_status');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($commonModel, 'site_status');?>
            <?php echo $form->dropDownList($commonModel, 'site_status', $commonModel->getSiteStatusOptions(), $commonModel->getHtmlOptions('site_status')); ?>
            <?php echo $form->error($commonModel, 'site_status');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($commonModel, 'check_version_update');?>
            <?php echo $form->dropDownList($commonModel, 'check_version_update', $commonModel->getYesNoOptions(), $commonModel->getHtmlOptions('check_version_update')); ?>
            <?php echo $form->error($commonModel, 'check_version_update');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($commonModel, 'site_offline_message');?>
            <?php echo $form->textField($commonModel, 'site_offline_message', $commonModel->getHtmlOptions('site_offline_message')); ?>
            <?php echo $form->error($commonModel, 'site_offline_message');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($commonModel, 'support_forum_url');?>
            <?php echo $form->textField($commonModel, 'support_forum_url', $commonModel->getHtmlOptions('support_forum_url')); ?>
            <?php echo $form->error($commonModel, 'support_forum_url');?>
        </div>
        <div class="form-group col-lg-3">
            <?php echo $form->labelEx($commonModel, 'ga_tracking_code_id');?>
            <?php echo $form->textField($commonModel, 'ga_tracking_code_id', $commonModel->getHtmlOptions('ga_tracking_code_id')); ?>
            <?php echo $form->error($commonModel, 'ga_tracking_code_id');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <hr />
        <h4><?php echo Yii::t('settings', 'Company info')?></h4>
        <hr />
        <div class="form-group col-lg-12">
            <?php echo $form->labelEx($commonModel, 'company_info');?>
            <?php echo $form->textArea($commonModel, 'company_info', $commonModel->getHtmlOptions('company_info', array('rows' => 5))); ?>
            <?php echo $form->error($commonModel, 'company_info');?>
        </div>
        <div class="clearfix"><!-- --></div>
        <hr />
        <h4><?php echo Yii::t('settings', 'Pagination / Time info')?></h4>
        <hr />
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'backend_page_size');?>
            <?php echo $form->dropDownList($commonModel, 'backend_page_size', $commonModel->paginationOptions->getOptionsList(), $commonModel->getHtmlOptions('backend_page_size')); ?>
            <?php echo $form->error($commonModel, 'backend_page_size');?>
        </div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'customer_page_size');?>
            <?php echo $form->dropDownList($commonModel, 'customer_page_size', $commonModel->paginationOptions->getOptionsList(), $commonModel->getHtmlOptions('customer_page_size')); ?>
            <?php echo $form->error($commonModel, 'customer_page_size');?>
        </div>  
        <div class="clearfix"><!-- --></div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'show_backend_timeinfo');?>
            <?php echo $form->dropDownList($commonModel, 'show_backend_timeinfo', $commonModel->getYesNoOptions(), $commonModel->getHtmlOptions('show_backend_timeinfo')); ?>
            <?php echo $form->error($commonModel, 'show_backend_timeinfo');?>
        </div>
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($commonModel, 'show_customer_timeinfo');?>
            <?php echo $form->dropDownList($commonModel, 'show_customer_timeinfo', $commonModel->getYesNoOptions(), $commonModel->getHtmlOptions('show_customer_timeinfo')); ?>
            <?php echo $form->error($commonModel, 'show_customer_timeinfo');?>
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