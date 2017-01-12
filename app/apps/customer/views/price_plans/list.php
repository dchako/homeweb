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
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <span class="glyphicon glyphicon-credit-card"></span> <?php echo Yii::t('price_plans', 'Available price plans');?>
                </h3>
            </div>
            <div class="pull-right"></div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <?php foreach ($pricePlans as $plan) { ?>
            <div class="col-lg-4 price-plan-box-wrapper">
                <div class="box box-<?php echo $plan->group_id == $customer->group_id ? 'primary' : 'success';?> price-plan-box">
                    <div class="box-header">
                        <h3 class="box-title"><?php echo $plan->name;?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($plan->isRecommended) { ?>
                            <span class="badge bg-<?php echo $plan->group_id == $customer->group_id ? 'blue' : 'red';?>"><?php echo Yii::t('app', 'Recommended');?></span>
                            <?php } ?>
                            <span class="badge bg-<?php echo $plan->group_id == $customer->group_id ? 'blue' : 'red';?>"><?php echo $plan->formattedPrice;?></span>
                        </div>
                    </div>
                    <div class="box-body">
                        <p> <?php echo $plan->description;?> </p>
                    </div>
                    <div class="box-footer">
                        <div class="pull-right">
                            <a class="btn btn-<?php echo $plan->group_id == $customer->group_id ? 'primary' : 'success';?> btn-sm btn-do-order" href="#payment-options-modal" data-toggle="modal" data-plan-uid="<?php echo $plan->uid;?>">
                                <?php echo $plan->group_id == $customer->group_id ? Yii::t('app', 'Your current plan, renew it') : Yii::t('app', 'Purchase');?>
                            </a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </div>
                </div>
            </div>
            <?php } ?>  
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
    
    <div class="modal fade" id="payment-options-modal" tabindex="-1" role="dialog" aria-labelledby="payment-options-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('price_plans', 'Select payment method');?></h4>
            </div>
            <div class="modal-body">
                <?php 
                echo CHtml::form(array('price_plans/payment'), 'post', array('id' => 'payment-options-form'));
                echo CHtml::hiddenField('plan_uid');
                ?>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('price_plans', 'Payment gateway selection'), 'payment_gateway');?>
                    <?php echo CHtml::dropDownList('payment_gateway', '', $paymentMethods, array('class' => 'form-control')); ?>
                 </div>
                <?php echo CHtml::endForm(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#payment-options-form').submit();"><?php echo Yii::t('price_plans', 'Proceed to payment');?></button>
            </div>
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