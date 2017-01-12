<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerTcpStream
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.7
 *
 * THIS IS BETA AND IS SUBJECT TO CHANGE!
 */

class DeliveryServerTcpStream extends DeliveryServer
{
    // hold the persistent socket connection
    private static $socket;

    protected $serverType = 'tcp-stream';

    protected $socketLastError;

    public $buffer_size = 8192;

    public $packet_separator = '|`s`|';

    public $end_of_packets = '|`e`|';

    public $socket_type_async = 'async';

    public $socket_type_sync = 'sync';

    public $socket_default_type = 'async';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array();
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array();
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return DeliveryServer the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function sendEmail(array $params = array())
    {
        $params    = (array)Yii::app()->hooks->applyFilters('delivery_server_before_send_email', $this->getParamsArray($params), $this);
        $message   = $this->getMailer()->getEmailMessage($params);
        $messageID = $this->getMailer()->getEmailMessageId();

        $payload = array(
            'ID'   => $messageID,
            'Body' => $message,
            'Type' => $this->socket_default_type,
        );

        if (($sent = $this->socketSend($payload)) !== false) {
            $sent = array('message_id' => $messageID);
            $this->logUsage();
            $this->getMailer()->addLog('OK');
        } elseif (!empty($this->socketLastError)) {
            $this->getMailer()->addLog($this->socketLastError);
        }

        Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);

        return $sent;
    }

    public function getParamsArray(array $params = array())
    {
        $params['transport'] = self::TRANSPORT_TCP_STREAM;
        return parent::getParamsArray($params);
    }

    public function requirementsFailed()
    {
        $requiredFunctions = array('pfsockopen', 'fclose', 'fwrite', 'fread', 'feof');
        $missing = array();
        foreach ($requiredFunctions as $func) {
            if (!CommonHelper::functionExists($func)) {
                $missing[] = $func;
            }
        }
        if (!empty($missing)) {
            return Yii::t('servers', 'The server type {type} requires following functions to be active on your host: {functions}!', array(
                '{type}'      => $this->serverType,
                '{functions}' => implode(', ', $missing),
            ));
        }
        return parent::requirementsFailed();
    }

    protected function afterFind()
    {
        parent::afterFind();
        $this->setDefaultAttributes();
    }

    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname = '127.0.0.1';
        $this->port     = 52014;
        $this->timeout  = 5;
    }

    protected function beforeValidate()
    {
        return parent::beforeValidate();
    }

    protected function afterValidate()
    {
        parent::afterValidate();
    }

    protected function setDefaultAttributes()
    {
        $defaults = array(
            'hostname'  => '127.0.0.1',
            'port'      => 52014,
            'timeout'   => 5,
        );
        foreach ($defaults as $key => $value) {
            if (empty($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    protected function socketConnect()
    {
        $this->socketDisconnect();
        self::$socket = pfsockopen($this->hostname, $this->port, $errNo, $errStr, $this->timeout);
        if (!$this->socketIsConnected()) {
            $this->socketLastError = $errStr;
            return false;
        }
        Yii::app()->attachEventHandler('onEndRequest', array($this, 'runOnEndRequest'));
        return true;
    }

    protected function socketDisconnect()
    {
        $this->socketLastError = null;
        if (self::$socket !== null && is_resource(self::$socket)) {
            fclose(self::$socket);
        }
        self::$socket = null;
        Yii::app()->detachEventHandler('onEndRequest', array($this, 'runOnEndRequest'));
        return $this;
    }

    protected function socketIsConnected()
    {
        return self::$socket !== null && is_resource(self::$socket);
    }

    public function socketSend($message)
    {
        if (!$this->socketIsConnected() && !$this->socketConnect()) {
            return false;
        }

        if (is_array($message)) {
            $payload = CJSON::encode($message);
            $fullPayload = $payload . $this->packet_separator;
        } elseif ($message == $this->end_of_packets) {
            $payload = $fullPayload = $message;
        } else {
            return false;
        }

        static $strlen, $substr;
        if ($strlen === null) {
            $strlen = CommonHelper::functionExists('mb_strlen') ? 'mb_strlen' : 'strlen';
        }
        if ($substr === null) {
            $substr = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
        }

        $sentBytes = 0;
        while (!empty($fullPayload)) {
            $sentBytes = fwrite(self::$socket, $fullPayload, $this->buffer_size);
            $fullPayload = $substr($fullPayload, $sentBytes);
        }

        $response = '';
        while(!feof(self::$socket)) {
            $response .= fread(self::$socket, $this->buffer_size);
            if ($substr($response, -$strlen($this->packet_separator)) == $this->packet_separator) {
                break;
            }
        }
        $response = str_replace($this->packet_separator, '', $response);
        return $response;
    }

    // run on end of request and close the socket connection.
    public function runOnEndRequest(CEvent $event)
    {
        $this->socketDisconnect();
    }
}
