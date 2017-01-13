<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * RedisQueue
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
 
class RedisQueue extends CApplicationComponent
{
    // redis hostname
    public $hostname;
    
    // redis port
    public $port;
    
    // redis database number
    public $database;
    
    // redis database prefix
    public $prefix;
    
    /**
     * Init the component
     */
    public function init()
    {
        Yii::import('common.components.queue.redis-jobs.*');
        
        if ($this->hostname === null) {
            if ($hostname = Yii::app()->options->get('system.queue.redis_queue.hostname')) {
                $this->hostname = $hostname;
            } else {
                $this->hostname = 'localhost';
            }
        }
        
        if ($this->port === null) {
            if ($port = Yii::app()->options->get('system.queue.redis_queue.port')) {
                $this->port = $port;
            } else {
                $this->port = 6379;
            }
        }
        
        if ($this->database === null) {
            if (($database = Yii::app()->options->get('system.queue.redis_queue.database')) !== false) {
                $this->database = (int)$database;
            } else {
                $this->database = 0;
            }
        }
        
        Resque::setBackend($this->hostname . ':' . $this->port, $this->database);
        
        if ($this->prefix === null) {
            $hash = sha1(__FILE__);
            $this->prefix = substr($hash, 0, 5) . substr($hash, -5);
        }
        
        Resque_Redis::prefix($this->prefix);
        
        parent::init();
    }

	/**
	 * Remove items of the specified queue
	 *
	 * @param string $queue The name of the queue to fetch an item from.
	 * @param array $items
	 * @return integer number of deleted items
	 */
	public function dequeue($queue, $items = array())
	{
	    return Resque::dequeue($queue, $items);
	}

	/**
	 * Return the size (number of pending jobs) of the specified queue.
	 *
	 * @param string $queue name of the queue to be checked for pending jobs
	 *
	 * @return int The size of the queue.
	 */
	public function size($queue)
	{
		return Resque::size($queue);
	}
    
    /**
	 * Create a new job and save it to the specified queue.
	 *
	 * @param string $queue The name of the queue to place the job in.
	 * @param string $class The name of the class that contains the code to execute the job.
	 * @param array $args Any optional arguments that should be passed when the job is executed.
	 * @param boolean $trackStatus Set to true to be able to monitor the status of a job.
	 *
	 * @return string
	 */
    public function enqueue($queue, $class, $args = null, $trackStatus = false)
    {
        return Resque::enqueue($queue, $class, $args, $trackStatus);
    }
    
	/**
	 * Get an array of all known queues.
	 *
	 * @return array array of queues.
	 */
	public function queues()
	{
		return Resque::queues();
	}
}