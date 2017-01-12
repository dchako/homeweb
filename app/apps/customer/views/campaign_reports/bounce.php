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
        $text = 'This report shows all the subscribers that did not get your email.<br />
        These subscribers are not removed from the list, but at a certain point the delivery for them will be denied by the system.<br />
        This report is a good start point to clean up your list of subscribers.';
        echo Yii::t('campaign_reports', StringHelper::normalizeTranslationString($text));
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
                <?php if (!empty($canExportStats)) { ?>
                <?php echo CHtml::link(Yii::t('campaign_reports', 'Export reports'), array('campaign_reports_export/bounce', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('campaign_reports', 'Export reports')));?>
                <?php } ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <div class="pull-left bulk-selected-options" style="display:none; margin-bottom: 5px;">
                    <?php echo CHtml::dropDownList('bulk_action', null, CMap::mergeArray(array('' => Yii::t('list_subscribers', 'With selected:')), $bulkActions), array(
                        'class'         => 'form-control bulk-action',
                        'data-bulkurl'  => $this->createUrl('list_subscribers/bulk_action', array('list_uid' => $campaign->list->list_uid)),
                        'data-delete'   => Yii::t('app', 'Are you sure you want to delete this item? There is no way coming back after you do it.'),
                    ));?>
                </div>
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
                    'id'                => $bounceLogs->modelName.'-grid',
                    'dataProvider'      => $bounceLogs->customerSearch(),
                    'filter'            => $bounceLogs,
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
                            'class'                 => 'CCheckBoxColumn',
                            'header'                => '',
                            'footer'                => '',
                            'name'                  => 'bulk_select[]',
                            'id'                    => 'bulk_select',
                            'selectableRows'        => 2,
                            'value'                 => '$data->subscriber->subscriber_id',
                            'checkBoxHtmlOptions'   => array('class' => 'bulk-select'),
                        ),
                        array(
                            'name'  => 'subscriber.email',
                            'value' => 'CHtml::link($data->subscriber->email, Yii::app()->createUrl("list_subscribers/update", array("list_uid" => $data->subscriber->list->list_uid, "subscriber_uid" => $data->subscriber->subscriber_uid)))',
                            'filter'=> false,
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'bounce_type',
                            'value' => 'strtoupper($data->bounce_type)',
                            'filter'=> $bounceLogs->getBounceTypesArray(),
                        ),
                        array(
                            'name'  => 'message',
                            'value' => '$data->message',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $bounceLogs->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-pencil"></span> &nbsp;',
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/update", array("list_uid" => $data->subscriber->list->list_uid, "subscriber_uid" => $data->subscriber->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaign_reports', 'Update subscriber'), 'class' => ''),
                                ),
                                'delete' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ',
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/delete", array("list_uid" => $data->subscriber->list->list_uid, "subscriber_uid" => $data->subscriber->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaign_reports', 'Delete subscriber'), 'class' => 'delete'),
                                ),
                            ),
                            'htmlOptions' => array(
                                'style' => 'width:110px;',
                            ),
                            'template'=>'{update} {delete}'
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
