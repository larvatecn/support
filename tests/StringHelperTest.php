<?php
namespace Tests;


use Larva\Support\StringHelper;

class StringHelperTest extends TestCase
{
    public function testCamel()
    {
        $this->assertSame('fooBar', StringHelper::camel('FooBar'));
        $this->assertSame('fooBar', StringHelper::camel('FooBar')); // cached
        $this->assertSame('fooBar', StringHelper::camel('foo_bar'));
        $this->assertSame('fooBar', StringHelper::camel('_foo_bar'));
        $this->assertSame('fooBar', StringHelper::camel('_foo_bar_'));
    }

    public function testStudly()
    {
        $this->assertSame('FooBar', StringHelper::studly('fooBar'));
        $this->assertSame('FooBar', StringHelper::studly('_foo_bar'));
        $this->assertSame('FooBar', StringHelper::studly('_foo_bar_'));
        $this->assertSame('FooBar', StringHelper::studly('_foo_bar_'));
    }

    public function testSnake()
    {
        $this->assertSame('laravel_p_h_p_framework', StringHelper::snake('LaravelPHPFramework'));
        $this->assertSame('laravel_php_framework', StringHelper::snake('LaravelPhpFramework'));
        $this->assertSame('laravel php framework', StringHelper::snake('LaravelPhpFramework', ' '));
        $this->assertSame('laravel_php_framework', StringHelper::snake('Laravel Php Framework'));
        $this->assertSame('laravel_php_framework', StringHelper::snake('Laravel    Php      Framework   '));
        // ensure cache keys don't overlap
        $this->assertSame('laravel__php__framework', StringHelper::snake('LaravelPhpFramework', '__'));
        $this->assertSame('laravel_php_framework_', StringHelper::snake('LaravelPhpFramework_', '_'));
        $this->assertSame('laravel_php_framework', StringHelper::snake('laravel php Framework'));
        $this->assertSame('laravel_php_frame_work', StringHelper::snake('laravel php FrameWork'));
        // prevent breaking changes
        $this->assertSame('foo-bar', StringHelper::snake('foo-bar'));
        $this->assertSame('foo-_bar', StringHelper::snake('Foo-Bar'));
        $this->assertSame('foo__bar', StringHelper::snake('Foo_Bar'));
        $this->assertSame('żółtałódka', StringHelper::snake('ŻółtaŁódka'));
    }

    public function testTitle()
    {
        $this->assertSame('Welcome Back', StringHelper::title('welcome back'));
    }

    public function testRandom()
    {
        $this->assertIsString(StringHelper::random(10));
        $this->assertTrue(16 === strlen(StringHelper::random()));
    }

    public function testQuickRandom()
    {
        $this->assertIsString(StringHelper::quickRandom(10));
        $this->assertTrue(16 === strlen(StringHelper::quickRandom()));
    }

    public function testUpper()
    {
        $this->assertSame('USERNAME', StringHelper::upper('username'));
        $this->assertSame('USERNAME', StringHelper::upper('userNaMe'));
    }
}
