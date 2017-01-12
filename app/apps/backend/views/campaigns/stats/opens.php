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
            <h3 class="panel-title"><?php echo Yii::t('campaign_reports', 'Open rate');?></h3>
        </div>
        <div class="panel-body" style="height:360px;">
            <div class="circliful-graph" data-dimension="250" data-text="<?php echo $campaign->stats->getUniqueOpensRate(true);?>%" data-info="<?php echo Yii::t('campaign_reports', 'Open rate');?>" data-width="30" data-fontsize="38" data-percent="<?php echo ceil($campaign->stats->getUniqueOpensRate());?>" data-fgcolor="#3c8dbc" data-bgcolor="#eee" data-border="inline" data-type="half"></div>
            <ul class="list-group">
                <li class="list-group-item"><span class="badge"><?php echo $campaign->stats->getProcessedCount(true);?></span> <?php echo Yii::t('campaign_reports', 'Processed');?></li>
                <li class="list-group-item"><span class="badge"><?php echo $campaign->stats->getUniqueOpensCount(true);?></span> <?php echo Yii::t('campaign_reports', 'Unique opens');?></li>
                <li class="list-group-item active"><span class="badge"><?php echo $campaign->stats->getUniqueOpensRate(true);?>%</span> <?php echo Yii::t('campaign_reports', 'Unique opens rate');?></li>
                <li class="list-group-item"><span class="badge"><?php echo $campaign->stats->getOpensCount(true);?></span> <?php echo Yii::t('campaign_reports', 'All opens');?></li>
                <li class="list-group-item active"><span class="badge"><?php echo $campaign->stats->getOpensRate(true);?>%</span> <?php echo Yii::t('campaign_reports', 'All opens rate');?></li>
                <?php if ($campaign->stats->getIndustryOpensRate()) { ?>
                <li class="list-group-item"><span class="badge"><?php echo $campaign->stats->getIndustryOpensRate(true);?>%</span> <?php echo Yii::t('campaign_reports', 'Industry avg({industry})', array('{industry}' => CHtml::link($campaign->stats->getIndustry()->name, array('account/company'))));?></li>
                <?php } ?>
                <li class="list-group-item active"><span class="badge"><?php echo $campaign->stats->getOpensToClicksRate(true);?>%</span> <?php echo Yii::t('campaign_reports', 'Opens to clicks rate');?></li>
            </ul>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="panel-footer">
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
</div>