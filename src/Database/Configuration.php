<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 9:08 PM
 */

namespace TomVerran\Stats\Storage\Database;


interface Configuration
{
    /**
     * Return a configuration array used by DatabaseStorage
     * @return array
     */
    public function toArray();
} 