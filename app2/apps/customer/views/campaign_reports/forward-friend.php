<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('views_before_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { ?>
    <div class="box box-primary borderless">
        <div class="box-header">
    		<div class="pull-left">
                <h3 class="box-title"><i class="fa fa-envelope"></i> <?php echo $pageHeading;?></h3>
            </div>
    		<div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('fa-envelope') . Yii::t('campaign_reports', 'Campaign overview'), array($this->campaignOverviewRoute, 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Back to campaign overview')));?>
            	<?php echo CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('campaign_reports/forward_friend', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
    		</div>
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
            $hooks->doAction('views_before_grid', $collection = new CAttributeCollection(array(
                'controller'   => $this,
                'renderGrid'   => true,
            )));
            
            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('views_grid_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route, array('campaign_uid' => $campaign->campaign_uid)),
                    'id'                => $forward->modelName.'-grid',
                    'dataProvider'      => $forward->search(),
                    'filter'            => $forward,
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
                    'columns' => $hooks->applyFilters('views_grid_columns', array(
                        array(
                            'name'  => 'subscriber_id',
                            'value' => '!empty($data->subscriber_id) ? $data->subscriber->email : "-"',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'to_email',
                            'value' => '$data->to_email',
                        ),
                        array(
                            'name'  => 'to_name',
                            'value' => '$data->to_name',
                        ),
                        array(
                            'name'  => 'from_email',
                            'value' => '$data->from_email',
                        ),
                        array(
                            'name'  => 'from_name',
                            'value' => '$data->from_name',
                        ),
                        array(
                            'name'  => 'subject',
                            'value' => '$data->subject',
                        ),
                        array(
                            'name'  => 'message',
                            'value' => '$data->message',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'ip_address',
                            'value' => '$data->ip_address',
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $forward->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(),
                            'htmlOptions' => array(
                                'style' => 'width:70px;',
                            ),
                            'template' => ''
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
            $hooks->doAction('views_after_grid', new CAttributeCollection(array(
                'controller'   => $this,
                'renderedGrid' => $collection->renderGrid,
            )));
            ?>
            </div>   
            <div class="clearfix"><!-- --></div> 
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
$hooks->doAction('views_after_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));