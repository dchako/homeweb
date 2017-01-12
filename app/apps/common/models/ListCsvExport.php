<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListCsvExport
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ListCsvExport extends FormModel
{
    public $list_id;
    
    public $count = 0;
    
    public $is_first_batch = 1;

    public $current_page = 1;

    public function rules()
    {
        $rules = array(
            array('count, current_page, is_first_batch', 'numerical', 'integerOnly' => true),
            array('list_id', 'unsafe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }
    
    public function countSubscribers()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$this->list_id);
        $criteria->compare('status', ListSubscriber::STATUS_CONFIRMED);
        return ListSubscriber::model()->count($criteria);
    }
    
    public function findSubscribers($limit = 10, $offset = 0)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'subscriber_id, source, ip_address, date_added';
        $criteria->compare('list_id', (int)$this->list_id);
        $criteria->compare('status', ListSubscriber::STATUS_CONFIRMED);
        $criteria->offset = (int)$offset;
        $criteria->limit = (int)$limit;
        $subscribers = ListSubscriber::model()->findAll($criteria);
        
        if (empty($subscribers)) {
            return array();
        }
        
        $criteria = new CDbCriteria();
        $criteria->select = 'field_id, tag';
        $criteria->compare('list_id', $this->list_id);
        $criteria->order = 'sort_order ASC, tag ASC';
        $fields = ListField::model()->findAll($criteria);
        
        if (empty($fields)) {
            return array();
        }
        
        $data = array();
        foreach ($subscribers as $subscriber) {
            $_data = array();
            foreach ($fields as $field) {
                $value = null;
                
                $criteria = new CDbCriteria();
                $criteria->select = 'value';
                $criteria->compare('field_id', (int)$field->field_id);
                $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
                $valueModels = ListFieldValue::model()->findAll($criteria);

                if (!empty($valueModels)) {
                    $value = array();
                    foreach($valueModels as $valueModel) {
                        $value[] = $valueModel->value;
                    }
                    $value = implode(', ', $value);
                }
                $_data[$field->tag] = CHtml::encode($value);
            }
            foreach (array('source', 'ip_address', 'date_added') as $key) {
                $tag = strtoupper($key);
                if (empty($_data[$tag])) {
                    $_data[$tag] = $subscriber->$key;
                }
            }
            $data[] = $_data;    
        }
        
        unset($subscribers, $fields, $_data, $subscriber, $field);
        
        return $data;
    }
}