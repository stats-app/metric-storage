<?php
namespace TomVerran\Stats\Storage;

/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 3:37 PM
 */

class ArrayStorageTest extends AbstractStorageTest
{
    /**
     * @return \TomVerran\Stats\Storage\Storage
     */
    public function getStorage()
    {
        return new ArrayStorage;
    }
}