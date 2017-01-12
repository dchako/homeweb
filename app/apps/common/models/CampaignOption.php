<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3
 */

/**
 * This is the model class for table "campaign_option".
 *
 * The followings are the available columns in table 'campaign_option':
 * @property integer $campaign_id
 * @property string $url_tracking
 * @property string $open_tracking
 * @property string $json_feed
 * @property string $xml_feed
 * @property string $embed_images
 * @property string $plain_text_email
 * @property string $autoresponder_event
 * @property string $autoresponder_time_unit
 * @property integer $autoresponder_time_value
 * @property string $autoresponder_include_imported
 * @property string $autoresponder_open_campaign_id;
 * @property string $email_stats
 * @property string $regular_open_unopen_action
 * @property integer $regular_open_unopen_campaign_id
 * @property string $cronjob
 * @property int $cronjob_enabled
 * @property string $blocked_reason
 * @property string $giveup_counter
 * @property string $max_send_count
 * @property string $max_send_count_random
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property Campaign $autoresponderOpenCampaign
 * @property Campaign $regularOpenUnopen
 */
class CampaignOption extends ActiveRecord
{
    const AUTORESPONDER_EVENT_AFTER_SUBSCRIBE = 'AFTER-SUBSCRIBE';

    const AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN = 'AFTER-CAMPAIGN-OPEN';

    const AUTORESPONDER_TIME_UNIT_MINUTE = 'minute';
    const AUTORESPONDER_TIME_UNIT_HOUR   = 'hour';
    const AUTORESPONDER_TIME_UNIT_DAY    = 'day';
    const AUTORESPONDER_TIME_UNIT_WEEK   = 'week';
    const AUTORESPONDER_TIME_UNIT_MONTH  = 'month';
    const AUTORESPONDER_TIME_UNIT_YEAR   = 'year';

    const REGULAR_OPEN_UNOPEN_ACTION_OPEN   = 'open';

    const REGULAR_OPEN_UNOPEN_ACTION_UNOPEN = 'unopen';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_option}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('url_tracking, open_tracking, json_feed, xml_feed, embed_images, plain_text_email', 'required'),
			array('url_tracking, open_tracking, json_feed, xml_feed, embed_images, plain_text_email', 'length', 'max' => 3),
            array('url_tracking, open_tracking, json_feed, xml_feed, embed_images, plain_text_email', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('email_stats', 'length', 'max' => 255),

            array('autoresponder_event, autoresponder_time_unit, autoresponder_time_value, autoresponder_include_imported', 'required', 'on' => 'step-confirm-ar'),
            array('autoresponder_event', 'in', 'range' => array_keys($this->getAutoresponderEvents())),
            array('autoresponder_time_unit', 'in', 'range' => array_keys($this->getAutoresponderTimeUnits())),
            array('autoresponder_time_value', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 365),
            array('autoresponder_include_imported', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('autoresponder_open_campaign_id, regular_open_unopen_campaign_id', 'exist', 'className' => 'Campaign', 'attributeName' => 'campaign_id'),
            array('regular_open_unopen_action', 'in', 'range' => array_keys($this->getRegularOpenUnopenActions())),
            
            // since 1.3.6.3
            array('max_send_count', 'length', 'max' => 11),
            array('max_send_count', 'numerical', 'integerOnly' => true, 'min' => 0),
            array('max_send_count_random', 'in', 'range' => array(0, 1)),
        );

        // since 1.3.5.3
        if (MW_COMPOSER_SUPPORT) {
            $rules[] = array('cronjob', 'validateCronExpression');
            $rules[] = array('cronjob_enabled', 'in', 'range' => array(0, 1));
        }

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaign'                   => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
            'autoresponderOpenCampaign'  => array(self::BELONGS_TO, 'Campaign', 'autoresponder_open_campaign_id'),
            'regularOpenUnopen'          => array(self::BELONGS_TO, 'Campaign', 'regular_open_unopen_campaign_id'),
		);

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'campaign_id'        => Yii::t('campaigns', 'Campaign'),
			'url_tracking'       => Yii::t('campaigns', 'Url tracking'),
            'open_tracking'      => Yii::t('campaigns', 'Open tracking'),
			'json_feed'          => Yii::t('campaigns', 'Json feed'),
			'xml_feed'           => Yii::t('campaigns', 'Xml feed'),
            'embed_images'       => Yii::t('campaigns', 'Embed images'),
            'plain_text_email'   => Yii::t('campaigns', 'Plain text email'),
            'email_stats'        => Yii::t('campaigns', 'Email stats'),

            'autoresponder_event'            => Yii::t('campaigns', 'Autoresponder event'),
            'autoresponder_time_unit'        => Yii::t('campaigns', 'Autoresponder time unit'),
            'autoresponder_time_value'       => Yii::t('campaigns', 'Autoresponder time value'),
            'autoresponder_include_imported' => Yii::t('campaigns', 'Include imported subscribers'),
            'autoresponder_open_campaign_id' => Yii::t('campaigns', 'Send when opening this campaign'),

            'regular_open_unopen_campaign_id' => Yii::t('campaigns', 'Following campaign'),
            'regular_open_unopen_action'      => Yii::t('campaigns', 'Subscriber that'),

            'cronjob'         => Yii::t('campaigns', 'Advanced recurring'),
            'cronjob_enabled' => Yii::t('campaigns', 'Enabled'),
            
            'max_send_count'         => Yii::t('campaigns', 'Max. subscribers'),
            'max_send_count_random'  => Yii::t('campaigns', 'Randomize subscribers'),
		);

        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        if ($this->autoresponder_event == self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN && empty($this->autoresponder_open_campaign_id)) {
            $this->addError('autoresponder_open_campaign_id', Yii::t('campaigns', 'Please select a campaign for this autoresponder!'));
        }
        parent::afterValidate();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignOption the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array
     */
    public function getYesNoOptionsArray()
    {
        return $this->getYesNoOptions();
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'url_tracking'      => Yii::t('campaigns', 'Whether to enable url tracking'),
            'open_tracking'     => Yii::t('campaigns', 'Whether to enable opens tracking'),
            'json_feed'         => Yii::t('campaigns', 'Whether your campaign will parse a {feedType} feed and dynamically insert content from the feed into template', array('{feedType}' => 'json')),
            'xml_feed'          => Yii::t('campaigns', 'Whether your campaign will parse a {feedType} feed and dynamically insert content from the feed into template', array('{feedType}' => 'xml(rss)')),
            'embed_images'      => Yii::t('campaigns', 'Whether to embed images in the template instead of loading them remotely'),
            'plain_text_email'  => Yii::t('campaigns', 'Whether to generate the plain text version of the campaign email based on your html email version'),
            'email_stats'       => Yii::t('campaigns', 'Where to send the campaign stats when it finish sending, separate multiple email addresses by a comma. Leave empty to not send the stats'),

            'autoresponder_event'            => Yii::t('campaigns', 'The event timing that will trigger this autoresponder'),
            'autoresponder_time_unit'        => Yii::t('campaigns', 'The time unit for this autoresponder'),
            'autoresponder_time_value'       => Yii::t('campaigns', 'Based on the time unit, how much to wait until this autoresponder gets sent. 0 means it will be sent immediatly after event'),
            'autoresponder_include_imported' => Yii::t('campaigns', 'Whether to include imported subscribers into this autoresponder'),
            'autoresponder_open_campaign_id' => Yii::t('campaigns', 'Which campaign must be opened in order to trigger this autoresponder'),

            'regular_open_unopen_campaign_id' => Yii::t('campaigns', 'Send to the subscribers that did opened or did not not opened this particular campaign'),
            'regular_open_unopen_action'      => Yii::t('campaigns', 'To which subscribers to send, the ones that opened a campaign or the ones that did not'),
            
            'max_send_count'        => Yii::t('campaigns', 'Whether to send only to this number of subscribers instead of sending to the whole list'),
            'max_send_count_random' => Yii::t('campaigns', 'If you limit the number of subscribers to which this campaigns goes to, enabling this option will pick them randomly from the list'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getAutoresponderEvents()
    {
        return array(
            self::AUTORESPONDER_EVENT_AFTER_SUBSCRIBE => Yii::t('campaigns', self::AUTORESPONDER_EVENT_AFTER_SUBSCRIBE),
            self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN => Yii::t('campaigns', self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN),
        );
    }

    /**
     * @return array
     */
    public function getAutoresponderTimeUnits()
    {
        return array(
            self::AUTORESPONDER_TIME_UNIT_MINUTE    => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_MINUTE)),
            self::AUTORESPONDER_TIME_UNIT_HOUR      => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_HOUR)),
            self::AUTORESPONDER_TIME_UNIT_DAY       => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_DAY)),
            self::AUTORESPONDER_TIME_UNIT_WEEK      => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_WEEK)),
            self::AUTORESPONDER_TIME_UNIT_MONTH     => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_MONTH)),
            self::AUTORESPONDER_TIME_UNIT_YEAR      => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_YEAR)),
        );
    }

    /**
     * @param null $name
     * @return mixed|null|string
     */
    public function getAutoresponderEventName($name = null)
    {
        if (empty($name)) {
            $name = $this->autoresponder_event;
        }
        $names = $this->getAutoresponderEvents();
        return isset($names[$name]) ? $names[$name] : $name;
    }

    /**
     * @return array
     */
    public function getRelatedCampaignsAsOptions()
    {
        if (empty($this->campaign_id) || empty($this->campaign) || empty($this->campaign->list)) {
            return array();
        }

        static $_openRelatedCampaigns;
        if ($_openRelatedCampaigns !== null) {
            return $_openRelatedCampaigns;
        }
        $_openRelatedCampaigns = array();

        $list = $this->campaign->list;

        $criteria = new CDbCriteria();
        $criteria->select = 't.campaign_id, t.name, t.type';
        $criteria->compare('t.list_id', $this->campaign->list_id);

        $criteria->addCondition('t.campaign_id != :cid');
        $criteria->params[':cid'] = $this->campaign_id;
        $criteria->order = 't.campaign_id DESC';
        $campaigns = Campaign::model()->findAll($criteria);

        foreach ($campaigns as $campaign) {
            if ($campaign->isAutoresponder) {
                $_openRelatedCampaigns[$campaign->campaign_id] = sprintf('%s (%s/%s)', $campaign->name, $campaign->getTypeName(), $campaign->option->getAutoresponderEventName());
            } else {
                $_openRelatedCampaigns[$campaign->campaign_id] = sprintf('%s (%s)', $campaign->name, $campaign->getTypeName());
            }
        }

        return $_openRelatedCampaigns;
    }

    /**
     * @return array
     */
    public function getAutoresponderOpenRelatedCampaigns()
    {
        if (empty($this->campaign_id) || empty($this->campaign) || empty($this->campaign->list)) {
            return array();
        }

        static $_openRelatedCampaigns;
        if ($_openRelatedCampaigns !== null) {
            return $_openRelatedCampaigns;
        }
        $_openRelatedCampaigns = array();

        $list = $this->campaign->list;

        $criteria = new CDbCriteria();
        $criteria->select = 't.campaign_id, t.name, t.type';
        $criteria->compare('t.list_id', $this->campaign->list_id);
        $criteria->compare('t.status', Campaign::STATUS_DRAFT);

        $criteria->addCondition('t.campaign_id != :cid');
        $criteria->params[':cid'] = $this->campaign_id;
        $criteria->order = 't.campaign_id DESC';
        $campaigns = Campaign::model()->findAll($criteria);

        foreach ($campaigns as $campaign) {
            if ($campaign->isAutoresponder) {
                $_openRelatedCampaigns[$campaign->campaign_id] = sprintf('%s (%s/%s)', $campaign->name, $campaign->getTypeName(), $campaign->option->getAutoresponderEventName());
            } else {
                $_openRelatedCampaigns[$campaign->campaign_id] = sprintf('%s (%s)', $campaign->name, $campaign->getTypeName());
            }
        }

        return $_openRelatedCampaigns;
    }

    /**
     * @return array
     */
    public function getRegularOpenUnopenActions()
    {
        return array(
            self::REGULAR_OPEN_UNOPEN_ACTION_OPEN   => ucfirst(Yii::t('campaigns', self::REGULAR_OPEN_UNOPEN_ACTION_OPEN)),
            self::REGULAR_OPEN_UNOPEN_ACTION_UNOPEN => ucfirst(Yii::t('campaigns', self::REGULAR_OPEN_UNOPEN_ACTION_UNOPEN)),
        );
    }

    /**
     * @return string|void
     */
    public function getRegularOpenUnopenDisplayText()
    {
        if (!$this->campaign->isRegular || empty($this->regular_open_unopen_action) || empty($this->regular_open_unopen_campaign_id)) {
            return;
        }
        if (empty($this->regularOpenUnopen)) {
            return;
        }
        $campaign = $this->regularOpenUnopen;
        $action   = Yii::t('campaigns', self::REGULAR_OPEN_UNOPEN_ACTION_OPEN);
        if ($this->regular_open_unopen_action == self::REGULAR_OPEN_UNOPEN_ACTION_UNOPEN) {
            $action = Yii::t('campaigns', 'not ' . self::REGULAR_OPEN_UNOPEN_ACTION_OPEN);
        }
        return Yii::t('campaigns', 'Subscribers that did {action} the campaign {campaign}', array(
            '{action}' => $action,
            '{campaign}' => CHtml::link($campaign->name, array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid)),
        ));
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateCronExpression($attribute, $params)
    {
        if ($this->hasErrors() || !$this->cronjob_enabled || empty($this->$attribute)) {
            return;
        }

        if (empty($this->campaign) || !$this->campaign->getIsRegular()) {
            $this->addError($attribute, Yii::t('campaigns', 'No valid assigned campaign!'));
            return;
        }

        try {
            $cron = call_user_func(array('\Cron\CronExpression', 'factory'), $this->$attribute);
            $cron = $cron->getNextRunDate()->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $this->addError($attribute, $e->getMessage());
        }
    }

    /**
     * @param $reason
     * @return bool
     */
    public function setBlockedReason($reason)
    {
        if (empty($this->campaign_id)) {
            return false;
        }
        if (is_array($reason)) {
            $reason = implode('|', $reason);
        }
        $reason = StringHelper::truncateLength($reason, 255);
        Yii::app()->getDb()->createCommand()->update($this->tableName(), array('blocked_reason' => $reason), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
        $this->blocked_reason = $reason;
        return true;
    }

    /**
     * @param int $by
     * @return int
     */
    public function updateSendingGiveupCounter($by = 1)
    {
        if ((int)$by <= 0) {
            $this->giveup_counter = 0;
            return self::updateAll(array(
                'giveup_counter' => 0
            ), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
        }
        
        $this->giveup_counter += (int)$by;
        return self::updateCounters(array(
            'giveup_counter' => (int)$by
        ), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
    }

    /**
     * @return bool
     */
    public function getCanSetMaxSendCount()
    {
        return $this->campaign->isRegular && !empty($this->max_send_count) && $this->max_send_count > 0;
    }

    /**
     * @return bool
     */
    public function getCanSetMaxSendCountRandom()
    {
        return $this->getCanSetMaxSendCount() && !empty($this->max_send_count_random) && $this->max_send_count_random == 'yes';
    }
}
