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
        <div class="login-box-body">
            <div style='width: 400px;height: 200px;/*! background-image: ; */background-color: red;padding-left: 0px;margin-left: -20px;margin-top: -20px;margin-right: -20px;padding-right: -20px;background-image: url("http://www.mailclick.com.ar/img/login.png");'></div>
            <p class="login-box-msg" style="padding: 20px 20px 20px;"><?php echo Yii::t('users', 'Acceso Usuarios');?></p>
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
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'email');?>
                        <?php echo $form->emailField($model, 'email', $model->getHtmlOptions('email')); ?>
                        <?php echo $form->error($model, 'email');?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'password');?>
                        <?php echo $form->passwordField($model, 'password', $model->getHtmlOptions('password')); ?>
                        <?php echo $form->error($model, 'password');?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>
                            <?php echo $form->checkBox($model, 'remember_me') . ' ' . $model->getAttributeLabel('remember_me');?>
                        </label>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <div class="pull-left">
                        <a href="<?php echo $this->createUrl('guest/forgot_password')?>" class="btn btn-default btn-flat"><?php echo IconHelper::make('fa-lock') . '&nbsp;' . Yii::t('users', 'Forgot password?');?></a>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('next') . '&nbsp;' .Yii::t('app', 'Login');?></button>
                    </div>
                    <div class="clearfix"><!-- --></div>
                </div>
            </div>
            
            <?php
            /**
             * This hook gives a chance to append content after the active form fields.
             * Please note that from inside the action callback you can access all the controller view variables
             * via {@CAttributeCollection $collection->controller->data}
             *
             * @since 1.3.3.1
             */
            $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
                'controller'    => $this,
                'form'          => $form
            )));
            ?>
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