<?php

use PHPUnit\Framework\TestCase;
use System\Collection\Collection;
use System\Collection\CollectionImmutable;

class CollectionTest extends TestCase
{
    /** @test */
    public function itCollectionFunctionalWorKProperly(): void
    {
        $original = [
            'buah_1' => 'mangga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
            'buah_6' => 'peer',
        ];
        $test = new Collection($original);

        // getter
        $this->assertEquals($test->buah_1, 'mangga', 'add new item colection using __set');
        $this->assertEquals($test->get('buah_1'), 'mangga', 'add new item collection using set()');

        // add new item
        $test->set('buah_7', 'kelengkeng');
        $test->buah_8 = 'cherry';
        $this->assertEquals($test->buah_8, 'cherry', 'get item colection using __get');
        $this->assertEquals($test->get('buah_7'), 'kelengkeng', 'get item colection using get()');

        // raname item
        $test->set('buah_7', 'durian');
        $test->buah_8 = 'nanas';
        $this->assertEquals($test->buah_8, 'nanas', 'replece exis item colection using __get');
        $this->assertEquals($test->get('buah_7'), 'durian', 'replece exis item colection using get()');

        // cek array key
        $this->assertTrue($test->has('buah_1'), 'collection have item with key');

        // cek contain
        $this->assertTrue($test->contain('mangga'), 'collection have item');

        // remove item
        $test->remove('buah_2');
        $this->assertFalse($test->has('buah_2'), 'remove some item using key');

        // reset to origin
        $test->replace($original);

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

        // test clear and empty cek
        $this->assertFalse($test->isEmpty());
        $test->clear();
        $this->assertTrue($test->isEmpty(), 'cek collection empty');
        // same with origin
        $test->replace($original);
        $this->assertEquals($test->all(), $original, 'replace axis collection with new data');

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

        // map funtion
        $test->map(fn ($item) => ucfirst($item));
        $copy_origin = array_map(fn ($item) => ucfirst($item), $original);
        $this->assertEquals($test->all(), $copy_origin, 'replace some/all item using map');
        $test->replace($original);

        // filter funtion
        $test->filter(function ($item) {
            // find letter contain 'e' letter
            return strpos($item, 'e') !== false ? true : false;
        });
        $copy_origin = array_filter($original, function ($item) {
            // find letter contain 'e' letter
            return strpos($item, 'e') !== false ? true : false;
        });
        $this->assertEquals($test->all(), $copy_origin, 'filter item in collection');
        $test->replace($original);

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

        // collection reverse
        $copy_origin = $original;
        $this->assertEquals(
            $test->reverse()->all(),
            array_reverse($copy_origin),
            'test reverse collection'
        );
        $test->replace($original);

        // sort collection
        // sort asc
        $this->assertEquals(
            $test->sort()->first(),
            'apel',
            'testing sort asc collection'
        );
        // sort desc
        $this->assertEquals(
            $test->sortDesc()->first(),
            'rambutan',
            'testing sort desc collection'
        );
        // sort using collback
        $test->sortBy(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        $this->assertEquals(
            $test->first(),
            'apel',
            'sort using user define asceding'
        );
        $test->sortByDecs(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        $this->assertEquals(
            $test->first(),
            'rambutan',
            'sort using user define decsending'
        );

        // sort colection by key
        $this->assertEquals(
            $test->sortKey()->first(),
            'mangga',
            'sort collection asc with key'
        );
        $this->assertEquals(
            $test->sortKeyDesc()->first(),
            'peer',
            'sort collection desc with key'
        );
        $test->replace($original);

        // clone collection
        $this->assertEquals(
            $test->clone()->reverse()->first(),
            $test->last(),
            'clone collection without interupt original'
        );

        // reject
        $copy_origin = $original;
        unset($copy_origin['buah_2']);
        $this->assertEquals(
            $test->reject(fn ($item) => $item == 'jeruk')->all(),
            $copy_origin,
            'its like filter but the oposite'
        );

        // chunk
        $chunk = $test->clone()->chunk(3)->all();
        $this->assertEquals(
            [
                ['buah_1' => 'mangga', 'buah_3' => 'apel', 'buah_4' => 'melon'],
                ['buah_5' => 'rambutan', 'buah_6' => 'peer'],
            ],
            $chunk,
            'chunk to 3'
        );

        // split
        $split = $test->clone()->split(3)->all();
        $this->assertEquals(
            [
                ['buah_1' => 'mangga', 'buah_3' => 'apel'],
                ['buah_4' => 'melon', 'buah_5' => 'rambutan'],
                ['buah_6' => 'peer'],
            ],
            $split,
            'split to 2'
        );

        $only = $test->clone()->only(['buah_1', 'buah_5']);
        $this->assertEquals(
            ['buah_1' => 'mangga', 'buah_5' => 'rambutan'],
            $only->all(),
            'show only some'
        );

        $except = $test->clone()->except(['buah_3', 'buah_4', 'buah_6']);
        $this->assertEquals(
            ['buah_1' => 'mangga', 'buah_5' => 'rambutan'],
            $except->all(),
            'show list with except'
        );

        // fletten
        $array_nesting = [
            'first' => ['buah_1' => 'mangga', ['buah_2' => 'jeruk', 'buah_3' => 'apel', 'buah_4' => 'melon']],
            'mid'   => ['buah_4' => 'melon', ['buah_5' => 'rambutan']],
            'last'  => ['buah_6' => 'peer'],
        ];
        $flatten = new Collection($array_nesting);
        $this->assertEquals(
            $original,
            $flatten->flatten()->all(),
            'flatten nesting array'
        );
    }

    /** @test */
    public function itCollectionChainWorkGreat(): void
    {
        $origin     = [0, 1, 2, 3, 4];
        $collection = new Collection($origin);

        $chain = $collection
            ->add($origin)
            ->remove(0)
            ->set(0, 0)
            ->clear()
            ->replace($origin)
            ->each(fn ($el) => in_array($el, $origin))
            ->map(fn ($el) => $el + 100 - (2 * 50)) // equal +0
            ->filter(fn ($el) => $el > -1)
            ->sort()
            ->sortDesc()
            ->sortKey()
            ->sortKeyDesc()
            ->sortBy(function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            })
            ->sortByDecs(function ($a, $b) {
                if ($b == $a) {
                    return 0;
                }

                return ($b < $a) ? -1 : 1;
            })
            ->all()
        ;

        $this->assertEquals($chain, $origin, 'all collection with chain is wotk');
    }

    /** @test */
    public function itCanAddCollectionFromCollection()
    {
        $arr_1 = ['a' => 'b'];
        $arr_2 = ['c' => 'd'];

        $collect_1 = new Collection($arr_1);
        $collect_2 = new CollectionImmutable($arr_2);

        $collect = new Collection([]);
        $collect->ref($collect_1)->ref($collect_2);

        $this->assertEquals(['a'=>'b', 'c'=>'d'], $collect->all());
    }

    /** @test */
    public function itCanActingLikeArray()
    {
        $coll = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertArrayHasKey('one', $coll);
        $this->assertArrayHasKey('two', $coll);
        $this->assertArrayHasKey('three', $coll);
    }

    /** @test */
    public function itCanDoLikeArray()
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new Collection($arr);

        // get
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }

        // set
        $coll['four'] = 4;
        $this->assertArrayHasKey('four', $coll);

        // has
        $this->assertTrue(isset($coll['four']));

        // unset
        unset($coll['four']);
        $this->assertEquals($arr, $coll->all());
    }

    /** @test */
    public function itCanByIterator()
    {
        $coll = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);

        foreach ($coll as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }
    }

    /** @test */
    public function itCanByShuffle()
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new Collection($arr);

        $coll->shuffle();

        foreach ($arr as $key => $val) {
            $this->assertArrayHasKey($key, $coll);
        }
    }

    /** @test */
    public function itCanMapWithKeys()
    {
        $arr = new Collection([
            [
                'name'  => 'taylor',
                'email' => 'taylor@laravel.com',
            ], [
                'name'  => 'pradana',
                'email' => 'pradana@savanna.com',
            ],
        ]);

        $assocBy = $arr->assocBy(fn ($item) => [$item['name'] => $item['email']]);

        $this->assertEquals([
            'taylor'  => 'taylor@laravel.com',
            'pradana' => 'pradana@savanna.com',
        ], $assocBy->toArray());
    }

    /** @test */
    public function itCanCloneColection()
    {
        $ori = new Collection([
            'one' => 'one',
            'two' => [
                'one',
                'two' => [1, 2],
            ],
            'three' => new Collection([]),
        ]);

        $clone = clone $ori;

        $ori->set('one', 'uno');
        $this->assertEquals('one', $clone->get('one'));

        $clone->set('one', 1);
        $this->assertEquals('uno', $ori->get('one'));
    }

    /** @test */
    public function itCanGetSumUsingReduce()
    {
        $collection = new Collection([1, 2, 3, 4]);

        $sum = $collection->reduse(fn ($carry, $item) => $carry + $item);

        $this->assertTrue($sum === 10);
    }

    /** @test */
    public function itCanGetTakeFirst()
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([10, 20], $coll->take(2)->toArray());
    }

    /** @test */
    public function itCanGetTakeLast()
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([80, 90], $coll->take(-2)->toArray());
    }

    /** @test */
    public function itCanPushNewItem()
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);
        $coll->push(100);

        $this->assertTrue(in_array(100, $coll->toArray()));
    }

    /** @test */
    public function itCanGetDiff()
    {
        $coll = new Collection([1, 2, 3, 4, 5]);
        $coll->diff([2, 4, 6, 8]);

        $this->assertEquals([1, 3, 5], $coll->items());
    }

    /** @test */
    public function itCanGetDiffUsingKey()
    {
        $coll = new Collection([
            'buah_1' => 'mangga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
        ]);
        $coll->diffKeys([
            'buah_2' => 'orange',
            'buah_4' => 'water malon',
            'buah_6' => 'six',
            'buah_8' => 'eight',
        ]);

        $this->assertEquals([
            'buah_1' => 'mangga',
            'buah_3' => 'apel',
            'buah_5' => 'rambutan',
        ], $coll->toArray());
    }

    /** @test */
    public function itCanGetDiffUsingAssoc()
    {
        $coll = new Collection([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ]);
        $coll->diffAssoc([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ]);

        $this->assertEquals([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ], $coll->toArray());
    }

    /** @test */
    public function itCanGetcomplement()
    {
        $coll = new Collection([1, 2, 3, 4, 5]);
        $coll->complement([2, 4, 6, 8]);

        $this->assertEquals([6, 8], $coll->items());
    }

    /** @test */
    public function itCanGetComplementUsingKey()
    {
        $coll = new Collection([
            'buah_1' => 'mangga',
            'buah_2' => 'jeruk',
            'buah_3' => 'apel',
            'buah_4' => 'melon',
            'buah_5' => 'rambutan',
        ]);
        $coll->complementKeys([
            'buah_2' => 'orange',
            'buah_4' => 'water malon',
            'buah_6' => 'six',
            'buah_8' => 'eight',
        ]);

        $this->assertEquals([
            'buah_6' => 'six',
            'buah_8' => 'eight',
        ], $coll->toArray());
    }

    /** @test */
    public function itCanGetComplementUsingAssoc()
    {
        $coll = new Collection([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ]);
        $coll->complementAssoc([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ]);

        $this->assertEquals([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ], $coll->toArray());
    }

    /**
     * @test
     */
    public function itCanGetFilteredUsingWhere()
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];
        $equal = (new Collection($data))->where('age', '=', '13');
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
        ], $equal->toArray());

        $identical = (new Collection($data))->where('age', '===', 13);
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
        ], $identical->toArray());

        $notequal = (new Collection($data))->where('age', '!=', '13');
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            4       => ['user' => 'user5', 'age' => 14],
        ], $notequal->toArray());

        $notequalidentical = (new Collection($data))->where('age', '!==', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            4       => ['user' => 'user5', 'age' => 14],
        ], $notequalidentical->toArray());

        $greathat = (new Collection($data))->where('age', '>', 13);
        $this->assertEquals([
            4 => ['user' => 'user5', 'age' => 14],
        ], $greathat->toArray());

        $greathatequal = (new Collection($data))->where('age', '>=', 13);
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
            4 => ['user' => 'user5', 'age' => 14],
        ], $greathatequal->toArray());

        $lessthat = (new Collection($data))->where('age', '<', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $lessthat->toArray());

        $lessthatequal = (new Collection($data))->where('age', '<=', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
        ], $lessthatequal->toArray());
    }

    /**
     * @test
     */
    public function itCanFilterDataUsingWhereIn()
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];

        $wherein = (new Collection($data))->whereIn('age', [10, 12]);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $wherein->toArray());
    }

    /**
     * @test
     */
    public function itCanFilterDataUsingWhereNotIn()
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];

        $wherein = (new Collection($data))->whereNotIn('age', [13, 14]);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $wherein->toArray());
    }
}
