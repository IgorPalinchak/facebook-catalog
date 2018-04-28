<?php

use CMSFactory\assetManager;
use CMSFactory\Events;
use Propel\Runtime\Exception\PropelException;

require __DIR__ . '/vendor/autoload.php';

use facebook_feed\src\AggregatorFactory;
use facebook_feed\src\IAggregator;
use facebook_feed\classes\Facebook_feed_worker;


(defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Image CMS
 * Module Frame
 * @property Cms_base $cms_base
 * @link https://enhancedecommerce.appspot.com/
 */
class facebook_feed extends MY_Controller
{

    /**
     * facebook_feed constructor.
     */
    public static $check_event = false;

    public function __construct()
    {
        parent::__construct();
        $lang = new MY_Lang();
        $lang->load('facebook_feed');
        $this->load->model('facebook_feed_model');
    }

    public function autoload()
    {
//        $access_token = 'EAACP7g7k3JEBAAFv3hpdsNSv1sEBn3Brl7rj1ZBdh8qwErBxWRPENIvW8T7oLHzDEPIdcM5mNvY84wRNhsKYxBYNwEZBHZAx8CxPFpSv3ZCFDJGurpiUyNy3hZCmwZC1sEwsUGfnYZAQzbfwudBnRgM9RDZCmU0jEls15iSgpD2XogZDZD';
//        $app_secret = '2be58fbb5d402bf63f3d1e7e45892a2c';
//        $app_id = '158252614868113';
//        $id = '1247262862072814';//1247262862072814
//
//        try{
//        $api = Api::init($app_id, $app_secret, $access_token);
//
//        $api->setLogger(new CurlLogger());
//
//
//        }catch ( Exception $e){
//            dump($e);
//            $e->getMessage();
//            dd(333);
//        }
//
//        $fields = array(
//        );
//        $params = array(
//            'name' => 'Test 1111',
//        );
//
//        try{
//        $business = new Business($id);
//        $business->getSelf();
//            $business->getOwnedAdAccounts();
//
//        }catch (RequestException $e){
//            dump($e);
//            $e->getMessage();
//            dd(222);
//        }

//        try {
//            $product_feed = new ProductFeed(null, '2152750658075893');
//            $product_feed->setData(array(
//                ProductFeedFields::NAME => 'Test Feed1',
//                ProductFeedFields::SCHEDULE => array(
//                    ProductFeedScheduleFields::INTERVAL => 'DAILY',
//                    ProductFeedScheduleFields::URL => base_url() . 'facebook_feed/getXml/2152750658075893' . $catalog_id,
//                    ProductFeedScheduleFields::HOUR => 22,
//                ),
//            ));
//            $product_feed->save();
//dump($product_feed);
//            dd($product_feed->getData()['id']);
//        } catch (RequestException $e) {
//            dd($e);
//            return false;
//
//        }

//        $product_feed = new ProductFeed($feed_id='866564766864027', $catalog_id='2152750658075893');
//dd($product_feed->getSelf());


//        try{
//            $catalod_api = new ProductCatalog('943685105801400');//94368510580149999 //943685105801400  REAL
//            $existeed_catalog = $catalod_api->getSelf();
//        }catch ( RequestException $e){
//            dump($e);
//            $e->getMessage();
//            dd(1111);
//        }


//        try{
//        $ctalog_created = $business->createOwnedProductCatalog($fields, $params);
//        }catch (RequestException $e){
//            dump($e);
//            $e->getMessage();
//            dd(666);
//        }
//
//
//        dump($ctalog_created);
//        dd($ctalog_created->getData()['id']);
////        (new Business($id))->createOwnedProductCatalog();
//        echo json_encode((new Business($id))->createProductCatalog(
//            $fields,
//            $params
//        ));
////            ->getResponse()->getContent(), JSON_PRETTY_PRINT);
//        dd('tttt');
    }


    public function index()
    {
    }

    public function getXml($feed_id, $file)
    {
        $all_catalogs = $this->facebook_feed_model->getAllCatalogs();
        if (count($all_catalogs) > 0) {
            $check_facebook_connect = new Facebook_feed_worker();
            if ($check_facebook_connect::$check_event) {

            } else {
                $error = $check_facebook_connect::$error;
                $connect_error = true;
                return false;
            }

            foreach ($all_catalogs as $id_shop => $catalogs) {
                $full_catalog_data[$catalogs['feed_id']] = $catalogs;
                $full_catalog_data[$catalogs['feed_id']]['categories'] = json_decode($catalogs['categories']);
                $full_catalog_data[$catalogs['feed_id']]['exist_in_facebook'] = $connect_error ?
                    false : $check_facebook_connect->check_catalog($catalogs['catalog_id']);
            }
        }
        
        if (key_exists($feed_id, $full_catalog_data)
            && $full_catalog_data[$feed_id]['active'] == '1'
            && $full_catalog_data[$feed_id]['exist_in_facebook']
        ) {
            if(in_array('none', $full_catalog_data[$feed_id]['categories'])){
                return false;
            }

           $cats = in_array('all', $full_catalog_data[$feed_id]['categories']) ? '' : $full_catalog_data[$feed_id]['categories'];
        }
 
        $aggregatorContainer = AggregatorFactory::getAggregatorContainer(['categories'=>$cats]);
        $aggregator = $aggregatorContainer->getAggregator('FaceBookXml');

        if ($aggregator) {
            $aggregator->generateXml($file);
        } else {
            $this->core->error_404();
        }
    }

    public function _deinstall()
    {
        $this->load->dbforge();
        ($this->dx_auth->is_admin()) OR exit;
        $this->dbforge->drop_table('mod_facebook_feed');
    }

    public function _install()
    {
        $this->load->dbforge();
        ($this->dx_auth->is_admin()) OR exit;

        $this->db
            ->where('name', 'facebook_feed')
            ->update('components', ['autoload' => '1', 'enabled' => '1']);

        $fields_api = array(
            'id' => array(
                'type' => 'INT',
                'auto_increment' => TRUE
            ),
            'catalog_name' => array(
                'type' => 'varchar',
                'constraint' => '255',
                'null' => false
            ),
            'catalog_id' => array(
                'type' => 'BIGINT',
                'constraint' => '255',
                'null' => false
            ),
            'categories' => array(
                'type' => 'text',
            ),
            'active' => array(
                'type' => 'TINYINT',
            ),
            'feed_id' => array(
                'type' => 'BIGINT',
                'constraint' => '255',
                'null' => true
            ),
        );

        $this->dbforge->add_field($fields_api);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('mod_facebook_feed');
    }

}

/* End of file sample_module.php */