<?php namespace facebook_feed\src\Systems;

use facebook_feed\src\Aggregator;
use facebook_feed\src\DataProvider;

class FaceBookXml extends Aggregator
{

    /**
     * @var array
     */
//    private $offerNodes
//        = [
////           'currencyId'  => 'currencyId',
//           'g:title'        => 'name',
//           'g:brand'      => 'vendor',
//           'g:description' => 'description',
//          ];

    /**
     * YMarket constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        parent::__construct($dataProvider);
        $this->name = lang('FaceBookXml', 'facebook_feed');
        $this->id = 'FaceBookXml';

    }

    public function getProductViewFields()
    {

        $month = [
            'false' => 'нет',
            'true' => 'есть',
            'P1M' => 1,
            'P2M' => 2,
            'P3M' => 3,
            'P6M' => 6,
            'P9M' => 9,
            'P1Y' => 12,
            'P1Y6M' => 18,
            'P2Y' => 24,
            'P2Y6M' => 30,
            'P3Y' => 36,
            'P3Y6M' => 42,
            'P4Y' => 48,
        ];

        return [
            'country_of_origin' => [
                'name' => 'country_of_origin',
                'label' => lang('Сountry of product manufacture', 'aggregator'),
                'type' => 'product_select',
                'options' => $this->dataProvider->getCountries(),
            ],
            'manufacturer_warranty' => [
                'name' => 'manufacturer_warranty',
                'label' => lang('Manufacturer warranty, months', 'aggregator'),
                'type' => 'product_select',
                'options' => $month,
            ],
            'seller_warranty' => [
                'name' => 'seller_warranty',
                'label' => lang('Seller warranty, months', 'aggregator'),
                'type' => 'product_select',
                'options' => $month,
            ],

        ];
    }

    public function getModuleViewFields()
    {
        return [
            'brands' => [
                'name' => 'brands',
                'multiple' => true,
                'label' => lang('Brands', 'aggregator'),
                'type' => 'select',
                'options' => $this->dataProvider->getBrands(),

            ],
            'categories' => [
                'name' => 'categories',
                'multiple' => true,
                'label' => lang('Categories', 'aggregator'),
                'type' => 'select',
                'options' => $this->dataProvider->getCategoriesOptions(),

            ],
            'adult' => [
                'name' => 'adult',
                'label' => lang('Adult products', 'aggregator'),
                'type' => 'checkbox',

            ],
            'apply_discount' => [
                'name' => 'apply_discount',
                'label' => lang('apply discount', 'aggregator'),
                'type' => 'checkbox',

            ],

        ];
    }

    public function generateXml($file)
    {
        /* create a dom document with encoding utf8 */
        $dom = new \DOMDocument('1.0', 'utf-8');

        $nsUrl = 'http://base.google.com/ns/1.0';
        $atom_url='http://www.w3.org/2005/Atom';

        /* create the root element of the xml tree */
        $dom->createElement('feed');
        $dom->createElement('feed');
        $rootNode = $dom->createElement('feed');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', $nsUrl);
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $atom_url);
        $dom->appendChild($rootNode);

//        $shopNode = $rootNode->appendChild($dom->createElement('shop'));
        $siteInfo = $this->dataProvider->getSiteInfo();

        $rootNode->appendChild($dom->createElement('title', $siteInfo['site_short_title']));
        $link = $rootNode->appendChild($dom->createElement('link'));
        $link->setAttribute('rel', 'self');
        $link->setAttribute('href', $siteInfo['base_url']);

        $this->dataProvider->getCategories(false, $this->getConfigItem('categories'));


        $products = $this->dataProvider->getProducts($this->getConfigItem('categories'), $this->getConfigItem('brands'));

//        $productsNode = $dom->createElement('entry');
//        $rootNode->appendChild($productsNode);
 
        foreach ($products as $id => $product) {

            $productNode = $dom->createElement('entry');
            $rootNode->appendChild($productNode);

            $productNode->appendChild($dom->createElement('g:id', $id));
            $productNode->appendChild($dom->createElement('g:title', $product['name']));

            if(!$product['description'] || $product['description'] =='' || $product['description'] ==null){
                $productNode->appendChild($dom->createElement('g:description', strtolower($product['name'])));
            }else{
                $productNode->appendChild($dom->createElement('g:description', $product['description']));
            }

            $productNode->appendChild($dom->createElement('g:link', $product['url']));
            if ($product['picture']) {
                if(file_exists($product['picture']['0'])){
                    $productNode->appendChild($dom->createElement('g:image_link', $product['picture']['0']));
                }else{
                    $productNode->appendChild($dom->createElement('g:image_link', base_url().'uploads/shop/nophoto/nophoto.jpg'));
                }


            }else{
                $productNode->appendChild($dom->createElement('g:image_link', base_url().'uploads/shop/nophoto/nophoto.jpg'));
            }
            $productNode->appendChild($dom->createElement('g:brand', $product['vendor']));

            $productNode->appendChild($dom->createElement('g:condition', 'new'));
            if ($product['unserorder'] > 0) {
                $productNode->appendChild($dom->createElement('g:availability', 'preorder'));
            } else{
                $productNode->appendChild($dom->createElement('g:availability', $product['quantity'] > 0 ? 'in stock' : 'out of stock'));
            }


            $discount = 0;
            if ($this->getConfigItem('apply_discount') == 'on') {
                $discount = $this->dataProvider->getDiscount($product['product_id'], $product['categoryId'], $product['vendor_id'], $product['id'], $product['price']);
            }

            if ($discount > 0) {
//                $productNode->appendChild($dom->createElement('oldprice', (float)$product['price']));
                $productNode->appendChild($dom->createElement('g:price', str_replace(',','.',(float)round(($product['price'] - $discount), 2). ' UAH')));
            } else {
//                if ($product['old_price'] > 0) {
//                    $productNode->appendChild($dom->createElement('oldprice', (float)$product['old_price']));
//                }
                $productNode->appendChild($dom->createElement('g:price', str_replace(',','.',(float)round($product['price'], 2). ' UAH')));
            }

            $productNode->appendChild($dom->createElement('g:google_product_category'));


//            $prodParams = $this->dataProvider->getProductConfig($this->getId(), $product['product_id']);

//            foreach ($prodParams as $key => $prodParam) {
//
//                if (in_array(
//                    $key,
//                    [
//                     'country_of_origin',
//                     'manufacturer_warranty',
//                     'seller_warranty',
//                    ]
//                )
//                ) {
//                    $productNode->appendChild($dom->createElement($key, $prodParam));
//
//                }
//            }


            $rootNode->appendChild($productNode);
        }

        if ($file == 'file') {
            $this->saveToXml($dom);
        } else {
            header('content-type: text/xml');
            echo $dom->saveXML();
        }
    }

}