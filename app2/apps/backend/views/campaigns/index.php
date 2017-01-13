<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since @since 1.3.5.5
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
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('envelope') .  $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('campaigns/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
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
                // since 1.3.5.6
                if (AccessHelper::hasRouteAccess('campaigns/bulk_action')) {
                    $this->widget('common.components.web.widgets.GridViewBulkAction', array(
                        'model'      => $campaign,
                        'formAction' => $this->createUrl('campaigns/bulk_action'),
                    ));
                }
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $campaign->modelName.'-grid',
                    'dataProvider'      => $campaign->search(),
                    'filter'            => $campaign,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
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
                            'class'               => 'CCheckBoxColumn',
                            'name'                => 'campaign_uid',
                            'selectableRows'      => 100,
                            'checkBoxHtmlOptions' => array('name' => 'bulk_item[]'),
                        ),
                        array(
                            'name'  => 'campaign_uid',
                            'value' => 'HtmlHelper::accessLink($data->campaign_uid, array("campaigns/overview", "campaign_uid" => $data->campaign_uid))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'customer_id',
                            'value' => 'HtmlHelper::accessLink($data->customer->fullName, array("customers/update", "id" => $data->customer_id))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'name',
                            'value' => 'HtmlHelper::accessLink($data->name, array("campaigns/overview", "campaign_uid" => $data->campaign_uid))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'type',
                            'value' => 'ucfirst(strtolower($data->getTypeNameDetails()))',
                            'type'  => 'raw',
                            'filter'=> $campaign->getTypesList(),
                            'htmlOptions' => array('style' => 'max-width: 150px')
                        ),
                        array(
                            'name'  => 'group_id',
                            'value' => '!empty($data->group_id) ? $data->group->name : "-"',
                            'filter'=> $campaign->getGroupsDropDownArray(),
                            'type'  => 'raw'
                        ),
                        array(
                            'name'  => 'list_id',
                            'value' => '$data->list->name',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'segment_id',
                            'value' => '!empty($data->segment_id) ? $data->segment->name : "-"',
                            'filter'=> false,
                            'type'  => 'raw',
                        ),
                        array(
                            'name'        => 'search_recurring',
                            'value'       => 'Yii::t("app", $data->option->cronjob_enabled ? "Yes" : "No")',
                            'filter'      => $campaign->getYesNoOptions(),
                            'htmlOptions' => array('style' => 'max-width: 150px')
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->getStatusWithStats()',
                            'filter'=> $campaign->getStatusesList(),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'send_at',
                            'value' => '$data->getSendAt()',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $campaign->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'overview'=> array(
                                    'label'     => IconHelper::make('glyphicon-info-sign'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Overview'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/overview") && (!$data->editable || $data->isPaused) && !$data->pendingDelete && !$data->isDraft',
                                ),
                                'pause'=> array(
                                    'label'     => IconHelper::make('glyphicon-pause'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/pause_unpause", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Pause sending'), 'class' => 'btn btn-primary btn-flat pause-sending', 'data-message' => Yii::t('campaigns', 'Are you sure you want to pause this campaign ?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/pause_unpause") && $data->canBePaused',
                                ),
                                'unpause'=> array(
                                    'label'     => IconHelper::make('glyphicon-play-circle'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/pause_unpause", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Unpause sending'), 'class' => 'btn btn-primary btn-flat unpause-sending', 'data-message' => Yii::t('campaigns', 'Are you sure you want to unpause sending emails for this campaign ?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/pause_unpause") && $data->isPaused',
                                ),
                                'block'=> array(
                                    'label'     => IconHelper::make('glyphicon-off'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/block_unblock", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Block sending'), 'class' => 'btn btn-primary btn-flat block-sending', 'data-message' => Yii::t('campaigns', 'Are you sure you want to block this campaign ?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/block_unblock") && $data->canBeBlocked',
                                ),
                                'unblock'=> array(
                                    'label'     => IconHelper::make('fa-play'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/block_unblock", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Unblock sending'), 'class' => 'btn btn-primary btn-flat unblock-sending', 'data-message' => Yii::t('campaigns', 'Are you sure you want to unblock sending emails for this campaign ?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/block_unblock") && $data->isBlocked',
                                ),
                                'reset'=> array(
                                    'label'     => IconHelper::make('refresh'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/resume_sending", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Resume sending'), 'class' => 'btn btn-primary btn-flat resume-campaign-sending', 'data-message' => Yii::t('campaigns', 'Resume sending, use this option if you are 100% sure your campaign is stuck and does not send emails anymore!')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/resume_sending") && $data->canBeResumed',
                                ),
                                'marksent'=> array(
                                    'label'     => IconHelper::make('glyphicon-ok'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/marksent", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Mark as sent'), 'class' => 'btn btn-primary btn-flat mark-campaign-as-sent', 'data-message' => Yii::t('campaigns', 'Are you sure you want to mark this campaign as sent ?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/marksent") && $data->canBeMarkedAsSent',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/delete", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaigns/delete") && $data->removable',
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{overview} {pause} {unpause} {reset} {marksent} {block} {unblock} {delete}'
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
