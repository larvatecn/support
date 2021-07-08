<?php

namespace Tests;

use Larva\Support\ArrayHelper;
use Larva\Support\Collection;
use stdClass;

class ArrayHelperTest extends TestCase
{
    public function testAdd()
    {
        $array = ArrayHelper::add(['name' => 'EasyWeChat'], 'price', 100);
        $this->assertSame(['name' => 'EasyWeChat', 'price' => 100], $array);
    }

    public function testCrossJoin()
    {
        // Single dimension
        $this->assertSame(
            [[1, 'a'], [1, 'b'], [1, 'c']],
            ArrayHelper::crossJoin([1], ['a', 'b', 'c'])
        );
        // Square matrix
        $this->assertSame(
            [[1, 'a'], [1, 'b'], [2, 'a'], [2, 'b']],
            ArrayHelper::crossJoin([1, 2], ['a', 'b'])
        );
        // Rectangular matrix
        $this->assertSame(
            [[1, 'a'], [1, 'b'], [1, 'c'], [2, 'a'], [2, 'b'], [2, 'c']],
            ArrayHelper::crossJoin([1, 2], ['a', 'b', 'c'])
        );
        // 3D matrix
        $this->assertSame(
            [
                [1, 'a', 'I'], [1, 'a', 'II'], [1, 'a', 'III'],
                [1, 'b', 'I'], [1, 'b', 'II'], [1, 'b', 'III'],
                [2, 'a', 'I'], [2, 'a', 'II'], [2, 'a', 'III'],
                [2, 'b', 'I'], [2, 'b', 'II'], [2, 'b', 'III'],
            ],
            ArrayHelper::crossJoin([1, 2], ['a', 'b'], ['I', 'II', 'III'])
        );
        // With 1 empty dimension
        $this->assertSame([], ArrayHelper::crossJoin([], ['a', 'b'], ['I', 'II', 'III']));
        $this->assertSame([], ArrayHelper::crossJoin([1, 2], [], ['I', 'II', 'III']));
        $this->assertSame([], ArrayHelper::crossJoin([1, 2], ['a', 'b'], []));
        // With empty arrays
        $this->assertSame([], ArrayHelper::crossJoin([], [], []));
        $this->assertSame([], ArrayHelper::crossJoin([], []));
        $this->assertSame([], ArrayHelper::crossJoin([]));
        // Not really a proper usage, still, test for preserving BC
        $this->assertSame([[]], ArrayHelper::crossJoin());
    }

    public function testDivide()
    {
        list($keys, $values) = ArrayHelper::divide(['name' => 'EasyWeChat']);
        $this->assertSame(['name'], $keys);
        $this->assertSame(['EasyWeChat'], $values);
    }

    public function testDot()
    {
        $array = ArrayHelper::dot(['foo' => ['bar' => 'baz']]);
        $this->assertSame(['foo.bar' => 'baz'], $array);
        $array = ArrayHelper::dot([]);
        $this->assertSame([], $array);
        $array = ArrayHelper::dot(['foo' => []]);
        $this->assertSame(['foo' => []], $array);
        $array = ArrayHelper::dot(['foo' => ['bar' => []]]);
        $this->assertSame(['foo.bar' => []], $array);
    }

    public function testExcept()
    {
        $array = ['name' => 'EasyWeChat', 'price' => 100];
        $array = ArrayHelper::except($array, ['price']);
        $this->assertSame(['name' => 'EasyWeChat'], $array);
    }

    public function testExists()
    {
        $this->assertTrue(ArrayHelper::exists([1], 0));
        $this->assertTrue(ArrayHelper::exists([null], 0));
        $this->assertTrue(ArrayHelper::exists(['a' => 1], 'a'));
        $this->assertTrue(ArrayHelper::exists(['a' => null], 'a'));
        $this->assertFalse(ArrayHelper::exists([1], 1));
        $this->assertFalse(ArrayHelper::exists([null], 1));
        $this->assertFalse(ArrayHelper::exists(['a' => 1], 0));
    }

    public function testFirst()
    {
        $array = [100, 200, 300];
        $value = ArrayHelper::first($array, function ($value) {
            return $value >= 150;
        });
        $this->assertSame(200, $value);
        $this->assertSame(100, ArrayHelper::first($array));

        $this->assertSame('default', ArrayHelper::first([], null, 'default'));

        $this->assertSame('default', ArrayHelper::first([], function () {
            return false;
        }, 'default'));
    }

    public function testLast()
    {
        $array = [100, 200, 300];
        $last = ArrayHelper::last($array, function ($value) {
            return $value < 250;
        });
        $this->assertSame(200, $last);
        $last = ArrayHelper::last($array, function ($value, $key) {
            return $key < 2;
        });
        $this->assertSame(200, $last);
        $this->assertSame(300, ArrayHelper::last($array));
    }

    public function testFlatten()
    {
        // Flat arrays are unaffected
        $array = ['#foo', '#bar', '#baz'];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten(['#foo', '#bar', '#baz']));
        // Nested arrays are flattened with existing flat items
        $array = [['#foo', '#bar'], '#baz'];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten($array));
        // Flattened array includes "null" items
        $array = [['#foo', null], '#baz', null];
        $this->assertSame(['#foo', null, '#baz', null], ArrayHelper::flatten($array));
        // Sets of nested arrays are flattened
        $array = [['#foo', '#bar'], ['#baz']];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten($array));
        // Deeply nested arrays are flattened
        $array = [['#foo', ['#bar']], ['#baz']];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten($array));
        // Nested arrays are flattened alongside arrays
        $array = [new Collection(['#foo', '#bar']), ['#baz']];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten($array));
        // Nested arrays containing plain arrays are flattened
        $array = [new Collection(['#foo', ['#bar']]), ['#baz']];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten($array));
        // Nested arrays containing arrays are flattened
        $array = [['#foo', new Collection(['#bar'])], ['#baz']];
        $this->assertSame(['#foo', '#bar', '#baz'], ArrayHelper::flatten($array));
        // Nested arrays containing arrays containing arrays are flattened
        $array = [['#foo', new Collection(['#bar', ['#zap']])], ['#baz']];
        $this->assertSame(['#foo', '#bar', '#zap', '#baz'], ArrayHelper::flatten($array));
    }

    public function testFlattenWithDepth()
    {
        // No depth flattens recursively
        $array = [['#foo', ['#bar', ['#baz']]], '#zap'];
        $this->assertSame(['#foo', '#bar', '#baz', '#zap'], ArrayHelper::flatten($array));
        // Specifying a depth only flattens to that depth
        $array = [['#foo', ['#bar', ['#baz']]], '#zap'];
        $this->assertSame(['#foo', ['#bar', ['#baz']], '#zap'], ArrayHelper::flatten($array, 1));
        $array = [['#foo', ['#bar', ['#baz']]], '#zap'];
        $this->assertSame(['#foo', '#bar', ['#baz'], '#zap'], ArrayHelper::flatten($array, 2));
    }

    public function testGet()
    {
        $array = ['products.item' => ['price' => 100]];
        $this->assertSame(['price' => 100], ArrayHelper::get($array, 'products.item'));
        $array = ['products' => ['item' => ['price' => 100]]];
        $value = ArrayHelper::get($array, 'products.item');
        $this->assertSame(['price' => 100], $value);
        // Test null array values
        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertNull(ArrayHelper::get($array, 'foo', 'default'));
        $this->assertNull(ArrayHelper::get($array, 'bar.baz', 'default'));
        // Test null key returns the whole array
        $array = ['foo', 'bar'];
        $this->assertSame($array, ArrayHelper::get($array, null));
        // Test $array is empty and key is null
        $this->assertSame([], ArrayHelper::get([], null));
        $this->assertSame([], ArrayHelper::get([], null, 'default'));
    }

    public function testHas()
    {
        $array = ['products.item' => ['price' => 100]];
        $this->assertTrue(ArrayHelper::has($array, 'products.item'));
        $array = ['products' => ['item' => ['price' => 100]]];
        $this->assertTrue(ArrayHelper::has($array, 'products.item'));
        $this->assertTrue(ArrayHelper::has($array, 'products.item.price'));
        $this->assertFalse(ArrayHelper::has($array, 'products.foo'));
        $this->assertFalse(ArrayHelper::has($array, 'products.item.foo'));
        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertTrue(ArrayHelper::has($array, 'foo'));
        $this->assertTrue(ArrayHelper::has($array, 'bar.baz'));
        $array = ['foo', 'bar'];
        $this->assertFalse(ArrayHelper::has($array, null));
        $this->assertFalse(ArrayHelper::has([], null));
        $array = ['products' => ['item' => ['price' => 100]]];
        $this->assertTrue(ArrayHelper::has($array, ['products.item']));
        $this->assertTrue(ArrayHelper::has($array, ['products.item', 'products.item.price']));
        $this->assertTrue(ArrayHelper::has($array, ['products', 'products']));
        $this->assertFalse(ArrayHelper::has($array, ['foo']));
        $this->assertFalse(ArrayHelper::has($array, []));
        $this->assertFalse(ArrayHelper::has($array, ['products.item', 'products.price']));
        $this->assertFalse(ArrayHelper::has([], [null]));
    }

    public function testIsAssoc()
    {
        $this->assertTrue(ArrayHelper::isAssoc(['a' => 'a', 0 => 'b']));
        $this->assertTrue(ArrayHelper::isAssoc([1 => 'a', 0 => 'b']));
        $this->assertTrue(ArrayHelper::isAssoc([1 => 'a', 2 => 'b']));
        $this->assertFalse(ArrayHelper::isAssoc([0 => 'a', 1 => 'b']));
        $this->assertFalse(ArrayHelper::isAssoc(['a', 'b']));
    }

    public function testOnly()
    {
        $array = ['name' => 'EasyWeChat', 'price' => 100, 'orders' => 10];
        $array = ArrayHelper::only($array, ['name', 'price']);
        $this->assertSame(['name' => 'EasyWeChat', 'price' => 100], $array);
    }

    public function testPrepend()
    {
        $array = ArrayHelper::prepend(['one', 'two', 'three', 'four'], 'zero');
        $this->assertSame(['zero', 'one', 'two', 'three', 'four'], $array);
        $array = ArrayHelper::prepend(['one' => 1, 'two' => 2], 0, 'zero');
        $this->assertSame(['zero' => 0, 'one' => 1, 'two' => 2], $array);
    }

    public function testPull()
    {
        $array = ['name' => 'EasyWeChat', 'price' => 100];
        $name = ArrayHelper::pull($array, 'name');
        $this->assertSame('EasyWeChat', $name);
        $this->assertSame(['price' => 100], $array);
        // Only works on first level keys
        $array = ['i@example.com' => 'Joe', 'jack@localhost' => 'Jane'];
        $name = ArrayHelper::pull($array, 'i@example.com');
        $this->assertSame('Joe', $name);
        $this->assertSame(['jack@localhost' => 'Jane'], $array);
        // Does not work for nested keys
        $array = ['emails' => ['i@example.com' => 'Joe', 'jack@localhost' => 'Jane']];
        $name = ArrayHelper::pull($array, 'emails.i@example.com');
        $this->assertNull($name);
        $this->assertSame(['emails' => ['i@example.com' => 'Joe', 'jack@localhost' => 'Jane']], $array);
    }

    public function testRandom()
    {
        $randomValue = ArrayHelper::random(['foo', 'bar', 'baz']);
        $this->assertContains($randomValue, ['foo', 'bar', 'baz']);
        $randomValues = ArrayHelper::random(['foo', 'bar', 'baz'], 1);
        $this->assertIsArray($randomValues);
        $this->assertCount(1, $randomValues);
        $this->assertContains($randomValues[0], ['foo', 'bar', 'baz']);
        $randomValues = ArrayHelper::random(['foo', 'bar', 'baz'], 2);
        $this->assertIsArray($randomValues);
        $this->assertCount(2, $randomValues);
        $this->assertContains($randomValues[0], ['foo', 'bar', 'baz']);
        $this->assertContains($randomValues[1], ['foo', 'bar', 'baz']);
    }

    public function testSet()
    {
        $array = ['products' => ['item' => ['price' => 100]]];
        ArrayHelper::set($array, 'products.item.price', 200);
        ArrayHelper::set($array, 'goods.item.price', 200);
        $this->assertSame(['products' => ['item' => ['price' => 200]], 'goods' => ['item' => ['price' => 200]]], $array);
    }

    public function testWhere()
    {
        $array = [100, '200', 300, '400', 500];
        $array = ArrayHelper::where($array, function ($value, $key) {
            return is_string($value);
        });
        $this->assertSame([1 => '200', 3 => '400'], $array);
    }

    public function testWhereKey()
    {
        $array = ['10' => 1, 'foo' => 3, 20 => 2];
        $array = ArrayHelper::where($array, function ($value, $key) {
            return is_numeric($key);
        });
        $this->assertSame(['10' => 1, 20 => 2], $array);
    }

    public function testForget()
    {
        $array = ['products' => ['item' => ['price' => 100]]];
        ArrayHelper::forget($array, null);
        $this->assertSame(['products' => ['item' => ['price' => 100]]], $array);
        $array = ['products' => ['item' => ['price' => 100]]];
        ArrayHelper::forget($array, []);
        $this->assertSame(['products' => ['item' => ['price' => 100]]], $array);
        $array = ['products' => ['item' => ['price' => 100]]];
        ArrayHelper::forget($array, 'products.item');
        $this->assertSame(['products' => []], $array);
        $array = ['products' => ['item' => ['price' => 100]]];
        ArrayHelper::forget($array, 'products.item.price');
        $this->assertSame(['products' => ['item' => []]], $array);
        $array = ['products' => ['item' => ['price' => 100]]];
        ArrayHelper::forget($array, 'products.final.price');
        $this->assertSame(['products' => ['item' => ['price' => 100]]], $array);
        $array = ['shop' => ['cart' => [150 => 0]]];
        ArrayHelper::forget($array, 'shop.final.cart');
        $this->assertSame(['shop' => ['cart' => [150 => 0]]], $array);
        $array = ['products' => ['item' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        ArrayHelper::forget($array, 'products.item.price.taxes');
        $this->assertSame(['products' => ['item' => ['price' => ['original' => 50]]]], $array);
        $array = ['products' => ['item' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        ArrayHelper::forget($array, 'products.item.final.taxes');
        $this->assertSame(['products' => ['item' => ['price' => ['original' => 50, 'taxes' => 60]]]], $array);
        $array = ['products' => ['item' => ['price' => 50], null => 'something']];
        ArrayHelper::forget($array, ['products.amount.all', 'products.item.price']);
        $this->assertSame(['products' => ['item' => [], null => 'something']], $array);
        // Only works on first level keys
        $array = ['i@example.com' => 'Joe', 'i@easywechat.com' => 'Jane'];
        ArrayHelper::forget($array, 'i@example.com');
        $this->assertSame(['i@easywechat.com' => 'Jane'], $array);
        // Does not work for nested keys
        $array = ['emails' => ['i@example.com' => ['name' => 'Joe'], 'jack@localhost' => ['name' => 'Jane']]];
        ArrayHelper::forget($array, ['emails.i@example.com', 'emails.jack@localhost']);
        $this->assertSame(['emails' => ['i@example.com' => ['name' => 'Joe']]], $array);
    }

    public function testWrap()
    {
        $string = 'a';
        $array = ['a'];
        $object = new stdClass();
        $object->value = 'a';
        $this->assertSame(['a'], ArrayHelper::wrap($string));
        $this->assertSame($array, ArrayHelper::wrap($array));
        $this->assertSame([$object], ArrayHelper::wrap($object));
    }
}
