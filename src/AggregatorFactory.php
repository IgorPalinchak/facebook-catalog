<?php namespace facebook_feed\src;

/**
 * Class AggregatorFactory
 * Responsive for creation Aggregator container
 *
 * @package aggregator\src
 */
class AggregatorFactory
{

    const AGGREGATORS_NAMESPACE = 'facebook_feed\src\Systems\\';

    const AGGREGATORS_PATH      = 'modules/facebook_feed/src/Systems/FaceBookXml.php';

    public static function getAggregatorContainer(array $config) {

        $aggregatorContainer = new AggregatorContainer($config);
        $file               = glob(APPPATH . self::AGGREGATORS_PATH);
        $dataProvider        = new DataProvider();


            $class = self::AGGREGATORS_NAMESPACE . str_replace('.php', '', array_pop(explode('/', $file['0'])));

            if (class_exists($class) && is_a($class, IAggregator::class, true)) {

                $aggregatorContainer->addAggregator(new $class($dataProvider));
            }


        return $aggregatorContainer;

    }

}