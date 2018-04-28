<?php namespace facebook_feed\src;

use CMSFactory\assetManager;
use core\models\Route;
use Exception;
use Map\SBrandsI18nTableMap;
use Map\SBrandsTableMap;
use Map\SCategoryI18nTableMap;
use Map\SCategoryTableMap;
use mod_discount\Discount_product;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ArrayCollection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Exception\PropelException;

class DataProvider
{

    /**
     * @var ArrayCollection
     */
    private $brands;

    /**
     * @var ArrayCollection
     */
    private $categories;

    /**
     * @var array
     */
    private $siteInfo;

    /**
     * @var array
     */
    private $currencies;

    public function getSiteInfo() {

        $ci = \CI::$APP;

        if (!$this->siteInfo) {
            $this->siteInfo                        = [];
            $settings                              = $ci->cms_base->get_settings();
            $this->siteInfo['site_short_title']    = $settings['site_short_title'];
            $this->siteInfo['site_title']          = $settings['site_title'];
            $this->siteInfo['base_url']            = $ci->config->item('base_url');
            $this->siteInfo['imagecms_number']     = IMAGECMS_NUMBER;
            $this->siteInfo['siteinfo_adminemail'] = siteinfo('siteinfo_adminemail');

        }

        return $this->siteInfo;
    }

    /**
     * @param bool $array
     *
     * @return array|\Propel\Runtime\Collection\ObjectCollection|\SBrands[]
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBrands($array = true) {

        if (!$this->brands) {

            $this->brands = \SBrandsQuery::create()->select(
                [
                 SBrandsTableMap::COL_ID,
                 SBrandsI18nTableMap::COL_NAME,
                ]
            )->joinWithI18n(\MY_Controller::defaultLocale())->find();

        }

        return $array ? $this->brands->toKeyValue(SBrandsTableMap::COL_ID, SBrandsI18nTableMap::COL_NAME) : $this->brands;
    }

    /**
     * @param bool       $array
     * @param array|null $filterIds
     *
     * @return array
     */
    public function getCategories($array = true, $filterIds = null) {


        if (!$this->categories) {
            $this->categories = \SCategoryQuery::create()
                ->select(
                    [
                     'Name',
                     'Id',
                     'ParentId',
                    ]
                )
                ->withColumn(SCategoryI18nTableMap::COL_NAME, 'Name')
                ->withColumn(SCategoryTableMap::COL_ID, 'Id')
                ->withColumn(SCategoryTableMap::COL_PARENT_ID, 'ParentId')
                ->joinWithI18n(\MY_Controller::defaultLocale())
                ->filterByActive(true)
                ->orderById()
                ->_if(is_array($filterIds))
                ->filterById($filterIds, Criteria::IN)
                ->_endif()
                ->find();

        }


        return $array ? $this->categories->toKeyValue('Id', 'Name') : $this->categories;
    }

    /**
     * @return array
     */
    public function getCategoriesOptions() {
        $categories = \SCategoryQuery::create()
                                     ->getTree(
                                         0,
                                         \SCategoryQuery::create()
                                         ->joinWithI18n(\MY_Controller::defaultLocale())
                                     )
                                     ->getCollection();

        $options = [];
        foreach ($categories as $category) {
            $options[$category->getId()] = str_repeat('-', $category->getLevel()) . $category->getName();
        }

        return $options;

    }

    /**
     * @param bool $need
     *
     * @return array
     * @throws Exception
     */
    public function getCurrencies($need = false) {
        $dbCurrencies = \SCurrenciesQuery::create()->find();
        $currencies   = [];
        $multiplier   = false;
        foreach ($dbCurrencies as $currency) {
            $code = $currency->getCode(); // USD

            if ($currency->isMain() AND $need == false) {
                $multiplier = $currency->getRate();
            } elseif ($need == $code) {
                $multiplier = $currency->getRate();
            }
            $currencies[$currency->getId()] = [
                                               'code' => $code,
                                               'rate' => 1 / $currency->getRate(),
                                              ];
        }
        if (!$multiplier) {
            throw new Exception("You have to add the following currency: $need. You can do in in Admin panel.");
        }

        foreach ($currencies as $id => $currency) {
            $rate                    = $currency['rate'] * $multiplier;
            $currencies[$id]['rate'] = $rate;
            if ($currency['code'] == $need) {
                unset($currencies[$id]);
            }
        }

        foreach ($currencies as $id => $currency) {
            $currencies[$id]['rate'] = number_format($currency['rate'], 3);
        }
        $this->currencies = $currencies;

        return $currencies;

    }

    /**
     * @param ObjectCollection $products - products collection
     *
     * @return array
     * @throws PropelException
     */
    public function getProperties($products) {

        $productsIds = [];
        /**
         * @var \SProductVariants $product
         */
        foreach ($products as $product) {
            $productsIds[] = $product['product_id'];
        }

        $properties = \SProductPropertiesDataQuery::create()
                                                  ->select(
                                                      [
                                                       'ProductId',
                                                       'Value',
                                                       'Name',
                                                      ]
                                                  )
                                                  ->useSPropertiesQuery()
                                                  ->joinWithI18n(\MY_Controller::getCurrentLocale(), Criteria::INNER_JOIN)
                                                  ->orderByPosition()
                                                  ->endUse()
                                                  ->useSPropertyValueQuery()
                                                  ->joinWithI18n(\MY_Controller::getCurrentLocale(), Criteria::INNER_JOIN)
                                                  ->orderByPosition()
                                                  ->endUse()
                                                  ->withColumn('SProductPropertiesData.ProductId', 'ProductId')
                                                  ->withColumn('SPropertyValueI18n.Value', 'Value')
                                                  ->withColumn('SPropertiesI18n.Name', 'Name')
                                                  ->filterByProductId($productsIds, Criteria::IN)
                                                  ->where('SProperties.Active = ?', 1)
                                                  ->where('SPropertyValueI18n.Value != ?', '')
                                                  ->where('SProperties.ShowOnSite = ?', 1)
                                                  ->find()
                                                  ->toArray();

        $productsData = [];

        foreach ($properties as $property) {
            if (!$productsData[$property['ProductId']][$property['Name']]) {
                $productsData[$property['ProductId']][$property['Name']] = [
                                                                            'name'  => $property['Name'],
                                                                            'value' => $property['Value'],
                                                                           ];
            } else {
                $productsData[$property['ProductId']][$property['Name']]['value'] .= ', ' . $property['Value'];
            }
        }

        return $productsData;
    }

    /**
     * @param array $categoryIds
     * @param array $brandIds
     *
     * @return array
     */
    public function getProducts($categoryIds, $brandIds) {

        $variants         = $this->getVariants($categoryIds, $brandIds);
        $params           = $this->getProperties($variants);
        $additionalImages = $this->getAdditionalImagesBYVariants($variants);
        $currencies       = $this->getCurrencies();

        /**
         * @var \SProductVariants $variant
         */
        foreach ($variants as $id => $variant) {

            $variants[$id]['url']        = site_url(Route::createRouteUrl($variant['url'], $variant['parent_url'], Route::TYPE_PRODUCT));
            $variants[$id]['currencyId'] = $currencies[$variant['currencyId']]['code'];
            $mainPhoto                   = $variant['picture'] ? productImageUrl('products/main/' . $variant['picture']) : null;
            $photos                      = $additionalImages[$variant['product_id']] ?: [];

            $variants[$id]['picture']     = array_merge([$mainPhoto], $photos);
            $variants[$id]['name']        = $this->formName($variant['product_name'], $variant['variant_name']);
            $variants[$id]['vendor']      = htmlspecialchars($variant['vendor']);
            $variants[$id]['description'] = htmlspecialchars($variant['description']);

            if ($params[$variant['product_id']]) {
                $variants[$id]['param'] = $params[$variant['product_id']];
            }
        }

        return $variants;
    }

    /**
     * @param $productId
     * @param $categoryId
     * @param $brandId
     * @param $variantId
     * @param $price
     *
     * @return int
     */
    public function getDiscount($productId, $categoryId, $brandId, $variantId, $price) {
        $arr_for_discount                = [
                                            'product_id'  => $productId,
                                            'category_id' => $categoryId,
                                            'brand_id'    => $brandId,
                                            'vid'         => $variantId,
                                            'id'          => $productId,
                                           ];
        assetManager::create()->discount = 0;

        Discount_product::create()->getProductDiscount($arr_for_discount, $price);
        $discount = assetManager::create()->discount;

        return isset($discount['discount_value']) ? $discount['discount_value'] : 0;
    }

    /**
     * Generates a name of the product depending on the name and version of the product name.
     *
     * @param  string $productName product name
     * @param  string $variantName variant name
     *
     * @return string
     */
    private function formName($productName, $variantName) {

        if (encode($productName) == encode($variantName)) {
            $name = htmlspecialchars($productName);
        } else {
            $name = htmlspecialchars($productName . ' ' . $variantName);
        }

        return $name;
    }

    /**
     * @param $categoryIds
     * @param $brandIds
     *
     * @return array|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection|\SProductVariants[]
     */
    private function getVariants($categoryIds = null, $brandIds = null) {
 
        $query = \SProductVariantsQuery::create()
                                       ->select(
                                           [
                                            'id',
                                            'url',
                                            'currencyId',
                                            'product_id',
                                           ]
                                       )
                                       ->addAsColumn('url', 'Route.Url')
                                       ->addAsColumn('parent_url', 'Route.ParentUrl')
                                       ->addAsColumn('categoryId', 'SProducts.category_id')
                                       ->addAsColumn('old_price', 'SProducts.old_price')
             ->addAsColumn('unserorder', 'SProducts.unserorder')
                                       ->addAsColumn('currencyId', 'currency')
                                       ->addAsColumn('product_name', 'SProductsI18n.name')
                                       ->addAsColumn('variant_name', 'SProductVariantsI18n.name')
                                       ->addAsColumn('vendor', 'SBrandsI18n.name')
                                       ->addAsColumn('vendor_id', 'SProducts.brand_id')
                                       ->addAsColumn('vendorCode', 'SProductVariants.number')
                                       ->addAsColumn('picture', 'SProductVariants.mainImage')
                                       ->addAsColumn('price', 'SProductVariants.price_in_main')
                                       ->addAsColumn('description', 'SProductsI18n.full_description')
                                       ->addAsColumn('cpa', 'if(SProductVariants.stock > 0, 1,0)')
                                       ->addAsColumn('quantity', 'SProductVariants.stock')
                                       ->joinWithI18n(\MY_Controller::getCurrentLocale())
                                       ->useSProductsQuery()
                                       ->joinRoute()
                                       ->joinWithI18n(\MY_Controller::getCurrentLocale())
                                       ->joinWithBrand(Criteria::LEFT_JOIN)
                                       ->useBrandQuery(null, Criteria::LEFT_JOIN)
                                       ->joinWithI18n(\MY_Controller::getCurrentLocale(), Criteria::LEFT_JOIN)
                                       ->endUse()
                                       ->useMainCategoryQuery()
                                       ->filterByActive(true)
                                       ->endUse()
                                       ->filterByActive(true)
                                       ->filterByArchive(false)
                                       ->_if($categoryIds)
                                       ->filterByCategoryId($categoryIds)
                                       ->_endif()
                                       ->_if($brandIds)
                                       ->filterByBrandId($brandIds)
                                       ->_endif()
                                       ->distinct()
                                       ->endUse()
                                       ->filterByStock(['min' => 1])
                                       ->filterByPrice(['min' => 0.00001]);

        return $query->find()->toArray('id');
    }

    /**
     * @param \SProductVariants $variants
     *
     * @return array
     */
    private function getAdditionalImagesBYVariants($variants) {

        $productsIds = [];
        foreach ($variants as $variant) {
            $productsIds[] = $variant['product_id'];
        }

        $images       = \CI::$APP->db->where_in('product_id', $productsIds)->get('shop_product_images');
        $images       = $images ? $images->result_array() : [];
        $productsData = [];

        foreach ($images as $image) {
            $productsData[$image['product_id']][] = productImageUrl('products/additional/' . $image['image_name']);
        }

        return $productsData;
    }

    /**
     * @return array
     */
    public function getCountries() {
        $countries = [
                      'Австралия',
                      'Австрия',
                      'Азербайджан',
                      'Албания',
                      'Алжир',
                      'Американские Виргинские острова',
                      'Ангилья',
                      'Ангола',
                      'Андорра',
                      'Антигуа и Барбуда',
                      'Аргентина',
                      'Армения',
                      'Аруба',
                      'Афганистан',
                      'Багамские острова',
                      'Бангладеш',
                      'Барбадос',
                      'Бахрейн',
                      'Беларусь',
                      'Белиз',
                      'Бельгия',
                      'Бенин',
                      'Бермудские Острова',
                      'Болгария',
                      'Боливия',
                      'Босния и Герцеговина',
                      'Ботсвана',
                      'Бразилия',
                      'Британские Виргинские острова',
                      'Бруней',
                      'Буркина-Фасо',
                      'Бурунди',
                      'Бутан',
                      'Вануату',
                      'Ватикан',
                      'Великобритания',
                      'Венгрия',
                      'Венесуэла',
                      'Восточный Тимор',
                      'Вьетнам',
                      'Габон',
                      'Гайана',
                      'Гаити',
                      'Гамбия',
                      'Гана',
                      'Гваделупа',
                      'Гватемала',
                      'Гвинея',
                      'Гвинея-Бисау',
                      'Германия',
                      'Гибралтар',
                      'Гондурас',
                      'Гонконг',
                      'Гренада',
                      'Гренландия',
                      'Греция',
                      'Грузия',
                      'Дания',
                      'Демократическая Республика Конго',
                      'Джибути',
                      'Доминика',
                      'Доминиканская Республика',
                      'Египет',
                      'Замбия',
                      'Западная Сахара',
                      'Зимбабве',
                      'Йемен',
                      'Израиль',
                      'Индия',
                      'Индонезия',
                      'Иордания',
                      'Ирак',
                      'Иран',
                      'Ирландия',
                      'Исландия',
                      'Испания',
                      'Италия',
                      'Кабо-Верде',
                      'Казахстан',
                      'Каймановы острова',
                      'Камбоджа',
                      'Камерун',
                      'Канада',
                      'Катар',
                      'Кения',
                      'Кипр',
                      'Киргизия',
                      'Кирибати',
                      'Китай',
                      'Колумбия',
                      'Коморские острова',
                      'Коста-Рика',
                      'Кот-д\'Ивуар',
                      'Куба',
                      'Кувейт',
                      'Лаос',
                      'Латвия',
                      'Лесото',
                      'Либерия',
                      'Ливан',
                      'Ливия',
                      'Литва',
                      'Лихтенштейн',
                      'Люксембург',
                      'Маврикий',
                      'Мавритания',
                      'Мадагаскар',
                      'Майотта',
                      'Макао',
                      'Македония',
                      'Малави',
                      'Малайзия',
                      'Мали',
                      'Мальдивы',
                      'Мальта',
                      'Марокко',
                      'Маршалловы острова',
                      'Мексика',
                      'Мозамбик',
                      'Молдова',
                      'Монако',
                      'Монголия',
                      'Мьянма',
                      'Намибия',
                      'Науру',
                      'Непал',
                      'Нигер',
                      'Нигерия',
                      'Нидерландские Антильские острова',
                      'Нидерланды',
                      'Никарагуа',
                      'Новая Зеландия',
                      'Новая Каледония',
                      'Норвегия',
                      'Объединённые Арабские Эмираты',
                      'Оман',
                      'Острова Кука',
                      'Пакистан',
                      'Палау',
                      'Панама',
                      'Папуа - Новая Гвинея',
                      'Парагвай',
                      'Перу',
                      'Польша',
                      'Португалия',
                      'Республика Конго',
                      'Реюньон',
                      'Россия',
                      'Руанда',
                      'Румыния',
                      'Самоа',
                      'Сан-Марино',
                      'Сан-Томе и Принсипи',
                      'Саудовская Аравия',
                      'Свазиленд',
                      'Северная Корея',
                      'Сейшельские острова',
                      'Сенегал',
                      'Сент-Винсент и Гренадины',
                      'Сент-Китс и Невис',
                      'Сент-Люсия',
                      'Сербия',
                      'Сингапур (страна)',
                      'Сирия',
                      'Словакия',
                      'Словения',
                      'Сомали',
                      'Судан',
                      'Суринам',
                      'США',
                      'Сьерра-Леоне',
                      'Таджикистан',
                      'Таиланд',
                      'Танзания',
                      'Тёркс и Кайкос',
                      'Того',
                      'Тонга',
                      'Тринидад и Тобаго',
                      'Тувалу',
                      'Тунис',
                      'Туркмения',
                      'Турция',
                      'Уганда',
                      'Узбекистан',
                      'Украина',
                      'Уругвай',
                      'Федеративные Штаты Микронезии',
                      'Фиджи',
                      'Филиппины',
                      'Финляндия',
                      'Франция',
                      'Французская Гвиана',
                      'Французская Полинезия',
                      'Хорватия',
                      'Центрально-Африканская Республика',
                      'Чад',
                      'Черногория',
                      'Чехия',
                      'Чили',
                      'Швейцария',
                      'Швеция',
                      'Шри-Ланка',
                      'Эквадор',
                      'Экваториальная Гвинея',
                      'Эритрея',
                      'Эстония',
                      'Эфиопия',
                      'ЮАР',
                      'Южная Корея',
                      'Ямайка',
                      'Япония',
                     ];

        return array_combine($countries, $countries);
    }

    public function getProductConfig($aggregatorId, $productId) {

        /**
         * @var \CI_DB_active_record $db
         */
        $db    = \CI::$APP->db;
        $items = [];
        $query = $db->where('aggregator_id', $aggregatorId)->where('product_id', $productId)->get('aggregator');

        if ($query->num_rows()) {
            $res = $query->result_array();
            foreach ($res as $item) {
                $items[$item['field']] = $item['value'];
            }
        }

        return $items;

    }

    public function clearText($text) {
        $text = strip_tags(html_entity_decode($text));
        $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
        $text = preg_replace('%[^A-Za-z\:\-А-Яа-я0-9.,\(\) ]%u', '', $text);

        return $text;
    }

}