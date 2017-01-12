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

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * 
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
    <div class="callout callout-info">
        <?php echo $pageType->description;?>
        <div class="clearfix"><!-- --></div>
    </div>  
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title"><?php echo Yii::t('list_pages', $pageType->name);?></h3>
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
            <div class="form-group">
                <?php echo $form->labelEx($page, 'content');?>
                <?php echo $form->textArea($page, 'content', $page->getHtmlOptions('content', array('rows' => 15))); ?>
                <?php echo $form->error($page, 'content');?>
            </div>
            <?php $this->renderPartial('_tags');?>
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
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_active_form', new CAttributeCollection(array(
    'controller'      => $this,
    'renderedForm'    => $collection->renderForm,
)));