<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5
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
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <span class="glyphicon glyphicon-comment"></span> <?php echo $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(Yii::t('app', 'Refresh'), array('campaign_abuse_reports/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
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
                'controller'  => $this,
                'renderGrid'  => true,
            )));
            
            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $reports->modelName.'-grid',
                    'dataProvider'      => $reports->search(),
                    'filter'            => $reports,
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
                            'name'  => 'customer_info',
                            'value' => '$data->customer_info',
                        ),
                        array(
                            'name'  => 'campaign_info',
                            'value' => '$data->campaign_info',
                        ),
                        array(
                            'name'  => 'list_info',
                            'value' => '$data->list_info',
                        ),
                        array(
                            'name'  => 'subscriber_info',
                            'value' => '$data->subscriber_info',
                        ),
                        array(
                            'name'  => 'reason',
                            'value' => '$data->reason',
                        ),
                        array(
                            'name'  => 'log',
                            'value' => 'nl2br($data->log)',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $reports->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'delete' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ', 
                                    'url'       => 'Yii::app()->createUrl("campaign_abuse_reports/delete", array("id" => $data->report_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaign_abuse_reports/delete")',
                                ), 
                                'blacklist' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-ban-circle"></span> &nbsp; ', 
                                    'url'       => 'Yii::app()->createUrl("campaign_abuse_reports/blacklist", array("id" => $data->report_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Blacklist email'), 'class' => 'blacklist-email', 'data-message' => Yii::t('campaigns', 'Are you sure you want to blacklist the email address specified in the abuse report?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("campaign_abuse_reports/blacklist")',
                                ),   
                            ),
                            'htmlOptions' => array(
                                'style' => 'width:70px;',
                            ),
                            'template' => '{delete} {blacklist}'
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
                'controller'  => $this,
                'renderedGrid'=> $collection->renderGrid,
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