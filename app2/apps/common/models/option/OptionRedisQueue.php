<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionRedisQueue
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
class OptionRedisQueue extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.queue.redis_queue';
    
    // whether queue is enabled
    public $enabled = 'no';
    
    // redis hostname
    public $hostname = 'localhost';
    
    // redis port
    public $port = 6379;
    
    // redis database number
    public $database = 0;
    
    public function rules()
    {
        $rules = array(
            array('enabled, hostname, port, database', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('port, database', 'numerical', 'integerOnly' => true),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }

    public function attributeLabels()
    {
        $labels = array(
            'enabled'  => Yii::t('settings', 'Enabled'),
            'hostname' => Yii::t('settings', 'Hostname'),
            'port'     => Yii::t('settings', 'Port'),
            'database' => Yii::t('settings', 'Database'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'hostname' => 'localhost',
            'port'     => 6379,
            'database' => 0,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'  => Yii::t('settings', 'Whether the queue feature is enabled'),
            'hostname' => Yii::t('settings', 'Redis server hostname, usually localhost'),
            'port'     => Yii::t('settings', 'Redis server port, usually 6379'),
            'database' => Yii::t('settings', 'Redis database number'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
