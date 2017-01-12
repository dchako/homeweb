<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * IOFilter
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class IOFilter extends CApplicationComponent
{
    /**
     * @var object CHtmlPurifier
     */
    private $_purifier;

    /**
     * @var object CI_Security
     */
    private $_CISecurity;

    /**
     * @var string $htmlPurifierClass
     * The name of the custom htmlpurifier class to load.
     * @since 1.3.4.7
     */
    public $htmlPurifierClass = 'MHtmlPurifier';

    /**
     * IOFilter::encode()
     *
     * @param mixed $content
     * @return mixed
     */
    public function encode($content)
    {
        if (is_array($content)) {
            $content = array_map(array($this, 'encode'), $content);
        } else {
            $content = CHtml::encode($this->decode($content));
        }
        return $content;
    }

    /**
     * IOFilter::decode()
     *
     * @param mixed $content
     * @return mixed
     */
    public function decode($content)
    {
        if (is_array($content)) {
            $content = array_map(array($this, 'decode'), $content);
        } else {
            $content = CHtml::decode($content);
        }
        return $content;
    }

    /**
     * IOFilter::stripClean()
     *
     * @param mixed $content
     * @return mixed
     */
    public function stripClean($content)
    {
        return $this->stripTags($this->xssClean($this->stripTags($content)));
    }

    /**
     * IOFilter::stripTags()
     *
     * @param mixed $content
     * @return mixed
     */
    public function stripTags($content)
    {
        if (is_array($content)) {
            $content = array_map(array($this, 'stripTags'), $content);
        } else {
            $content = rawurldecode($content);
            $content = CHtml::decode($content);
            $content = FilterVarHelper::filter($content, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);    
        }
        return $content;
    }

    /**
     * IOFilter::xssClean()
     *
     * @param mixed $content
     * @return mixed
     */
    public function xssClean($content)
    {
        return $this->getCISecurity()->xss_clean($content);
    }

    /**
     * IOFilter::purify()
     *
     * @param mixed $content
     * @return mixed
     */
    public function purify($content)
    {
        return $this->getPurifier()->purify($content);
    }

    /**
     * IOFilter::getPurifier()
     *
     * @return MHtmlPurifier
     */
    public function getPurifier()
    {
        if ($this->_purifier === null) {
            $htmlPurifierClass = $this->htmlPurifierClass;
            return $this->_purifier = new $htmlPurifierClass();
        }
        return $this->_purifier;
    }

    /**
     * IOFilter::getCISecurity()
     *
     * @return CI_Security
     */
    public function getCISecurity()
    {
        if ($this->_CISecurity === null) {
            require_once Yii::getPathOfAlias('common.vendors.Codeigniter.system.core.Security') . '.php';
            $this->_CISecurity = new CI_Security();
        }
        return $this->_CISecurity;
    }

    /**
     * IOFilter::cleanGlobals()
     *
     * @return
     */
    public function cleanGlobals()
    {
        if (Yii::app()->request->globalsCleaned) {
            return;
        }

        Yii::app()->params['POST']      = new CMap($_POST);
        Yii::app()->params['GET']       = new CMap($_GET);
        Yii::app()->params['COOKIE']    = new CMap($_COOKIE);
        Yii::app()->params['REQUEST']   = new CMap($_REQUEST);
        Yii::app()->params['SERVER']    = new CMap($_SERVER);

        $_POST      = $this->stripClean($_POST);
        $_GET       = $this->stripClean($_GET);
        $_COOKIE    = $this->stripClean($_COOKIE);
        $_REQUEST   = $this->stripClean($_REQUEST);
        $_SERVER    = $this->stripClean($_SERVER);

        Yii::app()->request->globalsCleaned = true;
    }
}
