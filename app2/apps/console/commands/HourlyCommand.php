<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HourlyCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.7.5
 */
 
class HourlyCommand extends ConsoleCommand 
{
    public function actionIndex()
    {
        Yii::app()->hooks->doAction('console_command_hourly_before_process', $this);

        $result = $this->process();

        Yii::app()->hooks->doAction('console_command_hourly_after_process', $this);

        return $result;
    }
    
    public function process()
    {
        return 0;
    }
}
