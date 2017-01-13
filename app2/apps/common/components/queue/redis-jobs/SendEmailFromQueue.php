<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * SendEmailFromQueue
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5
 * 
 * Please see https://github.com/chrisboulton/php-resque for more info.
 */
 
class SendEmailFromQueue
{
    public function setUp()
    {
        // ... Set up environment for this job
    }
    
    public function perform()
    {
        // if for some reason default args are not found, stop.
        if (empty($this->args['server_id']) || empty($this->args['server_type']) || empty($this->args['message_id']) || empty($this->args['campaign_id'])) {
            return;
        }

        try {
            
            // find the delivery server
            $mapping = DeliveryServer::getTypesMapping();
            if (empty($mapping[$this->args['server_type']])) {
                throw new Exception("QUEUE: Did not find the delivery server type!");
            }
            $server = DeliveryServer::model($mapping[$this->args['server_type']])->findByPk($this->args['server_id']);
            if (empty($server)) {
                throw new Exception("QUEUE: Did not find the delivery server!");
            }
            $server->disableLogUsage();
            
            $campaign = Campaign::model()->findByPk($this->args['campaign_id']);
            if (empty($campaign)) {
                throw new Exception("QUEUE: Did not find the campaign!");
            }
            
            // set delivery object 
            $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign);
            
            // send the email
            $sent = $server->sendEmail($this->args['params']);
            
            // this step is for web apis(i.e: amazon ses) where we use the message ID to process the bounces later
            // so we need the message id to be accurate.
            if ($sent && !empty($sent['message_id']) && stripos($server->type, '-api') !== false) {
                CampaignDeliveryLog::model()->updateAll(array(
                    'email_message_id' => $sent['message_id']
                ), 'campaign_id = :cid AND email_message_id = :mid', array(
                    ':cid' => $this->args['campaign_id'], 
                    ':mid' => $this->args['message_id']
                ));
            }
            
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
    }
    
    public function tearDown()
    {
        // ... Remove environment for this job
    }
}