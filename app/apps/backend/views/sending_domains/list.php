<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.6
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
        <?php echo Yii::t('sending_domains', 'With sending domains you can verify the authenticity of the domain used in the campaigns FROM email field.');?><br />
        <?php echo Yii::t('sending_domains', 'Verification is very simple, it involves adding just two DNS TXT records for the domain used in the FROM field of a campaign.');?><br />
        <?php echo Yii::t('sending_domains', 'Once a sending domain is verified, all future campaigns sent from the verified domain will be DKIM signed and will pass SPF validation, thus giving a higher inbox delivery rate.');?><br />
    </div>
    <div class="clearfix"><!-- --></div>
    <div class="box box-primary">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <span class="glyphicon glyphicon-globe"></span> <?php echo Yii::t('sending_domains', 'Sending domains');?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Create new'), array('sending_domains/create'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Create new')));?>
                <?php echo HtmlHelper::accessLink(Yii::t('app', 'Refresh'), array('sending_domains/index'), array('class' => 'btn btn-primary btn-xs', 'title' => Yii::t('app', 'Refresh')));?>
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
                    'id'                => $domain->modelName.'-grid',
                    'dataProvider'      => $domain->search(),
                    'filter'            => $domain,
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
                            'name'  => 'customer_id',
                            'value' => '!empty($data->customer) ? $data->customer->getFullName() : Yii::t("app", "System")',
                            'filter'=> CHtml::activeTextField($domain, 'customer_id'),
                        ),
                        array(
                            'name'  => 'name',
                            'value' => 'CHtml::link($data->name, Yii::app()->createUrl("sending_domains/update", array("id" => $data->domain_id)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'locked',
                            'value' => '$data->isLocked ? Yii::t("app", "Yes") : Yii::t("app", "No")',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'verified',
                            'value' => '$data->isVerified ? Yii::t("app", "Yes") : Yii::t("app", "No")',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'signing_enabled',
                            'value' => '$data->signingEnabled ? Yii::t("app", "Yes") : Yii::t("app", "No")',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'last_updated',
                            'value' => '$data->lastUpdated',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $domain->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-pencil"></span> &nbsp;', 
                                    'url'       => 'Yii::app()->createUrl("sending_domains/update", array("id" => $data->domain_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Update'), 'class' => ''),
                                    'visible'   => 'AccessHelper::hasRouteAccess("sending_domains/update")',
                                ),
                                'delete' => array(
                                    'label'     => ' &nbsp; <span class="glyphicon glyphicon-remove-circle"></span> &nbsp; ', 
                                    'url'       => 'Yii::app()->createUrl("sending_domains/delete", array("id" => $data->domain_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Delete'), 'class' => 'delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("sending_domains/delete")',
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