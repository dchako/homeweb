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
<div class="callout callout-info">
    <label><?php echo Yii::t('lists', 'Available tags:');?></label>
    <?php foreach ($tags as $tag) { ?>
    <a href="javascript:;" class="btn btn-primary btn-xs" data-tag-name="<?php echo CHtml::encode($tag['tag']);?>">
        <?php echo CHtml::encode($tag['tag']);?>
    </a>
    <?php } ?>
</div>