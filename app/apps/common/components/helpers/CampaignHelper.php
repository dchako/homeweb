<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class CampaignHelper
{
    /**
     * CampaignHelper::parseContent()
     *
     * This should be always connected with the CampaignTemplate model class::getAvailableTags().
     * Will parse the content tags and transform them
     *
     * It is used in:
     * console/components/behaviors/CampaignSenderBehavior.php
     * frontend/controllers/CampaignsController.php
     *
     * @param string $content
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @param bool $appendBeacon
     * @return array
     */
    public static function parseContent($content, Campaign $campaign, ListSubscriber $subscriber, $appendBeacon = false)
    {
        $content    = StringHelper::decodeSurroundingTags($content);
        $list       = $campaign->list;
        $options    = Yii::app()->options;

        $searchReplace = self::getCommonTagsSearchReplace($content, $campaign, $subscriber);
        $content       = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        $content       = self::getTagFilter()->apply($content, $searchReplace);

        $to      = isset($searchReplace['[CAMPAIGN_TO_NAME]']) ? $searchReplace['[CAMPAIGN_TO_NAME]'] : null;
        $subject = isset($searchReplace['[CAMPAIGN_SUBJECT]']) ? $searchReplace['[CAMPAIGN_SUBJECT]'] : null;

        // tags with params, if any...
        $searchReplace  = array();
        if (preg_match_all('/\[([a-z_]+)([^\]]+)?\]/i', $content, $matches)) {
            $matches = array_unique($matches[0]);
            foreach ($matches as $tag) {
                if (strpos($tag, '[DATETIME') === 0) {
                    $searchReplace[$tag] = self::parseDateTimeTag($tag);
                } elseif (strpos($tag, '[DATE') === 0) {
                    $searchReplace[$tag] = self::parseDateTag($tag);
                }
            }
            if (!empty($searchReplace)) {
                $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
            }
        }
        unset($searchReplace);

        if ($appendBeacon && !empty($subscriber->subscriber_id)) {
            $beaconUrl = $options->get('system.urls.frontend_absolute_url');
            $beaconUrl .= 'campaigns/' . $campaign->campaign_uid . '/track-opening/' . $subscriber->subscriber_uid;
            $beaconImage = CHtml::image($beaconUrl, '', array('width' => 1, 'height' => 1));
            $content = str_ireplace('</body>', $beaconImage . "\n" . '</body>', $content);
        }

        return array($to, $subject, $content);
    }

    /**
     * CampaignHelper::parseByTemplateEngine()
     * 
     * @param $content
     * @param $templateVariables 
     * @return string
     */
    public static function parseByTemplateEngine($content, array $templateVariables = array())
    {
        // twig requires php >= 5.2.7
        if (version_compare(PHP_VERSION, '5.2.7', '<')) {
            return $content;
        }
        
        static $twig;
        if ($twig === null) {
            $twig = new Twig_Environment(new Twig_Loader_String());
        }
        
        try {
            $data = array();
            foreach ($templateVariables as $key => $value) {
                $data[ str_replace(array('[', ']'), '', $key) ] = $value;
            }
            $template = $twig->createTemplate($content);
            $_content = $template->render($data);
        } catch (Exception $e) {
            $_content = null;
        }
        
        return $_content ? $_content : $content;
    }

    /**
     * CampaignHelper::isTemplateEngineEnabled()
     * 
     * @return bool
     */
    public static function isTemplateEngineEnabled()
    {
        static $enabled;
        if ($enabled !== null) {
            return $enabled;
        }
        if (version_compare(PHP_VERSION, '5.2.7', '<')) {
            return $enabled = false;
        }
        return $enabled = Yii::app()->options->get('system.campaign.template_engine.enabled', 'no') == 'yes';
    }

    /**
     * CampaignHelper::transformLinksForTracking()
     *
     * @param string $content
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @param bool $canSave
     * @return string
     */
    public static function transformLinksForTracking($content, Campaign $campaign, ListSubscriber $subscriber, $canSave = false)
    {
        static $trackingUrls = array();

        $content  = StringHelper::decodeSurroundingTags($content);
        $list     = $campaign->list;
        $mustSave = false;
        $cacheKey = md5($campaign->campaign_uid . '-' . $content);

        // since 1.3.5.9
        Yii::app()->hooks->doAction('campaign_content_before_transform_links_for_tracking', $collection = new CAttributeCollection(array(
            'content'       => &$content,
            'campaign'      => $campaign,
            'subscriber'    => $subscriber,
            'list'          => $list,
            'trackingUrls'  => &$trackingUrls,
            'cacheKey'      => $cacheKey,
        )));

        if (!isset($trackingUrls[$cacheKey])) {

            $trackingUrls[$cacheKey] = array();

            $urlModelsCount = 0;
            if ($canSave) {
                $urlModelsCount = CampaignUrl::model()->countByAttributes(array(
                    'campaign_id' => $campaign->campaign_id,
                ));
            }

            $mustSave    = $urlModelsCount == 0;
            $baseUrl     = Yii::app()->options->get('system.urls.frontend_absolute_url');
            $trackingUrl = $baseUrl . 'campaigns/[CAMPAIGN_UID]/track-url/[SUBSCRIBER_UID]';

            // (\042|\047) are octal quotes.
            $pattern = '/href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)/i';
            if (!preg_match_all($pattern, $content, $matches)) {
                return $content;
            }

            $urls = $matches[5];
            $urls = array_map('trim', $urls);
            // combine url with markup
            $urls = array_combine($urls, $matches[0]);
            $foundUrls = array();

            foreach ($urls as $url => $markup) {
                
                // since 1.3.6.3
                $url = StringHelper::normalizeUrl($url);

                // external url which may contain one or more tags(sharing maybe?)
                if (preg_match('/https?.*/i', $url, $matches) && FilterVarHelper::url($url)) {
                    $_url = trim($matches[0]);
                    $foundUrls[$_url] = $markup;
                    continue;
                }

                // local tag to be transformed
                if (preg_match('/^\[([A-Z_]+)_URL\]$/', $url, $matches)) {
                    $_url = trim($matches[0]);
                    $foundUrls[$_url] = $markup;
                    continue;
                }
            }

            if (empty($foundUrls)) {
                // since 1.3.5.9
                Yii::app()->hooks->doAction('campaign_content_after_transform_links_for_tracking', $collection = new CAttributeCollection(array(
                    'content'      => &$content,
                    'campaign'     => $campaign,
                    'subscriber'   => $subscriber,
                    'list'         => $list,
                    'trackingUrls' => &$trackingUrls,
                    'cacheKey'     => $cacheKey,
                )));
                return $content;
            }

            $prefix = $campaign->campaign_uid;
            $sort   = array();

            foreach ($foundUrls as $url => $markup) {

                $urlHash = sha1($prefix . $url);
                $track   = $trackingUrl . '/' . $urlHash;
                $length  = strlen($url);

                $trackingUrls[$cacheKey][] = array(
                    'url'       => $url,
                    'hash'      => $urlHash,
                    'track'     => $track,
                    'length'    => $length,
                    'markup'    => $markup,
                );

                $sort[] = $length;
            }

            unset($foundUrls);
            // make sure we order by the longest url to the shortest
            array_multisort($sort, SORT_DESC, SORT_NUMERIC, $trackingUrls[$cacheKey]);

            // one final check
            if (!$mustSave) {
                $mustSave = count($trackingUrls[$cacheKey]) != $urlModelsCount;
            }
        }

        if (!empty($trackingUrls[$cacheKey])) {

            $searchReplace = array();
            foreach ($trackingUrls[$cacheKey] as $urlData) {
                $searchReplace[$urlData['markup']] = 'href="'.$urlData['track'].'"';
            }

            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
            unset($searchReplace);

            // save the url tags.
            if ($mustSave && $canSave) {

                foreach ($trackingUrls[$cacheKey] as $urlData) {

                    $urlModel = CampaignUrl::model()->findByAttributes(array(
                        'campaign_id' => (int)$campaign->campaign_id,
                        'hash'        => $urlData['hash'],
                    ));

                    if (!empty($urlModel)) {
                        continue;
                    }

                    $urlModel = new CampaignUrl();
                    $urlModel->campaign_id = $campaign->campaign_id;
                    $urlModel->destination = $urlData['url'];
                    $urlModel->hash = $urlData['hash'];
                    $urlModel->save(false);
                }
            }
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('campaign_content_after_transform_links_for_tracking', $collection = new CAttributeCollection(array(
            'content'      => &$content,
            'campaign'     => $campaign,
            'subscriber'   => $subscriber,
            'list'         => $list,
            'trackingUrls' => &$trackingUrls,
            'cacheKey'     => $cacheKey,
        )));

        // return transformed
        return $content;
    }

    /**
     * CampaignHelper::htmlToText()
     *
     * @param string $content
     * @return string
     */
    public static function htmlToText($content)
    {
        static $html2text;

        if ($html2text === null) {
            Yii::import('common.vendors.Html2Text.*');
            $html2text = new Html2Text();

            if (!MW_IS_CLI) {
                $appName = Yii::app()->apps->getCurrentAppName();
                $options = Yii::app()->options;
                $html2text->set_base_url($options->get('system.urls.'.$appName.'_absolute_url'));
            }
        }

        $html2text->set_html($content);

        return $html2text->get_text();
    }

    /**
     * CampaignHelper::getSpamScore()
     *
     * @param Campaign $campaign
     * @return mixed
     */
    public static function getSpamScore(Campaign $campaign)
    {
        if (empty($campaign->template) || empty($campaign->template->content)) {
            return false;
        }

        if (!CommonHelper::functionExists('curl_init')) {
            return false;
        }

        if (!($server = DeliveryServer::pickServer())) {
            return false;
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'   => $campaign->list->list_id,
            'status'    => ListSubscriber::STATUS_CONFIRMED,
        ));

        $fromName       = $campaign->from_name;
        $toName         = $campaign->from_name;
        $emailSubject   = $campaign->subject;
        $emailContent   = $campaign->template->content;
        $emailAddress   = $campaign->reply_to;

        if (!empty($subscriber)) {

            if (!empty($campaign->option) && $campaign->option->xml_feed == CampaignOption::TEXT_YES) {
                $emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign);
            }

            if (!empty($campaign->option) && $campaign->option->json_feed == CampaignOption::TEXT_YES) {
                $emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign);
            }

            $emailData = self::parseContent($emailContent, $campaign, $subscriber, false);
            list(, $emailSubject, $emailContent) = $emailData;
        }

        $emailPlainText = null;
        if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) {
            $emailPlainText = self::htmlToText($emailContent);
        }

        $emailParams = array(
            'from'      => array($server->getFromEmail() => $campaign->from_name),
            'replyTo'   => array($campaign->reply_to => $campaign->from_name),
            'to'        => array($emailAddress => $toName),
            'subject'   => $emailSubject,
            'body'      => $emailContent,
            'plainText' => $emailPlainText,
        );

        $headers = array(
			'Accept: application/json',
			'Content-Type: application/json'
		);

        $encodedData = CJSON::encode(array(
            'email'     => Yii::app()->mailer->getEmailMessage($server->getParamsArray($emailParams)),
            'options'   => 'short'
        ));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://spamcheck.postmarkapp.com/filter');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_error($ch) != '') {
            return false;
        }

        return CJSON::decode($response);
    }

    /**
     * CampaignHelper::minifyContent()
     *
     * @param string $content
     * @return string $content
     */
    public static function minifyContent($content)
    {
        return EmailTemplateParser::minifyContent($content);
    }

    /**
     * CampaignHelper::embedContentImages()
     *
     * @param string $content
     * @param Campaign $campaign
     * @return mixed
     */
    public static function embedContentImages($content, Campaign $campaign)
    {
        if (empty($content)) {
            return array($content, array());
        }

        static $parsed = array();
        $key = sha1($campaign->campaign_uid . $content);

        if (isset($parsed[$key]) || array_key_exists($key, $parsed)) {
            return $parsed[$key];
        }

        if (!CommonHelper::functionExists('qp')) {
            require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        }

        $embedImages = array();
        $storagePath = Yii::getPathOfAlias('root.frontend.assets');
        $extensions  = array('jpg', 'jpeg', 'png', 'gif');

        libxml_use_internal_errors(true);

        try {

            $query = qp($content, 'body', array(
                'ignore_parser_warnings'    => true,
                'convert_to_encoding'       => Yii::app()->charset,
                'convert_from_encoding'     => Yii::app()->charset,
                'use_parser'                => 'html',
            ));

            // to do: what action should we take here?
            if (count(libxml_get_errors()) > 0) {}

            $images = $query->top()->find('img');

            if (empty($images) || !is_object($images) || $images->length == 0) {
                throw new Exception('No images found!');
            }

            foreach ($images as $image) {
                $src = urldecode($image->attr('src'));
                $src = str_replace(array('../', './', '..\\', '.\\', '..'), '', trim($src));

                if (empty($src)) {
                    continue;
                }

                $ext = pathinfo($src, PATHINFO_EXTENSION);
                if (empty($ext) || !in_array(strtolower($ext), $extensions)) {
                    continue;
                }
                unset($ext);

                if (preg_match('/\/frontend\/assets(\/gallery\/([a-zA-Z0-9]{13,})\/.*)/', $src, $matches)) {
                    $src = $matches[1];
                } elseif (preg_match('/\/frontend\/assets(\/files\/(customer|user)\/([a-zA-Z0-9]{13,})\/.*)/', $src, $matches)) {
                    $src = $matches[1];
                }

                if (preg_match('/^https?/i', $src)) {
                    continue;
                }

                $fullFilePath = $storagePath . '/' . $src;
                if (!is_file($fullFilePath)) {
                    continue;
                }

                $imageInfo = @getimagesize($fullFilePath);
                if (empty($imageInfo[0]) || empty($imageInfo[1]) || empty($imageInfo['mime'])) {
                    continue;
                }

                $cid = sha1($fullFilePath);
                $embedImages[] = array(
                    'name'  => basename($fullFilePath),
                    'path'  => $fullFilePath,
                    'cid'   => $cid,
                    'mime'  => $imageInfo['mime'],
                );

                $image->attr('src', 'cid:' . $cid);
                unset($fullFilePath, $cid, $imageInfo);
            }

            $content = $query->top()->html();
            unset($query, $images);

        } catch (Exception $e) {}

        libxml_use_internal_errors(false);
        return $parsed[$key] = array($content, $embedImages);
    }

    public static function extractTemplateUrls($content)
    {
        if (empty($content)) {
            return array();
        }

        static $urls = array();
        $hash = sha1($content);

        if (array_key_exists($hash, $urls)) {
            return $urls[$hash];
        }

        $urls[$hash] = array();
        if (!CommonHelper::functionExists('qp')) {
            require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        }

        libxml_use_internal_errors(true);

        try {

            $query = qp($content, 'body', array(
                'ignore_parser_warnings'    => true,
                'convert_to_encoding'       => Yii::app()->charset,
                'convert_from_encoding'     => Yii::app()->charset,
                'use_parser'                => 'html',
            ));

            // to do: what action should we take here?
            if (count(libxml_get_errors()) > 0) {}

            $anchors = $query->top()->find('a');

            if (empty($anchors) || !is_object($anchors) || $anchors->length == 0) {
                throw new Exception('No anchor found!');
            }

            foreach ($anchors as $anchor) {
                $urls[$hash][] = StringHelper::normalizeUrl(trim($anchor->attr('href')));
            }

            unset($query, $anchors);

        } catch (Exception $e) {}

        libxml_use_internal_errors(false);

        $urls[$hash] = array_unique($urls[$hash]);

        // remove tag urls
        foreach ($urls[$hash] as $index => $url) {
            if (empty($url) || !FilterVarHelper::url($url)) {
                unset($urls[$hash][$index]);
            }
        }

        sort($urls[$hash]);

        return $urls[$hash];
    }

    /**
     * @param $listId
     * @return mixed
     */
    public static function getListFields($listId)
    {
        return ListField::getAllByListId($listId);
    }

    /**
     * @param $content
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @return array
     */
    public static function getSubscriberFieldsSearchReplace($content, Campaign $campaign, ListSubscriber $subscriber)
    {
        // since 1.3.6.2
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_ENABLE_SUBSCRIBER_FIELD_CACHE) {
            return $subscriber->getAllCustomFieldsWithValues();
        }
        
        static $cacheTags = array();
        $cacheKey = md5($content . '-' . $campaign->campaign_uid . '-' . $subscriber->subscriber_uid);

        if (!empty($content) && isset($cacheTags[$cacheKey])) {
            return $cacheTags[$cacheKey];
        }
        
        $searchReplace = array();
        $list = $campaign->list;
        foreach (self::getListFields($list->list_id) as $field) {
            $tag = '['.$field['tag'];
            if (!self::isTemplateEngineEnabled() && strpos($content, $tag) === false &&
                strpos($campaign->subject, $tag) === false &&
                strpos($campaign->to_name, $tag) === false &&
                strpos($campaign->from_name, $tag) === false &&
                strpos($campaign->from_email, $tag) === false) {
                continue;
            }
            $tag .= ']';

            $values = Yii::app()->getDb()->createCommand()
                ->select('value')
                ->from('{{list_field_value}}')
                ->where('subscriber_id = :sid AND field_id = :fid', array(
                    ':sid' => (int)$subscriber->subscriber_id, 
                    ':fid' => (int)$field['field_id']
                ))
                ->queryAll();

            $value = array();
            foreach ($values as $val) {
                $value[] = $val['value'];
            }
            $searchReplace[$tag] = implode(', ', $value);
        }

        if (!empty($content)) {
            $cacheTags[$cacheKey] = $searchReplace;
        }

        return $searchReplace;
        
    }

    public static function getCommonTagsSearchReplace($content, Campaign $campaign, ListSubscriber $subscriber = null)
    {
        $cacheKey = md5($content . $campaign->campaign_uid . (!empty($subscriber->subscriber_uid) ? $subscriber->subscriber_uid : ''));
        static $cacheTags = array();

        if (!empty($content) && isset($cacheTags[$cacheKey])) {
            return $cacheTags[$cacheKey];
        }

        $list          = $campaign->list;
        $searchReplace = array();
        $options       = Yii::app()->options;

        if (!empty($subscriber) && !empty($subscriber->subscriber_id)) {
            $searchReplace = self::getSubscriberFieldsSearchReplace($content, $campaign, $subscriber);
        }

        $searchReplace['[LIST_NAME]']        = $list->display_name;
        $searchReplace['[LIST_DESCRIPTION]'] = $list->description;
        $searchReplace['[LIST_FROM_NAME]']   = $list->default->from_name;
        $searchReplace['[LIST_FROM_EMAIL]']  = $list->default->from_email;
        $searchReplace['[LIST_SUBJECT]']     = $list->default->subject;

        $searchReplace['[CURRENT_YEAR]']              = date('Y');
        $searchReplace['[CURRENT_MONTH]']             = date('m');
        $searchReplace['[CURRENT_DAY]']               = date('d');
        $searchReplace['[CURRENT_DATE]']              = date('m/d/Y');
        $searchReplace['[CURRENT_MONTH_FULL_NAME]']   = date('F');

        // company
        $searchReplace['[COMPANY_FULL_ADDRESS]'] = !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null;
        $company = $list->company;
        $searchReplace['[COMPANY_NAME]']        = $company->name;
        $searchReplace['[COMPANY_WEBSITE]']     = $company->website;
        $searchReplace['[COMPANY_ADDRESS_1]']   = $company->address_1;
        $searchReplace['[COMPANY_ADDRESS_2]']   = $company->address_2;
        $searchReplace['[COMPANY_CITY]']        = $company->city;
        $searchReplace['[COMPANY_ZONE]']        = !empty($company->zone) ? $company->zone->name : null;
        $searchReplace['[COMPANY_ZIP]']         = $company->zip_code;
        $searchReplace['[COMPANY_COUNTRY]']     = !empty($company->country) ? $company->country->name : null;
        $searchReplace['[COMPANY_PHONE]']       = $company->phone;

        // campaign
        $searchReplace['[CAMPAIGN_NAME]']             = $campaign->name;
        $searchReplace['[CAMPAIGN_FROM_NAME]']        = $campaign->from_name;
        $searchReplace['[CAMPAIGN_FROM_EMAIL]']       = $campaign->from_email;
        $searchReplace['[CAMPAIGN_REPLY_TO]']         = $campaign->reply_to;
        $searchReplace['[CAMPAIGN_UID]']              = $campaign->campaign_uid;
        $searchReplace['[CAMPAIGN_REPORT_ABUSE_URL]'] = '';
        $searchReplace['[CAMPAIGN_SEND_AT]']          = $campaign->send_at;
        $searchReplace['[CAMPAIGN_STARTED_AT]']       = $campaign->started_at;
        $searchReplace['[CAMPAIGN_DATE_ADDED]']       = $campaign->date_added;

        $campaignUrl      = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe';
        $forwardFriendUrl = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid . '/forward-friend';
        $updateProfileUrl = null;
        $webVersionUrl    = null;

        if (!empty($subscriber) && !empty($subscriber->subscriber_id)) {
            $unsubscribeUrl   .= '/' . $subscriber->subscriber_uid . '/' . $campaign->campaign_uid;
            $forwardFriendUrl .= '/' . $subscriber->subscriber_uid;
            $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
            $webVersionUrl    = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid . '/web-version/' . $subscriber->subscriber_uid;

            $searchReplace['[SUBSCRIBER_UID]']                  = $subscriber->subscriber_uid;
            $searchReplace['[SUBSCRIBER_IP]']                   = $subscriber->ip_address;
            $searchReplace['[SUBSCRIBER_DATE_ADDED]']           = $subscriber->date_added;
            $searchReplace['[SUBSCRIBER_DATE_ADDED_LOCALIZED]'] = $subscriber->dateAdded;
            $searchReplace['[CAMPAIGN_REPORT_ABUSE_URL]']       = $campaignUrl . '/report-abuse/' . $list->list_uid . '/' . $subscriber->subscriber_uid;
        }

        $searchReplace['[CURRENT_DOMAIN]']     = parse_url($options->get('system.urls.frontend_absolute_url'), PHP_URL_HOST);
        $searchReplace['[CURRENT_DOMAIN_URL]'] = $options->get('system.urls.frontend_absolute_url');

        // other urls
        $searchReplace['[UNSUBSCRIBE_URL]']         = $unsubscribeUrl;
        $searchReplace['[UNSUBSCRIBE_LINK]']        = CHtml::link(Yii::t('campaigns', 'Unsubscribe'), $unsubscribeUrl);
        $searchReplace['[UPDATE_PROFILE_URL]']      = $updateProfileUrl;
        $searchReplace['[WEB_VERSION_URL]']         = $webVersionUrl;
        $searchReplace['[CAMPAIGN_URL]']            = $campaignUrl;
        $searchReplace['[FORWARD_FRIEND_URL]']      = $forwardFriendUrl;
        $searchReplace['[DIRECT_UNSUBSCRIBE_URL]']  = $unsubscribeUrl . '/unsubscribe-direct';
        $searchReplace['[DIRECT_UNSUBSCRIBE_LINK]'] = CHtml::link(Yii::t('campaigns', 'Unsubscribe'), $unsubscribeUrl . '/unsubscribe-direct');

        // since 1.3.5, rotate content randomly
        if (strpos($content, '[RANDOM_CONTENT') !== false && preg_match_all('/\[RANDOM_CONTENT:([^\]]+)\]/', $content, $matches)) {
            foreach ($matches[0] as $index => $tag) {
                if (!isset($matches[1]) || !isset($matches[1][$index])) {
                    continue;
                }
                $tagValue = explode('|', $matches[1][$index]);
                $randKey  = array_rand($tagValue);
                $searchReplace[$tag] = $tagValue[$randKey];
            }
        }
        //

        $to  = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->to_name);
        $to  = self::getTagFilter()->apply($to, $searchReplace);
        if (empty($to) && !empty($subscriber) && !empty($subscriber->subscriber_id)) {
            $to = $subscriber->email;
        }
        if (empty($to)) {
            $to = 'unknown';
        }
        $searchReplace['[CAMPAIGN_TO_NAME]'] = $to;

        $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->subject);
        $subject = self::getTagFilter()->apply($subject, $searchReplace);
        if (empty($subject)) {
            $subject = 'unknown';
        }

        // since 1.3.5, rotate content randomly
        if (strpos($subject, '[RANDOM_CONTENT') !== false && preg_match_all('/\[RANDOM_CONTENT:([^\]]+)\]/', $subject, $matches)) {
            foreach ($matches[0] as $index => $tag) {
                if (!isset($matches[1]) || !isset($matches[1][$index])) {
                    continue;
                }
                $tagValue = explode('|', $matches[1][$index]);
                $randKey  = array_rand($tagValue);
                $subject  = str_replace($tag, $tagValue[$randKey], $subject);
            }
        }
        //

        $searchReplace['[CAMPAIGN_SUBJECT]'] = $subject;

        // since 1.3.5.9
        static $customerCampaignTags = array();
        if (!empty($campaign->customer_id) && strpos($content, '[' . CustomerCampaignTag::getTagPrefix()) !== false) {
            if (!isset($customerCampaignTags[$campaign->customer_id])) {
                $customerCampaignTags[$campaign->customer_id] = array();
                $criteria = new CDbCriteria();
                $criteria->select = 'tag, content, random';
                $criteria->compare('customer_id', (int)$campaign->customer_id);
                $criteria->limit = 100;
                $models = CustomerCampaignTag::model()->findAll($criteria);
                foreach ($models as $model) {
                    $customerCampaignTags[$campaign->customer_id][] = $model->getAttributes(array('tag', 'content', 'random'));
                }
                unset($models);
            }
            foreach ($customerCampaignTags[$campaign->customer_id] as $ccTag) {
                $ccTagName = '[' . CustomerCampaignTag::getTagPrefix() . $ccTag['tag'] . ']';
                $tagContent = $ccTag['content'];
                if ($ccTag['random'] == CustomerCampaignTag::TEXT_YES) {
                    $contentRandom = explode("\n", $tagContent);
                    $tagContent = $contentRandom[array_rand($contentRandom)];
                    unset($contentRandom);
                }
                if (strpos($tagContent, '[') !== false && strpos($tagContent, ']') !== false) {
                    $tagContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $tagContent);
                }
                $searchReplace[$ccTagName] = $tagContent;
            }
        }
        //

        $searchReplace = (array)Yii::app()->hooks->applyFilters('campaigns_get_common_tags_search_replace', $searchReplace);

        if (!empty($content)) {
            $cacheTags[$cacheKey] = $searchReplace;
        }

        return $searchReplace;
    }

    public static function getTagFilter()
    {
        static $tagFilter;
        if ($tagFilter === null) {
            $tagFilter = new EmailTemplateTagFilter();
        }
        return $tagFilter;
    }

    public static function injectEmailFooter($emailContent, $emailFooter, Campaign $campaign)
    {
        return str_ireplace('</body>', $emailFooter . "\n" . '</body>', $emailContent);
    }

    public static function parseDateTag($tag)
    {
        $params = array_merge(array(
            'FORMAT' => 'Y-m-d',
        ), StringHelper::getTagParams($tag));
        return @date($params['FORMAT']);
    }

    public static function parseDateTimeTag($tag)
    {
        $params = array_merge(array(
            'FORMAT' => 'Y-m-d H:i:s',
        ), StringHelper::getTagParams($tag));
        return @date($params['FORMAT']);
    }

    public static function injectGoogleUtmTagsIntoTemplate($content, $pattern)
    {
        $pattern = trim($pattern, '?&');
        $pattern = str_replace(array('&utm;', '&amp;', ';'), array('&utm', '&', ''), $pattern);

        $patternArray = array();
        parse_str($pattern, $patternArray);
        if (empty($patternArray)) {
            return $content;
        }

        if (!CommonHelper::functionExists('qp')) {
            require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        }

        libxml_use_internal_errors(true);

        try {

            $query = qp(CHtml::decode(urldecode($content)), 'body', array(
                'ignore_parser_warnings'    => true,
                'convert_to_encoding'       => Yii::app()->charset,
                'convert_from_encoding'     => Yii::app()->charset,
                'use_parser'                => 'html',
            ));

            // to do: what action should we take here?
            if (count(libxml_get_errors()) > 0) {}

            $anchors = $query->top()->find('a');

            if (empty($anchors) || !is_object($anchors) || $anchors->length == 0) {
                throw new Exception('No anchor found!');
            }

            foreach ($anchors as $anchor) {
                if (!($href = $anchor->attr('href'))) {
                    continue;
                }

                $href  = rtrim(trim(urldecode($href), '?&'), '/');
                $title = trim($anchor->attr('title'));

                //skip url tags
                if (preg_match('/^\[([A-Z_]+)_URL\]$/', $href)) {
                    continue;
                }

                if (!($parsedQueryString = parse_url($href, PHP_URL_QUERY))) {
                    $queryString = urldecode(http_build_query($patternArray, '', '&'));
                    if (!empty($title)) {
                        $queryString = str_replace('[TITLE_ATTR]', $title, $queryString);
                    }
                    $anchor->attr('href',  $href . '?' . $queryString);
                    continue;
                }

                $parsedUrlQueryArray = array();
                parse_str($parsedQueryString, $parsedUrlQueryArray);
                if (empty($parsedUrlQueryArray)) {
                    continue;
                }

                $href = str_replace($parsedQueryString, '[QS]', $href);
                $_patternArray = CMap::mergeArray($parsedUrlQueryArray, $patternArray);
                $queryString   = urldecode(http_build_query($_patternArray, '', '&'));
                if (!empty($title)) {
                    $queryString = str_replace('[TITLE_ATTR]', $title, $queryString);
                }
                $anchor->attr('href', str_replace('[QS]', $queryString, $href));
            }

            $content = CHtml::decode(urldecode($query->top()->html()));

            unset($anchors, $query);

        } catch (Exception $e) {}

        libxml_use_internal_errors(false);

        return $content;
    }
}
