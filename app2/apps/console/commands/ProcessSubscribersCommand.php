<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ProcessSubscribersCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.2
 */
 
class ProcessSubscribersCommand extends ConsoleCommand 
{
    /**
     * Since 1.3.3.1 all the logic from this command has been moved in the "daily" command
     * This command will be removed in future
     */
    public function actionIndex() 
    {
    }
}
