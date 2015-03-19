<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:34 PM
 */
namespace TomVerran\Stats\Storage;
use TomVerran\Stats\Metric;
use TomVerran\Stats\MetricSeries;

class ArrayStorage implements Storage
{
    /**
     * @var array
     */
    private $metrics = [];

    /**
     * Get the last metric with the given name submitted to the storage
     * @param string $name The name of the metric
     * @return Metric
     */
    public function getLastMetric( $name )
    {
        return end( $this->metrics[$name] );
    }

    /**
     * Store a metric
     * @param Metric $metric
     * @return mixed
     */
    public function store( Metric $metric )
    {
        if ( !array_key_exists( $metric->getName(), $this->metrics ) ) {
            $this->metrics[$metric->getName()] = [];
        }
        $this->metrics[$metric->getName()][] = $metric;
    }

    /**
     * Get a metric series - a series of values for a given metric name
     * @param string $name The metric name
     * @return MetricSeries
     */
    public function getMetricSeries( $name )
    {
        $values = [];
        usort( $this->metrics[$name], function( Metric $metric1, Metric $metric2 ) {
            return $metric1->getTimestamp() - $metric2->getTimestamp();
        } );

        foreach( $this->metrics[$name] as $metric ) {

            /** @var Metric $metric */
            $values[] = $metric->getValue();
        }

        $series = new MetricSeries( $name, $values );
        return $series;
    }

    /**
     * Get the names of all metrics saved currently.
     * @return array
     */
    public function getMetricNames()
    {
        return array_unique( array_keys( $this->metrics ) );
    }
}