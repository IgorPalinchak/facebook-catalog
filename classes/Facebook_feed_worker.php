<?php

namespace facebook_feed\classes;

use CMSFactory\assetManager;
use CMSFactory\Events;
use CMSFactory\ModuleSettings;


use FacebookAds\Object\Business;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;


use FacebookAds\Object\ProductFeed;
use FacebookAds\Object\Fields\ProductFeedFields;
use FacebookAds\Object\Fields\ProductFeedScheduleFields;

use FacebookAds\Http\Exception\RequestException;
use FacebookAds\Exception\Exception;


(defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Image CMS
 * Module Frame
 * @property Cms_base $cms_base
 * @link https://enhancedecommerce.appspot.com/
 */
class Facebook_feed_worker
{

    /**
     * facebook_feed constructor.
     */
    public static $check_event = false;
    public static $error = false;
    protected $connection;
    protected $business;
    public $facebook_catalog_id;

    public function __construct()
    {
        $this->getCheckConnection();
        $this->check_event = self::$check_event;
        $this->error = self::$error;

    }

    /**
     * @return mixed
     */
    public function getCheckConnection()
    {
        $settings_main = ModuleSettings::ofModule('facebook_feed')->get();
        if ($settings_main && $settings_main != null && $settings_main != '') {
            try {
                $this->connection = Api::init($settings_main['main_setting']['app_id'],
                    $settings_main['main_setting']['app_secret'],
                    $settings_main['main_setting']['access_token']);
                $this->connection->setLogger(new CurlLogger());
//                $connection->call();

                try {
                    $this->business = new Business((int)$settings_main['main_setting']['id']);
                    $this->business->getSelf();
                    /*хотя нет Екзепшенов на непраильный ИД бизнесс.
                    Если ввести не существующий (или какойто защищенный) - то будет
                    а если ввести ИД реальный но "не защищенный то пропускает,
                     поэтому будем еще ловить екзепшн на создание каталога по неправильному ИД"*/

                    /*костиль))*/
                    $this->business->getOwnedAdAccounts();
                    /*костиль))*/
                } catch (RequestException $e) {
                    self::$error = $e->getMessage();
                    return;
                }
            } catch (RequestException $e) {
                self::$error = $e->getMessage() . '--1';
                return;
            }
        } else {
            self::$error = 'Нет входных данных!';
            return;
        }

        self::$check_event = true;
    }

    /**
     * @return mixed
     */
    public function create_catalog($catalog_name)
    {
        $fields = array();
        $params = array(
            'name' => $catalog_name,
        );

        try {
            $ctalog_created = $this->business->createOwnedProductCatalog($fields, $params);
            $this->facebook_catalog_id = $ctalog_created->getData()['id'];
        } catch (RequestException $e) {

            self::$error = $e->getMessage();
            return;
        }
        return true;
    }

    public function check_catalog($catalog_id)
    {

        try {
            $catalod_api = new ProductCatalog($catalog_id);//94368510580149999 //943685105801400  REAL
            $existeed_catalog = $catalod_api->getSelf();
            return true;
        } catch (RequestException $e) {
            return false;

        }
    }


    public function deleteCatalog($catalog_id)
    {
        try {
            $catalod_api = new ProductCatalog($catalog_id);//94368510580149999 //943685105801400  REAL
            $existeed_catalog = $catalod_api->deleteSelf();
            return true;
        } catch (RequestException $e) {
            return false;
        }
    }

    public function upadateCatalog($catalog_id, $new_name)
    {
        $fields = array();
        $params = array(
            'name' => $new_name,
        );
        try {
            $existeed_catalog = new ProductCatalog($catalog_id);//94368510580149999 //943685105801400  REAL
            $existeed_catalog->updateSelf($fields, $params);
            $existeed_catalog->save();
            return true;
        } catch (RequestException $e) {

            return false;
        }
    }


    public function set_Up_Feed($catalog_id, $catalog_name)
    {
        try {
            $product_feed = new ProductFeed(null, $catalog_id);
            $product_feed->setData(array(
                ProductFeedFields::NAME => 'E-Shop Feed ' . $catalog_name,
                ProductFeedFields::SCHEDULE => array(
                    ProductFeedScheduleFields::INTERVAL => 'DAILY',
                    ProductFeedScheduleFields::URL => base_url() . 'facebook_feed/getXml/' . $catalog_id,
                    ProductFeedScheduleFields::HOUR => 22,
                ),
            ));
            $product_feed->save();
            $feed_id = $product_feed->getData()['id'];


            try {
            $product_feed1 = new ProductFeed($feed_id, $catalog_id);
            $product_feed1->update([
                ProductFeedFields::NAME => 'E-Shop Feed ' . $catalog_name,
                ProductFeedFields::SCHEDULE => array(
                    ProductFeedScheduleFields::INTERVAL => 'DAILY',
                    ProductFeedScheduleFields::URL => base_url() . 'facebook_feed/getXml/' . $feed_id,
                    ProductFeedScheduleFields::HOUR => 22,
                ),
            ]);
            $product_feed1->save();
            } catch (RequestException $e) {

                return false;
            }
            return $feed_id;
        } catch (RequestException $e) {

            return false;
        }
    }

    public function delete_Feed($catalog_id, $feed_id)
    {
        try {
            $product_feed = new ProductFeed($feed_id, $catalog_id);

            $product_feed->deleteSelf();
            return true;// $product_feed->getData()['id'];
        } catch (RequestException $e) {

            return false;
        }
    }


}

/* End of file sample_module.php */