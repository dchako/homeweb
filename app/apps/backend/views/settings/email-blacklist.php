<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.2
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
        $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Email blacklist settings')?></h3>
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
                    'controller'        => $this,
                    'form'              => $form    
                )));
                ?>
                <div class="clearfix"><!-- --></div>
                <div class="form-group col-lg-6">
                    <?php echo $form->labelEx($blacklistModel, 'local_check');?>
                    <?php echo $form->dropDownList($blacklistModel, 'local_check', $blacklistModel->getCheckOptions(), $blacklistModel->getHtmlOptions('local_check')); ?>
                    <?php echo $form->error($blacklistModel, 'local_check');?>
                </div>    
                <div class="form-group col-lg-6">
                    <?php echo $form->labelEx($blacklistModel, 'remote_check');?>
                    <?php echo $form->dropDownList($blacklistModel, 'remote_check', $blacklistModel->getCheckOptions(), $blacklistModel->getHtmlOptions('remote_check')); ?>
                    <?php echo $form->error($blacklistModel, 'remote_check');?>
                </div>
                <div class="clearfix"><!-- --></div>
                <div class="form-group col-lg-12">
                    <hr />
                    <?php echo $form->error($blacklistModel, 'remote_dnsbls');?>
                    <div class="pull-left">
                        <h5><?php echo Yii::t('settings', 'Available DNSBL services');?>:</h5>
                    </div>
                    <div class="pull-right">
                        <a href="javascript:;" class="btn btn-xs btn-primary add-dnsbl"><?php echo Yii::t('settings', 'Add new service');?></a>
                    </div>
                    <div class="clearfix"><!-- --></div>
                </div>
                <div class="form-group">
                    <div id="dnsbl-list">
                    <?php foreach ($blacklistModel->remote_dnsbls as $dnsbl) { ?>
                        <div class="form-group col-lg-6">
                            <?php echo $form->labelEx($blacklistModel, 'remote_dnsbls');?>
                            <div class="clearfix"><!-- --></div>
                            <div class="col-lg-9">
                                <?php echo CHtml::textField($blacklistModel->modelName . '[remote_dnsbls][]', $dnsbl, $blacklistModel->getHtmlOptions('remote_dnsbls'));?>
                            </div>
                            <div class="col-lg-3">
                                <a href="javascript:;" class="btn btn-sm btn-danger remove-dnsbl"><?php echo Yii::t('app', 'Remove');?></a>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <hr />
                    <div class="callout callout-info">
                        <?php echo Yii::t('settings', 'You can see a list of available DNSBL services by clicking {here}.', array(
                            '{here}' => '<a href="http://www.dnsbl.info/dnsbl-list.php" target="_blank">'.Yii::t('app', 'here').'</a>'
                        ));?>
                        <br />
                        <?php echo Yii::t('settings', 'Please note, remote checks are usually slow and they will be even slower if you add many remote DNSBL services to check against.');?>
                    </div>
                </div>
                
                <hr />
                <div class="form-group col-lg-12">
                    <?php echo $form->labelEx($blacklistModel, 'regular_expressions');?>
                    <div class="callout callout-info">
                        - <?php echo Yii::t('settings', 'All expressions will be passed as first parameter to PHP\'s preg_match function for which you can find documentation here: {url}.', array(
                            '{url}' => CHtml::link('http://php.net/preg_match', 'http://php.net/preg_match', array('target' => '_blank')),
                        ));?>
                        <br />
                       - <?php echo Yii::t('settings', 'Make sure you enter a single expression per line. Wrongly formatted expressions might generate runtime errors in your PHP environment that can lead to application malfunction. You can use {url} for testing your regular expressions.', array(
                        '{url}' => CHtml::link('https://regex101.com/', 'https://regex101.com/', array('target' => '_blank'))
                       ));?>
                    </div>
                    <?php echo $form->textArea($blacklistModel, 'regular_expressions', $blacklistModel->getHtmlOptions('regular_expressions', array('rows' => 10))); ?>
                    <?php echo $form->error($blacklistModel, 'regular_expressions');?>
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
                    'controller'        => $this,
                    'form'              => $form    
                )));
                ?>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <div class="box box-primary">
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
    ?>
    <div style="display: none;" id="dnsbl-item">
        <div class="form-group col-lg-6">
            <?php echo $form->labelEx($blacklistModel, 'remote_dnsbls');?>
            <div class="clearfix"><!-- --></div>
            <div class="col-lg-9">
                <?php echo CHtml::textField($blacklistModel->modelName . '[remote_dnsbls][]', null, $blacklistModel->getHtmlOptions('remote_dnsbls'));?>
            </div>
            <div class="col-lg-3">
                <a href="javascript:;" class="btn btn-sm btn-danger remove-dnsbl"><?php echo Yii::t('app', 'Remove');?></a>
            </div>
        </div>
    </div>
<?php 
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