<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:56 PM
 */
namespace TomVerran\Stats\Storage;
use Zend\Db\Adapter\Adapter;

class DatabaseStorageTest extends AbstractStorageTest
{
    /**
     * @return Storage
     */
    public function getStorage()
    {
        unlink( 'sqlite.db' );
        $adapter = new Adapter(array(
            'driver' => 'Pdo_Sqlite',
            'database' => 'sqlite.db'
        ));
        return new DatabaseStorage( $adapter );
    }
}