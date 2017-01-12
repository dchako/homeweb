<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { ?>
    <div class="callout callout-info">
        <?php
        $text = 'This report shows all the opens for this campaign, if a subscriber opens the email twice, you will see it twice.<br />
        If you need to see only the unique opens (subscribers number that opened the email) please click <a href="{href}">here</a>.';
        echo Yii::t('campaign_reports', StringHelper::normalizeTranslationString($text), array(
            '{href}' => $this->createUrl('campaign_reports/open_unique', array('campaign_uid' => $campaign->campaign_uid)),
        ));
        ?>
    </div>
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <span class="glyphicon glyphicon-list-alt"></span> <?php echo $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(Yii::t('campaign_reports', 'Campaign overview'), array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('campaign_reports', 'Back to campaign overview')));?>
                <?php echo CHtml::link(Yii::t('campaign_reports', 'Unique opens'), array('campaign_reports/open_unique', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('campaign_reports', 'View unique opens only')));?>
                <?php if (!empty($canExportStats)) { ?>
                <?php echo CHtml::link(Yii::t('campaign_reports', 'Export reports'), array('campaign_reports_export/open', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('campaign_reports', 'Export reports')));?>
                <?php } ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
            <?php
            /**
             * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderGrid} to false
             * in order to stop rendering the default content.
             * @since 1.3.3.1
             */
            $hooks->doAction('before_grid_view', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'renderGrid'    => true,
            )));

            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route, array('campaign_uid' => $campaign->campaign_uid)),
                    'id'                => $model->modelName.'-grid',
                    'dataProvider'      => $dataProvider,
                    'filter'            => null,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-bordered table-hover table-striped',
                    'selectableRows'    => 0,
                    'enableSorting'     => false,
                    'cssFile'           => false,
                    'pagerCssClass'     => 'pagination pull-right',
                    'pager'             => array(
                        'class'         => 'CLinkPager',
                        'cssFile'       => false,
                        'header'        => false,
                        'htmlOptions'   => array('class' => 'pagination')
                    ),
                    'columns' => $hooks->applyFilters('grid_view_columns', array(
                        array(
                            'name'  => 'subscriber.email',
                            'value' => 'CHtml::link($data->subscriber->email, Yii::app()->createUrl("list_subscribers/update", array("list_uid" => $data->subscriber->list->list_uid, "subscriber_uid" => $data->subscriber->subscriber_uid)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'ip_address',
                            'value' => 'CHtml::link($data->getIpWithLocationForGrid(), CommonHelper::getIpAddressInfoUrl($data->ip_address), array("target" => "_blank"))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'user_agent',
                            'value' => 'CHtml::link($data->user_agent, CommonHelper::getUserAgentInfoUrl($data->user_agent), array("target" => "_blank"))',
                            'type'  => 'raw',
                            'htmlOptions' => array('style' => 'max-width:220px;word-wrap:break-word;'),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $model->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'bysubscriber'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-info-sign"></span> &nbsp;',
                                    'url'       => 'Yii::app()->createUrl("campaign_reports/open_by_subscriber", array("campaign_uid" => $data->campaign->campaign_uid, "subscriber_uid" => $data->subscriber->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaign_reports', 'View all opens by this subscriber'), 'class' => ''),
                                ),
                            ),
                            'htmlOptions' => array(
                                'style' => 'width:70px;',
                            ),
                            'template'=>'{bysubscriber}'
                        ),
                    ), $this),
                ), $this));
            }
            /**
             * This hook gives a chance to append content after the grid view content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * @since 1.3.3.1
             */
            $hooks->doAction('after_grid_view', new CAttributeCollection(array(
                'controller'    => $this,
                'renderedGrid'  => $collection->renderGrid,
            )));
            ?>
            <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>
<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));
