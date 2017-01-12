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

<div class="col-lg-4">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo Yii::t('campaign_reports', 'Latest clicked links');?></h3>
        </div>
        <div class="panel-body" style="height:360px; overflow-y: scroll;">
            <ul class="list-group">
                <?php foreach ($models as $model) { ?>
                <li class="list-group-item">
                    <div class="pull-left">
                        <?php echo $model->url->getDisplayGridDestination(30);?>
                    </div>
                    <div class="pull-right">
                        <?php echo Yii::t('campaign_reports', 'by {email} at {date}', array(
                            '{email}'   => $model->subscriber->email,
                            '{date}'    => $model->dateAdded,
                        ));?>
                    </div>
                    <div class="clearfix"><!-- --></div>
                </li>
                <?php } ?>
            </ul>
        </div>
        <div class="panel-footer">
            <?php if ($this->showDetailLinks) { ?>
            <div class="pull-right">
                <a href="<?php echo $this->controller->createUrl('campaign_reports/click', array('campaign_uid' => $campaign->campaign_uid));?>" class="btn btn-primary btn-xs"><?php echo Yii::t('campaign_reports', 'View all clicks');?></a>
                <a href="<?php echo $this->controller->createUrl('campaign_reports/click', array('campaign_uid' => $campaign->campaign_uid, 'show' => 'latest'));?>" class="btn btn-primary btn-xs"><?php echo Yii::t('campaign_reports', 'View latest clicks');?></a>
            </div>
            <?php } ?>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
</div>