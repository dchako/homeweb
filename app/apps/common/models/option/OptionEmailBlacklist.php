<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionEmailBlacklist
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.2
 */
 
class OptionEmailBlacklist extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.email_blacklist';
    
    public $local_check = 'yes';
    
    public $remote_check = 'no';
    
    public $remote_dnsbls = array();
    
    public $regular_expressions;

    public function rules()
    {
        $rules = array(
            array('local_check, remote_check', 'required'),
            array('local_check, remote_check', 'in', 'range' => array_keys($this->getCheckOptions())),
            array('remote_dnsbls, regular_expressions', 'safe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'local_check'         => Yii::t('settings', 'Local checks'),
            'remote_check'        => Yii::t('settings', 'Remote checks'),
            'remote_dnsbls'       => Yii::t('settings', 'Remote dnsbl'),
            'regular_expressions' => Yii::t('settings', 'Regular expressions'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'local_check'         => '',
            'remote_check'        => '',
            'remote_dnsbls'       => '',
            'regular_expressions' => "/abuse@(.*)/i\n/spam@(.*)/i\n/(.*)@abc\.com/i",
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'local_check'         => Yii::t('settings', 'Whether to check the email addresses against local database.'),
            'remote_check'        => Yii::t('settings', 'Whether to check the email addresses against remote DNSRBL services.'),
            'regular_expressions' => Yii::t('settings', 'List of regular expressions for blacklisting an email. Please use one expression per line and make sure it is correct.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    protected function beforeValidate()
    {
        if ($this->remote_check == self::TEXT_YES && $this->local_check == self::TEXT_NO) {
            $this->addError('remote_check', Yii::t('settings', 'Local check must be enabled in order to perform remote checks!'));
        }
        
        if (!is_array($this->remote_dnsbls)) {
            $this->remote_dnsbls = array();
        }
        
        $this->remote_dnsbls = array_unique($this->remote_dnsbls);
        
        $errors = array();
        foreach ($this->remote_dnsbls as $index => $domain) {
            $domain = trim($domain);
            if (empty($domain)) {
                unset($this->remote_dnsbls[$index]);
                continue;
            }
            
            if (!@parse_url('tcp://'.$domain, PHP_URL_HOST)) {
                $errors[] = Yii::t('settings', 'The DNSBL domain of {domain} does not seem to be valid!', array(
                    '{domain}' => $domain,
                ));
            }
        }
        
        if (!empty($errors)) {
            $this->addError('remote_dnsbls', implode('<br />', $errors));
        }
        
        if (!$this->hasErrors() && count($this->remote_dnsbls) == 0) {
            $this->remote_check = self::TEXT_NO;
        }
        
        return parent::beforeValidate();
    }
    
    public function getCheckOptions()
    {
        return $this->getYesNoOptions();
    }
}
