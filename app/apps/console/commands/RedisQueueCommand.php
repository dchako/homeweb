<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RedisQueueCommand
 *
 * THIS COMMAND HAS BEEN DISABLED SINCE 1.3.5.9
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5
 *
 * Please see https://github.com/chrisboulton/php-resque/ for more info
 */

class RedisQueueCommand extends CConsoleCommand
{
    // lock name
    protected $_lockName = 'redis-queue-command';

    // flag
    protected $_restoreStates = true;

    // flag
    protected $_improperShutDown = false;

    // queues to process
    public $queues = '*';

    // whether to block
    public $blocking = 0;

    // number of workers
    public $workers = 10;

    // check interval
    public $interval = 5;

    // debug in console
    public $verbose = 0;

    // flag to only show the size
    public $show_size = 0;

    public function init()
    {
        parent::init();

        // the lock name alteration
        $this->_lockName .= ($this->queues . (int)$this->blocking . (int)$this->workers . (int)$this->interval . (int)$this->verbose);

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
    }

    public function actionIndex()
    {
        // if we only need to show the sizes
        if ($this->show_size) {
            $queues = Yii::app()->queue->queues();
            foreach ($queues as $queue) {
                echo $queue . ' has ' . Yii::app()->queue->size($queue) . ' items' . "\n";
            }
            Yii::app()->end();
        }

        // acquire a lock on the command
        if (!Yii::app()->mutex->acquire($this->_lockName, 5)) {
            return 0;
        }

        // this makes sure all classes are loaded and connection to redis is done properly.
        $queue = Yii::app()->queue;

        // close the database connection because of forking
        Yii::app()->getDb()->setActive(false);

        $logger = new Resque_Log((bool)$this->verbose);

        if((int)$this->workers > 1) {
            // start multiple workers
            for($i = 0; $i < (int)$this->workers; ++$i) {
                $pid = Resque::fork();
                if($pid == -1) {
                    $logger->log(Psr\Log\LogLevel::EMERGENCY, 'Could not fork worker {count}', array('count' => $i));
                    Yii::app()->end();
                } elseif (!$pid) {
                    // Child, start the worker
                    $queues = explode(',', $this->queues);
                    $worker = new Resque_Worker(array_map('trim', $queues));
                    $worker->setLogger($logger);
                    $logger->log(Psr\Log\LogLevel::NOTICE, 'Starting worker {worker}', array('worker' => $worker));
                    $worker->work((int)$this->interval, (bool)$this->blocking);
                    break;
                }
            }
        } else {
            // Start a single worker
            $queues = explode(',', $this->queues);
            $worker = new Resque_Worker(array_map('trim', $queues));
            $worker->setLogger($logger);
            $logger->log(Psr\Log\LogLevel::NOTICE, 'Starting worker {worker}', array('worker' => $worker));
            $worker->work((int)$this->interval, (bool)$this->blocking);
        }
    }
}
