<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SendTransactionalEmailsCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.5
 */
 
class SendTransactionalEmailsCommand extends CConsoleCommand 
{
    protected $_lockName;
    
    public function actionIndex() 
    {
        $mutex    = Yii::app()->mutex;
        $lockName = $this->getLockName();
        
        if (!$mutex->acquire($lockName)) {
            return 1;
        }
        
        // added in 1.3.4.7
        Yii::app()->hooks->doAction('console_command_transactional_emails_before_process', $this);
        
        $this->process();
        
        // added in 1.3.4.7
        Yii::app()->hooks->doAction('console_command_transactional_emails_after_process', $this);
        
        $mutex->release($lockName);
        return 0;
    }
    
    protected function process()
    {
        $offset = (int)Yii::app()->options->get('system.cron.transactional_emails.offset', 0);
        $limit  = 100;
        
        $emails = TransactionalEmail::model()->findAll(array(
            'condition' => '`status` = "unsent" AND `send_at` < NOW() AND `retries` < `max_retries`',
            'order'     => 'email_id ASC',
            'limit'     => $limit,
            'offset'    => $offset
        ));
        
        if (empty($emails)) {
            Yii::app()->options->set('system.cron.transactional_emails.offset', 0);
            return $this;
        }
        Yii::app()->options->set('system.cron.transactional_emails.offset', $offset + $limit);
        
        foreach ($emails as $email) {
            $email->send();
        }

        Yii::app()->getDb()->createCommand('UPDATE {{transactional_email}} SET `status` = "sent" WHERE `status` = "unsent" AND send_at < NOW() AND retries >= max_retries')->execute();
        Yii::app()->getDb()->createCommand('DELETE FROM {{transactional_email}} WHERE `status` = "unsent" AND send_at < NOW() AND date_added < DATE_SUB(NOW(), INTERVAL 1 MONTH)')->execute();
        
        return $this;
    }
    
    protected function getLockName()
    {
        if ($this->_lockName !== null) {
            return $this->_lockName;
        }
        $offset = (int)Yii::app()->options->get('system.cron.transactional_emails.offset', 0);
        return $this->_lockName = md5(__FILE__ . __CLASS__ . $offset);
    }

}