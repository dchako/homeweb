<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5.5
 */
 
?>

<div class="col-lg-4">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo Yii::t('campaign_reports', 'Unsubscribe rate');?></h3>
        </div>
        <div class="panel-body" style="height:360px;">
            <div class="circliful-graph" data-dimension="250" data-text="<?php echo $campaign->stats->getUnsubscribesRate(true);?>%" data-info="<?php echo Yii::t('campaign_reports', 'Unsubscribe rate');?>" data-width="30" data-fontsize="38" data-percent="<?php echo ceil($campaign->stats->getUnsubscribesRate());?>" data-fgcolor="#b94a48" data-bgcolor="#eee" data-border="inline" data-type="half"></div>
            <ul class="list-group">
                <li class="list-group-item"><span class="badge"><?php echo $campaign->stats->getProcessedCount(true);?></span> <?php echo Yii::t('campaign_reports', 'Processed');?></li>
                <li class="list-group-item"><span class="badge"><?php echo $campaign->stats->getUnsubscribesCount(true);?></span> <?php echo Yii::t('campaign_reports', 'Unsubscribe');?></li>
                <li class="list-group-item active"><span class="badge"><?php echo $campaign->stats->getUnsubscribesRate(true);?>%</span> <?php echo Yii::t('campaign_reports', 'Unsubscribe rate');?></li>
            </ul>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="panel-footer">
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
</div>