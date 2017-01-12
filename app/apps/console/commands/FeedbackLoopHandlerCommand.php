<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FeedbackLoopHandlerCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class FeedbackLoopHandlerCommand extends CConsoleCommand 
{
    // lock name
    protected $_lockName;
    
    // flag 
    protected $_restoreStates = true;
    
    // flag
    protected $_improperShutDown = false;
    
    // current server
    protected $_server;
    
    public function init()
    {
        parent::init();

        // set the lock name
        $this->_lockName = md5(__FILE__);

        // this will catch exit signals and restore states
        if (CommonHelper::functionExists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT,  array($this, '_handleExternalSignal'));  
            pcntl_signal(SIGTERM, array($this, '_handleExternalSignal'));
            pcntl_signal(SIGHUP,  array($this, '_handleExternalSignal'));
        }
        
        register_shutdown_function(array($this, '_restoreStates'));
        Yii::app()->attachEventHandler('onError', array($this, '_restoreStates'));
        Yii::app()->attachEventHandler('onException', array($this, '_restoreStates'));
    }
    
    public function _handleExternalSignal($signalNumber)
    {
        // this will trigger all the handlers attached via register_shutdown_function
        $this->_improperShutDown = true;
        exit;
    }

    public function _restoreStates($event = null)
    {
        if (!$this->_restoreStates) {
            return;
        }
        $this->_restoreStates = false;
        
        // remove the lock
        Yii::app()->mutex->release($this->_lockName);
                
        // called as a callback from register_shutdown_function
        // must pass only if improper shutdown in this case
        if ($event === null && !$this->_improperShutDown) {
            return;
        }

        if (!empty($this->_server) && $this->_server instanceof FeedbackLoopServer) {
            if ($this->_server->status == FeedbackLoopServer::STATUS_CRON_RUNNING) {
                $this->_server->status = FeedbackLoopServer::STATUS_ACTIVE;
                $this->_server->saveStatus();
            }
        }
    }
    
    public function actionIndex() 
    {
        // because some cli are not compiled same way with the web module.
        if (!CommonHelper::functionExists('imap_open')) {
            Yii::log(Yii::t('servers', 'The PHP CLI binary is missing the IMAP extension!'), CLogger::LEVEL_ERROR);
            return 1;
        }
        
        if (!Yii::app()->mutex->acquire($this->_lockName, 5)) {
            return 0;
        }
        
        Yii::import('common.vendors.BounceHandler.*');
        $options = Yii::app()->options;
        
        if ($memoryLimit = $options->get('system.cron.process_feedback_loop_servers.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);    
        }
        
        // added in 1.3.4.7
        Yii::app()->hooks->doAction('console_command_feedback_loop_handler_before_process', $this);
        
        $this->process(0, (int)$options->get('system.cron.process_feedback_loop_servers.servers_at_once', 10));
        
        // added in 1.3.4.7
        Yii::app()->hooks->doAction('console_command_feedback_loop_handler_after_process', $this);
        
        Yii::app()->mutex->release($this->_lockName);
        
        return 0;
    }
    
    protected function process($offset = 0, $limit = 10)
    {
        $servers = FeedbackLoopServer::model()->findAll(array(
            'select'    => 't.server_id',
            'condition' => 't.status = :status',
            'params'    => array(':status' => FeedbackLoopServer::STATUS_ACTIVE),
            'limit'     => (int)$limit,
            'offset'    => (int)$offset,
        ));
        
        if (empty($servers)) {
            return $this;
        }
        
        $serversIds = array();
        foreach ($servers as $server) {
            $serversIds[] = $server->server_id;
        }
        unset($servers, $server);
        
        $options      = Yii::app()->options->resetLoaded();
        $processLimit = (int)$options->get('system.cron.process_feedback_loop_servers.emails_at_once', 500);
        
        try {
            foreach ($serversIds as $serverId) {
                $this->_server = FeedbackLoopServer::model()->findByPk((int)$serverId);
                if (empty($this->_server) || $this->_server->status != FeedbackLoopServer::STATUS_ACTIVE) {
                    $this->_server = null;
                    continue;
                }
                $this->_server->status = FeedbackLoopServer::STATUS_CRON_RUNNING;
                $this->_server->saveStatus();

                // close the db connection because it will time out!
                Yii::app()->getDb()->setActive(false);
                
                $bounceHandler = new BounceHandler($this->_server->getConnectionString(), $this->_server->username, $this->_server->password, array(
                    'processOnlyFeedbackReports' => true,
                    'deleteMessages'             => true,
                    'deleteAllMessages'          => $this->_server->getDeleteAllMessages(),
                    'processLimit'               => $processLimit,
                    'searchCharset'              => $this->_server->getSearchCharset(),
                    'imapOpenParams'             => $this->_server->getImapOpenParams(),
                    'requiredHeaders'            => array(
                        Yii::app()->params['email.custom.header.prefix'] . 'Campaign-Uid', 
                        Yii::app()->params['email.custom.header.prefix'] . 'Subscriber-Uid'
                    ),
                ));
    
                $results = $bounceHandler->getResults();
                
                // re-open the db connection
                Yii::app()->getDb()->setActive(true);
                
                if (empty($results)) {
                    $this->_server = FeedbackLoopServer::model()->findByPk((int)$this->_server->server_id);
                    if (empty($this->_server)) {
                        continue;
                    }
                    if ($this->_server->status == FeedbackLoopServer::STATUS_CRON_RUNNING) {
                        $this->_server->status = FeedbackLoopServer::STATUS_ACTIVE;
                        $this->_server->saveStatus();
                    }
                    continue;
                }
                
                foreach ($results as $result) {
                    foreach ($result['originalEmailHeadersArray'] as $key => $value) {
                        unset($result['originalEmailHeadersArray'][$key]);
                        $result['originalEmailHeadersArray'][strtoupper($key)] = $value;
                    }
                    
                    if (!isset($result['originalEmailHeadersArray']['X-MW-CAMPAIGN-UID'], $result['originalEmailHeadersArray']['X-MW-SUBSCRIBER-UID'])) {
                        continue;
                    }
                    
                    $campaignUid    = trim($result['originalEmailHeadersArray']['X-MW-CAMPAIGN-UID']);
                    $subscriberUid  = trim($result['originalEmailHeadersArray']['X-MW-SUBSCRIBER-UID']);
                    
                    $campaign = Campaign::model()->findByUid($campaignUid);
                    if (empty($campaign)) {
                        continue;
                    }
                    
                    $subscriber = ListSubscriber::model()->findByAttributes(array(
                        'list_id'           => $campaign->list->list_id,
                        'subscriber_uid'    => $subscriberUid,
                        'status'            => ListSubscriber::STATUS_CONFIRMED,
                    ));
                    
                    if (empty($subscriber)) {
                        continue;
                    }
                    
                    if ($options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                        $subscriber->delete();
                    } else {
                        $subscriber->status = ListSubscriber::STATUS_UNSUBSCRIBED;
                        $subscriber->save(false);
                        
                        $trackUnsubscribe = new CampaignTrackUnsubscribe();
                        $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                        $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                        $trackUnsubscribe->note          = 'Unsubscribed via FBL Report!';
                        $trackUnsubscribe->save(false);
                    }
                }

                $this->_server = FeedbackLoopServer::model()->findByPk((int)$this->_server->server_id);
                if (empty($this->_server)) {
                    continue;
                }
                
                if ($this->_server->status == FeedbackLoopServer::STATUS_CRON_RUNNING) {
                    $this->_server->status = FeedbackLoopServer::STATUS_ACTIVE;
                    $this->_server->saveStatus();
                }
                
                // close the db connection, save some resources...
                Yii::app()->getDb()->setActive(false);
                
                // sleep
                sleep((int)$options->get('system.cron.process_feedback_loop_servers.pause', 5));
                
                // open db connection
                Yii::app()->getDb()->setActive(true);
            }
        } catch (Exception $e) {
            if (!empty($this->_server)) {
                Yii::app()->getDb()->setActive(true);
                $this->_server = FeedbackLoopServer::model()->findByPk((int)$this->_server->server_id);
                if (!empty($this->_server) && $this->_server->status == FeedbackLoopServer::STATUS_CRON_RUNNING) {
                    $this->_server->status = FeedbackLoopServer::STATUS_ACTIVE;
                    $this->_server->saveStatus();
                }    
            }
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        
        $this->_server = null;
        
        return $this->process($offset + (int)$options->get('system.cron.process_feedback_loop_servers.servers_at_once', 10), $limit);
    }
}