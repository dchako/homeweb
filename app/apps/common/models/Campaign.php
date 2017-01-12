<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "campaign".
 *
 * The followings are the available columns in table 'campaign':
 * @property integer $campaign_id
 * @property string $campaign_uid
 * @property integer $customer_id
 * @property integer $list_id
 * @property integer $segment_id
 * @property integer $group_id
 * @property string $type
 * @property string $name
 * @property string $from_name
 * @property string $from_email
 * @property string $to_name
 * @property string $reply_to
 * @property string $subject
 * @property string $send_at
 * @property string $started_at
 * @property string $finished_at
 * @property string $delivery_logs_archived
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CampaignGroup $group
 * @property Customer $customer
 * @property List $list
 * @property ListSegment $segment
 * @property CampaignBounceLog[] $bounceLogs
 * @property CampaignDeliveryLog[] $deliveryLogs
 * @property CampaignDeliveryLogArchive[] $deliveryLogsArchive
 * @property CampaignForwardFriend[] $forwardFriends
 * @property CampaignOpenActionListField[] $openActionListFields
 * @property CampaignOpenActionSubscriber[] $openActionSubscribers
 * @property CampaignTemplateUrlActionListField[] $urlActionListFields
 * @property CampaignTemplateUrlActionSubscriber[] $urlActionSubscribers
 * @property CampaignTemporarySource[] $temporarySources
 * @property CampaignLink[] $links
 * @property CampaignOption $option
 * @property CampaignTemplate $template
 * @property CampaignTemplate[] $templates
 * @property CampaignAttachment[] $attachments
 * @property DeliveryServer[] $deliveryServers
 * @property CampaignTrackOpen[] $trackOpens
 * @property CampaignTrackUnsubscribe[] $trackUnsubscribes
 * @property CampaignUrl[] $urls
 */
class Campaign extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';

    const STATUS_PENDING_SENDING = 'pending-sending';

    const STATUS_SENDING = 'sending';

    const STATUS_SENT = 'sent';

    const STATUS_PROCESSING = 'processing';

    const STATUS_PAUSED = 'paused';

    const STATUS_PENDING_DELETE = 'pending-delete';

    const STATUS_BLOCKED = 'blocked';

    const TYPE_REGULAR = 'regular';

    const TYPE_AUTORESPONDER = 'autoresponder';

    const BULK_ACTION_PAUSE_UNPAUSE = 'pause-unpause';

    const BULK_ACTION_MARK_SENT = 'mark-sent';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{campaign}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, list_id', 'required', 'on' => 'step-name, step-confirm'),
            array('from_name, from_email, subject, reply_to, to_name', 'required', 'on' => 'step-setup, step-confirm'),
            array('send_at', 'required', 'on' => 'step-confirm'),

            array('list_id, segment_id, group_id', 'numerical', 'integerOnly' => true),
            array('list_id', 'exist', 'className' => 'Lists'),
            array('segment_id', 'exist', 'className' => 'ListSegment'),
            array('group_id', 'exist', 'className' => 'CampaignGroup'),
            array('name, to_name, subject', 'length', 'max'=>255),
            array('from_name, from_email, reply_to', 'length', 'max'=>100),
            array('from_email, reply_to', '_validateEMailWithTag'),
            array('type', 'in', 'range' => array_keys($this->getTypesList())),
            array('send_at', 'date', 'format' => 'yyyy-mm-dd hh:mm:ss', 'on' => 'step-confirm'),

            // The following rule is used by search().
            array('campaign_uid, customer_id, group_id, list_id, name, type, status', 'safe', 'on'=>'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'group'                 => array(self::BELONGS_TO, 'CampaignGroup', 'group_id'),
            'list'                  => array(self::BELONGS_TO, 'Lists', 'list_id'),
            'segment'               => array(self::BELONGS_TO, 'ListSegment', 'segment_id'),
            'bounceLogs'            => array(self::HAS_MANY, 'CampaignBounceLog', 'campaign_id'),
            'deliveryLogs'          => array(self::HAS_MANY, 'CampaignDeliveryLog', 'campaign_id'),
            'deliveryLogsArchive'   => array(self::HAS_MANY, 'CampaignDeliveryLogArchive', 'campaign_id'),
            'forwardFriends'        => array(self::HAS_MANY, 'CampaignForwardFriend', 'campaign_id'),
            'openActionListFields'  => array(self::HAS_MANY, 'CampaignOpenActionListField', 'campaign_id'),
            'openActionSubscribers' => array(self::HAS_MANY, 'CampaignOpenActionSubscriber', 'campaign_id'),
            'urlActionListFields'   => array(self::HAS_MANY, 'CampaignTemplateUrlActionListField', 'campaign_id'),
            'urlActionSubscribers'  => array(self::HAS_MANY, 'CampaignTemplateUrlActionSubscriber', 'campaign_id'),
            'temporarySources'      => array(self::HAS_MANY, 'CampaignTemporarySource', 'campaign_id'),
            'customer'              => array(self::BELONGS_TO, 'Customer', 'customer_id'),
            'links'                 => array(self::HAS_MANY, 'CampaignLink', 'campaign_id'),
            'option'                => array(self::HAS_ONE, 'CampaignOption', 'campaign_id'),
            'template'              => array(self::HAS_ONE, 'CampaignTemplate', 'campaign_id'),
            'templates'             => array(self::HAS_MANY, 'CampaignTemplate', 'campaign_id'),
            'attachments'           => array(self::HAS_MANY, 'CampaignAttachment', 'campaign_id'),
            'deliveryServers'       => array(self::MANY_MANY, 'DeliveryServer', '{{campaign_to_delivery_server}}(campaign_id, server_id)'),
            'trackOpens'            => array(self::HAS_MANY, 'CampaignTrackOpen', 'campaign_id'),
            'trackUnsubscribes'     => array(self::HAS_MANY, 'CampaignTrackUnsubscribe', 'campaign_id'),
            'urls'                  => array(self::HAS_MANY, 'CampaignUrl', 'campaign_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'campaign_id'           => Yii::t('campaigns', 'Campaign'),
            'campaign_uid'          => Yii::t('campaigns', 'Unique ID'),
            'customer_id'           => Yii::t('campaigns', 'Customer'),
            'list_id'               => Yii::t('campaigns', 'List'),
            'segment_id'            => Yii::t('campaigns', 'Segment'),
            'group_id'              => Yii::t('campaigns', 'Group'),
            'name'                  => Yii::t('campaigns', 'Campaign name'),
            'type'                  => Yii::t('campaigns', 'Type'),
            'from_name'             => Yii::t('campaigns', 'From name'),
            'from_email'            => Yii::t('campaigns', 'From email'),
            'to_name'               => Yii::t('campaigns', 'To name'),
            'reply_to'              => Yii::t('campaigns', 'Reply to'),
            'confirmed_reply_to'    => Yii::t('campaigns', 'Confirmed reply to'),
            'confirmation_code'     => Yii::t('campaigns', 'Confirmation code'),
            'subject'               => Yii::t('campaigns', 'Subject'),
            'send_at'               => Yii::t('campaigns', 'Send at'),
            'started_at'            => Yii::t('campaigns', 'Started at'),
            'finished_at'           => Yii::t('campaigns', 'Finished at'),

            'lastOpen'              => Yii::t('campaigns', 'Last open'),
            'totalDeliveryTime'     => Yii::t('campaigns', 'Total delivery time'),
            'webVersion'            => Yii::t('campaigns', 'Web version'),
        );

        if ($this->getIsAutoresponder()) {
            $labels['send_at'] = Yii::t('campaigns', 'Activate at');
        }

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
        $criteria->with = array();

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

        // since 1.3.5
        if (!empty($this->list_id)) {
            if (is_numeric($this->list_id)) {
                $criteria->compare('t.list_id', $this->list_id);
            } else {
                $criteria->with['list'] = array(
                    'condition' => 'list.name LIKE :listName',
                    'params'    => array(':listName' => '%' . $this->list_id)
                );
            }
        }

        $criteria->compare('t.segment_id', $this->segment_id);
        $criteria->compare('t.group_id', $this->group_id);
        $criteria->compare('t.campaign_uid', $this->campaign_uid);
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.type', $this->type);

        if (empty($this->status)) {
            $criteria->compare('t.status', '<>' . self::STATUS_PENDING_DELETE);
        } else {
            $criteria->compare('t.status', $this->status);
        }

        $criteria->order = 't.campaign_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.campaign_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Campaign the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeValidate()
    {
        if ($this->scenario == 'step-setup') {
            $tags = $this->getSubjectToNameAvailableTags();
            $hasErrors = false;
            $attributes = array('subject', 'to_name');

            foreach ($attributes as $attribute) {
                $content = CHtml::decode($this->$attribute);
                $missingTags = array();
                foreach ($tags as $tag) {
                    if (!isset($tag['tag']) || !isset($tag['required']) || !$tag['required']) {
                        continue;
                    }
                    if (!isset($tag['pattern']) && strpos($content, $tag['tag']) === false) {
                        $missingTags[] = $tag['tag'];
                    } elseif (isset($tag['pattern']) && !preg_match($tag['pattern'], $content)) {
                        $missingTags[] = $tag['tag'];
                    }
                }
                if (!empty($missingTags)) {
                    $missingTags = array_unique($missingTags);
                    $this->addError($attribute, Yii::t('campaigns', 'The following tags are required but were not found in your content: {tags}', array(
                        '{tags}' => implode(', ', $missingTags),
                    )));
                    $hasErrors = true;
                }
            }

            if ($hasErrors) {
                return false;
            }
        }

        return parent::beforeValidate();
    }

    protected function beforeSave()
    {
        if (empty($this->campaign_uid)) {
            $this->campaign_uid = $this->generateUid();
        }

        if ($this->status == self::STATUS_PENDING_SENDING) {
            $this->started_at = new CDbExpression('NOW()');
        } elseif ($this->status == self::STATUS_SENT) {
            $this->finished_at = new CDbExpression('NOW()');
        } elseif ($this->status == self::STATUS_DRAFT) {
            $this->started_at  = null;
            $this->finished_at = null;
        }

        return parent::beforeSave();
    }

    protected function beforeDelete()
    {
        // since 1.3.5
        if (!$this->getPendingDelete()) {
            $this->name  .= '(' . Yii::t('app', 'Deleted') . ')';
            $this->status = self::STATUS_PENDING_DELETE;
            $this->save(false);
            return false;
        }

        // only drafts are allowed to be deleted
        if (!$this->getRemovable()) {
            return false;
        }

        return parent::beforeDelete();
    }

    protected function afterFind()
    {
        if ($this->send_at == '0000-00-00 00:00:00') {
            $this->send_at = null;
        }

        if (empty($this->send_at)) {
            $this->send_at = date('Y-m-d H:i:s');
        }

        parent::afterFind();
    }

    protected function afterConstruct()
    {
        if (empty($this->send_at)) {
            $this->send_at = date('Y-m-d H:i:s');
        }
        parent::afterConstruct();
    }

    protected function afterDelete()
    {
        // clean campaign files, if any.
        $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');
        $campaignFiles = $storagePath.'/cmp'.$this->campaign_uid;
        if (file_exists($campaignFiles) && is_dir($campaignFiles)) {
            FileSystemHelper::deleteDirectoryContents($campaignFiles, true, 1);
        }

        // attachments
        $attachmentsPath = Yii::getPathOfAlias('root.frontend.assets.files.campaign-attachments.'.$this->campaign_uid);
        if (file_exists($attachmentsPath) && is_dir($attachmentsPath)) {
            FileSystemHelper::deleteDirectoryContents($attachmentsPath, true, 1);
        }

        parent::afterDelete();
    }

    public function findByUid($campaign_uid)
    {
        return $this->findByAttributes(array(
            'campaign_uid' => $campaign_uid,
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

    public function attributeHelpTexts()
    {
        $texts = array(
            'type'          => Yii::t('campaigns', 'The type of this campaign, either a regular one or autoresponder'),
            'name'          => Yii::t('campaigns', 'The campaign name, this is used internally so that you can differentiate between the campaigns. Will not be shown to subscribers.'),
            'list_id'       => Yii::t('campaigns', 'The list from where we will pick the subscribers. We will send to all the confirmed subscribers if no segment is specified.'),
            'segment_id'    => Yii::t('campaigns', 'Narrow the subscribers to a specific defined segment. If you have no segment so far, feel free to go ahead and create one to be used here.'),
            'send_at'       => Yii::t('campaigns', 'Uses your account timezone in "{format}" format.', array('{format}' => $this->getDateTimeFormat() )),

            'from_name' => Yii::t('campaigns', 'This is the name of the "From" header used in campaigns, use a name that your subscribers will easily recognize, like your website name or company name.'),
            'from_email'=> Yii::t('campaigns', 'This is the email of the "From" header used in campaigns, use a name that your subscribers will easily recognize, containing your website name or company name.'),
            'subject'   => Yii::t('campaigns', 'Campaign subject. There are a few available tags for customization.'),
            'reply_to'  => Yii::t('campaigns', 'If a subscriber replies to your campaign, this is the email address where the reply will go.'),
            'to_name'   => Yii::t('campaigns', 'This is the "To" header shown in the campaign. There are a few available tags for customization.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'name'          => Yii::t('campaigns', 'I.E: Weekly digest subscribers'),
            'list_id'       => null,
            'segment_id'    => null,
            'send_at'       => $this->getDateTimeFormat(),

            'from_name' => Yii::t('campaigns', 'My Super Company INC'),
            'from_email'=> Yii::t('campaigns', 'newsletter@my-super-company.com'),
            'subject'   => Yii::t('campaigns', 'Weekly newsletter'),
            'reply_to'  => Yii::t('campaigns', 'reply@my-super-company.com'),
            'to_name'   => Yii::t('campaigns', '[FNAME] [LNAME]'),
        );

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function pause()
    {
        if (!$this->getCanBePaused()) {
            return false;
        }
        return $this->saveStatus(self::STATUS_PAUSED);
    }

    public function unpause()
    {
        if (!$this->getIsPaused()) {
            return false;
        }
        return $this->saveStatus(self::STATUS_SENDING);
    }

    public function pauseUnpause()
    {
        if ($this->getIsPaused()) {
            $this->unpause();
        } elseif ($this->getCanBePaused()) {
            $this->pause();
        }
        return $this;
    }

    public function block($reason = null)
    {
        if (!$this->getCanBeBlocked()) {
            return false;
        }
        $saved = $this->saveStatus(self::STATUS_BLOCKED);
        if ($saved && !empty($this->option)) {
            $this->option->setBlockedReason($reason);
        }
        if ($saved) {
            $this->sendNotificationsForBlockedCampaign();
        }
        return $saved;
    }

    public function unblock()
    {
        if (!$this->getIsBlocked()) {
            return false;
        }

        if (!empty($this->option)) {
            $this->option->setBlockedReason(null);
        }

        return $this->saveStatus(self::STATUS_SENDING);
    }

    public function blockUnblock()
    {
        if ($this->getIsBlocked()) {
            $this->unblock();
        } elseif ($this->getCanBeBlocked()) {
            $this->block();
        }
        return $this;
    }

    public function copy()
    {
        $copied = false;

        if ($this->isNewRecord) {
            // 1.3.6.2
            Yii::app()->hooks->doAction('copy_campaign', new CAttributeCollection(array(
                'campaign' => $this,
                'copied'   => $copied,
            )));
            return $copied;
        }

        $transaction = Yii::app()->db->beginTransaction();

        try {

            $campaign = clone $this;
            $campaign->isNewRecord  = true;
            $campaign->campaign_id  = null;
            $campaign->campaign_uid = $campaign->generateUid();
            $campaign->send_at      = null;
            $campaign->date_added   = new CDbExpression('NOW()');
            $campaign->last_updated = new CDbExpression('NOW()');
            $campaign->started_at   = null;
            $campaign->finished_at  = null;
            $campaign->status       = self::STATUS_DRAFT;
            $campaign->delivery_logs_archived = self::TEXT_NO;

            if (preg_match('/\#(\d+)$/', $campaign->name, $matches)) {
                $counter = (int)$matches[1];
                $counter++;
                $campaign->name = preg_replace('/\#(\d+)$/', '#' . $counter, $campaign->name);
            } else {
                $campaign->name .= ' #1';
            }

            if (!$campaign->save(false)) {
                throw new CException($campaign->shortErrors->getAllAsString());
            }

            // campaign options
            $option = !empty($this->option) ? clone $this->option : new CampaignOption();
            $option->isNewRecord    = true;
            $option->campaign_id    = $campaign->campaign_id;
            $option->giveup_counter = 0;
            
            if (!$option->save()) {
                throw new Exception($option->shortErrors->getAllAsString());
            }

            // actions on open
            $openActions = CampaignOpenActionSubscriber::model()->findAllByAttributes(array(
                'campaign_id'   => $this->campaign_id,
            ));
            foreach ($openActions as $action) {
                $action = clone $action;
                $action->isNewRecord  = true;
                $action->action_id    = null;
                $action->campaign_id  = $campaign->campaign_id;
                $action->date_added   = new CDbExpression('NOW()');
                $action->last_updated = new CDbExpression('NOW()');
                $action->save(false);
            }

            // actions on open against custom fields
            $openListFieldActions = CampaignOpenActionListField::model()->findAllByAttributes(array(
                'campaign_id'   => $this->campaign_id,
            ));
            foreach ($openListFieldActions as $action) {
                $action = clone $action;
                $action->isNewRecord  = true;
                $action->action_id    = null;
                $action->campaign_id  = $campaign->campaign_id;
                $action->date_added   = new CDbExpression('NOW()');
                $action->last_updated = new CDbExpression('NOW()');
                $action->save(false);
            }

            // template related
            $templateClickActions = array();
            $templateClickActionsListFields = array();
            if (!empty($this->template)) {
                $templateClickActions = CampaignTemplateUrlActionSubscriber::model()->findAllByAttributes(array(
                    'campaign_id' => $this->campaign_id,
                    'template_id' => $this->template->template_id,
                ));
                $templateClickActionsListFields = CampaignTemplateUrlActionListField::model()->findAllByAttributes(array(
                    'campaign_id' => $this->campaign_id,
                    'template_id' => $this->template->template_id,
                ));
                $template = clone $this->template;
            } else {
                $template = new CampaignTemplate();
            }

            // campaign template
            $template->isNewRecord = true;
            $template->template_id = null;
            $template->campaign_id = $campaign->campaign_id;

            $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');
            $oldCampaignFilesPath = $storagePath.'/cmp'.$this->campaign_uid;
            $newCampaignFilesPath = $storagePath.'/cmp'.$campaign->campaign_uid;
            $canSaveTemplate = true;

            if (file_exists($oldCampaignFilesPath) && is_dir($oldCampaignFilesPath)) {
                if (!@mkdir($newCampaignFilesPath, 0777)) {
                    $canSaveTemplate = false;
                }

                if ($canSaveTemplate && !FileSystemHelper::copyOnlyDirectoryContents($oldCampaignFilesPath, $newCampaignFilesPath)) {
                    $canSaveTemplate = false;
                }
            }

            if (!$canSaveTemplate) {
                throw new Exception(Yii::t('campaigns', 'Campaign template could not be saved while copying campaign!'));
            }

            $template->content = str_replace('cmp'.$this->campaign_uid, 'cmp'.$campaign->campaign_uid, $template->content);

            if (!$template->save(false)) {
                if (file_exists($newCampaignFilesPath) && is_dir($newCampaignFilesPath)) {
                    FileSystemHelper::deleteDirectoryContents($newCampaignFilesPath, true, 1);
                }
                throw new Exception($template->shortErrors->getAllAsString());
            }

            // template click actions
            if (!empty($templateClickActions) || !empty($templateClickActionsListFields)) {
                $templateUrls = $template->getContentUrls();
                foreach ($templateClickActions as $clickAction) {
                    if (!in_array($clickAction->url, $templateUrls)) {
                        continue;
                    }
                    $clickAction = clone $clickAction;
                    $clickAction->isNewRecord  = true;
                    $clickAction->url_id       = null;
                    $clickAction->campaign_id  = $campaign->campaign_id;
                    $clickAction->template_id  = $template->template_id;
                    $clickAction->date_added   = new CDbExpression('NOW()');
                    $clickAction->last_updated = new CDbExpression('NOW()');
                    $clickAction->save(false);
                }
                foreach ($templateClickActionsListFields as $clickAction) {
                    if (!in_array($clickAction->url, $templateUrls)) {
                        continue;
                    }
                    $clickAction = clone $clickAction;
                    $clickAction->isNewRecord  = true;
                    $clickAction->url_id       = null;
                    $clickAction->campaign_id  = $campaign->campaign_id;
                    $clickAction->template_id  = $template->template_id;
                    $clickAction->date_added   = new CDbExpression('NOW()');
                    $clickAction->last_updated = new CDbExpression('NOW()');
                    $clickAction->save(false);
                }
            }

            // delivery servers - start
            if (!empty($this->deliveryServers)) {
                foreach ($this->deliveryServers as $server) {
                    $campaignToServer = new CampaignToDeliveryServer();
                    $campaignToServer->server_id    = $server->server_id;
                    $campaignToServer->campaign_id  = $campaign->campaign_id;
                    $campaignToServer->save();
                }
            }
            // delivery servers - end

            // attachments - start
            if (!empty($this->attachments)) {
                $copiedAttachments = false;
                $attachmentsPath = Yii::getPathOfAlias('root.frontend.assets.files.campaign-attachments');
                $oldAttachments  = $attachmentsPath . '/' . $this->campaign_uid;
                $newAttachments  = $attachmentsPath . '/' . $campaign->campaign_uid;
                if (file_exists($oldAttachments) && is_dir($oldAttachments) && @mkdir($newAttachments, 0777, true)) {
                    $copiedAttachments = FileSystemHelper::copyOnlyDirectoryContents($oldAttachments, $newAttachments);
                }
                if ($copiedAttachments) {
                    foreach ($this->attachments as $attachment) {
                        $attachment = clone $attachment;
                        $attachment->isNewRecord    = true;
                        $attachment->attachment_id  = null;
                        $attachment->campaign_id    = $campaign->campaign_id;
                        $attachment->date_added     = null;
                        $attachment->last_updated   = null;
                        $attachment->save(false);
                    }
                }
            }
            // attachments - end

            $transaction->commit();
            $copied = $campaign;
            
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            $transaction->rollBack();
        }

        // 1.3.6.2
        Yii::app()->hooks->doAction('copy_campaign', new CAttributeCollection(array(
            'campaign' => $this,
            'copied'   => $copied,
        )));
        
        return $copied;
    }

    public function getListsDropDownArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));
        $criteria->order = 'list_id DESC';
        $lists = Lists::model()->findAll($criteria);

        foreach ($lists as $list) {
            $_options[$list->list_id] = $list->name . ' ('. Yii::t('campaigns', '{subscribersCount} subscribers', array(
                '{subscribersCount}' => Yii::app()->format->formatNumber($list->confirmedSubscribersCount)
            )).')';
        }

        return $_options;
    }

    public function getSegmentsDropDownArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }

        if (empty($this->list_id)) {
            return $_options = array();
        }

        $segments = ListSegment::model()->findAllByListId($this->list_id);
        foreach ($segments as $segment) {
            $_options[$segment->segment_id] = $segment->name . ' ('. Yii::t('campaigns', '{subscribersCount} subscribers', array(
                '{subscribersCount}' => Yii::app()->format->formatNumber($segment->countSubscribers())
            )).')';
        }

        return $_options;
    }

    public function getGroupsDropDownArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->order = 'group_id DESC';
        $models = CampaignGroup::model()->findAll($criteria);

        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->compare('group_id', (int)$model->group_id);
            $criteria->addNotInCondition('status', array(self::STATUS_PENDING_DELETE));
            $count = Campaign::model()->count($criteria);

            $_options[$model->group_id] = $model->name . ' ('. Yii::t('campaigns', '{campaignsCount} campaigns', array(
                '{campaignsCount}' => Yii::app()->format->formatNumber($count)
            )).')';
        }

        return $_options;
    }

    public function getRemovable()
    {
        $removable = in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_PENDING_SENDING, self::STATUS_PAUSED, self::STATUS_PENDING_DELETE, self::STATUS_BLOCKED));
        if ($removable && !empty($this->customer_id) && !empty($this->customer)) {
            $removable = $this->customer->getGroupOption('campaigns.can_delete_own_campaigns', 'yes') == 'yes';
        }
        return $removable;
    }

    /**
     * Paused status introduced in 1.3.4.2
     */
    public function getEditable()
    {
        return in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_PENDING_SENDING, self::STATUS_PAUSED));
    }

    public function getAccessOverview()
    {
        return !in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_PENDING_SENDING));
    }

    public function getCanBePaused()
    {
        return in_array($this->status, array(self::STATUS_SENDING, self::STATUS_PROCESSING, self::STATUS_PENDING_SENDING));
    }

    public function getIsPaused()
    {
        return in_array($this->status, array(self::STATUS_PAUSED));
    }

    public function getCanBeResumed()
    {
        return in_array($this->status, array(self::STATUS_PROCESSING));
    }

    public function getCanBeMarkedAsSent()
    {
        return in_array($this->status, array(self::STATUS_BLOCKED, self::STATUS_PROCESSING, self::STATUS_PAUSED, self::STATUS_PENDING_SENDING));
    }

    public function getCanBeBlocked()
    {
        return !in_array($this->status, array(self::STATUS_BLOCKED, self::STATUS_DRAFT, self::STATUS_SENT));
    }

    public function getIsProcessing()
    {
        return $this->status == self::STATUS_PROCESSING;
    }

    public function getIsSending()
    {
        return $this->status == self::STATUS_SENDING;
    }

    public function getIsPendingSending()
    {
        return $this->status == self::STATUS_PENDING_SENDING;
    }

    public function getIsDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

    public function getPendingDelete()
    {
        return $this->status == self::STATUS_PENDING_DELETE;
    }

    public function getIsBlocked()
    {
        return $this->status == self::STATUS_BLOCKED;
    }

    public function getBlockedReasons()
    {
        if (!$this->getIsBlocked()) {
            return array();
        }
        $reasons = array();
        if ($bw = $this->getSubjectBlacklistWords()) {
            $reasons[] = Yii::t('campaigns', 'Campaign subject matched following blacklisted words: {words}', array(
                '{words}' => implode(', ', $bw),
            ));
        }
        if ($bw = $this->getContentBlacklistWords()) {
            $reasons[] = Yii::t('campaigns', 'Campaign content matched following blacklisted words: {words}', array(
                '{words}' => implode(', ', $bw),
            ));
        }
        if (empty($reasons)) {
            $reasons[] = Yii::t('campaigns', 'The campaign has been blocked by an administrator!');
        }
        return $reasons;
    }

    public function getSendAt()
    {
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->send_at);
    }

    public function getStartedAt()
    {
        if (empty($this->started_at) || $this->started_at == '0000-00-00 00:00:00') {
            return null;
        }
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->started_at);
    }

    public function getFinishedAt()
    {
        if (empty($this->finished_at) || $this->finished_at == '0000-00-00 00:00:00') {
            return null;
        }
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->finished_at);
    }

    public function getLastOpen()
    {
        if ($this->isNewRecord) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'date_added';
        $criteria->compare('campaign_id', $this->campaign_id);
        $criteria->order = 'id DESC';
        $criteria->limit = 1;

        $lastOpen = CampaignTrackOpen::model()->find($criteria);
        if (empty($lastOpen)) {
            return;
        }

        return $lastOpen->dateAdded;
    }

    public function getUid()
    {
        return $this->campaign_uid;
    }

    public function getSubjectToNameAvailableTags()
    {
        $tags = array(
            array('tag' => '[LIST_NAME]', 'required' => false),
            array('tag' => '[RANDOM_CONTENT:a|b|c]', 'required' => false),
        );

        if (!empty($this->list)) {
            $fields = $this->list->fields;
            foreach ($fields as $field) {
                $tags[] = array('tag' => '['.$field->tag.']', 'required' => false);
            }
        }

        return $tags;
    }

    public function getDateTimeFormat()
    {
        $locale = Yii::app()->locale;
        $searchReplace = array(
            '{1}' => $locale->getDateFormat('short'),
            '{0}' => $locale->getTimeFormat('short'),
        );

        return str_replace(array_keys($searchReplace), array_values($searchReplace), $locale->getDateTimeFormat());
    }

    public function getStatusesList()
    {
        return array(
            self::STATUS_DRAFT              => ucfirst(Yii::t('campaigns', self::STATUS_DRAFT)),
            self::STATUS_PENDING_SENDING    => ucfirst(Yii::t('campaigns', self::STATUS_PENDING_SENDING)),
            self::STATUS_SENDING            => ucfirst(Yii::t('campaigns', self::STATUS_SENDING)),
            self::STATUS_SENT               => ucfirst(Yii::t('campaigns', self::STATUS_SENT)),
            self::STATUS_PROCESSING         => ucfirst(Yii::t('campaigns', self::STATUS_PROCESSING)),
            self::STATUS_PAUSED             => ucfirst(Yii::t('campaigns', self::STATUS_PAUSED)),
            self::STATUS_BLOCKED             => ucfirst(Yii::t('campaigns', self::STATUS_BLOCKED)),
            //self::STATUS_PENDING_DELETE     => ucfirst(Yii::t('campaigns', self::STATUS_PENDING_DELETE)),
        );
    }

    public function getStatusWithStats()
    {
        static $_status = array();
        if (!$this->isNewRecord && isset($_status[$this->campaign_id])) {
            return $_status[$this->campaign_id];
        }

        if ($this->isNewRecord || in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_PENDING_SENDING))) {
            return $_status[$this->campaign_id] = $this->getStatusName();
        }

        // added in 1.3.4.7 to avoid confusion
        if ($this->status == self::STATUS_SENT) {
            return $_status[$this->campaign_id] = sprintf('%s (%d%s)', $this->getStatusName(), 100, '%');
        }

        $count = 0;
        $sent  = CampaignDeliveryLog::model()->countByAttributes(array(
            'campaign_id' => (int)$this->campaign_id,
        ));

        if (!$this->getIsAutoresponder() || $this->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_SUBSCRIBE) {
            if (!empty($this->segment_id)) {
                $count = $this->segment->countSubscribers();
            } else {
                $count = $this->list->confirmedSubscribersCount;
            }
        } elseif ($this->getIsAutoresponder() && $this->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN && ! empty($this->option->autoresponder_open_campaign_id)) {
            $count = $this->option->autoresponderOpenCampaign->getUniqueOpensCount();
        }

        $formatter = Yii::app()->format;

        // added in 1.3.4.7 to avoid confusion
        if ($sent > $count) {
            $sent = $count;
        }
        // added in 1.3.5
        if ($sent == 0 || $count == 0) {
            return $_status[$this->campaign_id] = sprintf('%s (%d%s)', $this->getStatusName(), 0, '%');
        }
        $percent = $formatter->formatNumber(($sent * 100) / $count);
        return $_status[$this->campaign_id] = sprintf('%s (%d%s)', $this->getStatusName(), $percent, '%');
    }

    public function getTypesList()
    {
        return array(
            self::TYPE_REGULAR          => ucfirst(Yii::t('campaigns', self::TYPE_REGULAR)),
            self::TYPE_AUTORESPONDER    => ucfirst(Yii::t('campaigns', self::TYPE_AUTORESPONDER))
        );
    }

    public function getTypeName($type = null)
    {
        if (empty($type)) {
            $type = $this->type;
        }
        $types = $this->getTypesList();
        return isset($types[$type]) ? $types[$type] : $type;
    }

    public function getTypeNameDetails($type = null, $lineBreak = '<br />')
    {
        $type = $this->getTypeName($type);
        if (!$this->isAutoresponder) {
            return $type;
        }
        if (empty($this->option)) {
            return $type;
        }

        $timeUnit = $this->option->autoresponder_time_unit;
        if ($this->option->autoresponder_time_value > 1) {
            $timeUnit .= 's';
        }
        $timeUnit = Yii::t('app', $timeUnit);
        return sprintf('%s%s(%d %s/%s)', $type, $lineBreak, $this->option->autoresponder_time_value, $timeUnit, $this->option->getAutoresponderEventName());
    }

    public function getIsAutoresponder()
    {
        return $this->type == self::TYPE_AUTORESPONDER;
    }

    public function getIsRegular()
    {
        return $this->type == self::TYPE_REGULAR;
    }

    public function getUniqueOpensCount($format = false)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(DISTINCT(subscriber_id))';
        $criteria->compare('campaign_id', (int)$this->campaign_id);
        // $criteria->group = 'subscriber_id';
        $count = CampaignTrackOpen::model()->count($criteria);
        if ($format) {
            $count = Yii::app()->format->formatNumber($count);
        }
        return $count;
    }

    public function getListSegmentName()
    {
        $names  = array();
        if (isset($names[$this->campaign_id])) {
            return $names[$this->campaign_id];
        }

        $name   = array();
        $count  = Yii::app()->format->formatNumber(!empty($this->segment_id) ? $this->segment->countSubscribers() : $this->list->confirmedSubscribersCount);
        $name[] = (empty($this->segment_id) ? $this->list->name : $this->list->name . '/' . $this->segment->name) . '('.Yii::t('campaigns', '{n} confirmed subscribers', $count).')';

        if (!empty($this->temporarySources)) {
            foreach ($this->temporarySources as $source) {
                $count  = Yii::app()->format->formatNumber(!empty($source->segment_id) ? $source->segment->countSubscribers() : $source->list->confirmedSubscribersCount);
                $name[] = (empty($source->segment_id) ? $source->list->name : $source->list->name . '/' . $source->segment->name) . '('.Yii::t('campaigns', '{n}  confirmed subscribers', $count).')';
            }
        }
        return $names[$this->campaign_id] = implode(', ', $name);
    }

    public function countForwards()
    {
        return CampaignForwardFriend::model()->countByAttributes(array('campaign_id' => $this->campaign_id));
    }

    public function countAbuseReports()
    {
        return CampaignAbuseReport::model()->countByAttributes(array('campaign_id' => $this->campaign_id));
    }

    public function saveStatus($status = null)
    {
        if (empty($this->campaign_id)) {
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
        if ($this->status == self::STATUS_SENT) {
            $this->finished_at = $attributes['finished_at'] = new CDbExpression('NOW()');
        }
        return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
    }

    public function saveSendAt($sendAt = null)
    {
        if (empty($this->campaign_id)) {
            return false;
        }
        if ($sendAt) {
            $this->send_at = $sendAt;
        }
        $attributes = array('send_at' => $this->send_at);
        return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
    }

    public function getIsRecurring()
    {
        return MW_COMPOSER_SUPPORT && !empty($this->campaign_id) && $this->getIsRegular() && !empty($this->option) && !empty($this->option->cronjob) && !empty($this->option->cronjob_enabled) ? $this->option->cronjob : false;
    }

    public function tryReschedule($copy = false)
    {
        if (!($cronjob = $this->getIsRecurring())) {
            return false;
        }

        $campaign = null;
        if ($copy && !($campaign = $this->copy())) {
            return false;
        }
        if (!$campaign) {
            $campaign = $this;
        }
        $ok = false;
        try {
            // to avoid parsing errors on php < 5.3
            $className = '\DateTime';
            $currentTime = new $className($this->send_at);

            $className = '\DateTimeZone';
            $currentTime->setTimezone(new $className(Yii::app()->timeZone));

            $cron = call_user_func(array('\Cron\CronExpression', 'factory'), $this->option->cronjob);
            $campaign->send_at = $cron->getNextRunDate($currentTime)->format('Y-m-d H:i:s');
            $campaign->status  = self::STATUS_SENDING;
            $attributes = array(
                'send_at' => $campaign->send_at,
                'status'  => $campaign->status,
            );
            $ok = Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'campaign_id = :cid', array(':cid' => (int)$campaign->campaign_id));
            $this->send_at = $campaign->send_at;
            $this->status  = $campaign->status;
        } catch (Exception $e) {
            $ok = false;
        }
        return $ok;
    }

    public function getDeliveryLogsArchived()
    {
        return $this->delivery_logs_archived == self::TEXT_YES;
    }

    public function getTotalDeliveryTime()
    {
        if (empty($this->send_at) || empty($this->finished_at) || ($sendAt = strtotime($this->send_at)) == ($finishedAt = strtotime($this->finished_at))) {
            return;
        }

        return DateTimeHelper::timespan($sendAt, $finishedAt);
    }

    public function countSubscribers(CDbCriteria $mergeCriteria = null)
    {
        if (!empty($this->segment_id)) {
            $count = $this->countSubscribersByListSegment($mergeCriteria);
        } else {
            $count = $this->countSubscribersByList($mergeCriteria);
        }

        return $count;
    }

    public function findSubscribers($offset = 0, $limit = 100, CDbCriteria $mergeCriteria = null)
    {
        if (!empty($this->segment_id)) {
            $subscribers = $this->findSubscribersByListSegment($offset, $limit, $mergeCriteria);
        } else {
            $subscribers = $this->findSubscribersByList($offset, $limit, $mergeCriteria);
        }
        return $subscribers;
    }

    public function getBulkActionsList()
    {
        return array(
            self::BULK_ACTION_DELETE         => Yii::t('app', 'Delete'),
            self::BULK_ACTION_COPY           => Yii::t('app', 'Copy'),
            self::BULK_ACTION_PAUSE_UNPAUSE  => Yii::t('app', 'Pause/Unpause'),
            self::BULK_ACTION_MARK_SENT      => Yii::t('app', 'Mark as sent'),
        );
    }

    public function getSubjectBlacklistWords()
    {
        if (empty($this->subject)) {
            return array();
        }

        static $subjectWords;
        if ($subjectWords !== null && empty($subjectWords)) {
            return array();
        }
        if ($subjectWords === null || !is_array($subjectWords)) {
            $subjectWords = array();
            if (Yii::app()->options->get('system.campaign.blacklist_words.enabled', 'no') == 'yes') {
                $subjectWords = Yii::app()->options->get('system.campaign.blacklist_words.subject', '');
                $subjectWords = explode(',', $subjectWords);
                $subjectWords = array_map('trim', $subjectWords);
            }
        }
        if (empty($subjectWords)) {
            return array();
        }
        $found = array();
        foreach ($subjectWords as $word) {
            if (stripos($this->subject, $word) !== false) {
                $found[] = $word;
            }
        }
        return $found;
    }

    public function getContentBlacklistWords()
    {
        if (empty($this->template)) {
            return array();
        }

        static $contentWords;
        if ($contentWords !== null && empty($contentWords)) {
            return array();
        }
        if ($contentWords === null || !is_array($contentWords)) {
            $contentWords = array();
            if (Yii::app()->options->get('system.campaign.blacklist_words.enabled', 'no') == 'yes') {
                $contentWords = Yii::app()->options->get('system.campaign.blacklist_words.content', '');
                $contentWords = explode(',', $contentWords);
                $contentWords = array_map('trim', $contentWords);
            }
        }
        if (empty($contentWords)) {
            return array();
        }
        $found   = array();
        $content = strip_tags($this->template->content);
        foreach ($contentWords as $word) {
            if (stripos($content, $word) !== false) {
                $found[] = $word;
            }
        }
        if (empty($found) && !empty($this->template->plain_text)) {
            $content = $this->template->plain_text;
            foreach ($contentWords as $word) {
                if (stripos($content, $word) !== false) {
                    $found[] = $word;
                }
            }
        }
        return $found;
    }

    public function sendNotificationsForBlockedCampaign()
    {
        if (!$this->getIsBlocked()) {
            return false;
        }

        $options = Yii::app()->options;
        if ($options->get('system.campaign.blacklist_words.enabled', 'no') != 'yes') {
            return false;
        }

        $emails = $options->get('system.campaign.blacklist_words.notifications_to', '');
        if (empty($emails)) {
            return false;
        }
        $emails = explode(",", $emails);
        $emails = array_map('trim', $emails);
        $emails = array_unique($emails);
        if (empty($emails)) {
            return false;
        }

        if (empty($this->option) || empty($this->option->blocked_reason)) {
            return false;
        }

        $emailTemplate = $options->get('system.email_templates.common');

        $message   = array();
        $message[] = Yii::t('campaigns', 'A campaign sending has been blocked because of the following reasons:');
        $reasons = explode("|", $this->option->blocked_reason);
        foreach ($reasons as $reason) {
            $message[] = Yii::t('campaigns', $reason);
        }
        $message[] = CHtml::link(Yii::t('campaigns', 'Click here to see it and take action!'), $options->get('system.urls.backend_absolute_url') . 'campaigns/' . $this->campaign_uid . '/overview');

        $emailBody = implode("<br />", $message);
        $emailTemplate = str_replace('[CONTENT]', $emailBody, $emailTemplate);

        foreach ($emails as $email) {
            $_email = new TransactionalEmail();
            $_email->sendDirectly = false;
            $_email->to_name      = $email;
            $_email->to_email     = $email;
            $_email->from_name    = $options->get('system.common.site_name', 'Marketing website');
            $_email->subject      = Yii::t('campaigns', 'A campaign has been blocked!');
            $_email->body         = $emailTemplate;
            $_email->save();
        }

        return true;
    }


    protected function countSubscribersByListSegment(CDbCriteria $mergeCriteria = null)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', (int)$this->list_id);
        $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);

        if ($this->getIsAutoresponder() && !$this->addAutoresponderCriteria($criteria)) {
            return 0;
        }

        if ($this->getIsRegular() && !$this->addRegularCriteria($criteria)) {
            return 0;
        }

        if ($mergeCriteria) {
            $criteria->mergeWith($mergeCriteria);
        }

        return $this->segment->countSubscribers($criteria);
    }

    protected function findSubscribersByListSegment($offset = 0, $limit = 100, CDbCriteria $mergeCriteria = null)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.list_id, t.subscriber_id, t.subscriber_uid, t.email, t.ip_address, t.source, t.date_added';
        $criteria->compare('t.list_id', (int)$this->list_id);
        $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);

        if ($this->getIsAutoresponder() && !$this->addAutoresponderCriteria($criteria)) {
            return array();
        }

        if ($this->getIsRegular() && !$this->addRegularCriteria($criteria)) {
            return array();
        }

        if ($mergeCriteria) {
            $criteria->mergeWith($mergeCriteria);
        }

        return $this->segment->findSubscribers($offset, $limit, $criteria);
    }

    protected function countSubscribersByList(CDbCriteria $mergeCriteria = null)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', (int)$this->list_id);
        $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);

        if ($this->getIsAutoresponder() && !$this->addAutoresponderCriteria($criteria)) {
            return 0;
        }

        if ($this->getIsRegular() && !$this->addRegularCriteria($criteria)) {
            return 0;
        }

        if ($mergeCriteria) {
            $criteria->mergeWith($mergeCriteria);
        }

        //$criteria->select = 'COUNT(DISTINCT t.subscriber_id) as counter';
        //$criteria->group  = '';

        return ListSubscriber::model()->count($criteria);
    }

    protected function findSubscribersByList($offset = 0, $limit = 100, CDbCriteria $mergeCriteria = null)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.list_id, t.subscriber_id, t.subscriber_uid, t.email, t.ip_address, t.source, t.date_added';
        $criteria->compare('t.list_id', (int)$this->list_id);
        $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);
        $criteria->offset = $offset;
        $criteria->limit  = $limit;

        if ($this->getIsAutoresponder() && !$this->addAutoresponderCriteria($criteria)) {
            return array();
        }

        if ($this->getIsRegular() && !$this->addRegularCriteria($criteria)) {
            return array();
        }

        if ($mergeCriteria) {
            $criteria->mergeWith($mergeCriteria);
        }

        //$criteria->group = 't.subscriber_id';

        return ListSubscriber::model()->findAll($criteria);
    }

    protected function addRegularCriteria(CDbCriteria $criteria)
    {
        if (!empty($this->option->regular_open_unopen_campaign_id) && !empty($this->option->regular_open_unopen_action)) {
            if ($this->option->regular_open_unopen_action == CampaignOption::REGULAR_OPEN_UNOPEN_ACTION_OPEN) {
                $criteria->with['trackOpens'] = array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'on'        => 'trackOpens.campaign_id = :opensUnopensCid',
                    'condition' => 'trackOpens.id = (SELECT id FROM {{campaign_track_open}} WHERE campaign_id = :opensUnopensCid AND subscriber_id = t.subscriber_id ORDER BY id ASC LIMIT 1)',
                    'params'    => array(':opensUnopensCid' => $this->option->regular_open_unopen_campaign_id),
                );
                return true;
            } elseif ($this->option->regular_open_unopen_action == CampaignOption::REGULAR_OPEN_UNOPEN_ACTION_UNOPEN) {
                $criteria->with['trackOpens'] = array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'LEFT OUTER JOIN',
                    'on'        => 'trackOpens.campaign_id = :opensUnopensCid',
                    'condition' => 'trackOpens.subscriber_id IS NULL',
                    'params'    => array(':opensUnopensCid' => $this->option->regular_open_unopen_campaign_id),
                );
                return true;
            }
            return false;
        }

        return true;
    }

    protected function addAutoresponderCriteria(CDbCriteria $criteria)
    {
        if ($this->option->autoresponder_include_imported == CampaignOption::TEXT_NO) {
            $criteria->addCondition('t.source != :src');
            $criteria->params[':src'] = ListSubscriber::SOURCE_IMPORT;
        }

        $timeValue = (int)$this->option->autoresponder_time_value;
        $timeUnit  = strtoupper($this->option->autoresponder_time_unit);

        if ($this->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_SUBSCRIBE) {
            $criteria->addCondition('t.date_added >= :cdate');
            $criteria->params[':cdate'] = $this->date_added;
            $criteria->addCondition(sprintf('DATE_ADD(t.date_added, INTERVAL %d %s) <= NOW()', $timeValue, $timeUnit));
        } elseif ($this->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN) {
            if (empty($this->option->autoresponder_open_campaign_id)) {
                return false;
            }
            if (!is_array($criteria->with)) {
                $criteria->with = array();
            }
            $criteria->with['trackOpens'] = array(
                'select'    => false,
                'joinType'  => 'INNER JOIN',
                'together'  => true,
                'on'        => 'trackOpens.campaign_id = :tocid',
                'condition' => 'trackOpens.id = (SELECT id FROM {{campaign_track_open}} WHERE campaign_id = :tocid AND subscriber_id = t.subscriber_id ORDER BY id ASC LIMIT 1)',
                'params'    => array(':tocid' => $this->option->autoresponder_open_campaign_id),
            );
            $criteria->addCondition(sprintf('DATE_ADD(trackOpens.date_added, INTERVAL %d %s) <= NOW()', $timeValue, $timeUnit));
        } else {
            return false;
        }

        return true;
    }

    public function _validateEMailWithTag($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        if (strpos($this->$attribute, '[') !== false && strpos($this->$attribute, ']') !== false) {
            if (empty($this->list_id)) {
                return $this->addError($attribute, Yii::t('campaigns', 'Please associate a list first!'));
            }
            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id' => $this->list_id,
                'status'  => ListSubscriber::STATUS_CONFIRMED,
            ));
            if (empty($subscriber)) {
                return $this->addError($attribute, Yii::t('campaigns', 'You need at least one subscriber in your selected list!'));
            }
            $tags = CampaignHelper::getSubscriberFieldsSearchReplace($this->$attribute, $this, $subscriber);
            $attr = str_replace(array_keys($tags), array_values($tags), $this->$attribute);
            if (!FilterVarHelper::email($attr)) {
                return $this->addError($attribute, Yii::t('campaigns', '{attr} is not a valid email address (even after the tag has been parsed).', array('{attr}' => $this->getAttributeLabel($attribute))));
            }
            return;
        }
        if (FilterVarHelper::email($this->$attribute)) {
            return;
        }
        $this->addError($attribute, Yii::t('campaigns', '{attr} is not a valid email address.', array('{attr}' => $this->getAttributeLabel($attribute))));
    }

    /**
     * @param int $by
     * @return int
     */
    public function updateSendingGiveupCounter($by = 1)
    {
        return $this->option->updateSendingGiveupCounter($by);
    }

    /**
     * @return int
     */
    public function getSendingGiveupsCount()
    {
        return CampaignDeliveryLog::model()->countByAttributes(array(
            'campaign_id' => (int)$this->campaign_id,
            'status'      => CampaignDeliveryLog::STATUS_GIVEUP,
        ));
    }

    /**
     * @return int
     */
    public function resetSendingGiveups()
    {
        return CampaignDeliveryLog::model()->deleteAllByAttributes(array(
            'campaign_id' => (int)$this->campaign_id,
            'status'      => CampaignDeliveryLog::STATUS_GIVEUP,
        ));
    }
}
