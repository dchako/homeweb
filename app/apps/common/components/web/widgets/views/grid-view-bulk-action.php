<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5.4
 */
?>
<?php if (!empty($bulkActions)) { 
    $form = $this->beginWidget('CActiveForm', array(
        'action'      => $formAction,
        'id'          => 'bulk-action-form',
        'htmlOptions' => array('style' => 'display:none'),
    )); 
    $this->endWidget(); 
?>
<div class="col-lg-4" id="bulk-actions-wrapper" style="display: none;">
    <div class="col-lg-8">
        <?php echo CHtml::dropDownList('bulk_action', null, CMap::mergeArray(array('' => ''), $bulkActions), array(
            'class'           => 'form-control',
            'data-delete-msg' => Yii::t('app', 'Are you sure you want to remove the selected items?'),
        ));?>
    </div>
    <div class="col-lg-4">
        <a href="javascript:;" class="btn btn-sm btn-primary" id="btn-run-bulk-action" style="display:none"><?php echo Yii::t('app', 'Run bulk action');?></a>
    </div>
</div>
<div class="clearfix"><!-- --></div>
<?php } ?>