<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AccountController
 * 
 * Handles the actions for account related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */
 
class AccountController extends Controller
{
    /**
     * Default action, allowing to update the account
     */
    public function actionIndex()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $user       = Yii::app()->user->getModel();
        $user->confirm_email = $user->email;
        
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($user->modelName, array()))) {
            $user->attributes = $attributes;
            if (!$user->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }
            
            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'user'      => $user,
            )));
            
            if ($collection->success) {
                $this->redirect(array('account/index'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Update account'),
            'pageHeading'       => Yii::t('users', 'Update account'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('users', 'Update account'),
            )
        ));

        $this->render('index', compact('user'));
    }
    
    /**
     * Log the user out from the application
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->user->loginUrl);    
    }
}