<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTemporaryQueueTableBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.7.9
 * 
 */
 
class CampaignTemporaryQueueTableBehavior extends CActiveRecordBehavior
{
    /**
     * @var bool
     */
    public $verbose = false;
    
    /**
     * @return string
     */
    public function getTableName()
    {
        return '{{tmp_cmp_queue_' . (int)$this->owner->campaign_id . '}}';
    }

    /**
     * @return CDbTableSchema
     */
    public function tableExists()
    {
        return Yii::app()->db->schema->getTable($this->getTableName(), true);
    }

    /**
     * 
     */
    public function createTable()
    {
        if ($this->tableExists()) {
            return;
        }
        
        $this->stdout('Creating the campaign temporary queue table...');
        
        $tableName = $this->getTableName();
        $schema    = Yii::app()->db->schema;
        
        Yii::app()->db->createCommand($schema->createTable($tableName, array(
            'subscriber_id' => 'INT(11) NOT NULL UNIQUE',
            'failures'      => 'INT(11) NOT NULL DEFAULT 0',
        )))->execute();
        
        $fk = $schema->addForeignKey('subscriber_id_fk_' . $this->owner->campaign_id, $tableName, 'subscriber_id', '{{list_subscriber}}', 'subscriber_id', 'CASCADE', 'NO ACTION');
        Yii::app()->db->createCommand($fk)->execute();
    }

    /**
     * 
     */
    public function dropTable()
    {
        if (!$this->tableExists()) {
            return;
        }

        $this->stdout('Dropping the campaign temporary queue table...');

        $tableName = $this->getTableName();
        $schema    = Yii::app()->db->schema;

        Yii::app()->db->createCommand()->delete($this->getTableName());
        Yii::app()->db->createCommand($schema->dropForeignKey('subscriber_id_fk_' . $this->owner->campaign_id, $tableName))->execute();
        Yii::app()->db->createCommand($schema->dropTable($tableName))->execute();
    }

    /**
     * 
     */
    public function populateTable()
    {
        if ($this->tableExists()) {
            return;
        }

        $start = microtime(true);
        $fail  = false;
        $this->stdout('Populating the campaign temporary queue table, this will take a while...');
        
        $this->createTable();

        $criteria = new CDbCriteria();
        $criteria->select = 't.subscriber_id';
        $offset = 0;
        $limit  = 500;
        
        $count  = 0;
        $max    = 0;
        $subsCache = array();
        
        if ($this->owner->option->canSetMaxSendCount) {
            $max = $this->owner->option->max_send_count;
            if ($this->owner->option->canSetMaxSendCountRandom) {
                $criteria->order = 'RAND()';
            }
        }
        
        try {
            
            $subscribers = $this->owner->findSubscribers($offset, $limit, $criteria);
            
            while (!empty($subscribers)) {
                
                $insert = array();
                
                foreach ($subscribers as $subscriber) {
                    
                    $canInsert = !isset($subsCache[$subscriber->subscriber_id]);
                    
                    if ($canInsert) {
                        $insert[] = array('subscriber_id' => $subscriber->subscriber_id);
                        $subsCache[$subscriber->subscriber_id] = true;
                        $count++;
                    }
                    
                    if ($max > 0 && $count >= $max) {
                        break;
                    }
                }
                
                if (!empty($insert)) {
                    $connection = Yii::app()->db->getSchema()->getCommandBuilder();
                    $command = $connection->createMultipleInsertCommand($this->getTableName(), $insert);
                    $command->execute();
                }
                
                if ($max > 0 && $count >= $max) {
                    break;
                }
                
                $offset = $offset + $limit;
                $subscribers = $this->owner->findSubscribers($offset, $limit, $criteria);
            }
            unset($subscribers, $subsCache);
        
        } catch (Exception $e) {
            
            $this->dropTable();
            
            $this->stdout($e->getMessage());

            $fail = true;
        }
        
        if (!$fail) {
            $this->stdout('Done populating the campaign temporary queue table, took: ' . round(microtime(true) - $start, 3));
        }
    }

    /**
     * @param $subscriberId
     */
    public function deleteSubscriber($subscriberId)
    {
        $this->stdout('Deleting subscriber from the campaign temporary queue table...');
        
        Yii::app()->db->createCommand()->delete($this->getTableName(), 'subscriber_id = :sid', array(
            ':sid' => $subscriberId,
        ));
    }

    /**
     * @return int
     */
    public function countSubscribers()
    {
        $row = Yii::app()->db->createCommand()->select('count(*) as cnt')->from($this->getTableName())->queryRow();
        return (int)$row['cnt'];
    }

    /**
     * @param $offset
     * @param $limit
     * @return array
     */
    public function findSubscribers($offset, $limit)
    {
        $query = Yii::app()->db->createCommand()
            ->select('subscriber_id')
            ->from($this->getTableName())
            ->offset($offset)
            ->limit($limit);
        
        $rows   = $query->queryAll();
        $chunks = array_chunk($rows, 300);
        $subscribers = array();
        
        foreach ($chunks as $chunk) {
            $ids = array();
            foreach ($chunk as $row) {
                $ids[] = $row['subscriber_id'];
            }
            $criteria = new CDbCriteria();
            $criteria->addInCondition('subscriber_id', $ids);
            $models = ListSubscriber::model()->findAll($criteria);
            foreach ($models as $model) {
                $subscribers[] = $model;
            }
        }
        
        return $subscribers;
    }

    /**
     * @param $message
     * @param bool $timer
     * @param string $separator
     * @return int
     */
    protected function stdout($message, $timer = true, $separator = "\n")
    {
        if (!$this->verbose) {
            return 0;
        }

        if (!is_array($message)) {
            $message = array($message);
        }

        $out = '';

        foreach ($message as $msg) {

            if ($timer) {
                $out .= '[' . date('Y-m-d H:i:s') . '] - ';
            }

            $out .= $msg;

            if ($separator) {
                $out .= $separator;
            }
        }

        echo $out;
        return 0;
    }
}