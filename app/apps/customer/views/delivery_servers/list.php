<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.3.1
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
                    <span class="glyphicon glyphicon-send"></span> <?php echo $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <div class="btn-group" style="margin-right: 10px;">
                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown"> <?php echo Yii::t('servers', 'Create new server');?> <span class="caret"></span> </button>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach (DeliveryServer::getCustomerTypesList() as $type => $name) { ?>
                        <li><a href="<?php echo $this->createUrl('delivery_servers/create', array('type' => $type));?>"><?php echo $name;?></a></li>
                        <?php } ?>
                    </ul>
                </div>
                <?php echo CHtml::link(Yii::t('app', 'Refresh'), array('delivery_servers/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
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
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $server->modelName.'-grid',
                    'dataProvider'      => $server->search(),
                    'filter'            => $server,
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
                            'name'  => 'name',
                            'value' => 'empty($data->name) ? null : CHtml::link($data->name, Yii::app()->createUrl("delivery_servers/update", array("type" => $data->type, "id" => $data->server_id)))',
                            'type'  => 'raw',
                        ), 
                        array(
                            'name'  => 'hostname',
                            'value' => 'CHtml::link($data->hostname, Yii::app()->createUrl("delivery_servers/update", array("type" => $data->type, "id" => $data->server_id)))',
                            'type'  => 'raw',
                        ), 
                        array(
                            'name'  => 'username',
                            'value' => '$data->username',
                        ), 
                        array(
                            'name'  => 'from_email',
                            'value' => '$data->from_email',
                        ),
                        array(
                            'name'  => 'type',
                            'value' => 'DeliveryServer::getNameByType($data->type)',
                            'filter'=> $server->getTypesList(),
                        ), 
                        array(
                            'name'  => 'status',
                            'value' => 'ucfirst(Yii::t("app", $data->status))',
                            'filter'=> $server->getStatusesList(),
                        ), 
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $server->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-pencil"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/update", array("type" => $data->type, "id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Update'), 'class' => ''),
                                    'visible'   => '$data->getCanBeUpdated()',
                                ),
                                'copy'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-subtitles"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/copy", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Copy'), 'class' => 'copy-server'),
                                ),
                                'enable'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-open"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/enable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Enable'), 'class' => 'enable-server'),
                                    'visible'   => '$data->getIsDisabled()',
                                ),
                                'disable'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-save"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/disable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Disable'), 'class' => 'disable-server'),
                                    'visible'   => '$data->getIsActive()',
                                ),
                                'delete' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/delete", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Delete'), 'class' => 'delete'),
                                    'visible'   => '$data->getCanBeDeleted()',
                                ),    
                            ),
                            'htmlOptions' => array(
                                'style' => 'width:120px;',
                            ),
                            'template' => '{update} {copy} {enable} {disable} {delete}'
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