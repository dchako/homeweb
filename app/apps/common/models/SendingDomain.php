<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SendingDomain
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

/**
 * This is the model class for table "{{sending_domain}}".
 *
 * The followings are the available columns in table '{{sending_domain}}':
 * @property integer $domain_id
 * @property integer $customer_id
 * @property string $name
 * @property string $dkim_private_key
 * @property string $dkim_public_key
 * @property string $locked
 * @property string $verified
 * @property string $signing_enabled
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class SendingDomain extends ActiveRecord
{
    // both constants are deprecated and will be removed.
    const DKIM_SELECTOR = 'mailer';
    const DKIM_FULL_SELECTOR = 'mailer._domainkey';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{sending_domain}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name', 'required'),
			array('customer_id', 'numerical', 'integerOnly' => true),
            array('customer_id', 'exist', 'className' => 'Customer'),
			array('name', 'length', 'max' => 64),
            array('name', 'match', 'pattern' => '/\w+\.\w{2,10}(\.(\w{2,10}))?/i'),
            array('dkim_private_key', 'match', 'pattern' => '/-----BEGIN\sRSA\sPRIVATE\sKEY-----(.*)-----END\sRSA\sPRIVATE\sKEY-----/sx'),
            array('dkim_public_key', 'match', 'pattern' => '/-----BEGIN\sPUBLIC\sKEY-----(.*)-----END\sPUBLIC\sKEY-----/sx'),
            array('dkim_private_key, dkim_public_key', 'length', 'max' => 10000),
			array('locked, verified, signing_enabled', 'length', 'max' => 3),
            array('locked, verified, signing_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),

			// The following rule is used by search().
			array('customer_id, name, locked, verified, signing_enabled', 'safe', 'on'=>'search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

    /**
	 * @return array scopes.
	 */
    public function scopes()
    {
        $scopes = new CMap(array(
            'verified' => array(
                'condition' => $this->getTableAlias(false, false).'.`verified` = :vf',
                'params'    => array(':vf' => self::TEXT_YES),
            ),
            'signingEnabled' => array(
                'condition' => $this->getTableAlias(false, false).'.`signing_enabled` = :se',
                'params'    => array(':se' => self::TEXT_YES),
            ),
        ));
        return CMap::mergeArray($scopes, parent::scopes());
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'domain_id'          => Yii::t('sending_domains', 'Domain'),
			'customer_id'        => Yii::t('sending_domains', 'Customer'),
			'name'               => Yii::t('sending_domains', 'Domain name'),
			'dkim_private_key'   => Yii::t('sending_domains', 'Dkim private key'),
			'dkim_public_key'    => Yii::t('sending_domains', 'Dkim public key'),
			'locked'             => Yii::t('sending_domains', 'Locked'),
			'verified'           => Yii::t('sending_domains', 'Verified'),
            'signing_enabled'    => Yii::t('sending_domains', 'DKIM Signing')
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeHelpTexts()
	{
		$labels = array(
            'customer_id'        => Yii::t('sending_domains', 'If this domain is verified in behalf of a customer, choose the customer.'),
			'name'               => Yii::t('sending_domains', 'Domain name, i.e: example.com'),
			'verified'           => Yii::t('sending_domains', 'Set this to yes only if you already have DNS records set for this domain.'),
            'locked'             => Yii::t('sending_domains', 'Whether this domain is locked and the customer cannot modify or delete it.'),
            'signing_enabled'    => Yii::t('sending_domains', 'Whether we should use DKIM to sign outgoing campaigns for this domain.'),
            'dkim_private_key'   => Yii::t('sending_domains', 'DKIM private key, leave this empty to be auto-generated. Please do not edit this record unless you really know what you are doing.'),
			'dkim_public_key'    => Yii::t('sending_domains', 'DKIM public key, leave this empty to be auto-generated. Please do not edit this record unless you really know what you are doing.'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with = array(
                    'customer' => array(
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :name',
                        'params'    => array(
                            ':name'    => '%' . $this->customer_id . '%',
                        ),
                    )
                );
            }
        }

		$criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.locked', $this->locked);
        $criteria->compare('t.verified', $this->verified);
        $criteria->compare('t.signing_enabled', $this->signing_enabled);

        $criteria->order = 't.domain_id DESC';

		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.domain_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SendingDomain the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function getIsVerified()
    {
        return $this->verified === self::TEXT_YES;
    }

    public function getIsLocked()
    {
        return $this->locked == self::TEXT_YES;
    }

    public function getSigningEnabled()
    {
        return $this->signing_enabled == self::TEXT_YES;
    }

    protected function beforeSave()
    {
        if (!$this->isNewRecord) {
            $keys  = array('name', 'dkim_private_key', 'dkim_public_key');
            $model = self::model()->findByPk($this->domain_id);
            foreach ($keys as $key) {
                if ($model->$key != $this->$key) {
                    $this->verified = self::TEXT_NO;
                    break;
                }
            }
        }
        return parent::beforeSave();
    }

    protected function afterValidate()
    {
        if (!$this->hasErrors()) {
            $this->generateDkimKeys();
        }
        parent::afterValidate();
    }

    public function getRequirementsErrors()
    {
        $errors = array();
        if (!defined('PKCS7_TEXT')) {
            $errors[] = Yii::t('sending_domains', 'OpenSSL extension missing.');
        }
        $functions = array('exec', 'escapeshellarg', 'dns_get_record', 'openssl_pkey_get_private', 'openssl_sign', 'openssl_error_string');
        foreach ($functions as $func) {
            if (!CommonHelper::functionExists($func)) {
                $errors[] = Yii::t('sending_domains', '{func} function must be enabled in order to handle the DKIM keys.', array('{func}' => $func));
            }
        }
        return $errors;
    }

    public function generateDkimKeys()
    {
        if (!empty($this->dkim_public_key) && !empty($this->dkim_private_key)) {
            return true;
        }

        $key = StringHelper::random(10);
        $publicKey   = $key . '.public';
        $privateKey  = $key . '.private';
        $tempStorage = Yii::getPathOfAlias('common.runtime.dkim');

        if ((!file_exists($tempStorage) || !is_dir($tempStorage)) && !mkdir($tempStorage, 0777)) {
            $this->addError('name', Yii::t('sending_domains', 'Unable to create {dir} directory.', array('{dir}' => $tempStorage)));
            return false;
        }
        // private key
        $line = exec(sprintf('cd %s && /usr/bin/openssl genrsa -out %s 1024', escapeshellarg($tempStorage), escapeshellarg($privateKey)), $output, $return);
        if ((int)$return != 0) {
            $fail = !empty($output) ? implode("<br />", $output) : $line;
            $this->addError('name', Yii::t('sending_domains', 'While generating the private key, exec failed with: {fail}', array(
                '{fail}' => !empty($fail) ? $fail : Yii::t('sending_domains', 'Unknown error, most probably cannot exec the openssl command!'),
            )));
            return false;
        }
        if (!is_file($tempStorage . '/' . $privateKey)) {
            $this->addError('name', Yii::t('sending_domains', 'Unable to check the private key file.'));
            return false;
        }

        // public key
        $line = exec(sprintf('cd %s && /usr/bin/openssl rsa -in %s -out %s -pubout -outform PEM', escapeshellarg($tempStorage), escapeshellarg($privateKey), escapeshellarg($publicKey)), $output, $return);
        if ((int)$return != 0) {
            $fail = !empty($output) ? implode("<br />", $output) : $line;
            $this->addError('name', Yii::t('sending_domains', 'While generating the public key, exec failed with: {fail}', array(
                '{fail}' => !empty($fail) ? $fail : Yii::t('sending_domains', 'Unknown error, most probably cannot exec the openssl command!')
            )));
            return false;
        }
        if (!is_file($tempStorage . '/' . $publicKey)) {
            $this->addError('name', Yii::t('sending_domains', 'Unable to check the public key file.'));
            return false;
        }

        $this->dkim_private_key = file_get_contents($tempStorage . '/' . $privateKey);
        $this->dkim_public_key  = file_get_contents($tempStorage . '/' . $publicKey);

        unlink($tempStorage . '/' . $privateKey);
        unlink($tempStorage . '/' . $publicKey);

        return true;
    }

    public function getCleanPublicKey()
    {
        $publicKey = str_replace(array('-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'), '', $this->dkim_public_key);
        $publicKey = trim(preg_replace('/\s+/', '', $publicKey));
        return $publicKey;
    }

    public function getDnsTxtDkimSelectorToAdd()
    {
        $record = sprintf('%s         TXT     "v=DKIM1; k=rsa; p=%s;"', self::getDkimFullSelector(), $this->getCleanPublicKey());

        // since 1.3.5.9
        $record = Yii::app()->hooks->applyFilters('sending_domain_get_dns_txt_dkim_record', $record, $this);

        return $record;
    }

    public function getDnsTxtSpfRecordToAdd()
    {
        $smtpHosts = array();
        $criteria  = new CDbCriteria();
        $criteria->select    = '`t`.`hostname`';
        $criteria->addCondition('`t`.`status` = :st AND (`t`.`customer_id` = :cid OR `t`.`customer_id` IS NULL)');
        $criteria->addInCondition('t.type', array('smtp', 'smtp-amazon'));
        $criteria->params[':st']  = DeliveryServer::STATUS_ACTIVE;
        $criteria->params[':cid'] = (int)$this->customer_id;
        $servers = DeliveryServer::model()->findAll($criteria);
        foreach ($servers as $server) {
            $smtpHosts[] = sprintf('a:%s', $server->hostname);
        }
        if (isset($_SERVER['HTTP_HOST'])) {
            $smtpHosts[] = sprintf('a:%s', $_SERVER['HTTP_HOST']);
        }
        if (isset($_SERVER['SERVER_ADDR'])) {
            $blocks = explode('.', $_SERVER['SERVER_ADDR']);
            if (count($blocks) == 4) {
                $smtpHosts[] = sprintf('ip4:%s', $_SERVER['SERVER_ADDR']);
            } else {
                $smtpHosts[] = sprintf('ip6:%s', $_SERVER['SERVER_ADDR']);
            }
        }

        $record = sprintf('%s.      IN TXT     "v=spf1 mx a ptr %s ~all"', $this->name, implode(" ", $smtpHosts));

        // since 1.3.5.9
        $record = Yii::app()->hooks->applyFilters('sending_domain_get_dns_txt_spf_record', $record, $this, $smtpHosts);

        return $record;
    }

    public function findVerifiedByEmailForCustomer($email, $customer_id)
    {
        if (!FilterVarHelper::email($email)) {
            return null;
        }

        static $domains = array();

        $parts  = explode('@', $email);
        $domain = $parts[1];

        if (isset($domains[$domain]) || array_key_exists($domain, $domains)) {
            return $domains[$domain];
        }

        $criteria = new CDbCriteria();
        $criteria->compare('t.name', $domain);
        $criteria->compare('t.verified', self::TEXT_YES);
        $criteria->compare('t.customer_id', $customer_id);

        return $domains[$domain] = self::model()->find($criteria);
    }

    public function findVerifiedByEmailForSystem($email)
    {
        if (!FilterVarHelper::email($email)) {
            return null;
        }

        static $domains = array();

        $parts  = explode('@', $email);
        $domain = $parts[1];

        if (isset($domains[$domain]) || array_key_exists($domain, $domains)) {
            return $domains[$domain];
        }

        $criteria = new CDbCriteria();
        $criteria->compare('t.name', $domain);
        $criteria->compare('t.verified', self::TEXT_YES);
        $criteria->addCondition('t.customer_id IS NULL');

        return $domains[$domain] = self::model()->find($criteria);
    }

    public function findVerifiedByEmail($email, $customer_id = null)
    {
        $domain = null;
        if ($customer_id > 0) {
            $domain = $this->findVerifiedByEmailForCustomer($email, $customer_id);
        }
        if (!$domain) {
            $domain = $this->findVerifiedByEmailForSystem($email);
        }
        return $domain;
    }

    public static function getDkimSelector()
    {
        return Yii::app()->params['email.custom.dkim.selector'];
    }

    public static function getDkimFullSelector()
    {
        return Yii::app()->params['email.custom.dkim.full_selector'];
    }
}
