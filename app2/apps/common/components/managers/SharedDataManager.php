<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SharedDataManager
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

class SharedDataManager extends CApplicationComponent
{
    /**
     * @var bool
     */
    protected $canUseSharedMemory = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->canUseSharedMemory = CommonHelper::functionExists('shmop_open');
    }

    /**
     * @param $id
     * @param $value
     * @return $this
     */
    public function set($id, $value)
    {
        if ($this->canUseSharedMemory && is_numeric($id)) {
            $block = new \Simple\SHM\Block($id);
            $block->write((string)$value);
        } else {
            Yii::app()->cache->set($id, $value, 3600 * 24);
        }
        
        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        if ($this->canUseSharedMemory && is_numeric($id)) {
            $block = new \Simple\SHM\Block($id);
            $value = $block->read($id);
        } else {
            $value = Yii::app()->cache->get($id);
        }
        return $value;
    }

    /**
     * @param $id
     * @return $this
     */
    public function delete($id)
    {
        if ($this->canUseSharedMemory && is_numeric($id)) {
            $block = new \Simple\SHM\Block($id);
            $block->delete($id);
        } else {
            Yii::app()->cache->delete($id);
        }
        return $this;
    }

}
