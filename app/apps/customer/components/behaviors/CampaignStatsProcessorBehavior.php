<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignStatsProcessor
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.3
 */
 
class CampaignStatsProcessorBehavior extends CBehavior 
{
    // whether this campaign can be processed
    protected $canBeProcessed = false;
    
    // how many confirmed subscribers has the campaign list
    protected $_listSubscribers = 0;
    
    // how many confirmed subscribers in this segment
    protected $_segmentSubscribers = 0;
    
    // how many subscribers to be processed
    protected $_subscribersCount = 0;
    
    // how many processed so far
    protected $_processedCount = 0;
    
    // how many delivered successfully
    protected $_deliverySuccessCount = 0;
    
    // how many delivered with errors
    protected $_deliveryErrorCount = 0;
    
    // the delivery success rate
    protected $_deliverySuccessRate = 0;
    
    // the delivery error rate
    protected $_deliveryErrorRate = 0;
    
    // the opens count
    protected $_opensCount = 0;
    
    // unique opens count
    protected $_uniqueOpensCount = 0;
    
    // opens rate
    protected $_opensRate = 0;
    
    // unique opens rate
    protected $_uniqueOpensRate = 0;
    
    // bounce count
    protected $_bouncesCount = 0;
    
    // hard bounces count
    protected $_hardBouncesCount = 0;
    
    // soft bounces count
    protected $_softBouncesCount = 0;
    
    // bounce rate
    protected $_bouncesRate = 0;
    
    // hard bounce rate
    protected $_hardBouncesRate = 0;
    
    // soft bounce rate
    protected $_softBouncesRate = 0;
    
    // how many unsubscribed
    protected $_unsubscribesCount = 0;
    
    // the unsubscribe rate
    protected $_unsubscribesRate = 0;
    
    // estimate completition time
    protected $_completitionDuration;
    
    // completition rate
    protected $_completitionRate;
    
    // all campaign urls
    protected $_trackingUrlsCount;
    
    // clicks count
    protected $_clicksCount;
    
    // unique clicks count
    protected $_uniqueClicksCount;
    
    // clicks rate
    protected $_clicksRate;
    
    // unique cliks rate
    protected $_uniqueClicksRate;
    
    // the industry for customer
    protected $_industry;
    
    // the industry processed count
    protected $_industryProcessedCount;
    
    // the industry opens rate
    protected $_industryOpensRate;
    
    // the industry clicks rate
    protected $_industryClicksRate;
    
    public function attach($owner)
    {
        if (!($owner instanceof Campaign)) {
            throw new CException(Yii::t('customers', 'The {className} behavior can only be attach to a Campaign model', array(
                '{className}' => get_class($this),
            )));
        }
        
        parent::attach($owner);
        
        $this->canBeProcessed = !$this->getOwner()->isNewRecord && !in_array($this->getOwner()->status, array(Campaign::STATUS_DRAFT));
        if ($this->canBeProcessed) {
            $this->processStats();
        }
    }
    
    public function getListSubscribers($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_listSubscribers);
        }
        return $this->_listSubscribers;
    }
    
    public function getSegmentSubscribers($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_segmentSubscribers);
        }
        return $this->_segmentSubscribers;
    }
    
    public function getSubscribersCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_subscribersCount);
        }
        return $this->_subscribersCount;
    }
    
    public function getProcessedCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_processedCount);
        }
        return $this->_processedCount;
    }
    
    public function getDeliverySuccessCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_deliverySuccessCount);
        }
        return $this->_deliverySuccessCount;
    }
    
    public function getDeliverySuccessRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_deliverySuccessRate);
        }
        return $this->_deliverySuccessRate;
    }
    
    public function getDeliveryErrorCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_deliveryErrorCount);
        }
        return $this->_deliveryErrorCount;
    }
    
    public function getDeliveryErrorRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_deliveryErrorRate);
        }
        return $this->_deliveryErrorRate;
    }
    
    public function getOpensCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_opensCount);
        }
        return $this->_opensCount;
    }
    
    public function getUniqueOpensCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_uniqueOpensCount);
        }
        return $this->_uniqueOpensCount;
    }
    
    public function getOpensRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_opensRate);
        }
        return $this->_opensRate;
    }
    
    public function getUniqueOpensRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_uniqueOpensRate);
        }
        return $this->_uniqueOpensRate;
    }
    
    public function getClicksToOpensRate($formatNumber = false)
    {
        $rate   = null;
        $clicks = $this->getUniqueClicksCount();
        $opens  = $this->getUniqueOpensCount();
        
        if ($clicks == 0 || $opens == 0) {
            return $rate = 0;
        }
        
        if ($rate === null) {
            $rate = ($this->getUniqueClicksCount() / $this->getUniqueOpensCount()) * 100;
        }
        
        if ($formatNumber) {
            return $this->format($rate); 
        }
        
        return $rate;
    }

    public function getOpensToClicksRate($formatNumber = false)
    {
        $rate   = null;
        $clicks = $this->getUniqueClicksCount();
        $opens  = $this->getUniqueOpensCount();

        if ($clicks == 0 || $opens == 0) {
            return $rate = 0;
        }

        if ($rate === null) {
            $rate = ($this->getUniqueOpensCount() / $this->getUniqueClicksCount()) * 100;
        }

        if ($formatNumber) {
            return $this->format($rate);
        }

        return $rate;
    }
    
    public function getBouncesCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_bouncesCount);
        }
        return $this->_bouncesCount;
    }
    
    public function getBouncesRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_bouncesRate);
        }
        return $this->_bouncesRate;
    }
    
    public function getHardBouncesCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_hardBouncesCount);
        }
        return $this->_hardBouncesCount;
    }
    
    public function getHardBouncesRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_hardBouncesRate);
        }
        return $this->_hardBouncesRate;
    }
    
    public function getSoftBouncesCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_softBouncesCount);
        }
        return $this->_softBouncesCount;
    }
    
    public function getSoftBouncesRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_softBouncesRate);
        }
        return $this->_softBouncesRate;
    }
    
    public function getUnsubscribesCount($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_unsubscribesCount);
        }
        return $this->_unsubscribesCount;
    }
    
    public function getUnsubscribesRate($formatNumber = false)
    {
        if ($formatNumber) {
            return $this->format($this->_unsubscribesRate);
        }
        return $this->_unsubscribesRate;
    }
    
    public function getCompletitionDuration()
    {
        return null;
        /*
        $cmp = $this->getOwner();
        
        if (!$cmp->isRegular || $this->_completitionDuration !== null || !$this->canBeProcessed || $cmp->status == Campaign::STATUS_SENT) {
            return $this->_completitionDuration;
        }
        
        // based on last hour
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $criteria->addCondition('date_added >= DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        
        $cdlModel = $cmp->deliveryLogsArchived ? CampaignDeliveryLogArchive::model() : CampaignDeliveryLog::model();
        $count    = $cdlModel->count($criteria);
        
        if ($count > 0) {
            $count              = $count / 3600;
            $estimateSeconds    = floor(($this->_subscribersCount - $this->_processedCount) / $count);
            $now                = time();
            $this->_completitionDuration = DateTimeHelper::timespan($now - $estimateSeconds, $now);
        }

        return $this->_completitionDuration;
        */
    }
    
    public function getCompletitionRate($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if (!$cmp->isRegular || $this->_completitionRate !== null || !$this->canBeProcessed || $cmp->status == Campaign::STATUS_SENT) {
            if ($formatNumber) {
                return $this->format($this->_completitionRate);
            }
            return $this->_completitionRate;
        }
        
        $this->_completitionRate = ($this->_processedCount / $this->_subscribersCount) * 100;
        
        // how can this happen?
        if ($this->_completitionRate > 100) {
            $this->_completitionRate = 100;
        }
        
        if ($formatNumber) {
            return $this->format($this->_completitionRate);
        }
        
        return $this->_completitionRate; 
    }
    
    public function getTrackingUrlsCount($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if ($this->_trackingUrlsCount !== null || !$this->canBeProcessed || empty($cmp->option) || $cmp->option->url_tracking != CampaignOption::TEXT_YES) {
            if ($formatNumber) {
                return $this->format($this->_trackingUrlsCount);
            }
            return $this->_trackingUrlsCount;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $this->_trackingUrlsCount = (int)CampaignUrl::model()->count($criteria);
        
        if ($formatNumber) {
            return $this->format($this->_trackingUrlsCount);
        }
        
        return $this->_trackingUrlsCount; 
    }
    
    public function getClicksCount($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if ($this->_clicksCount !== null || !$this->canBeProcessed || empty($cmp->option) || $cmp->option->url_tracking != CampaignOption::TEXT_YES) {
            if ($formatNumber) {
                return $this->format($this->_clicksCount);
            }
            return $this->_clicksCount;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 't.url_id';
        $criteria->with = array(
            'url' => array(
                'select'    => false,
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'condition' => 'url.campaign_id = :cid',
                'params'    => array(':cid' => $this->getOwner()->campaign_id)
            ),
        );
        
        $this->_clicksCount = (int)CampaignTrackUrl::model()->count($criteria);
        
        if ($formatNumber) {
            return $this->format($this->_clicksCount);
        }
        
        return $this->_clicksCount;
    }
    
    public function getClicksRate($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if ($this->_clicksRate !== null || !$this->canBeProcessed || empty($cmp->option) || $cmp->option->url_tracking != CampaignOption::TEXT_YES) {
            if ($formatNumber) {
                return $this->format($this->_clicksRate);
            }
            return $this->_clicksRate;
        }

        $pcnt = $this->getDeliverySuccessCount() - $this->getBouncesCount();
        $pcnt = $pcnt < 1 ? 1 : $pcnt;
        
        if ($this->getClicksCount() > 0) {
            $this->_clicksRate = ($this->getClicksCount() / $pcnt) * 100;
            if ($this->_clicksRate > 100) {
                $this->_clicksRate = 100;
            }
        }
        
        if ($formatNumber) {
            return $this->format($this->_clicksRate);
        }
        return $this->_clicksRate;
    }
    
    public function getUniqueClicksCount($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if ($this->_uniqueClicksCount !== null || !$this->canBeProcessed || empty($cmp->option) || $cmp->option->url_tracking != CampaignOption::TEXT_YES) {
            if ($formatNumber) {
                return $this->format($this->_uniqueClicksCount);
            }
            return $this->_uniqueClicksCount;
        }
        
        $criteria = new CDbCriteria();
        $criteria->select = 't.url_id';
        $criteria->with = array(
            'url' => array(
                'select'    => false,
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'condition' => 'url.campaign_id = :cid',
                'params'    => array(':cid' => $cmp->campaign_id)
            ),
        );
        $criteria->group = 't.subscriber_id';
        
        $this->_uniqueClicksCount = (int)CampaignTrackUrl::model()->count($criteria);
        
        if ($formatNumber) {
            return $this->format($this->_uniqueClicksCount);
        }
        
        return $this->_uniqueClicksCount;
    }
    
    public function getUniqueClicksRate($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if ($this->_uniqueClicksRate !== null || !$this->canBeProcessed || empty($cmp->option) || $cmp->option->url_tracking != CampaignOption::TEXT_YES) {
            if ($formatNumber) {
                return $this->format($this->_uniqueClicksRate);
            }
            return $this->_uniqueClicksRate;
        }

        $pcnt = $this->getDeliverySuccessCount() - $this->getBouncesCount();
        $pcnt = $pcnt < 1 ? 1 : $pcnt;
        
        if ($this->getUniqueClicksCount() > 0) {
            $this->_uniqueClicksRate = ($this->getUniqueClicksCount() / $pcnt) * 100;  
            if ($this->_uniqueClicksRate > 100) {
                $this->_uniqueClicksRate = 100;
            }
        }
        
        if ($formatNumber) {
            return $this->format($this->_uniqueClicksRate);
        }
        return $this->_uniqueClicksRate;
    }
    
    public function getClicksThroughRate($formatNumber = false)
    {
        $ctr = 0;
        if ($this->getUniqueClicksCount() > 0 && $this->getDeliverySuccessCount() > 0) {
            $ctr = ($this->getUniqueClicksCount() / $this->getDeliverySuccessCount()) * 100;
            // $ctr = ($this->getUniqueClicksCount() / $this->getOpensCount()) * 100;
        }
        if ($formatNumber) {
            return $this->format($ctr);
        }
        return $ctr;
    }
    
    public function getIndustry()
    {
        return $this->_industry;
    }
    
    public function getIndustryOpensRate($formatNumber = false)
    {
        $cmp = $this->getOwner();
        
        if (!empty($cmp->customer) && !empty($cmp->customer->company) && !empty($cmp->customer->company->type_id)) {
            $this->_industry = $cmp->customer->company->type;
            $cacheKey = sha1(__FILE__ . __METHOD__ . $this->_industry->type_id);
        }
        
        if (!$this->_industry) {
            $this->_industryOpensRate = 0;
        }
        
        if ($this->_industry && $this->_industryOpensRate === null && ($rate = Yii::app()->cache->get($cacheKey)) !== false) {
            $this->_industryOpensRate = $rate;
        }
        
        if ($this->_industryOpensRate !== null) {
            if ($formatNumber) {
                return $this->format($this->_industryOpensRate);
            }
            return $this->_industryOpensRate;
        }
        
        if ($this->_industryProcessedCount === null) {
            $cdlModel = $cmp->deliveryLogsArchived ? CampaignDeliveryLogArchive::model() : CampaignDeliveryLog::model();
            $command = Yii::app()->getDb()->createCommand('
                SELECT COUNT(*) AS counter FROM `{{customer_company}}` cc 
                INNER JOIN `{{campaign}}` c ON cc.customer_id = c.customer_id
                INNER JOIN `'.$cdlModel->tableName().'` cdl ON cdl.campaign_id = c.campaign_id
                WHERE cc.type_id = :type_id AND c.status = :status AND cdl.status = :cdl_status
            ');
            $row = $command->queryRow(true, array(
                ':type_id'    => $this->_industry->type_id, 
                ':status'     => Campaign::STATUS_SENT,
                ':cdl_status' => CampaignDeliveryLog::STATUS_SUCCESS,
            ));
            $this->_industryProcessedCount = $row['counter'];
        }

        $command = Yii::app()->getDb()->createCommand('
            SELECT COUNT(DISTINCT cto.campaign_id, cto.subscriber_id) AS counter FROM `{{customer_company}}` cc 
            INNER JOIN `{{campaign}}` c ON cc.customer_id = c.customer_id
            INNER JOIN `{{campaign_track_open}}` cto ON cto.campaign_id = c.campaign_id
            WHERE cc.type_id = :type_id AND c.status = :status
        ');
        $row = $command->queryRow(true, array(
            ':type_id' => $this->_industry->type_id, 
            ':status'  => Campaign::STATUS_SENT
        ));
        $industryOpensCount = $row['counter'];
        
        $this->_industryOpensRate = 0;
        if ($industryOpensCount > 0) {
            $this->_industryOpensRate = ($industryOpensCount * 100) / $this->_industryProcessedCount;
        }
        
        Yii::app()->cache->set($cacheKey, $this->_industryOpensRate, 3600 * 6);
        
        if ($formatNumber) {
            return $this->format($this->_industryOpensRate);
        }
        return $this->_industryOpensRate;
    }
    
    public function getIndustryClicksRate($formatNumber = false)
    {
        $cmp = $this->getOwner();

        if (!empty($cmp->customer) && !empty($cmp->customer->company) && !empty($cmp->customer->company->type_id)) {
            $this->_industry = $cmp->customer->company->type;
            $cacheKey = sha1(__FILE__ . __METHOD__ . $this->_industry->type_id);
        }
        
        if (!$this->_industry) {
            $this->_industryClicksRate = 0;
        }
        
        if ($this->_industry && $this->_industryOpensRate === null && ($rate = Yii::app()->cache->get($cacheKey)) !== false) {
            $this->_industryClicksRate = $rate;
        }
        
        if ($this->_industryClicksRate !== null) {
            if ($formatNumber) {
                return $this->format($this->_industryClicksRate);
            }
            return $this->_industryClicksRate;
        }
        
        // $trackingUrlsCount = (int)CampaignUrl::model()->count();
        
        if ($this->_industryProcessedCount === null) {
            $cdlModel = $cmp->deliveryLogsArchived ? CampaignDeliveryLogArchive::model() : CampaignDeliveryLog::model();
            $command = Yii::app()->getDb()->createCommand('
                SELECT COUNT(*) AS counter FROM `{{customer_company}}` cc 
                INNER JOIN `{{campaign}}` c ON cc.customer_id = c.customer_id
                INNER JOIN `'.$cdlModel->tableName().'` cdl ON cdl.campaign_id = c.campaign_id
                WHERE cc.type_id = :type_id AND c.status = :status AND cdl.status = :cdl_status
            ');
            $row = $command->queryRow(true, array(
                ':type_id'    => $this->_industry->type_id, 
                ':status'     => Campaign::STATUS_SENT,
                ':cdl_status' => CampaignDeliveryLog::STATUS_SUCCESS,
            ));
            $this->_industryProcessedCount = $row['counter'];
        }
        
        $command = Yii::app()->getDb()->createCommand('
            SELECT COUNT(DISTINCT cu.campaign_id, ctu.subscriber_id) AS counter FROM `{{customer_company}}` cc 
            INNER JOIN `{{campaign}}` c ON cc.customer_id = c.customer_id
            INNER JOIN `{{campaign_url}}` cu ON cu.campaign_id = c.campaign_id
            INNER JOIN `{{campaign_track_url}}` ctu ON ctu.url_id = cu.url_id
            WHERE cc.type_id = :type_id AND c.status = :status
        ');
        $row = $command->queryRow(true, array(
            ':type_id' => $this->_industry->type_id, 
            ':status'  => Campaign::STATUS_SENT
        ));
        $industryClicksCount = $row['counter'];

        $this->_industryClicksRate = 0;
        if ($industryClicksCount > 0) {
            // $this->_industryClicksRate = ($industryClicksCount * 100) / $trackingUrlsCount / $this->_industryProcessedCount; 
            $this->_industryClicksRate = ($industryClicksCount / $this->_industryProcessedCount) * 100;
            if ($this->_industryClicksRate > 100) {
                $this->_industryClicksRate = 100;
            } 
        }
        
        Yii::app()->cache->set($cacheKey, $this->_industryClicksRate, 3600 * 6);
        
        if ($formatNumber) {
            return $this->format($this->_industryClicksRate);
        }
        return $this->_industryClicksRate;
    }
    
    protected function processStats()
    {
        $cmp = $this->getOwner();
        
        if (!empty($cmp->list_id)) {
            $this->_listSubscribers = (int)$cmp->list->confirmedSubscribersCount;
        }
        
        if (!empty($cmp->segment_id)) {
            $this->_segmentSubscribers = (int)$cmp->segment->countSubscribers();
        }
        
        $this->_subscribersCount = $cmp->countSubscribers();

        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        
        $cdlModel = $cmp->deliveryLogsArchived ? CampaignDeliveryLogArchive::model() : CampaignDeliveryLog::model();
        $this->_processedCount = (int)$cdlModel->count($criteria);

        $this->setDeliveryStats()->setBouncesStats()->setUnsubscribesStats()->setOpensStats();
        
        return $this;
    }
    
    protected function setDeliveryStats()
    {
        $cmp = $this->getOwner();
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $criteria->compare('status', CampaignDeliveryLog::STATUS_SUCCESS);
        // since 1.3.6.1
        $criteria->compare('delivery_confirmed', CampaignDeliveryLog::TEXT_YES);
        
        $cdlModel = $cmp->deliveryLogsArchived ? CampaignDeliveryLogArchive::model() : CampaignDeliveryLog::model();
        $this->_deliverySuccessCount = (int)$cdlModel->count($criteria);
        
        if ($this->_deliverySuccessCount > $this->_processedCount) {
            $this->_deliverySuccessCount = $this->_processedCount;
        }

        // since 1.3.6.1
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $criteria->compare('status', CampaignDeliveryLog::STATUS_ERROR);
        $criteria->compare('delivery_confirmed', CampaignDeliveryLog::TEXT_YES);
        $this->_deliveryErrorCount = (int)$cdlModel->count($criteria);
        
        if ($this->_deliveryErrorCount < 0) {
            $this->_deliveryErrorCount = 0;
        }
        
        if ($this->_deliverySuccessCount > 0) {
            $this->_deliverySuccessRate = ($this->_deliverySuccessCount * 100) / $this->_processedCount;
        }
        
        if ($this->_deliveryErrorCount > 0) {
            $this->_deliveryErrorRate = ($this->_deliveryErrorCount * 100) / $this->_processedCount;
        }
        
        return $this;
    }
    
    protected function setOpensStats()
    { 
        $cmp = $this->getOwner();
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $this->_opensCount = (int)CampaignTrackOpen::model()->count($criteria);
        
        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(DISTINCT(subscriber_id))';
        $criteria->compare('campaign_id', $cmp->campaign_id);
        // $criteria->group = 'subscriber_id';
        
        $this->_uniqueOpensCount = (int)CampaignTrackOpen::model()->count($criteria);
        
        $pcnt = $this->getDeliverySuccessCount() - $this->getBouncesCount();
        $pcnt = $pcnt < 1 ? 1 : $pcnt;
        
        if ($this->_opensCount > 0) {
            $this->_opensRate = ($this->_opensCount * 100) / $pcnt;    
        }
        
        if ($this->_uniqueOpensCount > 0) {
            $this->_uniqueOpensRate = ($this->_uniqueOpensCount * 100) / $pcnt;    
        }
        
        return $this;
    }
    
    protected function setBouncesStats()
    {
        $cmp = $this->getOwner();
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $this->_bouncesCount = (int)CampaignBounceLog::model()->count($criteria);
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $criteria->compare('bounce_type', CampaignBounceLog::BOUNCE_HARD);
        $this->_hardBouncesCount = (int)CampaignBounceLog::model()->count($criteria);

        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $criteria->compare('bounce_type', CampaignBounceLog::BOUNCE_SOFT);
        $this->_softBouncesCount = (int)CampaignBounceLog::model()->count($criteria);

        if ($this->_bouncesCount > 0) {
            $this->_bouncesRate = ($this->_bouncesCount * 100) / $this->_processedCount;    
        }
        
        if ($this->_hardBouncesCount > 0) {
            $this->_hardBouncesRate = ($this->_hardBouncesCount * 100) / $this->_bouncesCount; // $this->_processedCount    
        }
        
        if ($this->_softBouncesCount > 0) {
            $this->_softBouncesRate = ($this->_softBouncesCount * 100) / $this->_bouncesCount; // $this->_processedCount   
        }
        
        return $this;
    }
    
    protected function setUnsubscribesStats()
    {
        $cmp = $this->getOwner();
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', $cmp->campaign_id);
        $this->_unsubscribesCount = (int)CampaignTrackUnsubscribe::model()->count($criteria);

        if ($this->_unsubscribesCount > 0) {
            $this->_unsubscribesRate = ($this->_unsubscribesCount * 100) / $this->_processedCount;    
        }

        return $this;
    }
    
    
    protected function format($number)
    {
        if (!is_numeric($number)) {
            $number = (int)$number;
        }
        return Yii::app()->format->formatNumber($number);
    }
}