<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');
use CMSFactory\ModuleSettings;
use FacebookAds\Object\Business;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;

use FacebookAds\Http\Exception\RequestException;


use facebook_feed\classes\Facebook_feed_worker;

/**
 * Image CMS
 * Sample Module Admin
 */
class Admin extends BaseAdminController
{

    public function __construct()
    {
        parent::__construct();
        require __DIR__ . '/vendor/autoload.php';
        $lang = new MY_Lang();
        $lang->load('facebook_feed');
        $this->load->model('facebook_feed_model');
    }

    public function index()
    {
        $error = false;
        $settings_main = ModuleSettings::ofModule('facebook_feed')->get();
        if ($settings_main && $settings_main != null && $settings_main != '') {
            try {
                $connection = Api::init($settings_main['main_setting']['app_id'],
                    $settings_main['main_setting']['app_secret'],
                    $settings_main['main_setting']['access_token']);
                $connection->setLogger(new CurlLogger());
//                $connection->call();

                try {
                    $business = new Business((int)$settings_main['main_setting']['id']);
                    $business->getSelf();
                    /*хотя нет Екзепшенов на непраильный ИД бизнесс.
                    Если ввести не существующий (или какойто защищенный) - то будет
                    а если ввести ИД реальный но "не защищенный то пропускает,
                     поэтому будем еще ловить екзепшн на создание каталога по неправильному ИД"*/

                    /*костиль))*/
                    $business->getOwnedAdAccounts();
                    /*костиль))*/
                } catch (RequestException $e) {

                    $error = $e->getMessage();

                }
            } catch (RequestException $e) {
                $error = $e->getMessage() . '--1';
            }
        } else {
            $error = 'Нет входных данных!';
        }

        \CMSFactory\assetManager::create()
            ->setData('error', $error)
            ->setData('settings', $settings_main['main_setting'])
            ->registerScript('sets', TRUE)
            ->registerStyle('style', TRUE)
            ->renderAdmin('settings');
    }


    public function save_main_settings()
    {
        if ($this->input->post('main_setting')) {

            if (ModuleSettings::ofModule('facebook_feed')->set(
                ['main_setting' => $this->input->post('main_setting'),
                ])
            ) {
                showMessage(lang('Settings saved', 'facebook_feed'), lang('Message', 'facebook_feed'));
            }
            $this->cache->delete_all();
        }
    }

    public function facebook_catalogs()
    {
        $error = false;
        $settings_main = ModuleSettings::ofModule('facebook_feed')->get();

        if ($settings_main && $settings_main != null && $settings_main != '') {
            $shop_categories = SCategoryQuery::create()->getTree(0, SCategoryQuery::create()->joinWithI18n(\MY_Controller::defaultLocale()))->getCollection();

            $all_catalogs = $this->facebook_feed_model->getAllCatalogs();

            if (count($all_catalogs) > 0) {
                $check_facebook_connect = new Facebook_feed_worker();
                if ($check_facebook_connect::$check_event) {

                } else {
                    $error = $check_facebook_connect::$error;
                    $connect_error = true;
                }

                foreach ($all_catalogs as $id_shop => $catalogs) {

                    $full_catalog_data[$id_shop] = $catalogs;
                    $full_catalog_data[$id_shop]['categories'] = json_decode($catalogs['categories']);
                    $full_catalog_data[$id_shop]['exist_in_facebook'] = $connect_error ?
                        false : $check_facebook_connect->check_catalog($catalogs['catalog_id']);
                }
            }
        }
        \CMSFactory\assetManager::create()
            ->setData('error', $error)
            ->setData('all_catalogs', $full_catalog_data)
            ->setData('shop_categories', $shop_categories)
            ->setData('settings', $settings_main['main_setting'])
            ->registerScript('script_prov', TRUE)
            ->registerStyle('style', TRUE)
            ->renderAdmin('catalogs');
    }

    public function addCatalog()
    {

        $check_facebook_connect = new Facebook_feed_worker();
        if ($check_facebook_connect::$check_event) {
            if ($this->input->post('variable') && $this->input->post('variableCatsEdit')) {
                $created_catalog = $check_facebook_connect->create_catalog($this->input->post('variable'));

                if ($created_catalog) {
                    $this->facebook_feed_model->addCatalog($this->input->post('variable'), $check_facebook_connect->facebook_catalog_id, $this->input->post('variableCatsEdit'));
                    showMessage(lang('Success: ', 'facebook_feed'), lang('Message', 'facebook_feed'));
                    return true;

                } else {
                    showMessage(lang('Error: ', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                    return false;
                }

            } else {
                showMessage(lang('Error', 'facebook_feed'), lang('Message', 'facebook_feed'), 'r');
                return false;
            }
        } else {
            showMessage(lang('Error: ', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
            return false;
        }
    }

    public function deleteCatalog()
    {
        if ($this->input->post('variable')) {
            $catalog_shop_id = $this->input->post('variable');
            $check_facebook_connect = new Facebook_feed_worker();

            if ($check_facebook_connect::$check_event) {

            } else {
                $error = $check_facebook_connect::$error;
                $connect_error = true;
                showMessage(lang('Error: Не возможно проверить аккаунт', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                return false;
            }

            $all_catalogs = $this->facebook_feed_model->getAllCatalogs();
            foreach ($all_catalogs as $id_shop => $catalogs) {
                $full_catalog_data[$id_shop] = $catalogs;
                $full_catalog_data[$id_shop]['categories'] = json_decode($catalogs['categories']);
                $full_catalog_data[$id_shop]['exist_in_facebook'] = $connect_error ?
                    false : $check_facebook_connect->check_catalog($catalogs['catalog_id']);
            }
            if ($full_catalog_data[$catalog_shop_id] && !empty($full_catalog_data[$catalog_shop_id])) {
                if ($full_catalog_data[$catalog_shop_id]['exist_in_facebook']) {
                    $deleted = $check_facebook_connect->deleteCatalog($full_catalog_data[$catalog_shop_id]['catalog_id']);

                    if (!$deleted) {
                        showMessage(lang('Error: Не возможно удалить  каталог на FaceBook', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                        return false;
                    }
                }

                return $this->facebook_feed_model->deleteCatalog($this->input->post('variable'));
            }

            $this->cache->delete_all();
        } else {
            return false;
        }
    }

    public function activateCatalog()
    {

        if ($this->input->post('variable') && ($this->input->post('active_cat') || $this->input->post('active_cat') == '0')) {

            $catalog_shop_id = $this->input->post('variable');
            $check_facebook_connect = new Facebook_feed_worker();

            if ($check_facebook_connect::$check_event) {

            } else {
                $error = $check_facebook_connect::$error;
                $connect_error = true;
                showMessage(lang('Error: Не возможно проверить аккаунт', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                return false;
            }

            $all_catalogs = $this->facebook_feed_model->getAllCatalogs();
            foreach ($all_catalogs as $id_shop => $catalogs) {
                $full_catalog_data[$id_shop] = $catalogs;
                $full_catalog_data[$id_shop]['categories'] = json_decode($catalogs['categories']);
                $full_catalog_data[$id_shop]['exist_in_facebook'] = $connect_error ?
                    false : $check_facebook_connect->check_catalog($catalogs['catalog_id']);
            }

            if ($this->input->post('active_cat') == '1') {

                if ($full_catalog_data[$catalog_shop_id] && !empty($full_catalog_data[$catalog_shop_id])) {

                    if ($full_catalog_data[$catalog_shop_id]['exist_in_facebook']) {

                        $created_feed = $check_facebook_connect->set_Up_Feed($full_catalog_data[$catalog_shop_id]['catalog_id'], $full_catalog_data[$catalog_shop_id]['catalog_name']);

                        if (!$created_feed) {
                            showMessage(lang('Error: Не возможно установить фид для каталога на FaceBook', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                            return false;
                        } else {
                            return $this->facebook_feed_model->setActiveCatalog($this->input->post('variable'), '1', $created_feed);

                        }
                    }else{
                        showMessage(lang('Error: Каталог не найден на FaceBook', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                   }
                }
            } elseif ($this->input->post('active_cat') == 0) {

                $deleted_feed = $check_facebook_connect->delete_Feed($full_catalog_data[$catalog_shop_id]['catalog_id'], $full_catalog_data[$catalog_shop_id]['feed_id']);

                if (!$deleted_feed) {
                    showMessage(lang('Error: ННе возможно установить фид для каталога на FaceBook', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                    return false;
                }
                return $this->facebook_feed_model->setActiveCatalog($this->input->post('variable'), '0');
            }
//            return $this->facebook_feed_model->setActiveCatalog($this->input->post('variable'), $this->input->post('active_cat'));

        } else {
            return false;
        }
    }


    public function updateCatalog()
    {
        if ($this->input->post('id_catalog_shop') && $this->input->post('variable') && $this->input->post('shop_cats')) {
            $catalog_shop_id = $this->input->post('id_catalog_shop');
            $check_facebook_connect = new Facebook_feed_worker();

            if ($check_facebook_connect::$check_event) {

            } else {
                $error = $check_facebook_connect::$error;
                $connect_error = true;
                showMessage(lang('Error: Не возможно проверить аккаунт', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
                return false;
            }

            $all_catalogs = $this->facebook_feed_model->getAllCatalogs();
            foreach ($all_catalogs as $id_shop => $catalogs) {
                $full_catalog_data[$id_shop] = $catalogs;
                $full_catalog_data[$id_shop]['categories'] = json_decode($catalogs['categories']);
                $full_catalog_data[$id_shop]['exist_in_facebook'] = $connect_error ?
                    false : $check_facebook_connect->check_catalog($catalogs['catalog_id']);
            }


            if ($full_catalog_data[$catalog_shop_id] && !empty($full_catalog_data[$catalog_shop_id])) {

                if ($full_catalog_data[$catalog_shop_id]['exist_in_facebook']) {

                    $updated_catalog = $check_facebook_connect->upadateCatalog($full_catalog_data[$catalog_shop_id]['catalog_id'], $this->input->post('variable'));

                    if (!$updated_catalog) {
                        showMessage(lang('Error: Не возможно установить фид для каталога на FaceBook', 'facebook_feed') . $check_facebook_connect::$error, lang('Message', 'facebook_feed'), 'r');
//                            return false;
                    } else {
                        return $this->facebook_feed_model->upadateCatalog($catalog_shop_id, $this->input->post('variable') , $this->input->post('shop_cats'));
                    }
                }else{
                    showMessage(lang('Error: Каталог не найден на FaceBook', 'facebook_feed'), lang('Message', 'facebook_feed'), 'r');
                }
            }

        }
    }


}