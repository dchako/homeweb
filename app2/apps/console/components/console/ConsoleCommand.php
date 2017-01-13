<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ConsoleCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.6.6
 *
 */

class ConsoleCommand extends CConsoleCommand
{
    // whether this should be verbose and output to console
    public $verbose = 0;

    /**
     * @param $message
     * @param bool $timer
     * @param string $separator
     * @return int
     */
    protected function stdout($message, $timer = true, $separator = "\n")
    {
        if (!$this->verbose) {
            return 0;
        }
        
        if (!is_array($message)) {
            $message = array($message);
        }

        $out = '';
        
        foreach ($message as $msg) {
            
            if ($timer) {
                $out .= '[' . date('Y-m-d H:i:s') . '] - ';
            }
            
            $out .= $msg;
            
            if ($separator) {
                $out .= $separator;
            }
        }

        echo $out;
        return 0;
    }
}
