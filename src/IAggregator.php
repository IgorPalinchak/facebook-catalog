<?php namespace facebook_feed\src;

interface IAggregator
{

    /**
     * IAggregator constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getProductViewFields();

    /**
     * @return array
     */
    public function getModuleViewFields();

    /**
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * @param $productId
     *
     * @return mixed
     */
    public function getProductConfigs($productId);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param string|null $name
     *
     * @return mixed|null
     */
    public function getConfigItem($name);

    /**
     * @param string $file
     *
     * @return string
     */
    public function generateXml($file);

}