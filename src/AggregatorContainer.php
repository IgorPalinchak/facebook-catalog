<?php namespace facebook_feed\src;

use CMSFactory\ModuleSettings;

class AggregatorContainer implements \IteratorAggregate
{

    /**
     * @var IAggregator[]
     */
    private $aggregators = [];

    /**
     * @var array
     */
    private $config;

    /**
     * AggregatorContainer constructor.
     *
     * @param array|ModuleSettings $config
     */
    public function __construct(array $config) {
        $this->config = $config;
    }

    /**
     * @param IAggregator $aggregator
     */
    public function addAggregator(IAggregator $aggregator) {
        $aggregatorConfig = $this->config;

        if ($aggregatorConfig) {
            $aggregator->setConfig($aggregatorConfig);
        }
        $this->aggregators[$aggregator->getId()] = $aggregator;

    }

    /**
     * @param string $id
     *
     * @return IAggregator
     */
    public function getAggregator($id) {
        if ($this->hasAggregator($id)) {
            return $this->aggregators[$id];
        }
    }

    /**
     * @return IAggregator[]
     */
    public function getAggregators() {
        return $this->aggregators;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function hasAggregator($id) {
        return array_key_exists($id, $this->aggregators);
    }

    /**
     * @return \Generator
     */
    public function getIterator() {
        foreach ($this->aggregators as $aggregator) {
            yield $aggregator;
        }
    }
}