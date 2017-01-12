<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4
 */
 
class OptionCommand extends CConsoleCommand 
{
    public function actionGet_option($name, $default = null)
    {
        exit((string)Yii::app()->options->get($name, $default));
    }
    
    public function actionSet_option($name, $value)
    {
        Yii::app()->options->set($name, $value);
    }
}