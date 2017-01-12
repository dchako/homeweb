<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.6.2
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
                    <span class="glyphicon glyphicon-ban-circle"></span> <?php echo $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(Yii::t('app', 'Remove all'), array('email_blacklist/delete_all'), array('class' => 'btn btn-primary btn-xs delete-all', 'title' => Yii::t('app', 'Remove all'), 'data-message' => Yii::t('dashboard', 'Are you sure you want to remove all suppressed emails?')));?>
                <?php echo CHtml::link(Yii::t('app', 'Add new'), array('email_blacklist/create'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Add new')));?>
                <?php echo CHtml::link(Yii::t('app', 'Export'), array('email_blacklist/export'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Export')));?>
                <?php echo CHtml::link(Yii::t('app', 'Import'), '#csv-import-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Import')));?>
                <?php echo CHtml::link(Yii::t('app', 'Refresh'), array('email_blacklist/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
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
                    $this->widget('common.components.web.widgets.GridViewBulkAction', array(
                        'model'      => $email,
                        'formAction' => $this->createUrl('email_blacklist/bulk_action'),
                    ));
                    $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                        'ajaxUrl'           => $this->createUrl($this->route),
                        'id'                => $email->modelName.'-grid',
                        'dataProvider'      => $email->search(),
                        'filter'            => $email,
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
                                'name'                => 'email_uid',
                                'selectableRows'      => 100,
                                'checkBoxHtmlOptions' => array('name' => 'bulk_item[]'),
                            ),
                            array(
                                'name'  => 'email',
                                'value' => '$data->email',
                            ),
                            array(
                                'name'  => 'reason',
                                'value' => '$data->reason',
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
                                'footer'    => $email->paginationOptions->getGridFooterPagination(),
                                'buttons'   => array(
                                    'update' => array(
                                        'label'     => ' &nbsp; <span class="glyphicon glyphicon-pencil"></span> &nbsp;',
                                        'url'       => 'Yii::app()->createUrl("email_blacklist/update", array("email_uid" => $data->email_uid))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('app', 'Update'), 'class' => ''),
                                    ),
                                    'delete' => array(
                                        'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ',
                                        'url'       => 'Yii::app()->createUrl("email_blacklist/delete", array("email_uid" => $data->email_uid))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'delete'),
                                    ),
                                ),
                                'htmlOptions' => array(
                                    'style' => 'width:70px;',
                                ),
                                'template' => '{update} {delete}'
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
                    <h4 class="modal-title"><?php echo Yii::t('email_blacklist', 'Import from CSV file');?></h4>
                </div>
                <div class="modal-body">
                    <div class="callout callout-info">
                        <?php echo Yii::t('email_blacklist', 'Please note, the csv file must contain a header with at least the email column.');?><br />
                        <?php echo Yii::t('email_blacklist', 'If unsure about how to format your file, do an export first and see how the file looks.');?>
                    </div>
                    <?php
                    $form = $this->beginWidget('CActiveForm', array(
                        'action'        => array('email_blacklist/import'),
                        'htmlOptions'   => array(
                            'id'        => 'import-csv-form',
                            'enctype'   => 'multipart/form-data'
                        ),
                    ));
                    ?>
                    <div class="form-group">
                        <?php echo $form->labelEx($email, 'file');?>
                        <?php echo $form->fileField($email, 'file', $email->getHtmlOptions('file')); ?>
                        <?php echo $form->error($email, 'file');?>
                    </div>
                    <?php $this->endWidget(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
                    <button type="button" class="btn btn-primary btn-submit" data-loading-text="<?php echo Yii::t('app', 'Please wait, processing...');?>" onclick="$('#import-csv-form').submit();">Import file</button>
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