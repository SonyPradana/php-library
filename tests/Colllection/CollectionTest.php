<?php

use PHPUnit\Framework\TestCase;
use System\Collection\Collection;
use System\Collection\CollectionImmutable;

class CollectionTest extends TestCase
{
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
}
