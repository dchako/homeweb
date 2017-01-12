<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignSenderBehavior
 * NOTE: SINCE 1.3.5.9 THIS FILE IS NOT USED ANYMORE.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class CampaignSenderBehavior extends CBehavior
{
    // reference flag for the type of campaigns we're sending
    public $campaigns_type;

    // reference flag for campaigns limit
    public $campaigns_limit = 0;

    // reference flag for campaigns offset
    public $campaigns_offset = 0;

    // whether this should be verbose and output to console
    public $verbose = 0;

    public function sendCampaign()
    {
        $campaign = $this->getOwner();

        // this should never happen unless the list is removed while sending
        if (empty($campaign->list) || empty($campaign->list->customer)) {
            return 0;
        }

        $options  = Yii::app()->options;
        $list     = $campaign->list;
        $customer = $list->customer;

        if ($this->verbose) {
            echo "[".date("Y-m-d H:i:s")."] Processing the campaign " . $campaign->name . " having the uid: " . $campaign->campaign_uid;
            echo " belonging to the customer: " . $customer->fullName . "(" . $customer->customer_id . ")\n";
        }

        // since 1.3.5
        if (!$customer->getIsActive()) {
            Yii::log(Yii::t('campaigns', 'This customer is inactive!'), CLogger::LEVEL_ERROR);
            $campaign->saveStatus(Campaign::STATUS_PAUSED);

            if ($this->verbose) {
                echo "[".date("Y-m-d H:i:s")."] The above customer is not active, campaign has been paused!\n";
            }

            return 0;
        }

        if ($this->verbose) {
            echo "[".date("Y-m-d H:i:s")."] Checking customer quota before we start...";
        }

        if ($customer->getIsOverQuota()) {
            Yii::log(Yii::t('campaigns', 'This customer(ID:{cid}) reached the assigned quota!', array('{cid}' => $customer->customer_id)), CLogger::LEVEL_ERROR);
            $campaign->saveStatus(Campaign::STATUS_PAUSED);

            if ($this->verbose) {
                echo "Customer is over quota, the campaign has been paused!\n";
            }

            return 0;
        }

        if ($this->verbose) {
            echo "OK\n";
            echo "[".date("Y-m-d H:i:s")."] Picking a delivery server...";
        }

        $dsParams = array('customerCheckQuota' => false, 'useFor' => array(DeliveryServer::USE_FOR_CAMPAIGNS));
        $server   = DeliveryServer::pickServer(0, $campaign, $dsParams);
        if (empty($server)) {
            Yii::log(Yii::t('campaigns', 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!'), CLogger::LEVEL_ERROR);

            if ($this->verbose) {
                echo "\n[".date("Y-m-d H:i:s")."] Unable to find a valid delivery server, aborting until a delivery server is available!\n";
            }

            return 0;
        }

        if ($this->verbose) {
            echo "OK\n";
        }

        if (!empty($customer->language_id)) {
            $language = Language::model()->findByPk((int)$customer->language_id);
            if (!empty($language)) {
                Yii::app()->setLanguage($language->getLanguageAndLocaleCode());
            }
        }

        // put proper status
        $campaign->saveStatus(Campaign::STATUS_PROCESSING);

        if ($this->verbose) {
            $timeStart = microtime(true);
            echo "[".date("Y-m-d H:i:s")."] Campaign status has been set to PROCESSING.\n";
            echo "[".date("Y-m-d H:i:s")."] Searching for subscribers to send for this campaign...";
        }

        // find the subscribers we need to send these emails at
        $limit = (int)$customer->getGroupOption('campaigns.subscribers_at_once', (int)Yii::app()->options->get('system.cron.send_campaigns.subscribers_at_once', 300));
        $subscribers = $this->findSubscribers($limit);

        if ($this->verbose) {
            echo "done, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
        }

        // in case we are done
        if (empty($subscribers)) {
            if ($this->verbose) {
                $timeStart = microtime(true);
                echo "[".date("Y-m-d H:i:s")."] Did not find any subscriber for sending, marking campaign as sent...\n";
            }

            $this->markCampaignSent();

            if ($this->verbose) {
                echo "[".date("Y-m-d H:i:s")."] Campaign has been marked as sent, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
            }

            return 0;
        }

        try {

            $mailerPlugins = array(
                'loggerPlugin' => true,
            );

            $sendAtOnce = (int)$customer->getGroupOption('campaigns.send_at_once', (int)$options->get('system.cron.send_campaigns.send_at_once', 0));
            if (!empty($sendAtOnce)) {
                $mailerPlugins['antiFloodPlugin'] = array(
                    'sendAtOnce'    => $sendAtOnce,
                    'pause'         => (int)$customer->getGroupOption('campaigns.pause', (int)$options->get('system.cron.send_campaigns.pause', 0)),
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

            $processedCounter = 0;
            $serverHasChanged = false;
            $changeServerAt   = (int)$customer->getGroupOption('campaigns.change_server_at', (int)$options->get('system.cron.send_campaigns.change_server_at', 0));

            //since 1.3.4.9
            $dsParams = array(
                'customerCheckQuota' => false,
                'serverCheckQuota'   => false,
                'useFor'             => array(DeliveryServer::USE_FOR_CAMPAIGNS),
            );

            if ($this->verbose) {
                echo "[".date("Y-m-d H:i:s")."] Running subscribers cleanup for " . count($subscribers) . " subscribers...\n";
            }

            // run some cleanup on subscribers
            $notAllowedEmailChars = array('-', '_');
            $subscribersQueue     = array();

            foreach ($subscribers as $index => $subscriber) {
                if (isset($subscribersQueue[$subscriber->subscriber_id])) {
                    unset($subscribers[$index]);
                    continue;
                }

                $containsNotAllowedEmailChars = false;
                $part = explode('@', $subscriber->email);
                $part = $part[0];
                foreach ($notAllowedEmailChars as $chr) {
                    if (strpos($part, $chr) === 0 || strrpos($part, $chr) === 0) {
                        $subscriber->addToBlacklist('Invalid email address format!');
                        $containsNotAllowedEmailChars = true;
                        break;
                    }
                }

                if ($containsNotAllowedEmailChars) {
                    unset($subscribers[$index]);
                    continue;
                }

                $subscribersQueue[$subscriber->subscriber_id] = true;
            }
            unset($subscribersQueue);

            // reset the keys
            $subscribers = array_values($subscribers);

            // since 1.3.5.7
            if (empty($subscribers)) {
                if ($this->verbose) {
                    echo "[".date("Y-m-d H:i:s")."] Subscribers cleanup completed, no valid subscribers left, we are marking this campaign as sent.\n";
                }
                $this->markCampaignSent();
                return 0;
            }

            if ($this->verbose) {
                $beforeForeachTime = microtime(true);
                $sendingAloneTime  = 0;
                echo "[".date("Y-m-d H:i:s")."] Subscribers cleanup completed.\n";
                echo "[".date("Y-m-d H:i:s")."] Entering into the foreach loop to send for all " . count($subscribers) . " subscribers.\n";
            }

            // sort subscribers
            $subscribers = $this->sortSubscribers($subscribers);

            foreach ($subscribers as $index => $subscriber) {

                if ($this->verbose) {
                    $timeStart = microtime(true);
                    echo "\n[".date("Y-m-d H:i:s")."] Current progress: " . ($index + 1) . " out of " . count($subscribers);
                    echo "\n[".date("Y-m-d H:i:s")."] Checking if the delivery server is allowed to send to the subscriber email address domain...";
                }

                // if this server is not allowed to send to this email domain, then just skip it.
                if (!$server->canSendToDomainOf($subscriber->email)) {
                    if ($this->verbose) {
                        echo "\n[".date("Y-m-d H:i:s")."] Server is not allowed to send to the subscriber domain, skipping this subscriber!\n";
                    }
                    continue;
                }

                if ($this->verbose) {
                    echo "OK, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
                    echo "[".date("Y-m-d H:i:s")."] Checking the subscriber email address into the blacklist...";
                    $timeStart = microtime(true);
                }

                // if blacklisted, goodbye.
                if ($subscriber->getIsBlacklisted()) {
                    $this->logDelivery($subscriber, Yii::t('campaigns', 'This email is blacklisted. Sending is denied!'), CampaignDeliveryLog::STATUS_BLACKLISTED);
                    if ($this->verbose) {
                        echo "\n[".date("Y-m-d H:i:s")."] The email address has been found into the blacklist, sending is denied!\n";
                    }
                    continue;
                }

                if ($this->verbose) {
                    echo "OK, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
                    echo "[".date("Y-m-d H:i:s")."] Checking server sending quota...";
                    $timeStart = microtime(true);
                }

                // in case the server is over quota
                if ($server->getIsOverQuota()) {
                    if ($this->verbose) {
                        echo "\n[".date("Y-m-d H:i:s")."] The delivery server is over quota, picking another one...\n";
                    }
                    $currentServerId = $server->server_id;
                    if (!($server = DeliveryServer::pickServer($currentServerId, $campaign, $dsParams))) {
                        throw new Exception(Yii::t('campaigns', 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!'), 99);
                    }
                }

                if ($this->verbose) {
                    echo "OK, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
                    echo "[".date("Y-m-d H:i:s")."] Checking customer sending quota...";
                    $timeStart = microtime(true);
                }

                // in case current customer is over quota
                if ($customer->getIsOverQuota()) {
                    if ($this->verbose) {
                        echo "\n[".date("Y-m-d H:i:s")."] The customer is over quota, pausing campaign!\n";
                    }
                    throw new Exception(Yii::t('campaigns', 'This customer reached the assigned quota!'), 98);
                }

                if ($this->verbose) {
                    echo "OK, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
                    echo "[".date("Y-m-d H:i:s")."] Preparing email...";
                    $timeStart = microtime(true);
                }

                $emailParams = $this->prepareEmail($subscriber);
                if (empty($emailParams) || !is_array($emailParams)) {
                    $this->logDelivery($subscriber, Yii::t('campaigns', 'Unable to prepare the email content!'), CampaignDeliveryLog::STATUS_ERROR);
                    continue;
                }

                if ($changeServerAt > 0 && $processedCounter >= $changeServerAt && !$serverHasChanged) {
                    $currentServerId = $server->server_id;
                    if ($newServer = DeliveryServer::pickServer($currentServerId, $campaign, $dsParams)) {
                        $server = $newServer;
                        unset($newServer);
                    }

                    $processedCounter = 0;
                    $serverHasChanged = true;
                }

                $listUnsubscribeHeaderValue = $options->get('system.urls.frontend_absolute_url');
                $listUnsubscribeHeaderValue .= 'lists/'.$list->list_uid.'/unsubscribe/'.$subscriber->subscriber_uid . '/' . $campaign->campaign_uid;
                $listUnsubscribeHeaderValue = '<'.$listUnsubscribeHeaderValue.'>';

                $reportAbuseUrl  = $options->get('system.urls.frontend_absolute_url');
                $reportAbuseUrl .= 'campaigns/'. $campaign->campaign_uid . '/report-abuse/' . $list->list_uid . '/' . $subscriber->subscriber_uid;

                // since 1.3.4.9
                if (!empty($campaign->reply_to)) {
                    $_subject = 'Unsubscribe';
                    $_body    = 'Please unsubscribe me from ' . $list->display_name . ' list.';
                    $mailToUnsubscribeHeader    = sprintf(', <mailto:%s?subject=%s&body=%s>', '[LIST_UNSUBSCRIBE_EMAIL]', $_subject, $_body);
                    $listUnsubscribeHeaderValue .= $mailToUnsubscribeHeader;
                }

                $headerPrefix = Yii::app()->params['email.custom.header.prefix'];
                $emailParams['headers'] = array(
                    $headerPrefix . 'Campaign-Uid'     => $campaign->campaign_uid,
                    $headerPrefix . 'Subscriber-Uid'   => $subscriber->subscriber_uid,
                    $headerPrefix . 'Customer-Uid'     => $customer->customer_uid,
                    $headerPrefix . 'Customer-Gid'     => (string)intval($customer->group_id), // because of sendgrid
                    $headerPrefix . 'Delivery-Sid'     => (string)intval($server->server_id), // because of sendgrid
                    $headerPrefix . 'Tracking-Did'     => (string)intval($server->tracking_domain_id), // because of sendgrid
                    'List-Unsubscribe'      => $listUnsubscribeHeaderValue,
                    'List-Id'               => $list->list_uid . ' <' . $list->display_name . '>',
                    'X-Report-Abuse'        => 'Please report abuse for this campaign here: ' . $reportAbuseUrl,
                );

                // since 1.3.4.6
                $headers = !empty($server->additional_headers) && is_array($server->additional_headers) ? $server->additional_headers : array();
                $headers = (array)Yii::app()->hooks->applyFilters('console_command_send_campaigns_campaign_custom_headers', $headers, $campaign, $subscriber, $customer, $server, $emailParams);

                if (!empty($headers)) {
                    $headerSearchReplace = array(
                        '[CAMPAIGN_UID]'    => $campaign->campaign_uid,
                        '[SUBSCRIBER_UID]'  => $subscriber->subscriber_uid,
                        '[SUBSCRIBER_EMAIL]'=> $subscriber->email,
                    );
                    foreach ($headers as $name => $value) {
                        $headers[$name] = str_replace(array_keys($headerSearchReplace), array_values($headerSearchReplace), $value);
                    }
                    $emailParams['headers'] = array_merge($headers, $emailParams['headers']);
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

                // since 1.3.4.6
                Yii::app()->hooks->doAction('console_command_send_campaigns_before_send_to_subscriber', $campaign, $subscriber, $customer, $server, $emailParams);

                if ($this->verbose) {
                    echo "done, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
                    echo "[".date("Y-m-d H:i:s")."] -> Sending the email for " . $subscriber->email;
                    if ($server->getUseQueue()) {
                        echo " by using the queue method";
                    } else {
                        echo " by using direct method";
                    }
                    echo "...";
                }

                // set delivery object
                $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign);

                // default status
                $status = CampaignDeliveryLog::STATUS_SUCCESS;

                // since 1.3.5 - try via queue
                $sent = null;
                if ($server->getUseQueue()) {
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
                    $sent     = $server->sendEmail($emailParams);
                    $response = $server->getMailer()->getLog();
                }

                $messageId = null;

                if ($this->verbose) {
                    $timeTook = round(microtime(true) - $timeStart, 3);
                    $sendingAloneTime += $timeTook;
                    echo "done, took " . $timeTook . " seconds.\n";
                }

                // make sure we're still connected to database...
                Yii::app()->getDb()->setActive(true);

                if (!$sent) {
                    $status = $this->getFailStatusFromResponse($response);
                }

                if ($sent && is_array($sent) && !empty($sent['message_id'])) {
                    $messageId = $sent['message_id'];
                }

                if ($this->verbose) {
                    if ($sent) {
                        echo "[".date("Y-m-d H:i:s")."] The email has been sent successfully!\n";
                    } else {
                        echo "[".date("Y-m-d H:i:s")."] The email has failed sending with the message " . $response . "\n";
                    }
                }

                if ($this->verbose) {
                    echo "[".date("Y-m-d H:i:s")."] Logging delivery...";
                    $timeStart = microtime(true);
                }

                $this->logDelivery($subscriber, $response, $status, $messageId);

                if ($this->verbose) {
                    echo "done, took " . round(microtime(true) - $timeStart, 3) . " seconds.\n";
                }

                // since 1.3.4.6
                Yii::app()->hooks->doAction('console_command_send_campaigns_after_send_to_subscriber', $campaign, $subscriber, $customer, $server, $sent, $response, $status);
            }

            if ($this->verbose) {
                echo "\n[".date("Y-m-d H:i:s")."] Exiting from the foreach loop, took " . round(microtime(true) - $beforeForeachTime, 3) . " seconds to send for all " . count($subscribers) . " subscribers from which " . round($sendingAloneTime, 3) . " seconds only to communicate with remote ends.\n";
            }

        } catch (Exception $e) {

            // exception code to be returned later
            $code = (int)$e->getCode();

            // make sure sending is resumed next time.
            $campaign->status = Campaign::STATUS_SENDING;

            // pause the campaigns of customers that reached the quota
            // they will only delay processing of other campaigns otherwise.
            if ($code == 98) {
                $campaign->status = Campaign::STATUS_PAUSED;
            }

            // save the changes, but no validation
            $campaign->saveStatus();

            // log the error so we can reference it
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

            if ($this->verbose) {
                echo "[".date("Y-m-d H:i:s")."] Cought exception with message: " . $e->getMessage() . "\n";
                echo "[".date("Y-m-d H:i:s")."] Campaign status has been changed to: " . strtoupper($campaign->status) . "\n";
            }

            // return the exception code
            return $code;
        }

        // since 1.3.5
        try {
            // make sure we're still connected to database...
            Yii::app()->getDb()->setActive(true);
        } catch (Exception $e) {
            // log the error so we can reference it
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

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
            }
            return 0;
        }

        // the sending batch is over.
        // if we don't have enough subscribers for next batch, we stop.
        $subscribers = $this->countSubscribers();
        if (empty($subscribers)) {
            $this->markCampaignSent();
            return 0;
        }

        // make sure sending is resumed next time
        $campaign->saveStatus(Campaign::STATUS_SENDING);

        if ($this->verbose) {
            echo "[".date("Y-m-d H:i:s")."] Campaign status has been changed to: " . strtoupper($campaign->status) . "\n";
        }

        return 0;
    }

    protected function logDelivery(ListSubscriber $subscriber, $message, $status, $messageId = null)
    {
        $campaign = $this->getOwner();

        $deliveryLog = CampaignDeliveryLog::model()->findByAttributes(array(
            'campaign_id'   => (int)$campaign->campaign_id,
            'subscriber_id' => (int)$subscriber->subscriber_id,
        ));

        if (empty($deliveryLog)) {
            $deliveryLog = new CampaignDeliveryLog();
            $deliveryLog->campaign_id = $campaign->campaign_id;
            $deliveryLog->subscriber_id = $subscriber->subscriber_id;
        }

        $deliveryLog->email_message_id = $messageId;
        $deliveryLog->message = str_replace("\n\n", "\n", $message);
        $deliveryLog->status = $status;

        return $deliveryLog->save();
    }

    protected function countSubscribers()
    {
        $criteria = new CDbCriteria();
        $criteria->with['deliveryLogs'] = array(
            'select'    => false,
            'together'  => true,
            'joinType'  => 'LEFT OUTER JOIN',
            'on'        => 'deliveryLogs.campaign_id = :cid',
            'condition' => '(deliveryLogs.subscriber_id IS NULL OR deliveryLogs.`status` = :tstatus)',
            'params'    => array(':cid' => $this->getOwner()->campaign_id, ':tstatus' => CampaignDeliveryLog::STATUS_TEMPORARY_ERROR),
        );

        return $this->getOwner()->countSubscribers($criteria);
    }

    // find subscribers
    protected function findSubscribers($limit = 300)
    {
        $criteria = new CDbCriteria();
        $criteria->with['deliveryLogs'] = array(
            'select'    => false,
            'together'  => true,
            'joinType'  => 'LEFT OUTER JOIN',
            'on'        => 'deliveryLogs.campaign_id = :cid',
            'condition' => '(deliveryLogs.subscriber_id IS NULL OR deliveryLogs.`status` = :tstatus)',
            'params'    => array(':cid' => $this->getOwner()->campaign_id, ':tstatus' => CampaignDeliveryLog::STATUS_TEMPORARY_ERROR),
        );

        // and find them
        return $this->getOwner()->findSubscribers(0, $limit, $criteria);
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

        return $subscribers;
    }

    protected function prepareEmail($subscriber)
    {
        $campaign = $this->getOwner();

        // how come ?
        if (empty($campaign->template)) {
            return false;
        }

        $list           = $campaign->list;
        $customer       = $list->customer;
        $emailContent   = $campaign->template->content;
        $embedImages    = array();
        $emailFooter    = null;
        $onlyPlainText  = !empty($campaign->template->only_plain_text) && $campaign->template->only_plain_text === CampaignTemplate::TEXT_YES;
        $emailAddress   = $subscriber->email;

        if (!$onlyPlainText) {
            if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
                $emailContent = CampaignHelper::injectEmailFooter($emailContent, $emailFooter, $campaign);
            }

            if (!empty($campaign->option) && !empty($campaign->option->embed_images) && $campaign->option->embed_images == CampaignOption::TEXT_YES) {
                list($emailContent, $embedImages) = CampaignHelper::embedContentImages($emailContent, $campaign);
            }

            if (!empty($campaign->option) && $campaign->option->xml_feed == CampaignOption::TEXT_YES) {
                $emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign, $subscriber, true);
            }

            if (!empty($campaign->option) && $campaign->option->json_feed == CampaignOption::TEXT_YES) {
                $emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign, $subscriber, true);
            }

            if (!empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES) {
                $emailContent = CampaignHelper::transformLinksForTracking($emailContent, $campaign, $subscriber, true);
            }

            $emailData = CampaignHelper::parseContent($emailContent, $campaign, $subscriber, true);
            list($toName, $emailSubject, $emailContent) = $emailData;
        }

        // Plain TEXT only supports basic tags transform, no xml/json feeds nor tracking.
        $emailPlainText = null;
        if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) {
            if ($campaign->template->auto_plain_text === CampaignTemplate::TEXT_YES /* && empty($campaign->template->plain_text)*/) {
                $emailPlainText = CampaignHelper::htmlToText($emailContent);
            }

            if (empty($emailPlainText) && !empty($campaign->template->plain_text) && !$onlyPlainText) {
                $_emailData = CampaignHelper::parseContent($campaign->template->plain_text, $campaign, $subscriber, false);
                list(, , $emailPlainText) = $_emailData;
                if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
                    $emailPlainText .= "\n\n\n";
                    $emailPlainText .= strip_tags($emailFooter);
                }
                $emailPlainText = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailPlainText);
            }
        }

        if ($onlyPlainText) {
            $_emailData = CampaignHelper::parseContent($campaign->template->plain_text, $campaign, $subscriber, false);
            list($toName, $emailSubject, $emailPlainText) = $_emailData;
            if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
                $emailPlainText .= "\n\n\n";
                $emailPlainText .= strip_tags($emailFooter);
            }
            $emailPlainText = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailPlainText);
        }

        // since 1.3.5.3
        if (!empty($campaign->option) && $campaign->option->xml_feed == CampaignOption::TEXT_YES) {
            $emailSubject = CampaignXmlFeedParser::parseContent($emailSubject, $campaign, $subscriber, true, $campaign->subject);
        }

        if (!empty($campaign->option) && $campaign->option->json_feed == CampaignOption::TEXT_YES) {
            $emailSubject = CampaignJsonFeedParser::parseContent($emailSubject, $campaign, $subscriber, true, $campaign->subject);
        }

        return array(
            'to'              => array($emailAddress => $toName),
            'subject'         => $emailSubject,
            'body'            => $emailContent,
            'plainText'       => $emailPlainText,
            'embedImages'     => $embedImages,
            'onlyPlainText'   => $onlyPlainText,
            // below disabled since 1.3.5.3
            //'trackingEnabled' => !empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES,
        );
    }

    protected function markCampaignSent()
    {
        $campaign = $this->getOwner();

        if ($campaign->isAutoresponder) {
            $campaign->saveStatus(Campaign::STATUS_SENDING);
            return;
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
    }

    protected function sendCampaignStats()
    {
        $campaign = $this->getOwner();
        if (empty($campaign->option->email_stats)) {
            return $this;
        }

        if (!($server = DeliveryServer::pickServer(0, $campaign))) {
            return $this;
        }

        $campaign->attachBehavior('stats', array(
            'class' => 'customer.components.behaviors.CampaignStatsProcessorBehavior',
        ));
        $viewData   = compact('campaign');

        // prepare and send the email.
        $emailTemplate  = Yii::app()->options->get('system.email_templates.common');
        $emailBody      = Yii::app()->command->renderFile(Yii::getPathOfAlias('console.views.campaign-stats').'.php', $viewData, true);
        $emailTemplate  = str_replace('[CONTENT]', $emailBody, $emailTemplate);

        $recipients = explode(',', $campaign->option->email_stats);
        $recipients = array_map('trim', $recipients);

        $emailParams            = array();
        $emailParams['fromName']= $campaign->from_name;
        $emailParams['replyTo'] = array($campaign->reply_to => $campaign->from_name);
        $emailParams['subject'] = Yii::t('campaign_reports', 'The campaign {name} has finished sending, here are the stats', array('{name}' => $campaign->name));
        $emailParams['body']    = $emailTemplate;

        foreach ($recipients as $recipient) {
            if (!FilterVarHelper::email($recipient)) {
                continue;
            }
            $emailParams['to']  = array($recipient => $campaign->from_name);
            $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign)->sendEmail($emailParams);
        }

        return $this;
    }

    protected function getFailStatusFromResponse($response)
    {
        if (empty($response) || strlen($response) < 5) {
            return CampaignDeliveryLog::STATUS_ERROR;
        }

        $status = CampaignDeliveryLog::STATUS_TEMPORARY_ERROR;

        if(preg_match('/code\s"(\d+)"/ix', $response, $matches)) {
            $code = (int)$matches[1];
            if ($code >= 450 && !in_array($code, array(503))) {
                $status = CampaignDeliveryLog::STATUS_FATAL_ERROR;
            }
        }

        $temporaryErrors = array(
            'graylist', 'greylist', 'nested mail command', 'incorrect authentication', 'failed',
            'timed out', 'sending suspended'
        );

        foreach ($temporaryErrors as $error) {
            if (stripos($response, $error) !== false) {
                $status = CampaignDeliveryLog::STATUS_TEMPORARY_ERROR;
                break;
            }
        }

        return $status;
    }
}
