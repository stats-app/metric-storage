<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:54 PM
 */

namespace TomVerran\Stats\Storage;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use TomVerran\Stats\Metric;
use TomVerran\Stats\MetricSeries;
use TomVerran\Stats\Storage\Database\Configuration;

class DatabaseStorage implements Storage
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * Construct this database storage
     * @param Database\Configuration $configuration
     */
    public function __construct( Configuration $configuration )
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = $configuration->toArray();
        $this->db = DriverManager::getConnection( $connectionParams, $config );
        $this->setUp();
    }

    /**
     * Set up the database table
     */
    private function setUp()
    {
        $sm = $this->db->getSchemaManager();
        $tables = $sm->listTables();

        foreach ( $tables as $table ) {
            if ( $table->getName() == 'metrics' ) {
                return;
            }
        }

        $schema = new Schema;
        $metrics = $schema->createTable('metrics');
        $metrics->addColumn( 'id', 'integer', ['autoincrement' => true] );
        $metrics->addColumn( 'time', 'datetime' );
        $metrics->addColumn( 'name', 'string', ['length' => 255] );
        $metrics->addColumn( 'value', 'float' );
        $metrics->addColumn( 'type', 'string', ['length' => 255] );

        $metrics->addIndex(['name']);
        $metrics->addIndex(['value']);
        $metrics->addIndex(['name', 'type', 'value']);
        $metrics->setPrimaryKey( ['id'] );

        $queries = $schema->toSql( $this->db->getDatabasePlatform() );
        foreach ( $queries as $query ) {
            $this->db->exec( $query );
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
        return $this->db->fetchColumn( 'SELECT MAX(id) FROM metrics' );
    }

    /**
     * Get the last metric with the given name submitted to the storage
     * @param string $name The name of the metric
     * @return Metric
     */
    public function getLastMetric( $name )
    {
        $qb = new QueryBuilder( $this->db );
        $select = $qb->select( 'm.*' )->from( 'metrics', 'm' )
                     ->leftJoin( 'm', 'metrics', 'm2', 'm2.name = m.name AND m2.id > m.id' )
                     ->where( 'm.name = ?' )->andWhere( 'm2.id IS NULL' )
                     ->setParameter( 0, $name );

        $result = $select->execute()->fetch();
        /** @var \DateTime $time */
        $time = $this->db->convertToPHPValue( $result['time'], 'datetime' );
        return new Metric( $result['name'], $result['value'], $result['type'], $time->format( 'U' ) );
    }

    /**
     * Get a metric series - a series of values for a given metric name
     * @param string $name The metric name
     * @return MetricSeries
     */
    public function getMetricSeries( $name, \DateTime $from = null, \DateTime $to = null )
    {
        $qb = new QueryBuilder( $this->db );
        $select = $qb->select( 'm.*' )->from( 'metrics', 'm' )->where( 'm.name = :name' )->orderBy( 'm.time', 'ASC' )
                     ->setParameter( ':name', $name );

        if ( $from ) {
            $dbFrom = $this->db->convertToDatabaseValue( $from, 'datetime' );
            $select->where('m.time >= :from')->setParameter( ':from', $dbFrom );
        }

        if ( $to ) {
            $dbTo = $this->db->convertToDatabaseValue( $to, 'datetime' );
            $select->where('m.time <= :to')->setParameter( ':to', $dbTo );
        }

        $results = $select->execute()->fetchAll();

        $values = [];
        foreach( $results as $point ) {

            /** @var \DateTime $time */
            $time = $this->db->convertToPHPValue( $point['time'], 'datetime' );
            $values[$time->format( 'U' )] = $point['value'];
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
        $data = [
            'id' => $this->getMaximumId() + 1,
            'time' => $this->db->convertToDatabaseValue(\DateTime::createFromFormat('U', $metric->getTimestamp() ), 'datetime' ),
            'name' => $metric->getName(),
            'value' => $metric->getValue(),
            'type' => $metric->getType()
        ];

        $this->db->insert('metrics', $data );
    }

    /**
     * Get the names of all metrics saved currently.
     * @return array
     */
    public function getMetricNames()
    {
        $qb = new QueryBuilder( $this->db );
        $select = $qb->select( 'name' )->from( 'metrics' )->groupBy( 'name' );
        $results = $select->execute()->fetchAll();

        foreach ( $results as &$result ) {
            $result = array_shift( $result );
        }

        return $results;
    }
}