<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
?>

<?php 
if (!$server->isNewRecord && $server->status === DeliveryServer::STATUS_INACTIVE) { 

    $form = $this->beginWidget('CActiveForm', array(
        'action'    => $this->createUrl('delivery_servers/validate', array('id' => $server->server_id)),
        'id'        => $server->modelName.'-form',
    ));
?>
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title"><?php echo Yii::t('servers', 'Validate this server');?></h3>
    </div>
    <div class="box-body">
        <div class="callout callout-info">
            <p>
            <?php 
            $text = 'In order to start sending emails using this server, we need to make sure that it works, therefore 
            we need to send you an email with a confirmation link. 
            Once you confirm this server, this message will go away and the server will become active and ready to be used.<br />
            Please note, for sending the confirmation email, we will use the information you provided when you created this server. 
            <br />
            If you think you need to adjust the options, please feel free to do it now and save your changes before going through the validation process.';
            echo Yii::t('servers', StringHelper::normalizeTranslationString($text));
            ?>
            </p>
        </div>
        <div class="form-group">
            <?php echo CHtml::label(Yii::t('servers', 'The email address where the validation email will be sent.'), 'email', array());?>
            <?php echo CHtml::textField('email', '', array('class' => 'form-control', 'placeholder' => Yii::t('servers', 'me@domain.com') )); ?>
        </div>            
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-default btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>"><?php echo Yii::t('servers', 'Validate server');?></button>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>
<hr />
<?php } ?>