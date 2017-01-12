<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FrontendSystemInit
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class FrontendSystemInit extends CApplicationComponent
{
    protected $_hasRanOnBeginRequest = false;
    protected $_hasRanOnEndRequest = false;

    public function init()
    {
        parent::init();
        Yii::app()->attachEventHandler('onBeginRequest', array($this, '_runOnBeginRequest'));
        Yii::app()->attachEventHandler('onEndRequest', array($this, '_runOnEndRequest'));
    }

    public function _runOnBeginRequest(CEvent $event)
    {
        if ($this->_hasRanOnBeginRequest) {
            return;
        }

        // register core assets if not cli mode and no theme active
        if (!MW_IS_CLI && (!Yii::app()->hasComponent('themeManager') || !Yii::app()->getTheme())) {
            $this->registerAssets();
        }

        // and mark the event as completed.
        $this->_hasRanOnBeginRequest = true;
    }

    public function _runOnEndRequest(CEvent $event)
    {
        if ($this->_hasRanOnEndRequest) {
            return;
        }

        // and mark the event as completed.
        $this->_hasRanOnEndRequest = true;
    }

    public function registerAssets()
    {
        Yii::app()->hooks->addFilter('register_scripts', array($this, '_registerScripts'));
        Yii::app()->hooks->addFilter('register_styles', array($this, '_registerStyles'));
    }

    public function _registerScripts(CList $scripts)
    {
        $apps = Yii::app()->apps;
        $scripts->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/js/bootstrap.min.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/notify.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/adminlte.js'), 'priority' => -1000),
            array('src' => AssetsUrl::js('app.js'), 'priority' => -1000),
        ));

        // since 1.3.4.8
        if (is_file(AssetsPath::js('app-custom.js'))) {
            $scripts->mergeWith(array(
                array('src' => AssetsUrl::js('app-custom.js'), 'priority' => -1000),
            ));
        }

        return $scripts;
    }

    public function _registerStyles(CList $styles)
    {
        $apps = Yii::app()->apps;
        $styles->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/css/bootstrap.min.css'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/css/adminlte.css'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/css/common.css'), 'priority' => -1000),
            array('src' => AssetsUrl::css('style.css'), 'priority' => -1000),
        ));

        // since 1.3.5.4 - skin
        $skinName = null;
        if (($_skinName = Yii::app()->options->get('system.customization.frontend_skin'))) {
            if (is_file(Yii::getPathOfAlias('root.frontend.assets.css') . '/' . $_skinName . '.css')) {
                $styles->add(array('src' => $apps->getBaseUrl('frontend/assets/css/' . $_skinName . '.css'), 'priority' => -1000));
                $skinName = $_skinName;
            } elseif (is_file(Yii::getPathOfAlias('root.assets.css') . '/' . $_skinName . '.css')) {
                $styles->add(array('src' => $apps->getBaseUrl('assets/css/' . $_skinName . '.css'), 'priority' => -1000));
                $skinName = $_skinName;
            } else {
                $_skinName = null;
            }
        }
        if (!$skinName) {
            $styles->add(array('src' => $apps->getBaseUrl('assets/css/skin-blue.css'), 'priority' => -1000));
            $skinName = 'skin-blue';
        }
        Yii::app()->getController()->getData('bodyClasses')->add($skinName);
        // end 1.3.5.4

        // since 1.3.4.8
        if (is_file(AssetsPath::css('style-custom.css'))) {
            $styles->mergeWith(array(
                array('src' => AssetsUrl::css('style-custom.css'), 'priority' => -1000),
            ));
        }

        return $styles;
    }
}
