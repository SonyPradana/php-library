<?php

use PHPUnit\Framework\TestCase;
use System\Collection\CollectionImmutable;
use System\Collection\Exceptions\NoModify;

class CollectionImmutableTest extends TestCase
{
    /** @test */
    public function itCollectionImutableFuntionalWorKProperly(): void
    {
        $original = [
            'buah_1' => 'mangga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
            'buah_6' => 'peer',
        ];
        $test = new CollectionImmutable($original);

        // getter
        $this->assertEquals($test->buah_1, 'mangga', 'add new item colection using __set');
        $this->assertEquals($test->get('buah_1'), 'mangga', 'add new item collection using set()');

        // cek array key
        $this->assertTrue($test->has('buah_1'), 'collection have item with key');

        // cek contain
        $this->assertTrue($test->contain('mangga'), 'collection have item');

        // count
        $this->assertEquals($test->count(), 6, 'count item in collection');

        // count by
        $countIf = $test->countIf(function ($item) {
            // find letter contain 'e' letter
            return strpos($item, 'e') !== false ? true : false;
        });
        $this->assertEquals(4, $countIf, 'count item in collection with some condition');

        // first and last item cek
        $this->assertEquals('mangga', $test->first('bukan buah'), 'get first item in collection');
        $this->assertEquals('peer', $test->last('bukan buah'), 'get last item in collection');

        // test array keys and vules
        $keys  = array_keys($original);
        $items = array_values($original);
        $this->assertEquals($keys, $test->keys(), 'get all key in collection');
        $this->assertEquals($items, $test->items(), 'get all item value in collection');

        // each funtion
        $test->each(function ($item, $key) use ($original) {
            $this->assertTrue(in_array($item, $original), 'test each with value');
            $this->assertTrue(array_key_exists($key, $original), 'test each with key');
        });

        // test the collection have item with e letter
        $some = $test->some(function ($item) {
            // find letter contain 'e' letter
            return strpos($item, 'e') !== false ? true : false;
        });
        $this->assertTrue($some, 'test the collection have item with "e" letter');

        // test the collection every item dont have 'x' letter
        $every = $test->every(function ($item) {
            // find letter contain 'x' letter
            return strpos($item, 'x') === false ? true : false;
        });
        $this->assertTrue($every, 'collection every item dont have "x" letter');

        // json output
        $json = json_encode($original);
        $this->assertJsonStringEqualsJsonString($test->json(), $json, 'collection convert to json string');
    }

    /** @test */
    public function itCanActingLikeArray()
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertArrayHasKey('one', $coll);
        $this->assertArrayHasKey('two', $coll);
        $this->assertArrayHasKey('three', $coll);
    }

    /** @test */
    public function itCanDoLikeArray()
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new CollectionImmutable($arr);

        // get
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }

        // has
        $this->assertTrue(isset($coll['one']));
    }

    /** @test */
    public function itCanByIterator()
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        foreach ($coll as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }
    }

    /** @test */
    public function itWillthrowExceptionWithSetMethod()
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->expectException(NoModify::class);
        $coll['one'] = 4;
    }

    /** @test */
    public function itWillthrowExceptionWithRemoveMethod()
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->expectException(NoModify::class);
        unset($coll['one']);
    }

    /** @test */
    public function itCanCountUsingCountFunction()
    {
        $coll = new CollectionImmutable(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertCount(3, $coll);

        $count = count($coll);

        $this->assertEquals(3, $count);
    }
}
