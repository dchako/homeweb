<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerIdentity
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class CustomerIdentity extends BaseUserIdentity
{
    public $impersonate = false;

    public function authenticate()
    {
        $customer = Customer::model()->findByAttributes(array(
            'email'     => $this->email,
            'status'    => Customer::STATUS_ACTIVE,
        ));

        if (empty($customer)) {
            $this->errorCode = Yii::t('customers', 'Invalid login credentials.');
            return !$this->errorCode;
        }

        if (!$this->impersonate && !Yii::app()->passwordHasher->check($this->password, $customer->password)) {
            $this->errorCode = Yii::t('customers', 'Invalid login credentials.');
            return !$this->errorCode;
        }

        $this->setId($customer->customer_id);
        $this->setAutoLoginToken($customer);

        $this->errorCode = self::ERROR_NONE;
        return !$this->errorCode;
    }

    public function setAutoLoginToken(Customer $customer)
    {
        $token = sha1(uniqid(rand(0, time()), true));
        $this->setState('__customer_auto_login_token', $token);

        CustomerAutoLoginToken::model()->deleteAllByAttributes(array(
            'customer_id' => (int)$customer->customer_id,
        ));

        $autologinToken                 = new CustomerAutoLoginToken();
        $autologinToken->customer_id    = (int)$customer->customer_id;
        $autologinToken->token          = $token;
        $autologinToken->save();

        return $this;
    }

}
