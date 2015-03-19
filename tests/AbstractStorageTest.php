<?php
namespace TomVerran\Stats\Storage;
use PHPUnit_Framework_TestCase;
use TomVerran\Stats\Metric;
use TomVerran\Stats\MetricSeries;

/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 4:01 PM
 */

abstract class AbstractStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Set up this test
     */
    public function setUp()
    {
        $this->storage = $this->getStorage();
    }

    /**
     * @return Storage
     */
    public abstract function getStorage();


    public function testCanStoreMetric()
    {
        $metric = new Metric( 'count', 1, 'counter', time() );
        $this->storage->store( $metric );
    }

    public function testCanRetrieveLastMetric()
    {
        $metric = new Metric( 'number', 2, 'counter', time() );
        $this->storage->store( $metric );
        $this->assertEquals( $metric, $this->storage->getLastMetric( 'number' ) );
    }

    public function testCanGetMetricSeries()
    {
        for( $i = 0; $i < 100; $i++ ) {
            $metric = new Metric('number', $i, 'counter', time() );
            $this->storage->store( $metric );
        }

        $metricSeries = $this->storage->getMetricSeries( 'number' );
        $this->assertInstanceOf( MetricSeries::class, $metricSeries );
        $this->assertEquals( 'number', $metricSeries->getName() );

        $expectedValue = 0;
        foreach( $metricSeries->getValues() as $value ) {
            $this->assertEquals( $expectedValue, $value );
            $expectedValue++;
        }
    }

    public function testMetricSeriesAreOrderedByTimestamp()
    {
        $now = time();
        $soon = $now + 100;
        for( $i = 0; $i < 100; $i++ ) {
            $metric = new Metric('number', $i, 'counter', ($soon--) - $now );
            $this->storage->store( $metric );
        }

        $metricSeries = $this->storage->getMetricSeries( 'number' );
        $this->assertCount( 100, $metricSeries->getValues() );

        $expectedValue = 99;
        foreach( $metricSeries->getValues() as $value ) {
            $this->assertEquals( $expectedValue--, $value );
        }
    }

    public function testGetMetricNames()
    {
        $names = ['a', 'a', 'b', 'c'];
        $expectedOutput = array_unique( $names );

        foreach( ['a', 'b', 'c'] as $metricName ) {
            $metric = new Metric( $metricName, 1, 'counter', time() );
            $this->storage->store( $metric );
        }

        $namesFromStorage = $this->storage->getMetricNames();
        $this->assertCount( count( $expectedOutput ), $namesFromStorage );

        foreach ( $namesFromStorage as $name ) {
            $this->assertContains( $name, $expectedOutput );
        }
    }

} 