<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:56 PM
 */
namespace TomVerran\Stats\Storage;
use TomVerran\Stats\Storage\Database\SqliteConfiguration;

class DatabaseStorageTest extends AbstractStorageTest
{
    /**
     * @return Storage
     */
    public function getStorage()
    {
        if ( file_exists('sqlite.db' ) ) {
            unlink( 'sqlite.db' );
        }
        return new DatabaseStorage( new SqliteConfiguration( 'sqlite.db' ) );
    }
}