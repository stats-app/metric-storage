<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:54 PM
 */

namespace TomVerran\Stats\Storage;
use TomVerran\Stats\Metric;
use TomVerran\Stats\MetricSeries;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class DatabaseStorage implements Storage
{
    /**
     * @var Adapter
     */
    private $db;

    /**
     * Construct this database storage
     * @param Adapter $db
     */
    public function __construct( Adapter $db )
    {
        $this->db = $db;
        $this->setUp();
    }

    /**
     * Set up the database table
     */
    private function setUp()
    {
        $table = new CreateTable( 'metrics' );
        $table->addColumn( new Column\Integer( 'id' ) );
        $table->addColumn( new Column\Varchar( 'name', 255 ) );
        $table->addColumn( new Column\Float( 'value', 6, 6 ) );
        $table->addColumn( new Column\Varchar( 'type', 255 ) );
        $sql = new Sql( $this->db );

        try {
            $this->db->query(
                $sql->getSqlStringForSqlObject( $table ),
                Adapter::QUERY_MODE_EXECUTE
            );
        } catch ( \PDOException $e ) {
            //probably already done
        }
    }

    /**
     * Get the maximum ID
     * it seems Zend\Db\Ddl doesn't support auto increments
     * so yes this should probably be fixed at some point
     * @return mixed
     */
    private function getMaximumId()
    {
        $result = $this->db->query( 'SELECT MAX(id) FROM metrics' )->execute();
        return $this->getColumn( $result );
    }

    /**
     * Helper function to get an array from a ResultInterface
     * I really cannot believe Zend\Db makes you do this
     * @param ResultInterface $r
     * @return array
     */
    private function getArray( ResultInterface $r ) {
        $results = [];
        foreach( $r as $result ) {
            $results[] = $result;
        }
        return $results;
    }

    /**
     * Get the first column from a ResultInterface
     * @param ResultInterface $r
     * @return mixed
     */
    private function getColumn( ResultInterface $r )
    {
        $out = $this->getArray( $r );
        return array_shift( $out[0] );
    }

    /**
     * Get the last metric with the given name submitted to the storage
     * @param string $name The name of the metric
     * @return Metric
     */
    public function getLastMetric( $name )
    {
        $sel = new Select( ['m' => 'metrics'] );
        $sel->join( ['m2' => 'metrics'], 'm2.name = m.name AND m2.id > m.id', [], Select::JOIN_LEFT )
            ->where->isNull( 'm2.id' )->and->equalTo( 'm.name', $name );

        $sql = ( new Sql( $this->db ) )->getSqlStringForSqlObject( $sel );
        $result = $this->getArray( $this->db->query( $sql )->execute() );
        $result = array_shift( $result );

        return new Metric( $result['name'], $result['value'], $result['type'] );
    }

    /**
     * Get a metric series - a series of values for a given metric name
     * @param string $name The metric name
     * @return MetricSeries
     */
    public function getMetricSeries( $name )
    {
        $sel = new Select( ['m' => 'metrics'] );
        $sel->where->equalTo( 'm.name', $name );

        $sql = ( new Sql( $this->db ) )->getSqlStringForSqlObject( $sel );
        $result = $this->getArray( $this->db->query( $sql )->execute() );

        $values = [];
        foreach( $result as $point ) {
            $values[] = $point['value'];
        }

        $series = new MetricSeries( $name, $values );
        return $series;
    }

    /**
     * Store a metric
     * @param Metric $metric
     * @return mixed
     */
    public function store( Metric $metric )
    {
        $stmt = new Insert('metrics');

        $stmt->values( [
            'id' => $this->getMaximumId() + 1,
            'name' => $metric->getName(),
            'value' => $metric->getValue(),
            'type' => $metric->getType()
        ] );

        $this->db->query( $stmt->getSqlString( $this->db->getPlatform() ), Adapter::QUERY_MODE_EXECUTE );
    }
}