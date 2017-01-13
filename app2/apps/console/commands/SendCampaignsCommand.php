<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SendCampaignsCommand
 *
 * Please do not alter/extend this file as it is subject to major changes always and future updates will break your app.
 * Since 1.3.5.9 this file has been changed drastically.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 *
 */

class SendCampaignsCommand extends ConsoleCommand
{
    // current campaign
    protected $_campaign;

    // flag
    protected $_restoreStates = true;

    // flag
    protected $_improperShutDown = false;

    // global command arguments

    // what type of campaigns this command is sending
    public $campaigns_type = '';

    // how many campaigns to process at once
    public $campaigns_limit = 0;

    // from where to start
    public $campaigns_offset = 0;
    
    // 1.3.7.3
    protected $_lockName;
    
    // 1.3.7.3 - hold customer data.
    protected $_customerData = array();

    // 1.3.7.9 - experimental
    protected $_useTempQueueTables = false;
    
    public function init()
    {
        parent::init();

        // set the lock name
        // in pcntl mode first batch closes the campaign thus we should lock on campaign type
        // and don't allow limit/offset in the lock since it can cause duplicate sending
        $this->_lockName = __FILE__ . date('Y-m-d') . $this->campaigns_type;
        $this->_lockName = sha1($this->_lockName);
        
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

        // if more than 6 hours then something is def. wrong?
        ini_set('max_execution_time', 6 * 3600);
        set_time_limit(6 * 3600);
        
        // 
        if ($memoryLimit = Yii::app()->options->get('system.cron.send_campaigns.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        
        if (isset(Yii::app()->params['send.campaigns.command.useTempQueueTables'])) {
            $this->_useTempQueueTables = (bool)Yii::app()->params['send.campaigns.command.useTempQueueTables'];
        }
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
        if ($this->getCanUsePcntl()) {
            Yii::app()->mutex->release($this->_lockName);
        }
        
        // called as a callback from register_shutdown_function
        // must pass only if improper shutdown in this case
        if ($event === null && !$this->_improperShutDown) {
            return;
        }

        if (!empty($this->_campaign) && $this->_campaign instanceof Campaign) {
            if ($this->_campaign->isProcessing) {
                $this->_campaign->saveStatus(Campaign::STATUS_SENDING);
            }
        }
    }

    public function actionIndex()
    {
        $timeStart        = microtime(true);
        $memoryUsageStart = memory_get_peak_usage(true);
        
        // added in 1.3.4.7
        Yii::app()->hooks->doAction('console_command_send_campaigns_before_process', $this);

        $result = $this->process();
        
        // 1.3.7.5 - do we need to send notifications for reaching the quota?
        // we do this after processing to not send notifications before the sending actually ends...
        if ($result === 0) {
            $this->checkCustomersQuotaLimits();
        }

        // added in 1.3.4.7
        Yii::app()->hooks->doAction('console_command_send_campaigns_after_process', $this);

        $timeEnd        = microtime(true);
        $memoryUsageEnd = memory_get_peak_usage(true);
        
        $time        = round($timeEnd - $timeStart, 2);
        $memoryUsage = CommonHelper::formatBytes($memoryUsageEnd - $memoryUsageStart);
        $this->stdout(sprintf('This cycle completed in %s and used %s of memory!', $time . ' seconds', $memoryUsage));
        
        if (CommonHelper::functionExists('sys_getloadavg')) {
            list($_1, $_5, $_15) = sys_getloadavg();
            $this->stdout(sprintf('CPU usage in last minute: %.2f, in last 5 minutes: %.2f, in last 15 minutes: %.2f!', $_1, $_5, $_15));
        }
        
        return $result;
    }

    protected function process()
    {
        // 1.3.7.3 - mutex
        if ($this->getCanUsePcntl() && !Yii::app()->mutex->acquire($this->_lockName, 5)) {
            return 1;
        }
        
        $options  = Yii::app()->options;
        $statuses = array(Campaign::STATUS_SENDING, Campaign::STATUS_PENDING_SENDING);
        $types    = array(Campaign::TYPE_REGULAR, Campaign::TYPE_AUTORESPONDER);
        $limit    = (int)$options->get('system.cron.send_campaigns.campaigns_at_once', 10);
        
        if ($this->campaigns_type !== null && !in_array($this->campaigns_type, $types)) {
            $this->campaigns_type = null;
        }

        if ((int)$this->campaigns_limit > 0) {
            $limit = (int)$this->campaigns_limit;
        }

        $criteria = new CDbCriteria();
        $criteria->addInCondition('t.status', $statuses);
        $criteria->addCondition('t.send_at <= NOW()');
        if (!empty($this->campaigns_type)) {
            $criteria->addCondition('t.type = :type');
            $criteria->params[':type'] = $this->campaigns_type;
        }
        $criteria->order  = 't.campaign_id ASC';
        $criteria->limit  = $limit;
        $criteria->offset = (int)$this->campaigns_offset;

        // offer a chance to alter this criteria.
        $criteria = Yii::app()->hooks->applyFilters('console_send_campaigns_command_find_campaigns_criteria', $criteria, $this);

        // in case it has been changed in hook
        $criteria->limit = $limit;

        $this->stdout(sprintf("Loading %d campaigns, starting with offset %d...", $criteria->limit, $criteria->offset));

        // and find all campaigns matching the criteria
        $campaigns = Campaign::model()->findAll($criteria);

        if (empty($campaigns)) {
            
            $this->stdout("No campaign found, stopping.");
            
            // remove the lock
            if ($this->getCanUsePcntl()) {
                Yii::app()->mutex->release($this->_lockName);
            }
            
            return 0;
        }

        $this->stdout(sprintf("Found %d campaigns and now starting processing them...", count($campaigns)));
        if ($this->getCanUsePcntl()) {
            $this->stdout(sprintf(
                'Since PCNTL is active, we will send %d campaigns in parallel and for each campaign, %d batches of subscribers in parallel.',
                $this->getCampaignsInParallel(),
                $this->getSubscriberBatchesInParallel()
            ));
        }
  
        $customersFail = array();
        $campaignIds   = array();
        $options       = Yii::app()->options;
        
        foreach ($campaigns as $campaign) {
            
            // reference
            $customer = $campaign->customer;
            
            // already processed but failed
            if (in_array($customer->customer_id, $customersFail)) {
                continue;
            }
            
            if (!$customer->getIsActive()) {
                Yii::log(Yii::t('campaigns', 'This customer is inactive!'), CLogger::LEVEL_ERROR);
                $campaign->saveStatus(Campaign::STATUS_PAUSED);
                $this->stdout("This customer is inactive!");
                $customersFail[] = $customer->customer_id;
                continue;
            }

            if ($customer->getIsOverQuota()) {
                Yii::log(Yii::t('campaigns', 'This customer(ID:{cid}) reached the assigned quota!', array('{cid}' => $customer->customer_id)), CLogger::LEVEL_ERROR);
                $campaign->saveStatus(Campaign::STATUS_PAUSED);
                $this->stdout("This customer reached the assigned quota!");
                $customersFail[] = $customer->customer_id;
                continue;
            }
            
            // 1.3.7.9 - create the queue table and populate it...
            if ($this->_useTempQueueTables && $campaign->isRegular) {

                // put proper status
                $this->stdout('Temporary changing the campaign status into PROCESSING!');
                $campaign->saveStatus(Campaign::STATUS_PROCESSING);
                
                $campaign->attachBehavior('temporaryQueueTable', array(
                    'class' => 'common.components.db.behaviors.CampaignTemporaryQueueTableBehavior',
                ));
                $campaign->temporaryQueueTable->verbose = $this->verbose;
                $campaign->temporaryQueueTable->populateTable();

                $this->stdout('Restoring the campaign status to SENDING!');
                $campaign->saveStatus(Campaign::STATUS_SENDING);
            }
            
            // populate the campaigns array
            $campaignIds[] = $campaign->campaign_id;
            
            // counter
            $subscribersAtOnce = (int)$customer->getGroupOption('campaigns.subscribers_at_once', (int)$options->get('system.cron.send_campaigns.subscribers_at_once', 300));
            if ($this->getCanUsePcntl()) {
                $subscribersAtOnce *= $this->getSubscriberBatchesInParallel();
            }
            
            // 1.3.7.3 - precheck and allow because pcntl mainly
            if (!isset($this->_customerData[$campaign->customer_id])) {
                $quotaTotal  = (int)$customer->getGroupOption('sending.quota', -1);

                $quotaUsage = 0;
                $quotaLeft  = PHP_INT_MAX;
                if ($quotaTotal > -1) {
                    $quotaUsage = (int)$customer->countUsageFromQuotaMark();
                    $quotaLeft  = $quotaTotal - $quotaUsage;
                    $quotaLeft  = $quotaLeft >= 0 ? $quotaLeft : 0;
                }
                
                $this->_campaign = $campaign;
                $this->_customerData[$campaign->customer_id] = array(
                    'customer'          => $customer,
                    'campaigns'         => array(),
                    'quotaTotal'        => $quotaTotal,
                    'quotaUsage'        => $quotaUsage,
                    'quotaLeft'         => $quotaLeft,
                    'subscribersAtOnce' => $subscribersAtOnce,
                    'subscribersCount'  => $this->countSubscribers(),
                );
                $this->_campaign = null;
            }
            
            $campaignMaxSubscribers = 0;
            if ($this->_customerData[$campaign->customer_id]['quotaLeft'] > 0) {
                if ($this->_customerData[$campaign->customer_id]['quotaLeft'] >= $subscribersAtOnce) {
                    $campaignMaxSubscribers = $subscribersAtOnce;
                } else {
                    $campaignMaxSubscribers = $this->_customerData[$campaign->customer_id]['quotaLeft'];
                }
                $this->_customerData[$campaign->customer_id]['quotaLeft'] -= $subscribersAtOnce;
                if ($this->_customerData[$campaign->customer_id]['quotaLeft'] < 0) {
                    $this->_customerData[$campaign->customer_id]['quotaLeft'] = 0;
                }
            }
            
            // how much each campaign is allowed to send
            $this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id] = $campaignMaxSubscribers;
        }
        
        // 1.3.7.5
        foreach ($campaigns as $campaign) {
            if (!$campaign->option->canSetMaxSendCount) {
                continue;
            }

            $campaignDeliveryLogSuccessCount = CampaignDeliveryLog::model()->countByAttributes(array(
                'campaign_id' => $campaign->campaign_id,
                'status'      => CampaignDeliveryLog::STATUS_SUCCESS,
            ));
            
            $sendingsLeft = $campaign->option->max_send_count - $campaignDeliveryLogSuccessCount;
            $sendingsLeft = $sendingsLeft >= 0 ? $sendingsLeft : 0;
            
            if (!$sendingsLeft) {

                unset($this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id]);
                if (($idx = array_search($campaign->campaign_id, $campaignIds)) !== false) {
                    unset($campaignIds[$idx]);
                }

                $this->_campaign = $campaign;
                if ($this->markCampaignSent()) {
                    $this->stdout('Campaign has been marked as sent because of MaxSendCount settings!');
                }
                $this->_campaign = null;
                
                continue;
            }

            $campaignMaxSubscribers = $this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id];
            if ($sendingsLeft < $campaignMaxSubscribers) {
                $this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id] = $sendingsLeft;
                continue;
            }
        }
        unset($campaigns);
        //
        
        $this->sendCampaignStep0($campaignIds);

        // remove the lock
        if ($this->getCanUsePcntl()) {
            Yii::app()->mutex->release($this->_lockName);
        }
        
        return 0;
    }

    protected function sendCampaignStep0(array $campaignIds = array())
    {
        $handled = false;

        if ($this->getCanUsePcntl() && ($campaignsInParallel = $this->getCampaignsInParallel()) > 1) {
            $handled = true;

            // make sure we close the database connection
            Yii::app()->getDb()->setActive(false);

            $campaignChunks = array_chunk($campaignIds, $campaignsInParallel);
            foreach ($campaignChunks as $index => $cids) {
                $childs = array();
                foreach ($cids as $cid) {
                    $pid = pcntl_fork();
                    if($pid == -1) {
                        continue;
                    }

                    // Parent
                    if ($pid) {
                        $childs[] = $pid;
                    }

                    // Child
                    if (!$pid) {
                        $mutexKey = sprintf('send-campaigns:campaign:%d:date:%s', $cid, date('Ymd'));
                        if (Yii::app()->mutex->acquire($mutexKey)) {
                            $this->sendCampaignStep1($cid, $index+1);
                            Yii::app()->mutex->release($mutexKey);
                        }
                        exit;
                    }
                }

                while (count($childs) > 0) {
                    foreach ($childs as $key => $pid) {
                        $res = pcntl_waitpid($pid, $status, WNOHANG);
                        if($res == -1 || $res > 0) {
                            unset($childs[$key]);
                        }
                    }
                    sleep(1);
                }
            }
        }

        if (!$handled) {
            foreach ($campaignIds as $campaignId) {
                $mutexKey = sprintf('send-campaigns:campaign:%d:date:%s', $campaignId, date('Ymd'));
                if (Yii::app()->mutex->acquire($mutexKey)) {
                    $this->sendCampaignStep1($campaignId, 0);
                    Yii::app()->mutex->release($mutexKey);
                }
            }
        }
    }

    protected function sendCampaignStep1($campaignId, $workerNumber = 0)
    {
        $this->stdout(sprintf("Campaign Worker #%d looking into the campaign with ID: %d", $workerNumber, $campaignId));

        $statuses = array(Campaign::STATUS_SENDING, Campaign::STATUS_PENDING_SENDING);
        $this->_campaign = $campaign = Campaign::model()->findByPk((int)$campaignId);
        
        // since 1.3.7.3
        Yii::app()->hooks->doAction('console_command_send_campaigns_send_campaign_step1_start', $campaign);
        
        if (empty($this->_campaign) || !in_array($this->_campaign->status, $statuses)) {
            $this->stdout(sprintf("The campaign with ID: %d is not ready for processing.", $campaignId));
            return 1;
        }

        // this should never happen unless the list is removed while sending
        if (empty($campaign->list_id)) {
            $this->stdout(sprintf("The campaign with ID: %d is not ready for processing.", $campaignId));
            return 1;
        }
        
        // since 1.3.7.9
        if ($this->_useTempQueueTables && $campaign->isRegular) {
            $campaign->attachBehavior('temporaryQueueTable', array(
                'class' => 'common.components.db.behaviors.CampaignTemporaryQueueTableBehavior',
            ));
            $campaign->temporaryQueueTable->verbose = $this->verbose;
        }
        //
        
        $options  = Yii::app()->options;
        $list     = $campaign->list;
        $customer = $list->customer;

        $this->stdout(sprintf("This campaign belongs to %s(uid: %s).", $customer->getFullName(), $customer->customer_uid));
        
        // put proper status
        $this->stdout('Changing the campaign status into PROCESSING!');
        $campaign->saveStatus(Campaign::STATUS_PROCESSING);
        
        $dsParams = array('customerCheckQuota' => false, 'useFor' => array(DeliveryServer::USE_FOR_CAMPAIGNS));
        $server   = DeliveryServer::pickServer(0, $campaign, $dsParams);
        if (empty($server)) {
            Yii::log(Yii::t('campaigns', 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!'), CLogger::LEVEL_ERROR);
            $this->stdout('Cannot find a valid server to send the campaign email, aborting until a delivery server is available!');
            $campaign->saveStatus(Campaign::STATUS_SENDING);
            return 1;
        }

        if (!empty($customer->language_id)) {
            $language = Language::model()->findByPk((int)$customer->language_id);
            if (!empty($language)) {
                Yii::app()->setLanguage($language->getLanguageAndLocaleCode());
            }
        }

        // find the subscribers limit
        $limit = (int)$customer->getGroupOption('campaigns.subscribers_at_once', (int)Yii::app()->options->get('system.cron.send_campaigns.subscribers_at_once', 300));
        
        $mailerPlugins = array(
            'loggerPlugin' => true,
        );

        $sendAtOnce = (int)$customer->getGroupOption('campaigns.send_at_once', (int)$options->get('system.cron.send_campaigns.send_at_once', 0));
        if (!empty($sendAtOnce)) {
            $mailerPlugins['antiFloodPlugin'] = array(
                'sendAtOnce' => $sendAtOnce,
                'pause'      => (int)$customer->getGroupOption('campaigns.pause', (int)$options->get('system.cron.send_campaigns.pause', 0)),
            );
        }

        $perMinute = (int)$customer->getGroupOption('campaigns.emails_per_minute', (int)$options->get('system.cron.send_campaigns.emails_per_minute', 0));
        if (!empty($perMinute)) {
            $mailerPlugins['throttlePlugin'] = array(
                'perMinute' => $perMinute,
            );
        }

        $attachments = CampaignAttachment::model()->findAll(array(
            'select'    => 'file',
            'condition' => 'campaign_id = :cid',
            'params'    => array(':cid' => $campaign->campaign_id),
        ));

        $changeServerAt = (int)$customer->getGroupOption('campaigns.change_server_at', (int)$options->get('system.cron.send_campaigns.change_server_at', 0));
        $maxBounceRate  = (int)$customer->getGroupOption('campaigns.max_bounce_rate', (int)$options->get('system.cron.send_campaigns.max_bounce_rate', -1));

        $this->sendCampaignStep2(array(
            'campaign'                => $campaign,
            'customer'                => $customer,
            'list'                    => $list,
            'server'                  => $server,
            'mailerPlugins'           => $mailerPlugins,
            'limit'                   => $limit,
            'offset'                  => 0,
            'changeServerAt'          => $changeServerAt,
            'maxBounceRate'           => $maxBounceRate,
            'options'                 => $options,
            'canChangeCampaignStatus' => true,
            'attachments'             => $attachments,
            'workerNumber'            => 0,
        ));
    }

    protected function sendCampaignStep2(array $params = array())
    {
        // max number of subs allowed to send this time
        $maxSubscribers = $this->_customerData[$params['customer']->customer_id]['campaigns'][$params['campaign']->campaign_id];
        
        $handled = false;
        if ($this->getCanUsePcntl() && ($subscriberBatchesInParallel = $this->getSubscriberBatchesInParallel()) > 1) {
            $handled = true;
            
            // make sure we deny this for all right now.
            $params['canChangeCampaignStatus'] = false;
            
            // make sure we close the database connection
            Yii::app()->getDb()->setActive(false);
            
            $childs = array();
            $subscriberBatchesInParallelCounter = $subscriberBatchesInParallel;
            for($i = 0; $i < $subscriberBatchesInParallel; ++$i) {
                
                // 1.3.5.7
                if ($maxSubscribers <= $params['limit']) {
                    $params['limit'] = $maxSubscribers;
                }
                $maxSubscribers -= $params['limit'];
                $maxSubscribers  = $maxSubscribers > 0 ? $maxSubscribers : 0;
                $params['limit'] = $params['limit'] > 0 ? $params['limit'] : 0;
                $subscriberBatchesInParallelCounter--;
                //
                
                $pid = pcntl_fork();
                if($pid == -1) {
                    continue;
                }

                // Parent
                if ($pid) {
                    $childs[] = $pid;
                }

                // Child
                if (!$pid) {
                    $params['workerNumber'] = $i + 1;
                    $params['offset'] = ($i * $params['limit']);
                    $params['canChangeCampaignStatus'] = ($i == 0); // keep an eye on this.

                    $mutexKey = sprintf('send-campaigns:campaign:%s:date:%s:offset:%d:limit:%d', 
                        $params['campaign']->campaign_uid, 
                        date('Ymd'),
                        $params['offset'], 
                        $params['limit']
                    );
                    
                    if (Yii::app()->mutex->acquire($mutexKey)) {
                        $this->sendCampaignStep3($params);
                        Yii::app()->mutex->release($mutexKey);
                    }
                    exit;
                }
            }

            if (count($childs) == 0) {
                $handled = false;
            }

            while (count($childs) > 0) {
                foreach ($childs as $key => $pid) {
                    $res = pcntl_waitpid($pid, $status, WNOHANG);
                    if($res == -1 || $res > 0) {
                        unset($childs[$key]);
                    }
                }
                sleep(1);
            }
        }

        if (!$handled) {

            // 1.3.5.7
            if ($maxSubscribers > $params['limit']) {
                $maxSubscribers -= $params['limit'];
            } else {
                $params['limit'] = $maxSubscribers;
            }
            $params['limit'] = $params['limit'] > 0 ? $params['limit'] : 0;
            //
            
            $mutexKey = sprintf('send-campaigns:campaign:%s:date:%s:offset:%d:limit:%d',
                $params['campaign']->campaign_uid,
                date('Ymd'),
                $params['offset'],
                $params['limit']
            );

            if (Yii::app()->mutex->acquire($mutexKey)) {
                $this->sendCampaignStep3($params);
                Yii::app()->mutex->release($mutexKey);
            }
        }

        return 0;
    }

    protected function sendCampaignStep3(array $params = array())
    {
        extract($params, EXTR_SKIP);

        $this->stdout(sprintf("Looking for subscribers for campaign with uid %s...(This is subscribers worker #%d)", $campaign->campaign_uid, $workerNumber));

        $subscribers = $this->findSubscribers($offset, $limit);
        
        $this->stdout(sprintf("This subscribers worker(#%d) will process %d subscribers for this campaign...", $workerNumber, count($subscribers)));
        
        // run some cleanup on subscribers
        $this->stdout("Running subscribers cleanup...");

        // since 1.3.6.2 - in some very rare conditions this happens!
        foreach ($subscribers as $index => $subscriber) {
            if (empty($subscriber->email)) {
                $subscriber->delete();
                unset($subscribers[$index]);
                continue;
            }
            
            // 1.3.7
            $separators = array(',', ';');
            foreach ($separators as $separator) {
                if (strpos($subscriber->email, $separator) === false) {
                    continue;
                }
                
                $emails = explode($separator, $subscriber->email);
                $emails = array_map('trim', $emails);
                
                while (!empty($emails)) {
                    $email = array_shift($emails);
                    if (!FilterVarHelper::email($email)) {
                        continue;
                    }
                    $exists = ListSubscriber::model()->findByAttributes(array(
                        'list_id' => $subscriber->list_id,
                        'email'   => $email,
                    ));
                    if (!empty($exists)) {
                        continue;
                    }
                    $subscriber->email = $email;
                    $subscriber->save(false);
                    break;
                }
                
                foreach ($emails as $index => $email) {
                    if (!FilterVarHelper::email($email)) {
                        continue;
                    }
                    $exists = ListSubscriber::model()->findByAttributes(array(
                        'list_id' => $subscriber->list_id,
                        'email'   => $email,
                    ));
                    if (!empty($exists)) {
                        continue;
                    }
                    $sub = new ListSubscriber();
                    $sub->list_id = $subscriber->list_id;
                    $sub->email   = $email;
                    $sub->save();
                }
                break;
            }
            //
            
            if (!FilterVarHelper::email($subscriber->email)) {
                $subscriber->delete();
                unset($subscribers[$index]);
                continue;
            }
        }
        
        // reset the keys
        $subscribers      = array_values($subscribers);
        $subscribersCount = count($subscribers);
        
        $this->stdout(sprintf("Checking subscribers count after cleanup: %d", $subscribersCount));
        
        try {
            
            $params['subscribers'] = &$subscribers;
            
            $this->processSubscribersLoop($params);
            
            // free mem
            unset($params);
            
        } catch (Exception $e) {
            
            // free mem
            unset($params);
            
            $this->stdout(sprintf('Exception thrown: %s', $e->getMessage()));

            // exception code to be returned later
            $code = (int)$e->getCode();

            // make sure sending is resumed next time.
            $campaign->status = Campaign::STATUS_SENDING;

            // pause the campaigns of customers that reached the quota
            // they will only delay processing of other campaigns otherwise.
            if ($code == 98) {
                $campaign->status = Campaign::STATUS_PAUSED;
            }

            if ($canChangeCampaignStatus) {
                // save the changes, but no validation
                $campaign->saveStatus();

                // since 1.3.5.9
                $this->checkCampaignOverMaxBounceRate($campaign, $maxBounceRate);
            }

            // log the error so we can reference it
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

            // return the exception code
            return $code;
        }

        $this->stdout("", false);
        $this->stdout(sprintf('Done processing %d subscribers!', $subscribersCount));
       
        if ($canChangeCampaignStatus) {
            
            // do a final check for this campaign to see if it still exists or has been somehow changed from web interface.
            // this used to exist in the foreach loop but would cause so much overhead that i think is better to move it here
            // since if a campaign is paused from web interface it will keep that status anyway so it won't affect customers and will improve performance
            $_campaign = Yii::app()->getDb()->createCommand()
                ->select('status')
                ->from($campaign->tableName())
                ->where('campaign_id = :cid', array(':cid' => (int)$campaign->campaign_id))
                ->queryRow();

            if (empty($_campaign) || $_campaign['status'] != Campaign::STATUS_PROCESSING) {
                if (!empty($_campaign)) {
                    $campaign->saveStatus($_campaign['status']);
                    $this->checkCampaignOverMaxBounceRate($campaign, $maxBounceRate);
                    $this->stdout('Campaign status has been changed successfully!');
                }
                return 0;
            }

            // the sending batch is over.
            
            // if we don't have enough subscribers for next batch, we stop.
            $subscribers = $this->countSubscribers();
            if (empty($subscribers)) {
                if ($this->markCampaignSent()) {
                    $this->stdout('Campaign has been marked as sent!');
                }
                return 0;
            }

            // make sure sending is resumed next time
            $campaign->saveStatus(Campaign::STATUS_SENDING);
            $this->checkCampaignOverMaxBounceRate($campaign, $maxBounceRate);
            $this->stdout('Campaign status has been changed successfully!');
        }

        $this->stdout('Done processing the campaign.');

        return 0;
    }
    
    protected function processSubscribersLoop(array $params = array()) 
    {
        extract($params, EXTR_SKIP);
        
        $subscribersCount = count($subscribers);
        $processedCounter = 0;
        $failuresCount    = 0;
        $serverHasChanged = false;
        
        $dsParams = empty($dsParams) || !is_array($dsParams) ? array() : $dsParams;
        $dsParams = CMap::mergeArray(array(
            'customerCheckQuota' => false,
            'serverCheckQuota'   => false,
            'useFor'             => array(DeliveryServer::USE_FOR_CAMPAIGNS),
            'excludeServers'     => array(),
        ), $dsParams);
        $domainPolicySubscribers = array();

        if (empty($server) && !($server = DeliveryServer::pickServer(0, $campaign, $dsParams))) {
            if (empty($serverNotFoundMessage)) {
                $serverNotFoundMessage = Yii::t('campaigns', 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!');
            }
            throw new Exception($serverNotFoundMessage, 99);
        }

        // $this->stdout('Sorting the subscribers...');
        // $subscribers = $this->sortSubscribers($subscribers);
        
        $this->stdout(sprintf('Entering the foreach processing loop for all %d subscribers...', $subscribersCount));
        
        foreach ($subscribers as $index => $subscriber) {
            $this->stdout("", false);
            $this->stdout(sprintf("%s - %d/%d", $subscriber->email, ($index+1), $subscribersCount));
            $this->stdout(sprintf('Checking if we can send to domain of %s...', $subscriber->email));
            
            // if this server is not allowed to send to this email domain, then just skip it.
            if (!$server->canSendToDomainOf($subscriber->email)) {
                $domainPolicySubscribers[] = $subscriber;
                unset($subscribers[$index]);
                continue;
            }

            $this->stdout(sprintf('Checking if %s is blacklisted...', $subscriber->email));
            // if blacklisted, goodbye.
            if ($blCheckInfo = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_CAMPAIGN))) {
                if ($blCheckInfo->customerBlacklist) {
                    $this->logDelivery($subscriber, $blCheckInfo->reason, CampaignDeliveryLog::STATUS_BLACKLISTED, null, $server);
                } else {
                    $this->logDelivery($subscriber, Yii::t('campaigns', 'This email is blacklisted. Sending is denied!'), CampaignDeliveryLog::STATUS_BLACKLISTED, null, $server);
                }
                continue;
            }

            $this->stdout('Checking if the server is over quota...');
            // in case the server is over quota
            if ($server->getIsOverQuota()) {
                $this->stdout('Server is over quota, choosing another one.');
                $currentServerId = $server->server_id;
                if (!($server = DeliveryServer::pickServer($currentServerId, $campaign, $dsParams))) {
                    throw new Exception(Yii::t('campaigns', 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!'), 99);
                }
            }

            $this->stdout('Preparing the entire email...');
            $emailParams = $this->prepareEmail($subscriber, $server);
            
            if (empty($emailParams) || !is_array($emailParams)) {
                $this->logDelivery($subscriber, Yii::t('campaigns', 'Unable to prepare the email content!'), CampaignDeliveryLog::STATUS_ERROR, null, $server);
                continue;
            }

            if ($failuresCount >= 5 || ($changeServerAt > 0 && $processedCounter >= $changeServerAt && !$serverHasChanged)) {
                $currentServerId = $server->server_id;
                if ($newServer = DeliveryServer::pickServer($currentServerId, $campaign, $dsParams)) {
                    $server = $newServer;
                    unset($newServer);
                }

                $failuresCount    = 0;
                $processedCounter = 0;
                $serverHasChanged = true;
            }

            $listUnsubscribeHeaderValue = $options->get('system.urls.frontend_absolute_url');
            $listUnsubscribeHeaderValue .= 'lists/'.$list->list_uid.'/unsubscribe/'.$subscriber->subscriber_uid . '/' . $campaign->campaign_uid . '/unsubscribe-direct?source=email-client-unsubscribe-button';
            $listUnsubscribeHeaderValue = '<'.$listUnsubscribeHeaderValue.'>';

            $reportAbuseUrl  = $options->get('system.urls.frontend_absolute_url');
            $reportAbuseUrl .= 'campaigns/'. $campaign->campaign_uid . '/report-abuse/' . $list->list_uid . '/' . $subscriber->subscriber_uid;

            // since 1.3.4.9
            // disabled since 1.3.6.2
            if (false && !empty($campaign->reply_to)) {
                $_subject = sprintf('[%s:%s] Please unsubscribe me.', $campaign->campaign_uid, $subscriber->subscriber_uid);
                $_body    = sprintf('Please unsubscribe me from %s list.', $list->display_name);
                $mailToUnsubscribeHeader    = sprintf(', <mailto:%s?subject=%s&body=%s>', $campaign->reply_to, $_subject, $_body);
                $listUnsubscribeHeaderValue .= $mailToUnsubscribeHeader;
            }
            
            $headerPrefix = Yii::app()->params['email.custom.header.prefix'];
            $emailParams['headers'] = array(
                array('name' => $headerPrefix . 'Campaign-Uid',   'value' => $campaign->campaign_uid),
                array('name' => $headerPrefix . 'Subscriber-Uid', 'value' => $subscriber->subscriber_uid),
                array('name' => $headerPrefix . 'Customer-Uid',   'value' => $customer->customer_uid),
                array('name' => $headerPrefix . 'Customer-Gid',   'value' => (string)intval($customer->group_id)), // because of sendgrid
                array('name' => $headerPrefix . 'Delivery-Sid',   'value' => (string)intval($server->server_id)), // because of sendgrid
                array('name' => $headerPrefix . 'Tracking-Did',   'value' => (string)intval($server->tracking_domain_id)), // because of sendgrid
                array('name' => 'List-Unsubscribe',               'value' => $listUnsubscribeHeaderValue),
                array('name' => 'List-Id',                        'value' => $list->list_uid . ' <' . $list->display_name . '>'),
                array('name' => 'X-Report-Abuse',                 'value' => 'Please report abuse for this campaign here: ' . $reportAbuseUrl),
                array('name' => 'Feedback-ID',                    'value' => $this->getFeedbackIdHeaderValue($campaign, $subscriber, $list, $customer)),
                // https://support.google.com/a/answer/81126?hl=en#unsub
                array('name' => 'Precedence',                    'value' => 'bulk'),
                
                // since 1.3.7.3
                array('name' => $headerPrefix . 'EBS',   'value' => $options->get('system.urls.frontend_absolute_url') . 'lists/block-address'),
            );

            // since 1.3.4.6
            $headers = !empty($server->additional_headers) && is_array($server->additional_headers) ? $server->additional_headers : array();
            $headers = (array)Yii::app()->hooks->applyFilters('console_command_send_campaigns_campaign_custom_headers', $headers, $campaign, $subscriber, $customer, $server, $emailParams);
            $headers = $server->parseHeadersFormat($headers);

            if (!empty($headers)) {
                $headerSearchReplace = array(
                    '[CAMPAIGN_UID]'    => $campaign->campaign_uid,
                    '[SUBSCRIBER_UID]'  => $subscriber->subscriber_uid,
                    '[SUBSCRIBER_EMAIL]'=> $subscriber->email,
                );
                foreach ($headers as $header) {
                    if (!is_array($header) || !isset($header['name'], $header['value'])) {
                        continue;
                    }
                    $header['value'] = str_replace(array_keys($headerSearchReplace), array_values($headerSearchReplace), $header['value']);
                    $emailParams['headers'][] = $header;
                }
                unset($headers);
            }

            $emailParams['mailerPlugins'] = $mailerPlugins;

            if (!empty($attachments)) {
                $emailParams['attachments'] = array();
                foreach ($attachments as $attachment) {
                    $emailParams['attachments'][] = Yii::getPathOfAlias('root') . $attachment->file;
                }
            }

            $processedCounter++;
            if ($processedCounter >= $changeServerAt) {
                $serverHasChanged = false;
            }
            
            // since 1.3.6.6
            if (!empty($campaign->option->tracking_domain_id) && !empty($campaign->option->trackingDomain)) {
                $emailParams['trackingEnabled']     = true;
                $emailParams['trackingDomainModel'] = $campaign->option->trackingDomain;
            }
            //

            // since 1.3.4.6 (will be removed, don't hook into it)
            Yii::app()->hooks->doAction('console_command_send_campaigns_before_send_to_subscriber', $campaign, $subscriber, $customer, $server, $emailParams);

            // since 1.3.5.9
            $emailParams = Yii::app()->hooks->applyFilters('console_command_send_campaigns_before_send_to_subscriber', $emailParams, $campaign, $subscriber, $customer, $server);

            // set delivery object
            $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign);

            // default status
            $status = CampaignDeliveryLog::STATUS_SUCCESS;

            $this->stdout(sprintf('Using delivery server: %s (ID: %d).', $server->hostname, $server->server_id));

            // since 1.3.5 - try via queue
            $response = null;
            $sent = null;
            if ($server->getUseQueue()) {
                $this->stdout('Sending the email message using the QUEUE method.');
                $sent = array('message_id' => $server->server_id . StringHelper::random(40));
                $response = 'OK';
                $allParams = array_merge(array(
                    'server_id'   => $server->server_id,
                    'server_type' => $server->type,
                    'campaign_id' => $campaign->campaign_id,
                    'params'      => $emailParams
                ), $sent);

                if ($server->getCampaignQueueEmailsChunkSize() > 1) {
                    if (!$server->pushEmailInCampaignQueue($allParams)) {
                        $sent = $response = null;
                    } else {
                        $server->logUsage();
                    }
                } else {
                    if (!Yii::app()->queue->enqueue($server->getQueueName(), 'SendEmailFromQueue', $allParams)) {
                        $sent = $response = null;
                    } else {
                        $server->logUsage();
                    }
                }

                unset($allParams);
            }
            
            // if not via queue or queue failed
            if (!$sent) {
                $this->stdout('Sending the email message using the DIRECT method.');
                try {
                    $sent     = $server->sendEmail($emailParams);
                    $response = $server->getMailer()->getLog();
                } catch (Exception $e) {
                    $sent     = false;
                    $response = $e->getMessage();
                }
            }

            // free mem
            unset($emailParams);
            
            $messageId = null;

            if (!$sent) {
                $failuresCount++;
                $status = CampaignDeliveryLog::STATUS_GIVEUP;
                $this->stdout(sprintf('Sending failed with: %s', $response));
            } else {
                $failuresCount = 0;
                $this->stdout(sprintf('Sending response is: %s', (!empty($response) ? $response : 'OK')));
            }

            if ($sent && is_array($sent) && !empty($sent['message_id'])) {
                $messageId = $sent['message_id'];
            }

            if ($sent) {
                $this->stdout('Sending OK.');
            }

            $this->stdout(sprintf('Done for %s, logging delivery...', $subscriber->email));
            $this->logDelivery($subscriber, $response, $status, $messageId, $server);

            // since 1.3.4.6
            Yii::app()->hooks->doAction('console_command_send_campaigns_after_send_to_subscriber', $campaign, $subscriber, $customer, $server, $sent, $response, $status);
        }
        
        // free mem
        unset($subscribers);
        
        // since 1.3.6.3 - it's not 100% bullet proof but should be fine
        // for most of the use cases
        if (!isset($params['domainPolicySubscribersCounter'])) {
            $params['domainPolicySubscribersCounter'] = 0;
        }
        $params['domainPolicySubscribersCounter']++;
        if (!empty($domainPolicySubscribers)) {
            if (empty($params['domainPolicySubscribersMaxRounds'])) {
                $params['domainPolicySubscribersMaxRounds'] = DeliveryServer::model()->countByAttributes(array(
                    'status' => DeliveryServer::STATUS_ACTIVE
                ));
            }
            if ($params['domainPolicySubscribersCounter'] <= $params['domainPolicySubscribersMaxRounds']) {
                $params['subscribers'] = &$domainPolicySubscribers;
                $params['changeServerAt'] = 0;
                $params['dsParams']['excludeServers'][] = $server->server_id;
                $params['server'] = null;
                $this->stdout("", false);
                $this->stdout(sprintf('Processing the rest of %d subscribers because of delivery server domain policies...', count($domainPolicySubscribers)));
                $this->stdout("", false);
                return $this->processSubscribersLoop($params);
            }
        }
        
        // free mem
        unset($params);
    }
    
    // since 1.3.6.6
    public function getFeedbackIdHeaderValue(Campaign $campaign, ListSubscriber $subscriber, Lists $list, Customer $customer)
    {
        $format = $customer->getGroupOption('campaigns.feedback_id_header_format', '');
        if (empty($format)) {
            return sprintf('%s:%s:%s:%s', $campaign->campaign_uid, $subscriber->subscriber_uid, $list->list_uid, $customer->customer_uid);
        }

        $searchReplace = array(
            '[CAMPAIGN_UID]'    => $campaign->campaign_uid,
            '[SUBSCRIBER_UID]'  => $subscriber->subscriber_uid,
            '[LIST_UID]'        => $list->list_uid,
            '[CUSTOMER_UID]'    => $customer->customer_uid,
            '[CUSTOMER_NAME]'   => StringHelper::truncateLength(URLify::filter($customer->getFullName()), 15, ''),
        );
        $searchReplace = Yii::app()->hooks->applyFilters('feedback_id_header_format_tags_search_replace', $searchReplace);
        
        return str_replace(array_keys($searchReplace), array_values($searchReplace), $format);
    }

    // since 1.3.5.9
    protected function checkCampaignOverMaxBounceRate($campaign, $maxBounceRate)
    {
        if ((int)$maxBounceRate < 0) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $campaign->campaign_id);

        $bouncesCount   = (int)CampaignBounceLog::model()->count($criteria);
        $processedCount = (int)CampaignDeliveryLog::model()->count($criteria);
        $bouncesRate    = -1;

        if ($processedCount > 0) {
            $bouncesRate = ($bouncesCount * 100) / $processedCount;
        }

        if ($bouncesRate > $maxBounceRate) {
            $campaign->block("Campaign bounce rate is higher than allowed!");
        }
    }

    // since 1.3.5.9
    protected function getCanUsePcntl()
    {
        static $canUsePcntl;
        if ($canUsePcntl !== null) {
            return $canUsePcntl;
        }
        if (Yii::app()->options->get('system.cron.send_campaigns.use_pcntl', 'no') != 'yes') {
            return $canUsePcntl = false;
        }
        if (!CommonHelper::functionExists('pcntl_fork') || !CommonHelper::functionExists('pcntl_waitpid')) {
            return $canUsePcntl = false;
        }
        return $canUsePcntl = true;
    }

    // since 1.3.5.9
    protected function getCampaignsInParallel()
    {
        return (int)Yii::app()->options->get('system.cron.send_campaigns.campaigns_in_parallel', 5);
    }

    // since 1.3.5.9
    protected function getSubscriberBatchesInParallel()
    {
        return (int)Yii::app()->options->get('system.cron.send_campaigns.subscriber_batches_in_parallel', 5);
    }

    protected function logDelivery(ListSubscriber $subscriber, $message, $status, $messageId = null, $server = null)
    {
        $campaign = $this->_campaign;

        // 1.3.7.9
        if ($this->_useTempQueueTables && $campaign->isRegular) {
            $campaign->temporaryQueueTable->deleteSubscriber($subscriber->subscriber_id);   
        }
        
        $deliveryLog = new CampaignDeliveryLog();
        $deliveryLog->campaign_id      = $campaign->campaign_id;
        $deliveryLog->subscriber_id    = $subscriber->subscriber_id;
        $deliveryLog->email_message_id = $messageId;
        $deliveryLog->message          = str_replace("\n\n", "\n", $message);
        $deliveryLog->status           = $status;
        
        // since 1.3.6.1
        $deliveryLog->delivery_confirmed = CampaignDeliveryLog::TEXT_YES;
        if ($server) {
            $deliveryLog->server_id = $server->server_id;
            if ($server->canConfirmDelivery && $server->must_confirm_delivery == DeliveryServer::TEXT_YES) {
                $deliveryLog->delivery_confirmed = CampaignDeliveryLog::TEXT_NO;
            }
        }
        
        return $deliveryLog->save(false);
    }

    protected function countSubscribers()
    {
        $campaign = $this->_campaign;
        
        // 1.3.7.9
        if ($this->_useTempQueueTables && $campaign->isRegular) {
            return $campaign->temporaryQueueTable->countSubscribers();
        }

        $criteria = new CDbCriteria();
        $criteria->with['deliveryLogs'] = array(
            'select'    => false,
            'together'  => true,
            'joinType'  => 'LEFT OUTER JOIN',
            'on'        => 'deliveryLogs.campaign_id = :cid',
            'condition' => 'deliveryLogs.subscriber_id IS NULL',
            'params'    => array(':cid' => $this->_campaign->campaign_id),
        );

        return $campaign->countSubscribers($criteria);
    }

    // find subscribers
    protected function findSubscribers($offset = 0, $limit = 300)
    {
        $campaign = $this->_campaign;
        
        // 1.3.7.3
        if (empty($limit) || $limit <= 0) {
            return array();
        }
        
        // 1.3.7.9
        if ($this->_useTempQueueTables && $campaign->isRegular) {
            return $campaign->temporaryQueueTable->findSubscribers($offset, $limit);
        }
        
        $criteria = new CDbCriteria();
        $criteria->with['deliveryLogs'] = array(
            'select'    => false,
            'together'  => true,
            'joinType'  => 'LEFT OUTER JOIN',
            'on'        => 'deliveryLogs.campaign_id = :cid',
            'condition' => 'deliveryLogs.subscriber_id IS NULL',
            'params'    => array(':cid' => $campaign->campaign_id),
        );
        
        // since 1.3.6.3
        if ($campaign->option->canSetMaxSendCountRandom) {
            $criteria->order = 'RAND()';
        }
        
        // and find them
        return $campaign->findSubscribers($offset, $limit, $criteria);
    }

    /**
     * Tries to:
     * 1. Group the subscribers by domain
     * 2. Sort them so that we don't send to same domain two times in a row.
     */
    protected function sortSubscribers($subscribers)
    {
        $subscribersCount = count($subscribers);
        $_subscribers = array();
        foreach ($subscribers as $index => $subscriber) {
            $emailParts = explode('@', $subscriber->email);
            $domainName = $emailParts[1];
            if (!isset($_subscribers[$domainName])) {
                $_subscribers[$domainName] = array();
            }
            $_subscribers[$domainName][] = $subscriber;
            unset($subscribers[$index]);
        }

        $subscribers = array();
        while ($subscribersCount > 0) {
            foreach ($_subscribers as $domainName => $subs) {
                foreach ($subs as $index => $sub) {
                    $subscribers[] = $sub;
                    unset($_subscribers[$domainName][$index]);
                    break;
                }
            }
            $subscribersCount--;
        }
        
        // free mem
        unset($_subscribers);
        
        return $subscribers;
    }

    protected function prepareEmail($subscriber, $server)
    {
        $campaign = $this->_campaign;

        // how come ?
        if (empty($campaign->template)) {
            return false;
        }

        $list           = $campaign->list;
        $customer       = $list->customer;
        $emailContent   = $campaign->template->content;
        $emailSubject   = $campaign->subject;
        $emailFooter    = null;
        $onlyPlainText  = !empty($campaign->template->only_plain_text) && $campaign->template->only_plain_text === CampaignTemplate::TEXT_YES;
        $emailAddress   = $subscriber->email;

        // since 1.3.5.9
        $fromEmailCustom= null;
        $fromNameCustom = null;
        $replyToCustom  = null;

        // really blind check to see if it contains a tag
        if (strpos($campaign->from_email, '[') !== false || strpos($campaign->from_name, '[') !== false || strpos($campaign->reply_to, '[') !== false) {
            $searchReplace = CampaignHelper::getSubscriberFieldsSearchReplace('', $campaign, $subscriber);
            if (strpos($campaign->from_email, '[') !== false) {
                $fromEmailCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_email);
                if (!FilterVarHelper::email($fromEmailCustom)) {
                    $fromEmailCustom = null;
                }
            }
            if (strpos($campaign->from_name, '[') !== false) {
                $fromNameCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_name);
            }
            if (strpos($campaign->reply_to, '[') !== false) {
                $replyToCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->reply_to);
                if (!FilterVarHelper::email($replyToCustom)) {
                    $replyToCustom = null;
                }
            }
        }

        if (!$onlyPlainText) {
            
            if (!empty($campaign->option->preheader)) {
                $emailContent = CampaignHelper::injectPreheader($emailContent, $campaign->option->preheader, $campaign);
            }
            
            if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
                $emailContent = CampaignHelper::injectEmailFooter($emailContent, $emailFooter, $campaign);
            }

            if (!empty($campaign->option) && $campaign->option->xml_feed == CampaignOption::TEXT_YES) {
                $emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign, $subscriber, true, null, $server);
            }

            if (!empty($campaign->option) && $campaign->option->json_feed == CampaignOption::TEXT_YES) {
                $emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign, $subscriber, true, null, $server);
            }

            if (!empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES) {
                $emailContent = CampaignHelper::transformLinksForTracking($emailContent, $campaign, $subscriber, true);
            }

            // since 1.3.5.9 - optional open tracking.
            $trackOpen = $campaign->option->open_tracking == CampaignOption::TEXT_YES;
            //
            $emailData = CampaignHelper::parseContent($emailContent, $campaign, $subscriber, $trackOpen, $server);
            list($toName, $emailSubject, $emailContent) = $emailData;
        }

        // Plain TEXT only supports basic tags transform, no xml/json feeds nor tracking.
        $emailPlainText = null;
        if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) {
            if ($campaign->template->auto_plain_text === CampaignTemplate::TEXT_YES /* && empty($campaign->template->plain_text)*/) {
                $emailPlainText = CampaignHelper::htmlToText($emailContent);
            }

            if (empty($emailPlainText) && !empty($campaign->template->plain_text) && !$onlyPlainText) {
                $_emailData = CampaignHelper::parseContent($campaign->template->plain_text, $campaign, $subscriber, false, $server);
                list(, , $emailPlainText) = $_emailData;
                if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
                    $emailPlainText .= "\n\n\n";
                    $emailPlainText .= strip_tags($emailFooter);
                }
                $emailPlainText = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailPlainText);
            }
        }

        if ($onlyPlainText) {
            $_emailData = CampaignHelper::parseContent($campaign->template->plain_text, $campaign, $subscriber, false, $server);
            list($toName, $emailSubject, $emailPlainText) = $_emailData;
            if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
                $emailPlainText .= "\n\n\n";
                $emailPlainText .= strip_tags($emailFooter);
            }
            $emailPlainText = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailPlainText);
        }

        // since 1.3.5.3
        if (!empty($campaign->option) && $campaign->option->xml_feed == CampaignOption::TEXT_YES) {
            $emailSubject = CampaignXmlFeedParser::parseContent($emailSubject, $campaign, $subscriber, true, $campaign->subject, $server);
        }

        if (!empty($campaign->option) && $campaign->option->json_feed == CampaignOption::TEXT_YES) {
            $emailSubject = CampaignJsonFeedParser::parseContent($emailSubject, $campaign, $subscriber, true, $campaign->subject, $server);
        }

        if (CampaignHelper::isTemplateEngineEnabled()) {
            if (!$onlyPlainText && !empty($emailContent)) {
                $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailContent, $campaign, $subscriber, $server);
                $emailContent = CampaignHelper::parseByTemplateEngine($emailContent, $searchReplace);
            }
            if (!empty($emailSubject)) {
                $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailSubject, $campaign, $subscriber, $server);
                $emailSubject  = CampaignHelper::parseByTemplateEngine($emailSubject, $searchReplace);
            }
            if (!empty($emailPlainText)) {
                $searchReplace  = CampaignHelper::getCommonTagsSearchReplace($emailPlainText, $campaign, $subscriber, $server);
                $emailPlainText = CampaignHelper::parseByTemplateEngine($emailPlainText, $searchReplace);
            }
        }
   
        return array(
            'to'              => array($emailAddress => $toName),
            'subject'         => !empty($emailSubject) ? $emailSubject : $campaign->subject,
            'body'            => $emailContent,
            'plainText'       => $emailPlainText,
            'onlyPlainText'   => $onlyPlainText,

            // since 1.3.5.9
            'fromEmailCustom' => $fromEmailCustom,
            'fromNameCustom'  => $fromNameCustom,
            'replyToCustom'   => $replyToCustom,
        );
    }

    protected function markCampaignSent()
    {
        $campaign = $this->_campaign;
        
        if ($campaign->isAutoresponder) {
            $campaign->saveStatus(Campaign::STATUS_SENDING);
            return true;
        }

        $campaign->saveStatus(Campaign::STATUS_SENT);
        
        if (Yii::app()->options->get('system.customer.action_logging_enabled', true)) {
            $list = $campaign->list;
            $customer = $list->customer;
            if (!($logAction = $customer->asa('logAction'))) {
                $customer->attachBehavior('logAction', array(
                    'class' => 'customer.components.behaviors.CustomerActionLogBehavior',
                ));
                $logAction = $customer->asa('logAction');
            }
            $logAction->campaignSent($campaign);
        }

        // since 1.3.4.6
        Yii::app()->hooks->doAction('console_command_send_campaigns_campaign_sent', $campaign);

        $this->sendCampaignStats();

        // since 1.3.5.3
        $campaign->tryReschedule(true);

        // 1.3.7.9
        if ($this->_useTempQueueTables && $campaign->isRegular) {
            $campaign->temporaryQueueTable->dropTable();
        }

        return true;
    }

    protected function sendCampaignStats()
    {
        $campaign = $this->_campaign;
        if (empty($campaign->option->email_stats)) {
            return $this;
        }
        
        $dsParams = array('useFor' => array(DeliveryServer::USE_FOR_REPORTS));
        if (!($server = DeliveryServer::pickServer(0, $campaign, $dsParams))) {
            return $this;
        }

        if (!$campaign->asa('stats')) {
            $campaign->attachBehavior('stats', array(
                'class' => 'customer.components.behaviors.CampaignStatsProcessorBehavior',
            ));
        }
        $viewData   = compact('campaign');

        // prepare and send the email.
        $emailTemplate  = Yii::app()->options->get('system.email_templates.common');
        $emailBody      = Yii::app()->command->renderFile(Yii::getPathOfAlias('console.views.campaign-stats').'.php', $viewData, true);
        $emailTemplate  = str_replace('[CONTENT]', $emailBody, $emailTemplate);

        $recipients = explode(',', $campaign->option->email_stats);
        $recipients = array_map('trim', $recipients);

        // because we don't have what to parse here!
        $fromName = strpos($campaign->from_name, '[') !== false ? $campaign->list->from_name : $campaign->from_name;

        $emailParams            = array();
        $emailParams['fromName']= $fromName;
        $emailParams['replyTo'] = array($campaign->reply_to => $fromName);
        $emailParams['subject'] = Yii::t('campaign_reports', 'The campaign {name} has finished sending, here are the stats', array('{name}' => $campaign->name));
        $emailParams['body']    = $emailTemplate;

        foreach ($recipients as $recipient) {
            if (!FilterVarHelper::email($recipient)) {
                continue;
            }
            $emailParams['to']  = array($recipient => $fromName);
            $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign)->sendEmail($emailParams);
        }

        return $this;
    }

    /**
     * Check customers quota limits
     */
    protected function checkCustomersQuotaLimits()
    {
        if (empty($this->_customerData) || !is_array($this->_customerData)) {
            return;
        }
        
        foreach ($this->_customerData as $customerId => $cdata) {

            $customer = $cdata['customer'];
            $enabled  = $customer->getGroupOption('sending.quota_notify_enabled', 'no') == 'yes';

            if (!$enabled) {
                continue;
            }
            
            if ($this->getCanUsePcntl()) {
                sleep(rand(1, 3));
            }

            $counter = 0;
            foreach ($cdata['campaigns'] as $campaignId => $campaignMaxSubscribers) {
                if ($cdata['subscribersCount'] > $campaignMaxSubscribers) {
                    $counter += $campaignMaxSubscribers;
                } else {
                    $counter += $cdata['subscribersCount'];
                }
            }

            $timeNow    = time();
            $lastNotify = (int)$customer->getOption('sending_quota.last_notification', 0);
            $notifyTs   = 6 * 3600; // no less than 6 hours.

            $quotaTotal = $cdata['quotaTotal'];
            $quotaUsage = $cdata['quotaUsage'] + $counter; // current usage + future usage

            if ($quotaTotal <= 0 || ($lastNotify + $notifyTs) > $timeNow) {
                continue;
            }

            $quotaNotifyPercent = (int)$customer->getGroupOption('sending.quota_notify_percent', 95);
            $quotaUsagePercent  = ($quotaUsage / $quotaTotal) * 100;

            if ($quotaUsagePercent < $quotaNotifyPercent) {
                continue;
            }

            $customer->setOption('sending_quota.last_notification', $timeNow);
            
            $this->notifyCustomerReachingQuota(array(
                'customer'           => $customer,
                'quotaTotal'         => $quotaTotal,
                'quotaLeft'          => $cdata['quotaLeft'],
                'quotaUsage'         => $quotaUsage,
                'quotaUsagePercent'  => $quotaUsagePercent,
                'quotaNotifyPercent' => $quotaNotifyPercent,
            ));
        }
    }

    /**
     * @param array $params
     */
    protected function notifyCustomerReachingQuota(array $params = array())
    {
        $customer = $params['customer'];
        
        // create the message
        $_message  = 'Your maximum allowed sending quota is set to {max} emails and you currently have sent {current} emails, which means you have used {percent} of your allowed sending quota!<br />'; 
        $_message .= 'Once your sending quota is over, you will not be able to send any emails!<br /><br />';
        $_message .= 'Please make sure you renew your sending quota.<br /> Thank you!';
        
        $message = new CustomerMessage();
        $message->customer_id = $customer->customer_id;
        $message->title       = 'Your sending quota is close to the limit!';
        $message->message     = $_message;
        $message->message_translation_params = array(
            '{max}'     => $params['quotaTotal'],
            '{current}' => $params['quotaUsage'],
            '{percent}' => round($params['quotaUsagePercent'], 2) . '%',
        );
        $message->save();
        
        $dsParams = array('useFor' => array(DeliveryServer::USE_FOR_REPORTS));
        if (!($server = DeliveryServer::pickServer(0, null, $dsParams))) {
            return;
        }

        // prepare and send the email.
        $emailTemplate  = Yii::app()->options->get('system.email_templates.common');
        $emailBody      = $customer->getGroupOption('sending.quota_notify_email_content');
        $emailTemplate  = str_replace('[CONTENT]', $emailBody, $emailTemplate);

        $searchReplace  = array(
            '[FIRST_NAME]'          => $customer->first_name,
            '[LAST_NAME]'           => $customer->last_name,
            '[FULL_NAME]'           => $customer->fullName,
            '[QUOTA_TOTAL]'         => $params['quotaTotal'],
            '[QUOTA_USAGE]'         => $params['quotaUsage'],
            '[QUOTA_USAGE_PERCENT]' => round($params['quotaUsagePercent'], 2) . '%',
            
        );
        $emailTemplate = str_replace(array_keys($searchReplace), array_values($searchReplace), $emailTemplate);
        
        $emailParams            = array();
        $emailParams['subject'] = Yii::t('customers', 'Your sending quota is close to the limit!');
        $emailParams['body']    = $emailTemplate;
        $emailParams['to']      = $customer->email;
        
        $server->sendEmail($emailParams);
        
    }
}
