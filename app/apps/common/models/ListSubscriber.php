<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriber
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "list_subscriber".
 *
 * The followings are the available columns in table 'list_subscriber':
 * @property integer $subscriber_id
 * @property integer $list_id
 * @property string $subscriber_uid
 * @property string $email
 * @property string $source
 * @property string $status
 * @property string $ip_address
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CampaignBounceLog[] $bounceLogs
 * @property CampaignDeliveryLog[] $deliveryLogs
 * @property CampaignDeliveryLogArchive[] $deliveryLogsArchive
 * @property CampaignForwardFriend[] $forwardFriends
 * @property CampaignTrackOpen[] $trackOpens
 * @property CampaignTrackUnsubscribe[] $trackUnsubscribes
 * @property CampaignTrackUrl[] $trackUrls
 * @property EmailBlacklist $emailBlacklist
 * @property ListFieldValue[] $fieldValues
 * @property Lists $list
 * @property ListSubscriberFieldCache $fieldsCache
 */
class ListSubscriber extends ActiveRecord
{
    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_UNCONFIRMED = 'unconfirmed';

    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    const STATUS_BLACKLISTED = 'blacklisted';
    
    const STATUS_UNAPPROVED = 'unapproved';

    const STATUS_DISABLED = 'disabled';
    
    const STATUS_MOVED = 'moved';
    
    const SOURCE_WEB = 'web';

    const SOURCE_API = 'api';

    const SOURCE_IMPORT = 'import';

    const BULK_SUBSCRIBE = 'subscribe';

    const BULK_UNSUBSCRIBE = 'unsubscribe';

    const BULK_DISABLE = 'disable';
    
    const BULK_DELETE = 'delete';

    const BULK_BLACKLIST = 'blacklist';

    const CAMPAIGN_FILTER_ACTION_DID_OPEN = 1;
    
    const CAMPAIGN_FILTER_ACTION_DID_CLICK = 2;
    
    const CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN = 3;
    
    const CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK = 4;
    
    const FILTER_TIME_UNIT_DAY = 1;
    
    const FILTER_TIME_UNIT_WEEK = 2;
    
    const FILTER_TIME_UNIT_MONTH = 3;
    
    const FILTER_TIME_UNIT_YEAR = 4;
    
    // when select count(x) as counter
    public $counter = 0;

    // for search in multilists
    public $listIds = array();
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_subscriber}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            array('list_id, subscriber_uid, email, source, ip_address, status', 'safe', 'on' => 'search'),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'bounceLogs'            => array(self::HAS_MANY, 'CampaignBounceLog', 'subscriber_id'),
            'deliveryLogs'          => array(self::HAS_MANY, 'CampaignDeliveryLog', 'subscriber_id'),
            'deliveryLogsArchive'   => array(self::HAS_MANY, 'CampaignDeliveryLogArchive', 'subscriber_id'),
            'forwardFriends'        => array(self::HAS_MANY, 'CampaignForwardFriend', 'subscriber_id'),
            'trackOpens'            => array(self::HAS_MANY, 'CampaignTrackOpen', 'subscriber_id'),
            'trackUnsubscribes'     => array(self::HAS_MANY, 'CampaignTrackUnsubscribe', 'subscriber_id'),
            'trackUrls'             => array(self::HAS_MANY, 'CampaignTrackUrl', 'subscriber_id'),
            'emailBlacklist'        => array(self::HAS_ONE, 'EmailBlacklist', 'subscriber_id'),
            'fieldValues'           => array(self::HAS_MANY, 'ListFieldValue', 'subscriber_id'),
            'list'                  => array(self::BELONGS_TO, 'Lists', 'list_id'),
            'fieldsCache'           => array(self::HAS_ONE, 'ListSubscriberFieldCache', 'subscriber_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'subscriber_id'     => Yii::t('list_subscribers', 'Subscriber'),
            'list_id'           => Yii::t('list_subscribers', 'List'),
            'subscriber_uid'    => Yii::t('list_subscribers', 'Unique ID'),
            'email'             => Yii::t('list_subscribers', 'Email'),
            'source'            => Yii::t('list_subscribers', 'Source'),
            'ip_address'        => Yii::t('list_subscribers', 'Ip Address'),
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

        if (!empty($this->list_id)) {
            $criteria->compare('t.list_id', (int)$this->list_id);
        } elseif (!empty($this->listIds)) {
            $criteria->addInCondition('t.list_id', array_map('intval', $this->listIds));
        }

        $criteria->compare('t.subscriber_uid', $this->subscriber_uid);
        $criteria->compare('t.email', $this->email, true);
        $criteria->compare('t.source', $this->source);
        $criteria->compare('t.ip_address', $this->ip_address, true);
        $criteria->compare('t.status', $this->status);

        $criteria->order = 't.subscriber_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.subscriber_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSubscriber the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        if (empty($this->subscriber_uid)) {
            $this->subscriber_uid = $this->generateUid();
        }

        return parent::beforeSave();
    }
    
    public function findByUid($subscriber_uid)
    {
        return $this->findByAttributes(array(
            'subscriber_uid' => $subscriber_uid,
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

    public function getIsBlacklisted()
    {
        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_SUBSCRIBER_BLACKLIST_CHECK) {
            return false;
        }

        // check since 1.3.4.7
        if ($this->status == self::STATUS_BLACKLISTED) {
            return new EmailBlacklistCheckInfo(array(
                'email'       => $this->email,
                'blacklisted' => true,
                'reason'      => 'Blacklisted',
            ));
        }

        $blCheckInfo = EmailBlacklist::isBlacklisted($this->email, $this);
        
        // added since 1.3.4.7
        if ($blCheckInfo !== false && $this->status != self::STATUS_BLACKLISTED) {
            $criteria = new CDbCriteria();
            $criteria->compare('subscriber_id', (int)$this->subscriber_id);

            ListSubscriber::model()->updateAll(array(
                'status'       => self::STATUS_BLACKLISTED, 
                'last_updated' => new CDbExpression('NOW()')
            ), $criteria);
            $this->status = self::STATUS_BLACKLISTED;
        }

        return $blCheckInfo;
    }

    public function addToBlacklist($reason = null)
    {
        if ($added = EmailBlacklist::addToBlacklist($this, $reason)) {
            $this->status = self::STATUS_BLACKLISTED;
        }
        return $added;
    }

    public function removeFromBlacklistByEmail()
    {
        if ($this->status == self::STATUS_BLACKLISTED) {
            return false;
        }
        return EmailBlacklist::removeByEmail($this->email);
    }

    public function getCanBeConfirmed()
    {
        return !in_array($this->status, array(self::STATUS_CONFIRMED, self::STATUS_BLACKLISTED));
    }

    public function getCanBeUnsubscribed()
    {
        return !in_array($this->status, array(self::STATUS_BLACKLISTED));
    }

    public function getCanBeDeleted()
    {
        return $this->getRemovable();
    }
    
    public function getCanBeApproved()
    {
        return $this->status == self::STATUS_UNAPPROVED;
    }
    
    public function getIsUnapproved()
    {
        return $this->status == self::STATUS_UNAPPROVED;
    }
    
    public function getIsConfirmed()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }
    
    public function getIsUnconfirmed()
    {
        return $this->status == self::STATUS_UNCONFIRMED;
    }

    public function getIsUnsubscribed()
    {
        return $this->status == self::STATUS_UNSUBSCRIBED;
    }
    
    public function getIsDisabled()
    {
        return $this->status == self::STATUS_DISABLED;
    }
    
    public function getCanBeDisabled()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }

    public function getIsMoved()
    {
        return $this->status == self::STATUS_MOVED;
    }
    
    public function getRemovable()
    {
        $removable = true;
        if (!empty($this->list_id) && !empty($this->list) && !empty($this->list->customer_id) && !empty($this->list->customer)) {
            $removable = $this->list->customer->getGroupOption('lists.can_delete_own_subscribers', 'yes') == 'yes';
        }
        return $removable;
    }

    public function getUid()
    {
        return $this->subscriber_uid;
    }

    public function getStatusesList()
    {
        return array(
            self::STATUS_CONFIRMED      => Yii::t('list_subscribers', ucfirst(self::STATUS_CONFIRMED)),
            self::STATUS_UNCONFIRMED    => Yii::t('list_subscribers', ucfirst(self::STATUS_UNCONFIRMED)),
            self::STATUS_UNSUBSCRIBED   => Yii::t('list_subscribers', ucfirst(self::STATUS_UNSUBSCRIBED)),
        );
    }

    public function getFilterStatusesList()
    {
        return array_merge($this->getStatusesList(), array(
            self::STATUS_UNAPPROVED  => Yii::t('list_subscribers', ucfirst(self::STATUS_UNAPPROVED)),
            self::STATUS_BLACKLISTED => Yii::t('list_subscribers', ucfirst(self::STATUS_BLACKLISTED)),
            self::STATUS_DISABLED    => Yii::t('list_subscribers', ucfirst(self::STATUS_DISABLED)),
            self::STATUS_MOVED       => Yii::t('list_subscribers', ucfirst(self::STATUS_MOVED)),
        ));
    }

    public function getBulkActionsList()
    {
        $list = array(
            self::BULK_SUBSCRIBE    => Yii::t('list_subscribers', ucfirst(self::BULK_SUBSCRIBE)),
            self::BULK_UNSUBSCRIBE  => Yii::t('list_subscribers', ucfirst(self::BULK_UNSUBSCRIBE)),
            self::BULK_DISABLE      => Yii::t('list_subscribers', ucfirst(self::BULK_DISABLE)),
            self::BULK_DELETE       => Yii::t('list_subscribers', ucfirst(self::BULK_DELETE)),
        );

        if (!$this->getCanBeDeleted()) {
            unset($list[self::BULK_DELETE]);
        }

        return $list;
    }

    public function getSourcesList()
    {
        return array(
            self::SOURCE_API    => Yii::t('list_subscribers', ucfirst(self::SOURCE_API)),
            self::SOURCE_IMPORT => Yii::t('list_subscribers', ucfirst(self::SOURCE_IMPORT)),
            self::SOURCE_WEB    => Yii::t('list_subscribers', ucfirst(self::SOURCE_WEB)),
        );
    }

    /**
     * 
     * Since 1.3.6.3 it will also update custom fields value.
     * 
     * @param $listId
     * @param bool $doTransaction
     * @return bool|ListSubscriber|static
     * @throws CDbException
     */
    public function copyToList($listId, $doTransaction = true)
    {

        $listId = (int)$listId;
        if (empty($listId) || $listId == $this->list_id) {
            return false;
        }

        static $targetLists      = array();
        static $cacheFieldModels = array();

        if (isset($targetLists[$listId]) || array_key_exists($listId, $targetLists)) {
            $targetList = $targetLists[$listId];
        } else {
            $targetList = $targetLists[$listId] = Lists::model()->findByPk($listId);
        }

        if (empty($targetList)) {
            return false;
        }
        
        $subscriber = self::model()->findByAttributes(array(
            'list_id' => $targetList->list_id,
            'email'   => $this->email
        ));
        
        if (empty($subscriber)) {
            $subscriber = clone $this;
            $subscriber->isNewRecord    = true;
            $subscriber->subscriber_id  = null;
            $subscriber->list_id        = $targetList->list_id;
            $subscriber->date_added     = new CDbExpression('NOW()');
            $subscriber->last_updated   = new CDbExpression('NOW()');
            $subscriber->subscriber_uid = $this->generateUid();
            $subscriber->addRelatedRecord('list', $targetList, false);
        }
        
        if ($doTransaction) {
            $transaction = Yii::app()->getDb()->beginTransaction();
        }

        try {

            if ($subscriber->isNewRecord && !$subscriber->save()) {
                throw new Exception(CHtml::errorSummary($subscriber));
            }


            $cacheListsKey = $this->list_id . '|' . $targetList->list_id;
            if (!isset($cacheFieldModels[$cacheListsKey])) {
                // the custom fields for source list
                $sourceFields = ListField::model()->findAllByAttributes(array(
                    'list_id' => $this->list_id,
                ));

                // the custom fields for target list
                $targetFields = ListField::model()->findAllByAttributes(array(
                    'list_id' => $targetList->list_id,
                ));

                // get only the same fields
                $_fieldModels = array();
                foreach ($sourceFields as $srcIndex => $sourceField) {
                    foreach ($targetFields as $trgIndex => $targetField) {
                        if ($sourceField->tag == $targetField->tag && $sourceField->type_id == $targetField->type_id) {
                            $_fieldModels[] = array($sourceField, $targetField);
                            unset($sourceFields[$srcIndex], $targetFields[$trgIndex]);
                            break;
                        }
                    }
                }
                $cacheFieldModels[$cacheListsKey] = $_fieldModels;
                unset($sourceFields, $targetFields, $_fieldModels);
            }
            $fieldModels = $cacheFieldModels[$cacheListsKey];

            if (empty($fieldModels)) {
                throw new Exception('No field models found, something went wrong!');
            }

            foreach ($fieldModels as $index => $models) {
                
                list($source, $target) = $models;
                
                $sourceValues = ListFieldValue::model()->findAllByAttributes(array(
                    'subscriber_id' => $this->subscriber_id,
                    'field_id'      => $source->field_id,
                ));
                
                ListFieldValue::model()->deleteAllByAttributes(array(
                    'subscriber_id' => $subscriber->subscriber_id,
                    'field_id'      => $target->field_id,
                ));
                
                foreach ($sourceValues as $sourceValue) {
                    $targetValue                = clone $sourceValue;
                    $targetValue->value_id      = null;
                    $targetValue->field_id      = $target->field_id;
                    $targetValue->subscriber_id = $subscriber->subscriber_id;
                    $targetValue->isNewRecord   = true;
                    $targetValue->date_added    = new CDbExpression('NOW()');
                    $targetValue->last_updated  = new CDbExpression('NOW()');
                    if (!$targetValue->save()) {
                        throw new Exception(CHtml::errorSummary($targetValue));
                    }
                }
                unset($models, $source, $target, $sourceValues, $sourceValue);
            }
            unset($fieldModels);

            if ($doTransaction) {
                $transaction->commit();
            }
        } catch (Exception $e) {
            if ($doTransaction) {
                $transaction->rollBack();
            } elseif (!empty($subscriber->subscriber_id)) {
                $subscriber->delete();
            }
            $subscriber = false;
        }

        return $subscriber;
    }
    
    public function moveToList($listId)
    {
        if (!($subscriber = $this->copyToList($listId))) {
            return false;
        }
        
        $exists = ListSubscriberListMove::model()->findByAttributes(array(
            'source_subscriber_id'  => $this->subscriber_id,
            'source_list_id'        => $this->list_id,
            'destination_list_id'   => $listId,
        ));
        
        if (!empty($exists)) {
            $this->saveStatus(ListSubscriber::STATUS_MOVED);
            return $subscriber;
        }

        $move = new ListSubscriberListMove();
        $move->source_subscriber_id      = $this->subscriber_id;
        $move->source_list_id            = $this->list_id;
        $move->destination_subscriber_id = $subscriber->subscriber_id;
        $move->destination_list_id       = $listId;
        
        try {
            $move->save(false);
            $this->saveStatus(ListSubscriber::STATUS_MOVED);
        } catch (Exception $e) {
            return false;
        }
        
        return $subscriber;
    }

    public function saveStatus($status = null)
    {
        if (empty($this->subscriber_id)) {
            return false;
        }
        if ($status && $status == $this->status) {
            return true;
        }
        if ($status) {
            $this->status = $status;
        }
        $attributes = array('status' => $this->status);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');
        return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'subscriber_id = :id', array(':id' => (int)$this->subscriber_id));
    }

    // since 1.3.5 - this should be expanded in future
    public function takeListSubscriberAction($actionName)
    {
        if ($this->isNewRecord || empty($this->list_id)) {
            return $this;
        }

        if ($actionName == ListSubscriberAction::ACTION_SUBSCRIBE && $this->status != self::STATUS_CONFIRMED) {
            return $this;
        }

        if ($actionName == ListSubscriberAction::ACTION_UNSUBSCRIBE && $this->status == self::STATUS_CONFIRMED) {
            return $this;
        }

        $allowedActions = array_keys(ListSubscriberAction::model()->getActions());
        if (!in_array($actionName, $allowedActions)) {
            return $this;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'target_list_id';
        $criteria->compare('source_list_id', (int)$this->list_id);
        $criteria->compare('source_action', $actionName);

        $_lists = ListSubscriberAction::model()->findAll($criteria);
        if (empty($_lists)) {
            return $this;
        }

        $lists = array();
        foreach ($_lists as $list) {
            $lists[] = $list->target_list_id;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('email', $this->email);
        $criteria->addInCondition('list_id', $lists);
        $criteria->addInCondition('status', array(self::STATUS_CONFIRMED));

        self::model()->updateAll(array('status' => self::STATUS_UNSUBSCRIBED), $criteria);

        return $this;
    }
    
    public function loadAllCustomFieldsWithValues()
    {
        $fields = array();
        foreach (ListField::getAllByListId($this->list_id) as $field) {
            $values = Yii::app()->getDb()->createCommand()
                ->select('value')
                ->from('{{list_field_value}}')
                ->where('subscriber_id = :sid AND field_id = :fid', array(
                    ':sid' => (int)$this->subscriber_id,
                    ':fid' => (int)$field['field_id']
                ))
                ->queryAll();

            $value = array();
            foreach ($values as $val) {
                $value[] = $val['value'];
            }
            $fields['['. $field['tag'] .']'] = CHtml::encode(implode(', ', $value));
        }

        return $fields;
    }
    
    public function getAllCustomFieldsWithValues($refresh = false)
    {
        static $fields = array();
        
        if (empty($this->subscriber_id)) {
            return array();
        }
        
        if ($refresh && isset($fields[$this->subscriber_id])) {
            unset($fields[$this->subscriber_id]);
        }
        
        if (isset($fields[$this->subscriber_id])) {
            return $fields[$this->subscriber_id];
        }
        
        $fields[$this->subscriber_id] = array();
        
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_ENABLE_SUBSCRIBER_FIELD_CACHE) {
            
            if (!$refresh && !empty($this->fieldsCache)) {
                return $fields[$this->subscriber_id] = $this->fieldsCache->data;
            }

            if ($refresh) {

                ListSubscriberFieldCache::model()->deleteAllByAttributes(array(
                    'subscriber_id' => $this->subscriber_id,
                ));

            }

            $data  = $this->loadAllCustomFieldsWithValues();
            $model = new ListSubscriberFieldCache();
            $model->subscriber_id = $this->subscriber_id;
            $model->data = $data;

            try {
                if (!$model->save()) {
                    throw new Exception('Not saved!');
                }
            } catch (Exception $e) {}

            $model->data = $data;
            $this->addRelatedRecord('fieldsCache', $model, false);

            return $fields[$this->subscriber_id]= $model->data;
        }
        
        return $fields[$this->subscriber_id] = $this->loadAllCustomFieldsWithValues();
    }

    public function getCustomFieldValue($field)
    {
        $field  = '['. strtoupper(str_replace(array('[', ']'), '', $field)) .']';
        $fields = $this->getAllCustomFieldsWithValues();
        $value  = isset($fields[$field]) || array_key_exists($field, $fields) ? $fields[$field] : null;
        unset($fields);
        return $value;
    }
    
    public function hasOpenedCampaign(Campaign $campaign)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', (int)$campaign->campaign_id);
        $criteria->compare('subscriber_id', (int)$this->subscriber_id);
        return CampaignTrackOpen::model()->count($criteria) > 0;
    }
    
    // since 1.3.6.2
    public function handleApprove($forcefully = false)
    {
        if (!$forcefully && !$this->getCanBeApproved()) {
            return $this;
        }
        
        if (empty($this->list_id) || empty($this->list) || $this->list->subscriber_require_approval != Lists::TEXT_YES) {
            return $this;
        }
        
        $pageType = ListPageType::model()->findBySlug('subscribe-confirm-approval-email');
        if (!($server = DeliveryServer::pickServer(0, $this->list))) {
            $pageType = null;
        }
        
        if (empty($pageType)) {
            return $this;
        }

        $options = Yii::app()->options;
        $page    = ListPage::model()->findByAttributes(array(
            'list_id' => $this->list_id,
            'type_id' => $pageType->type_id
        ));

        $_content         = !empty($page->content) ? $page->content : $pageType->content;
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/update-profile/' . $this->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/unsubscribe/' . $this->subscriber_uid;
        $searchReplace    = array(
            '[LIST_NAME]'           => $this->list->display_name,
            '[COMPANY_NAME]'        => !empty($this->list->company) ? $this->list->company->name : null,
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($this->list->company) ? nl2br($this->list->company->getFormattedAddress()) : null,
            '[CURRENT_YEAR]'        => date('Y'),
        );
        
        $subscriberCustomFields = $this->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        
        $_content = str_replace(array_keys($searchReplace), array_values($searchReplace), $_content);

        $params = array(
            'to'        => $this->email,
            'fromName'  => $this->list->default->from_name,
            'subject'   => Yii::t('list_subscribers', 'Your subscription has been approved!'),
            'body'      => $_content,
        );

        for ($i = 0; $i < 3; ++$i) {
            if ($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($this->list)->sendEmail($params)) {
                break;
            }
            $server = DeliveryServer::pickServer($server->server_id, $this->list);
        }
        
        return $this;
    }
    
    // since 1.3.6.2
    public function handleWelcome($forcefully = false)
    {
        if (!$forcefully && !$this->getIsConfirmed()) {
            return $this;
        }
        
        if (empty($this->list_id) || empty($this->list) || $this->list->welcome_email != Lists::TEXT_YES) {
            return $this;
        }

        $pageType = ListPageType::model()->findBySlug('welcome-email');
        if (!($server = DeliveryServer::pickServer(0, $this->list))) {
            $pageType = null;
        }

        if (empty($pageType)) {
            return $this;
        }

        $options = Yii::app()->options;
        $page    = ListPage::model()->findByAttributes(array(
            'list_id' => $this->list_id,
            'type_id' => $pageType->type_id
        ));

        $_content         = !empty($page->content) ? $page->content : $pageType->content;
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/update-profile/' . $this->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/unsubscribe/' . $this->subscriber_uid;
        $searchReplace    = array(
            '[LIST_NAME]'           => $this->list->display_name,
            '[COMPANY_NAME]'        => !empty($this->list->company) ? $this->list->company->name : null,
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($this->list->company) ? nl2br($this->list->company->getFormattedAddress()) : null,
            '[CURRENT_YEAR]'        => date('Y'),
        );

        // since 1.3.5.9
        $subscriberCustomFields = $this->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        //

        $_content = str_replace(array_keys($searchReplace), array_values($searchReplace), $_content);

        $params = array(
            'to'        => $this->email,
            'fromName'  => $this->list->default->from_name,
            'subject'   => Yii::t('list_subscribers', 'Thank you for your subscription!'),
            'body'      => $_content,
        );

        for ($i = 0; $i < 3; ++$i) {
            if ($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($this->list)->sendEmail($params)) {
                break;
            }
            $server = DeliveryServer::pickServer($server->server_id, $this->list);
        }
        
        return $this;
    }
    
    public function getCampaignFilterActions()
    {
        return array(
            self::CAMPAIGN_FILTER_ACTION_DID_OPEN      => Yii::t('list_subscribers', 'Did open'),
            self::CAMPAIGN_FILTER_ACTION_DID_CLICK     => Yii::t('list_subscribers', 'Did click'),
            self::CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN  => Yii::t('list_subscribers', 'Did not open'),
            self::CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK => Yii::t('list_subscribers', 'Did not click'),
        );
    }
    
    public function getFilterTimeUnits()
    {
        return array(
            self::FILTER_TIME_UNIT_DAY   => Yii::t('list_subscribers', 'Days'),
            self::FILTER_TIME_UNIT_WEEK  => Yii::t('list_subscribers', 'Weeks'),
            self::FILTER_TIME_UNIT_MONTH => Yii::t('list_subscribers', 'Months'),
            self::FILTER_TIME_UNIT_YEAR  => Yii::t('list_subscribers', 'Years'),
        );
    }
    
    public function getFilterTimeUnitValueForDb($in)
    {
        if ($in == self::FILTER_TIME_UNIT_DAY) {
            return 'DAY';
        }
        if ($in == self::FILTER_TIME_UNIT_WEEK) {
            return 'WEEK';
        }
        if ($in == self::FILTER_TIME_UNIT_MONTH) {
            return 'MONTH';
        }
        if ($in == self::FILTER_TIME_UNIT_YEAR) {
            return 'YEAR';
        }
        return 'MONTH';
    }
    
    public function getGridViewHtmlStatus()
    {
        if ($this->getIsMoved()) {
            
            $moved = ListSubscriberListMove::model()->findByAttributes(array(
                'source_subscriber_id'  => $this->subscriber_id,
                'source_list_id'        => $this->list_id,
            ));
            
            if (!empty($moved)) {
                $url = 'javascript:;';
                if (Yii::app()->apps->isAppName('customer')) {
                    $url = Yii::app()->createUrl('list_subscribers/update', array(
                        'list_uid'       => $moved->destinationList->list_uid,
                        'subscriber_uid' => $moved->destinationSubscriber->subscriber_uid,
                    ));
                }
                $where = CHtml::link($moved->destinationList->name, $url, array('target' => '_blank', 'title' => Yii::t('app', 'View')));
                return ucfirst(Yii::t('list_subscribers', $this->status)) . ': ' . $where;
            }
        }
        
        return ucfirst(Yii::t('list_subscribers', $this->status));
    }
}
