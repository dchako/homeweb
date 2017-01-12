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
                    <span class="glyphicon glyphicon-send"></span> <?php echo Yii::t('servers', 'Delivery servers');?>
                </h3>
            </div>
            <div class="pull-right">
                <?php if (AccessHelper::hasRouteAccess('delivery_servers/create')) { ?>
                <div class="btn-group" style="margin-right: 10px;">
                    <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown"> <?php echo Yii::t('servers', 'Create new server');?> <span class="caret"></span> </button>
                    <ul class="dropdown-menu" role="menu">
                        <?php foreach (DeliveryServer::getTypesList() as $type => $name) { ?>
                        <li><a href="<?php echo $this->createUrl('delivery_servers/create', array('type' => $type));?>"><?php echo $name;?></a></li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if (AccessHelper::hasRouteAccess('delivery_servers/import')) { ?>
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Import'), '#csv-import-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Import')));?>
                <?php } ?>
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Export'), array('delivery_servers/export'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Export')));?>
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Refresh'), array('delivery_servers/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
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
                // since 1.3.5.4
                if (AccessHelper::hasRouteAccess('delivery_servers/bulk_action')) { 
                    $this->widget('common.components.web.widgets.GridViewBulkAction', array(
                        'model'      => $server,
                        'formAction' => $this->createUrl('delivery_servers/bulk_action'),
                    ));
                }
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
                            'class'               => 'CCheckBoxColumn',
                            'name'                => 'server_id',
                            'selectableRows'      => 100,  
                            'checkBoxHtmlOptions' => array('name' => 'bulk_item[]'),
                            'visible'             => AccessHelper::hasRouteAccess('delivery_servers/bulk_action'),
                        ),
                        array(
                            'name'  => 'customer_id',
                            'value' => '!empty($data->customer) ? $data->customer->getFullName() : Yii::t("app", "System")',
                            'filter'=> CHtml::activeTextField($server, 'customer_id'),
                        ),
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
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/update") && $data->getCanBeUpdated()',
                                ),
                                'copy'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-subtitles"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/copy", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Copy'), 'class' => 'copy-server'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/copy")',
                                ),
                                'enable'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-open"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/enable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Enable'), 'class' => 'enable-server'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/enable") && $data->getIsDisabled()',
                                ),
                                'disable'=> array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-save"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/disable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Disable'), 'class' => 'disable-server'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/disable") && $data->getIsActive()',
                                ),
                                'delete' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ', 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/delete", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Delete'), 'class' => 'delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/delete") && $data->getCanBeDeleted()',
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
                'controller'  => $this,
                'renderedGrid'=> $collection->renderGrid,
            )));
            ?>
            <div class="clearfix"><!-- --></div>
            </div>    
        </div>
    </div>
    
    <div class="modal fade" id="csv-import-modal" tabindex="-1" role="dialog" aria-labelledby="csv-import-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('servers', 'Import from CSV file');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php echo Yii::t('servers', 'Please note, the csv file must contain a header with proper columns.');?><br />
                    <?php echo Yii::t('servers', 'If unsure about how to format your file, do an export first and see how the file looks.');?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('delivery_servers/import'),
                    'htmlOptions'   => array(
                        'id'        => 'import-csv-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($csvImport, 'file');?>
                    <?php echo $form->fileField($csvImport, 'file', $csvImport->getHtmlOptions('file')); ?>
                    <?php echo $form->error($csvImport, 'file');?>
                </div>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#import-csv-form').submit();"><?php echo Yii::t('app', 'Import file');?></button>
            </div>
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