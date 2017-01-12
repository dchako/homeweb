<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DailyCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class DailyCommand extends CConsoleCommand 
{
    public function actionIndex() 
    {
        $this
            ->deleteSubscribers()
            ->deleteDeliveryServersUsageLogs()
            ->deleteCustomerOldActionLogs()
            ->deleteUnconfirmedCustomers()
            ->deleteUncompleteOrders()
            ->deliveryAlgo()
            ->deleteGuestFailedAttempts()
            ->deleteCampaigns()
            ->deleteLists();
        
        Yii::app()->hooks->doAction('console_command_daily', $this);
        
        return 0;
    }
    
    protected function deleteSubscribers()
    {
        $options = Yii::app()->options;
        $unsubscribeDays = (int)$options->get('system.cron.process_subscribers.unsubscribe_days', 30);
        $unconfirmDays   = (int)$options->get('system.cron.process_subscribers.unconfirm_days', 3);
        $blacklistedDays = (int)$options->get('system.cron.process_subscribers.blacklisted_days', 0);
        
        if ($memoryLimit = $options->get('system.cron.process_subscribers.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        
        try {
            $connection = Yii::app()->getDb();
            
            if ($unsubscribeDays > 0) {
                $interval = 60 * 60 * 24 * $unsubscribeDays;
                $sql = 'DELETE FROM `{{list_subscriber}}` WHERE `status` = :st AND last_updated < DATE_SUB(NOW(), INTERVAL '.(int)$interval.' SECOND)';
                $command = $connection->createCommand($sql)->execute(array(
                    ':st' => ListSubscriber::STATUS_UNSUBSCRIBED,
                ));
            }
            
            if ($unconfirmDays > 0) {
                $interval = 60 * 60 * 24 * $unconfirmDays;
                $sql = 'DELETE FROM `{{list_subscriber}}` WHERE `status` = :st AND last_updated < DATE_SUB(NOW(), INTERVAL '.(int)$interval.' SECOND)';
                $command = $connection->createCommand($sql)->execute(array(
                    ':st' => ListSubscriber::STATUS_UNCONFIRMED,
                ));
            }
            
            if ($blacklistedDays > 0) {
                $interval = 60 * 60 * 24 * $blacklistedDays;
                $sql = 'DELETE FROM `{{list_subscriber}}` WHERE `status` = :st AND last_updated < DATE_SUB(NOW(), INTERVAL '.(int)$interval.' SECOND)';
                $command = $connection->createCommand($sql)->execute(array(
                    ':st' => ListSubscriber::STATUS_BLACKLISTED,
                ));
            }
        } catch(Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }
    
    protected function deleteDeliveryServersUsageLogs()
    {
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand('DELETE FROM `{{delivery_server_usage_log}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 YEAR)')->execute();    
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }
    
    protected function deleteCustomerOldActionLogs()
    {
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand('DELETE FROM `{{customer_action_log}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 MONTH)')->execute();    
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }
    
    protected function deleteUnconfirmedCustomers()
    {
        $options        = Yii::app()->options;
        $unconfirmDays  = (int)$options->get('system.customer_registration.unconfirm_days_removal', 7);
        
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand(sprintf('DELETE FROM `{{customer}}` WHERE `status` = :st AND date_added < DATE_SUB(NOW(), INTERVAL %d DAY)', (int)$unconfirmDays))->execute(array(
                ':st' => Customer::STATUS_PENDING_CONFIRM,
            ));    
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }
    
    protected function deleteUncompleteOrders()
    {
        $options        = Yii::app()->options;
        $unconfirmDays  = (int)$options->get('system.monetization.orders.uncomplete_days_removal', 7);
        
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand(sprintf('DELETE FROM `{{price_plan_order}}` WHERE `status` != :st AND `status` != :st2 AND date_added < DATE_SUB(NOW(), INTERVAL %d DAY)', (int)$unconfirmDays))->execute(array(
                ':st'   => PricePlanOrder::STATUS_COMPLETE,
                ':st2'  => PricePlanOrder::STATUS_REFUNDED,
            ));    
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }
    
    protected function deliveryAlgo()
    {
        Yii::app()->consoleSystemInit->_deliveryAlgo();
        return $this;
    }
    
    protected function deleteCampaigns()
    {
        $campaigns = Campaign::model()->findAllByAttributes(array(
            'status' => Campaign::STATUS_PENDING_DELETE,
        ));
        foreach ($campaigns as $campaign) {
            try {
                $campaign->delete();
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }
    
    protected function deleteLists()
    {
        $lists = Lists::model()->findAllByAttributes(array(
            'status' => Lists::STATUS_PENDING_DELETE,
        ));
        foreach ($lists as $list) {
            try {
                $list->delete();
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }
    
    protected function deleteGuestFailedAttempts()
    {
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand('DELETE FROM `{{guest_fail_attempt}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 HOUR)')->execute();    
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }
}
