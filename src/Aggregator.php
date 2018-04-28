<?php namespace facebook_feed\src;

abstract class Aggregator implements IAggregator
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $productConfig;

    /**
     * YMarket constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider) {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config) {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig() {
        return $this->config;

    }

    public function getProductConfigs($productId) {
        if (!$this->productConfig) {
            $this->productConfig = $this->dataProvider->getProductConfig($this->getId(), $productId);
        }

        return $this->productConfig;
    }

    /**
     * @param null|string $name
     *
     * @return array
     */
    public function getConfigItem($name) {
        return array_key_exists($name, $this->config) ? $this->config[$name] : null;

    }

    /**
     * @param \DOMDocument $dom
     */
    public function saveToXml($dom) {
        $fileName = '/'.$this->getId().'.xml';
        if(!is_dir(UPLOADSPATH . 'files/')) {
            mkdir(UPLOADSPATH . 'files/', 0777);
        }
        $saved = file_put_contents(UPLOADSPATH.'/files'.$fileName, $dom->saveXML());
        if($saved !== false) {
            echo 'New file saved:
                <a href="http://'.$_SERVER['SERVER_NAME'].'/uploads/files'.$fileName.'">
                '.$_SERVER['SERVER_NAME'].'/files/uploads'.$fileName.'</a>';
        } else {
            echo '<div>ERROR file save</div>';
        }

        echo '<div><a href="#" OnClick="history.back()">GoBack</a></div>';
    }

}