<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.7
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
if ($viewCollection->renderContent) { ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('glyphicon-text-width') .  $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('templates/gallery'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <?php foreach ($templates as $model) { ?>
            <div class="box box-primary borderless panel-template-box">
                <div class="box-header"><h3 class="box-title"><?php echo $model->shortName;?></h3></div>
                <div class="box-body">
                    <img class="img-rounded" src="<?php echo $model->screenshotSrc;?>" />
                </div>
                <div class="box-footer">
                    <a href="<?php echo Yii::app()->createUrl("templates/gallery_import", array("template_uid" => $model->template_uid));?>" class="btn btn-primary btn-flat" title="<?php echo Yii::t('app', 'Import');?>"><?php echo IconHelper::make('glyphicon-import') . Yii::t('app', 'Import');?></a>
                </div>
            </div>
            <?php } ?>
            <div class="clearfix"><!-- --></div>
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