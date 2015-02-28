<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 9:08 PM
 */

namespace TomVerran\Stats\Storage\Database;


class SqliteConfiguration implements Configuration
{
    public function __construct( $file )
    {
        $this->file = $file;
    }


    public function toArray()
    {
        return [
            'driver' => 'pdo_sqlite',
            'path' => $this->file
        ];
    }
}