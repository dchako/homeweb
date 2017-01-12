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

<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title"><?php echo Yii::t('app', 'Error {code}!', array('{code}' => (int)$code));?></h3></div>
    <div class="panel-body">
        <p class="info"><?php echo CHtml::encode($message);?></p>
    </div>
    <div class="panel-footer">
        <div class="pull-right">
            <a href="<?php echo $this->createUrl('site/index');?>" class="btn btn-default"> <i class="glyphicon glyphicon-circle-arrow-left"></i> <?php echo Yii::t('app', 'Back');?></a>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>