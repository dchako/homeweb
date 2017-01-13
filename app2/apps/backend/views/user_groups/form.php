<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5
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
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <h3 class="box-title"><?php echo IconHelper::make('glyphicon-user') .  $pageHeading;?></h3>
                </div>
                <div class="pull-right">
                    <?php if (!$group->isNewRecord) { ?>
                    <?php echo HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('user_groups/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new')));?>
                    <?php } ?>
                    <?php echo HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('user_groups/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel')));?>
                </div>
                <div class="clearfix"><!-- --></div>
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
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($group, 'name');?>
                            <?php echo $form->textField($group, 'name', $group->getHtmlOptions('name')); ?>
                            <?php echo $form->error($group, 'name');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="clearfix"><!-- --></div>
                <div class="box box-primary borderless">
                    <div class="box-header">
                        <h3 class="box-title"><?php echo Yii::t('user_groups', 'Access');?></h3>
                    </div>
                    <div class="box-body">
                        <?php foreach ($routesAccess as $index => $data) { ?>
                        <div class="box box-primary borderless">
                            <div class="box-header">
                                <div class="pull-left">
                                    <h3 class="box-title"><?php echo CHtml::encode($data['controller']['name']);?> <small><?php echo CHtml::encode($data['controller']['description']);?></small></h3>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:;" class="btn btn-primary btn-flat allow-all"><?php echo Yii::t('user_groups', 'Allow all');?></a>
                                    <a href="javascript:;" class="btn btn-primary btn-flat deny-all"><?php echo Yii::t('user_groups', 'Deny all');?></a>
                                    <?php if (!$group->isNewRecord) { ?>
                                        <button class="btn btn-primary btn-flat btn-submit btn-save-route-access" data-init-text="<?php echo Yii::t('app', 'Save changes');?>"><?php echo Yii::t('app', 'Save changes');?></button>
                                    <?php } ?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                <?php foreach ($data['routes'] as $route) { ?>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <?php echo CHtml::label($route->name, null);?>
                                            <?php echo CHtml::dropDownList($route->modelName.'['.$index.'][routes]['.$route->route.']', $route->access, $route->getAccessOptions(), $route->getHtmlOptions('action', array('id' => '', 'data-content' => $route->description)));?>
                                        </div>
                                    </div>
                                <?php } ?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                            </div>
                        </div><hr />
                        <?php } ?>   
                        <div class="clearfix"><!-- --></div>  
                    </div>
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
                    <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
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