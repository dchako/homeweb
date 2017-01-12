<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Frontend application main configuration file
 * 
 * This file should not be altered in any way!
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

return array(
    'basePath'          => Yii::getPathOfAlias('frontend'),
    'defaultController' => 'site', 
    
    'preload' => array(
        'frontendSystemInit'
    ),
    
    // autoloading model and component classes
    'import' => array(
        'frontend.components.*',
        'frontend.components.db.*',
        'frontend.components.db.ar.*',
        'frontend.components.db.behaviors.*',
        'customer.components.field-builder.*',
        'frontend.components.utils.*',
        'frontend.components.web.*',
        'frontend.components.web.auth.*',
        'frontend.models.*',   
    ),
    
    'components' => array(
        
        'request' => array( 
            'class'                   => 'frontend.components.web.FrontendHttpRequest',
            'noCsrfValidationRoutes'  => array('lists/*', 'dswh/*'),
        ),
        
        'urlManager' => array(
            'rules' => array(
                array('site/index', 'pattern' => ''),
                
                array('lists/subscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-subscribe/<subscriber_uid:([a-z0-9]+)>/<do:([a-z0-9\_\-]+)>'),
                array('lists/subscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-subscribe/<subscriber_uid:([a-z0-9]+)>'),
                
                array('lists/unsubscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-unsubscribe/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>'),
                array('lists/unsubscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-unsubscribe/<subscriber_uid:([a-z0-9]+)>'),
                
                array('lists/update_profile', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/update-profile/<subscriber_uid:([a-z0-9]+)>'),
                array('lists/subscribe_pending', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/pending-subscribe'),
                
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>/<type:(unsubscribe\-([a-z]+))>'),
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>'),
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>'),
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>/<type:(unsubscribe\-([a-z]+))>'),
                
                array('lists/subscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribe/<subscriber_uid:([a-z0-9]+)>'),
                array('lists/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/<action>'),
                
                array('campaigns/web_version', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/web-version/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns/track_opening', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-opening/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns/track_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-url/<subscriber_uid:([a-z0-9]+)>/<hash:([a-z0-9]+)>'),
                array('campaigns/web_version', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>'),
                array('campaigns/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/forward-friend/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/forward-friend'),
                array('campaigns/report_abuse', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/report-abuse/<list_uid:([a-z0-9]+)>/<subscriber_uid:([a-z0-9]+)>'),
                
                array('articles/index', 'pattern' => 'articles/page/<page:(\d+)>'),
                array('articles/index', 'pattern' => 'articles'),
                array('articles/category', 'pattern' => 'articles/<slug:(.*)>'),
                array('articles/view', 'pattern' => 'article/<slug:(.*)>'),
                
                array('dswh/index', 'pattern' => 'dswh/<id:([0-9]+)>'),
            ),
        ),
        
        'assetManager' => array(
            'basePath'  => Yii::getPathOfAlias('root.frontend.assets.cache'),
            'baseUrl'   => AppInitHelper::getBaseUrl('frontend/assets/cache')
        ),
        
        'themeManager' => array(
            'class'     => 'common.components.managers.ThemeManager',
            'basePath'  => Yii::getPathOfAlias('root.frontend.themes'),
            'baseUrl'   => AppInitHelper::getBaseUrl('frontend/themes'),
        ),
        
        'errorHandler' => array(
            'errorAction'   => 'site/error',
        ),
		
        'session' => array(
            'class'             => 'system.web.CDbHttpSession',
            'connectionID'      => 'db',
            'sessionName'       => 'mwsid',
            'timeout'           => 7200,
            'sessionTableName'  => '{{session}}',
            'cookieParams'      => array(
                'httponly'      => true,
            ),
        ),
        
        'user' => array(
            'class'             => 'backend.components.web.auth.WebUser',
            'allowAutoLogin'    => true,
            'authTimeout'       => 7200,
            'identityCookie'    => array(
                'httpOnly'      => true, 
            )
        ),
        
        'customer' => array(
            'class'             => 'customer.components.web.auth.WebCustomer',
            'allowAutoLogin'    => true,
            'authTimeout'       => 7200,
            'identityCookie'    => array(
                'httpOnly'      => true, 
            )
        ),

        'frontendSystemInit' => array(
            'class' => 'frontend.components.init.FrontendSystemInit',
        ),
    ),
    
    'modules' => array(),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(),
);