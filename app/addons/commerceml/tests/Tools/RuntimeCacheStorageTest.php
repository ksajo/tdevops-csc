<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Addons\CommerceML\Tests\Unit\Tools;


use Tygh\Addons\CommerceML\Tools\RuntimeCacheStorage;
use Tygh\Tests\Unit\ATestCase;

class RuntimeCacheStorageTest extends ATestCase
{
    public function testStorageLimit()
    {
        $limit = 5;
        $storage = new RuntimeCacheStorage($limit);

        $storage->add('one', 1);
        $storage->add('two', 2);
        $storage->add('three', 3);
        $storage->add('four', 4);
        $storage->add('five', 5);
        $storage->add('six', 6);
        $storage->add('seven', 7);

        $this->assertSame($limit, $storage->count());
    }

    public function testHotCache()
    {
        $limit = 5;
        $storage = new RuntimeCacheStorage($limit);

        $storage->add('one', 1);
        $storage->add('two', 2);
        $storage->add('three', 3);
        $storage->add('four', 4);
        $storage->add('five', 5);

        $storage->get('one');
        $storage->get('one');
        $storage->get('two');
        $storage->get('two');
        $storage->get('three');
        $storage->get('three');
        $storage->get('five');
        $storage->get('five');

        $storage->get('four');

        $storage->add('six', 6);

        $this->assertTrue($storage->has('five'));
        $this->assertFalse($storage->has('four'));
    }

    public function testGeneral()
    {
        $limit = 5;
        $storage = new RuntimeCacheStorage($limit);

        $storage->add('key1', 'value1');
        $storage->add('key2', 'value2');

        $this->assertEquals('value1', $storage->get('key1'));
        $this->assertEquals('value2', $storage->get('key2'));
        $this->assertTrue($storage->has('key1'));

        $value = $storage->getOrSet('key3', function () {
            return 'value3';
        });

        $this->assertEquals('value3', $value);

        $value = $storage->getOrSet('key3', function () {
            return 'value4';
        });

        $this->assertEquals('value3', $value);
        $this->assertEquals(3, $storage->count());

        $storage->remove('key1');
        $this->assertFalse($storage->has('key1'));
        $this->assertNull($storage->get('key1'));

        $storage->clear();
        $this->assertFalse($storage->has('key1'));
        $this->assertNull($storage->get('key1'));
        $this->assertFalse($storage->has('key2'));
        $this->assertNull($storage->get('key2'));
        $this->assertEquals(0, $storage->count());
    }
}
