<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerMessage
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

/**
 * This is the model class for table "customer_message".
 *
 * The followings are the available columns in table 'customer_message':
 * @property integer $message_id
 * @property string $message_uid
 * @property integer $customer_id
 * @property string $title
 * @property string $message
 * @property string $params
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerMessage extends ActiveRecord
{
	const STATUS_UNSEEN = 'unseen';

	const STATUS_SEEN = 'seen';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_message}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('customer_id, message', 'required'),
			array('customer_id', 'exist', 'className' => 'Customer'),
			array('title', 'length', 'max' => 255),
			array('message', 'length', 'min' => 5),
			array('status', 'in', 'range' => array_keys($this->getStatusesList())),

			// The following rule is used by search().
			array('customer_id, title, message, status', 'safe', 'on'=>'search'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'message_id'  => Yii::t('messages', 'Message'),
			'message_uid' => Yii::t('messages', 'Message'),
			'customer_id' => Yii::t('messages', 'Customer'),
			'title'		  => Yii::t('messages', 'Title'),
			'message' 	  => Yii::t('messages', 'Message'),
			'params'	  => Yii::t('messages', 'Params'),
		);
		return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with['customer'] = array(
                    'condition' => 'customer.email LIKE :name OR customer.first_name LIKE :name OR customer.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->customer_id . '%')
                );
            }
        }

		$criteria->compare('t.title', $this->title, true);
		$criteria->compare('t.message', $this->message, true);
		$criteria->compare('t.status', $this->status);

		$criteria->order = 't.message_id DESC';

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.message_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerMessage the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        if ($this->isNewRecord) {
            $this->message_uid = $this->generateUid();
        }

        return true;
    }

	public function findByUid($message_uid)
    {
        return $this->findByAttributes(array(
            'message_uid' => $message_uid,
        ));
    }

	public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);

        if (!empty($exists)) {
            return $this->generateUid();
        }

        return $unique;
    }

    public function getUid()
    {
        return $this->message_uid;
    }

	public function getStatusesList()
    {
        return array(
            self::STATUS_UNSEEN => Yii::t('messages', 'Unseen'),
            self::STATUS_SEEN   => Yii::t('messages', 'Seen'),
        );
    }

	public function getShortMessage($length = 45)
	{
		return StringHelper::truncateLength($this->message, $length);
	}

	public function getShortTitle($length = 25)
	{
		return StringHelper::truncateLength($this->title, $length);
	}

	public function getIsUnseen()
	{
		return $this->status == self::STATUS_UNSEEN;
	}

	public function getIsSeen()
	{
		return $this->status == self::STATUS_SEEN;
	}

	public function saveStatus($status = null)
    {
        if (empty($this->message_id)) {
            return false;
        }

        if ($status) {
            $this->status = $status;
        }

		$attributes = array('status' => $this->status);
		return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'message_id = :id', array(':id' => (int)$this->message_id));
    }

	public static function markAllAsSeenForCustomer($customerId)
	{
		$attributes = array('status' => self::STATUS_SEEN);
		$instance   = new self();
		return Yii::app()->getDb()->createCommand()->update($instance->tableName(), $attributes, 'customer_id = :id', array(':id' => (int)$customerId));
	}
}
