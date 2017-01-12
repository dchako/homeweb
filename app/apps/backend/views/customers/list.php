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
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <span class="glyphicon glyphicon-user"></span> <?php echo Yii::t('customers', 'Customers');?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Create new'), array('customers/create'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Create new')));?>
                <?php echo HtmlHelper::accessLink(Yii::t('customers', 'Manage groups'), array('customer_groups/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('customers', 'Manage groups')));?>
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Refresh'), array('customers/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
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
                    'id'                => $customer->modelName.'-grid',
                    'dataProvider'      => $customer->search(),
                    'filter'            => $customer,
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
                            'name'  => 'first_name',
                            'value' => '$data->first_name',
                        ),
                        array(
                            'name'  => 'last_name',
                            'value' => '$data->last_name'
                        ),
                        array(
                            'name'  => 'email',
                            'value' => '$data->email'
                        ),
                        array(
                            'name'  => 'company_name',
                            'value' => '!empty($data->company) ? $data->company->name : "-"'
                        ),
                        array(
                            'name'  => 'group_id',
                            'value' => '!empty($data->group_id) ? CHtml::link($data->group->name, array("customer_groups/update", "id" => $data->group_id)) : "-"',
                            'type'  => 'raw',
                            'filter'=> CustomerGroup::getGroupsArray(),
                        ),
                        array(
                            'name'  => 'sending_quota_usage',
                            'value' => '$data->getSendingQuotaUsageDisplay()',
                            'type'  => 'raw',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->status',
                            'filter'=> $customer->getStatusesArray(),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $customer->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'impersonate' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-random"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("customers/impersonate", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Login as this customer'), 'class' => ''),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/impersonate")',
                                ),
                                'reset_quota' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-refresh"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("customers/reset_sending_quota", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Reset sending quota'), 'class' => 'reset-sending-quota', 'data-message' => Yii::t('customers', 'Are you sure you want to reset the sending quota for this customer?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/reset_sending_quota")',
                                ),
                                'update' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-pencil"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("customers/update", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Update'), 'class' => ''),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/update")',
                                ),
                                'delete' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ', 
                                    'url'       => 'Yii::app()->createUrl("customers/delete", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/delete") && $data->removable === Customer::TEXT_YES',
                                ),    
                            ),
                            'htmlOptions' => array(
                                'style' => 'width:130px;',
                            ),
                            'template' => '{impersonate} {reset_quota} {update} {delete}'
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