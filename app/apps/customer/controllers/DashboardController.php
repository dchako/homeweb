<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DashboardController
 *
 * Handles the actions for dashboard related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class DashboardController extends Controller
{
    public function init()
    {
        $apps = Yii::app()->apps;
        $this->getData('pageScripts')->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.min.js')),
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.resize.min.js')),
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.categories.min.js')),
            array('src' => AssetsUrl::js('dashboard.js'))
        ));
        parent::init();
    }

    /**
     * Display dashboard informations
     */
    public function actionIndex()
    {
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('dashboard', 'Dashboard'),
            'pageHeading'       => Yii::t('dashboard', 'Dashboard'),
            'pageBreadcrumbs'   => array(
                Yii::t('dashboard', 'Dashboard'),
            ),
        ));

        $canSegmentList = Yii::app()->customer->getModel()->getGroupOption('lists.can_segment_lists', 'yes') == 'yes';

        $this->render('index', compact('canSegmentList'));
    }

    /**
     * Ajax only action to get one year subscribers growth
     */
    public function actionGlance()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer    = Yii::app()->customer->getModel();
        $customer_id = (int)$customer->customer_id;

        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', $customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));

        $lists = Lists::model()->count($criteria);
        $lists = Yii::app()->format->formatNumber($lists);

        $templates = CustomerEmailTemplate::model()->countByAttributes(array('customer_id' => $customer_id));
        $templates = Yii::app()->format->formatNumber($templates);

        $apiKeys = CustomerApiKey::model()->countByAttributes(array('customer_id' => $customer_id));
        $apiKeys = Yii::app()->format->formatNumber($apiKeys);

        // count unique subscribers.
        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';
        $criteria->addInCondition('t.list_id', $customer->getAllListsIds());
        $subscribers = Yii::app()->format->formatNumber(ListSubscriber::model()->count($criteria));

        // count all subscribers.
        $criteria = new CDbCriteria();
        $criteria->addInCondition('t.list_id', $customer->getAllListsIds());
        $allSubscribers = Yii::app()->format->formatNumber(ListSubscriber::model()->count($criteria));

        // count campaigns
        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)$customer_id);
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));

        $campaigns = Campaign::model()->count($criteria);
        $campaigns = Yii::app()->format->formatNumber($campaigns);

        $segments = 0;
        if ($customer->getGroupOption('lists.can_segment_lists', 'yes') == 'yes') {
            // count segments
            $criteria = new CDbCriteria();
            $criteria->with = array(
                'list'  => array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                    'params'    => array(':customer_id' => $customer_id, ':st' => Lists::STATUS_PENDING_DELETE)
                ),
            );

            $segments = ListSegment::model()->count($criteria);
            $segments = Yii::app()->format->formatNumber($segments);
        }

        return $this->renderJson(compact(
            'lists',
            'templates',
            'apiKeys',
            'subscribers',
            'allSubscribers',
            'campaigns',
            'segments'
        ));
    }

    /**
     * Ajax only action to get activity messages
     */
    public function actionChatter()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer_id = (int)Yii::app()->customer->getId();

        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) as date_added';
        $criteria->condition = 't.customer_id = :customer_id AND DATE(t.date_added) >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        $criteria->group     = 'DATE(t.date_added)';
        $criteria->order     = 't.date_added DESC';
        $criteria->limit     = 7;
        $criteria->params    = array(':customer_id' => $customer_id);
        $models = CustomerActionLog::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $_item = array(
                'date'  => $model->dateTimeFormatter->formatLocalizedDate($model->date_added),
                'items' => array(),
            );
            $criteria = new CDbCriteria();
            $criteria->select    = 't.log_id, t.customer_id, t.message, t.date_added';
            $criteria->condition = 't.customer_id = :customer_id AND DATE(t.date_added) = :date';
            $criteria->params    = array(':customer_id' => $customer_id, ':date' => $model->date_added);
            $criteria->limit     = 10;
            $criteria->order     = 't.date_added DESC';
            $criteria->with      = array(
                'customer' => array(
                    'select'   => 'customer.customer_id, customer.first_name, customer.last_name',
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                ),
            );
            $records = CustomerActionLog::model()->findAll($criteria);
            foreach ($records as $record) {
                $customer = $record->customer;
                $time     = $record->dateTimeFormatter->formatLocalizedTime($record->date_added);
                $_item['items'][] = array(
                    'time'         => $time,
                    'customerName' => $customer->getFullName(),
                    'customerUrl'  => $this->createUrl('account/index'),
                    'message'      => $record->message,
                );
            }
            $items[] = $_item;
        }

        return $this->renderJson($items);
    }

    /**
     * Ajax only action to get subscribers growth
     */
    public function actionSubscribers_growth()
    {
        set_time_limit(0);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer_id = (int)Yii::app()->customer->getId();
        $cacheKey    = md5(__FILE__ . __METHOD__ . $customer_id);
        if ($items = Yii::app()->cache->get($cacheKey)) {
            return $this->renderJson(array(
                'label' => Yii::t('app', '{n} months growth', 3),
                'data'  => $items,
                'color' => '#3c8dbc'
            ));
        }

        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) AS date_added';
        $criteria->condition = 'DATE(t.date_added) >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 3 MONTH)), INTERVAL 1 DAY)';
        $criteria->group     = 'MONTH(t.date_added)';
        $criteria->order     = 't.date_added ASC';
        $criteria->limit     = 3;

        $criteria->with = array(
            'list' => array(
                'select'    => false,
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE)
            ),
        );

        $models = ListSubscriber::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->select    = 'COUNT(DISTINCT(email)) as counter';
            $criteria->condition = 'YEAR(t.date_added) = YEAR(:year) AND MONTH(t.date_added) = MONTH(:month)';
            $criteria->params = array(
                ':year'   => $model->date_added,
                ':month'  => $model->date_added,
            );
            $criteria->with = array(
                'list' => array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                    'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE)
                ),
            );

            $monthName  = date('M', strtotime($model->date_added));
            $count      = ListSubscriber::model()->count($criteria);
            $items[]    = array(Yii::t('app', $monthName) . ' ' . date('Y', strtotime($model->date_added)), $count);
        }

        Yii::app()->cache->set($cacheKey, $items, 3600);

        return $this->renderJson(array(
            'label' => Yii::t('app', '{n} months growth', 3),
            'data'  => $items,
            'color' => '#3c8dbc'
        ));
    }

    /**
     * Ajax only action to get lists growth
     */
    public function actionLists_growth()
    {
        set_time_limit(0);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer_id = (int)Yii::app()->customer->getId();
        $cacheKey    = md5(__FILE__ . __METHOD__ . $customer_id);
        if ($items = Yii::app()->cache->get($cacheKey)) {
            return $this->renderJson(array(
                'label' => Yii::t('app', '{n} months growth', 3),
                'data'  => $items,
                'color' => '#3c8dbc'
            ));
        }

        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) AS date_added';
        $criteria->condition = 't.customer_id = :customer_id AND t.status != :st AND DATE(t.date_added) >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 3 MONTH)), INTERVAL 1 DAY)';
        $criteria->group     = 'MONTH(t.date_added)';
        $criteria->order     = 't.date_added ASC';
        $criteria->limit     = 3;
        $criteria->params    = array(':customer_id' => $customer_id, ':st' => Lists::STATUS_PENDING_DELETE);

        $models = Lists::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->condition = 't.customer_id = :customer_id AND t.status != :st AND YEAR(t.date_added) = YEAR(:year) AND MONTH(t.date_added) = MONTH(:month)';
            $criteria->params = array(
                ':customer_id'  => $customer_id,
                ':st'           => Lists::STATUS_PENDING_DELETE,
                ':year'         => $model->date_added,
                ':month'        => $model->date_added,
            );

            $monthName  = date('M', strtotime($model->date_added));
            $count      = Lists::model()->count($criteria);
            $items[]    = array(Yii::t('app', $monthName) . ' ' . date('Y', strtotime($model->date_added)), $count);
        }

        Yii::app()->cache->set($cacheKey, $items, 3600);

        return $this->renderJson(array(
            'label' => Yii::t('app', '{n} months growth', 3),
            'data'  => $items,
            'color' => '#3c8dbc'
        ));
    }

    /**
     * Ajax only action to get campaigns growth
     */
    public function actionCampaigns_growth()
    {
        set_time_limit(0);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer_id = (int)Yii::app()->customer->getId();
        $cacheKey    = md5(__FILE__ . __METHOD__ . $customer_id);
        if ($items = Yii::app()->cache->get($cacheKey)) {
            return $this->renderJson(array(
                'label' => Yii::t('app', '{n} months growth', 3),
                'data'  => $items,
                'color' => '#3c8dbc'
            ));
        }

        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) AS date_added';
        $criteria->condition = 't.customer_id = :cid AND t.status != :st AND DATE(t.date_added) >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 3 MONTH)), INTERVAL 1 DAY)';
        $criteria->group     = 'MONTH(t.date_added)';
        $criteria->order     = 't.date_added ASC';
        $criteria->limit     = 3;
        $criteria->params    = array(
            ':cid' => (int)$customer_id,
            ':st'  => Campaign::STATUS_PENDING_DELETE
        );

        $models = Campaign::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->condition = 't.customer_id = :cid AND t.status != :st AND YEAR(t.date_added) = YEAR(:year) AND MONTH(t.date_added) = MONTH(:month)';
            $criteria->params = array(
                ':year'  => $model->date_added,
                ':month' => $model->date_added,
                ':cid'   => (int)$customer_id,
                ':st'    => Campaign::STATUS_PENDING_DELETE
            );

            $monthName  = date('M', strtotime($model->date_added));
            $count      = Campaign::model()->count($criteria);
            $items[]    = array(Yii::t('app', $monthName) . ' ' . date('Y', strtotime($model->date_added)), $count);
        }

        Yii::app()->cache->set($cacheKey, $items, 3600);

        return $this->renderJson(array(
            'label' => Yii::t('app', '{n} months growth', 3),
            'data'  => $items,
            'color' => '#3c8dbc'
        ));
    }

    /**
     * Ajax only action to get delivery/bounce growth
     */
    public function actionDelivery_bounce_growth()
    {
        set_time_limit(0);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer_id = (int)Yii::app()->customer->getId();
        $cacheKey    = md5(__FILE__ . __METHOD__ . $customer_id);
        if ($lines = Yii::app()->cache->get($cacheKey)) {
            return $this->renderJson($lines);
        }

        $lines = array();

        // Delivery
        $cdlModel = !CampaignDeliveryLog::getArchiveEnabled() ? CampaignDeliveryLog::model() : CampaignDeliveryLogArchive::model();
        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) AS date_added';
        $criteria->condition = 'DATE(t.date_added) >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 3 MONTH)), INTERVAL 1 DAY)';
        $criteria->group     = 'MONTH(t.date_added)';
        $criteria->order     = 't.date_added ASC';
        $criteria->limit     = 3;

        $criteria->with = array(
            'subscriber' => array(
                'select'    => false,
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'with'      => array(
                    'list'  => array(
                        'select'    => false,
                        'together'  => true,
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                        'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE),
                    ),
                )
            ),
        );
        $models = $cdlModel->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->condition = 'YEAR(t.date_added) = YEAR(:year) AND MONTH(t.date_added) = MONTH(:month)';
            $criteria->params = array(
                ':year'   => $model->date_added,
                ':month'  => $model->date_added,
            );
            $criteria->with = array(
                'subscriber' => array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'with'      => array(
                        'list'  => array(
                            'select'    => false,
                            'together'  => true,
                            'joinType'  => 'INNER JOIN',
                            'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                            'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE),
                        ),
                    )
                ),
            );
            $monthName  = date('M', strtotime($model->date_added));
            $count      = $cdlModel->count($criteria);
            $items[]    = array(Yii::t('app', $monthName) . ' ' . date('Y', strtotime($model->date_added)), $count);
        }

        $lines[] = array(
            'label' => Yii::t('dashboard', 'Delivery, {n} months growth', 3),
            'data'  => $items,
            'color' => '#3c8dbc'
        );

        // Bounces
        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) AS date_added';
        $criteria->condition = 'DATE(t.date_added) >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 3 MONTH)), INTERVAL 1 DAY)';
        $criteria->group     = 'MONTH(t.date_added)';
        $criteria->order     = 't.date_added ASC';
        $criteria->limit     = 3;

        $criteria->with = array(
            'subscriber' => array(
                'select'    => false,
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'with'      => array(
                    'list'  => array(
                        'select'    => false,
                        'together'  => true,
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                        'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE),
                    ),
                )
            ),
        );
        $models = CampaignBounceLog::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->condition = 'YEAR(t.date_added) = YEAR(:year) AND MONTH(t.date_added) = MONTH(:month)';
            $criteria->params = array(
                ':year'   => $model->date_added,
                ':month'  => $model->date_added,
            );
            $criteria->with = array(
                'subscriber' => array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'with'      => array(
                        'list'  => array(
                            'select'    => false,
                            'together'  => true,
                            'joinType'  => 'INNER JOIN',
                            'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                            'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE),
                        ),
                    )
                ),
            );
            $monthName  = date('M', strtotime($model->date_added));
            $count      = CampaignBounceLog::model()->count($criteria);
            $items[]    = array(Yii::t('app', $monthName) . ' ' . date('Y', strtotime($model->date_added)), $count);
        }

        $lines[] = array(
            'label' => Yii::t('dashboard', 'Bounce, {n} months growth', 3),
            'data'  => $items,
            'color' => '#ff0000'
        );

        Yii::app()->cache->set($cacheKey, $lines, 3600);

        return $this->renderJson($lines);
    }

    /**
     * Ajax only action to get unsubscribes growth
     */
    public function actionUnsubscribe_growth()
    {
        set_time_limit(0);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $customer_id = (int)Yii::app()->customer->getId();
        $cacheKey    = md5(__FILE__ . __METHOD__ . $customer_id);
        if ($items = Yii::app()->cache->get($cacheKey)) {
            return $this->renderJson(array(
                'label' => Yii::t('app', '{n} months growth', 3),
                'data'  => $items,
                'color' => '#3c8dbc'
            ));
        }

        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) AS date_added';
        $criteria->condition = 'DATE(t.date_added) >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 3 MONTH)), INTERVAL 1 DAY)';
        $criteria->group     = 'MONTH(t.date_added)';
        $criteria->order     = 't.date_added ASC';
        $criteria->limit     = 3;
        $criteria->with = array(
            'subscriber' => array(
                'select'    => false,
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'with'      => array(
                    'list'  => array(
                        'select'    => false,
                        'together'  => true,
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                        'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE),
                    ),
                )
            ),
        );
        $models = CampaignTrackUnsubscribe::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $criteria = new CDbCriteria();
            $criteria->condition = 'YEAR(t.date_added) = YEAR(:year) AND MONTH(t.date_added) = MONTH(:month)';
            $criteria->params = array(
                ':year'   => $model->date_added,
                ':month'  => $model->date_added,
            );
            $criteria->with = array(
                'subscriber' => array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'with'      => array(
                        'list'  => array(
                            'select'    => false,
                            'together'  => true,
                            'joinType'  => 'INNER JOIN',
                            'condition' => 'list.customer_id = :customer_id AND list.status != :st',
                            'params'    => array(':customer_id' => (int)$customer_id, ':st' => Lists::STATUS_PENDING_DELETE),
                        ),
                    )
                ),
            );
            $monthName  = date('M', strtotime($model->date_added));
            $count      = CampaignTrackUnsubscribe::model()->count($criteria);
            $items[]    = array(Yii::t('app', $monthName) . ' ' . date('Y', strtotime($model->date_added)), $count);
        }

        Yii::app()->cache->set($cacheKey, $items, 3600);

        return $this->renderJson(array(
            'label' => Yii::t('app', '{n} months growth', 3),
            'data'  => $items,
            'color' => '#3c8dbc'
        ));
    }
}
