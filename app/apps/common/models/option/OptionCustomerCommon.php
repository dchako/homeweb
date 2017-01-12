<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerCommon
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
class OptionCustomerCommon extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_common';
    
    public $notification_message;

    public function rules()
    {
        $rules = array(
            array('notification_message', 'safe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'notification_message' => Yii::t('settings', 'Notification message'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'notification_message'  => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'notification_message'  => Yii::t('settings', 'A small persistent notification message shown in customers area'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
