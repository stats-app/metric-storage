<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:32 PM
 */

namespace TomVerran\Stats\Storage;
use DateTime;
use TomVerran\Stats\Metric;
use TomVerran\Stats\MetricSeries;

interface Storage
{
    /**
     * Get the last metric with the given name submitted to the storage
     * @param string $name The name of the metric
     * @return Metric
     */
    public function getLastMetric( $name );

    /**
     * Get a metric series - a series of values for a given metric name
     * @param string $name The metric name
     * @param \DateTime $from
     * @param \DateTime $to
     * @return MetricSeries
     */
    public function getMetricSeries( $name, DateTime $from = null, DateTime $to = null );

    /**
     * Store a metric
     * @param Metric $metric
     * @return mixed
     */
    public function store( Metric $metric );

    /**
     * Get the names of all metrics saved currently.
     * @return array
     */
    public function getMetricNames();
} 