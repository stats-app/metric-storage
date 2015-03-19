<?php
namespace TomVerran\Stats\Storage;
use DateTime;
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
        $time = time();
        for( $i = 0; $i < 100; $i++ ) {
            $metric = new Metric('number', $i, 'counter', $time + $i );
            $this->storage->store( $metric );
        }

        $metricSeries = $this->storage->getMetricSeries( 'number' );
        $this->assertInstanceOf( MetricSeries::class, $metricSeries );
        $this->assertEquals( 'number', $metricSeries->getName() );

        $expectedValue = 0;
        $expectedKey = $time;

        foreach( $metricSeries->getValues() as $key => $value ) {
            $this->assertEquals( $expectedValue, $value );
            $this->assertEquals( $expectedKey++, $key );
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

    public function testToAndFromConstraintsInGetMetricSeries()
    {
        $backInTheDay = DateTime::createFromFormat('Y-m-d H:i:s', '1989-12-31 00:00:00' );
        for( $day = 0; $day < 365; $day++ ) {
            $backInTheDay->modify( '+1 day' );
            $this->storage->store( new Metric('90s', $day+1, 'number', $backInTheDay->format( 'U' ) ) );
        }

        $metrics = $this->storage->getMetricSeries( '90s', $firstDay = DateTime::createFromFormat('Y-m-d H:i:s', '1990-01-01 00:00:00'),
                                                           $lastDay = DateTime::createFromFormat( 'Y-m-d H:i:s', '1990-01-31 00:00:00' ) );
        $this->assertCount( 31, $metrics->getValues() );
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