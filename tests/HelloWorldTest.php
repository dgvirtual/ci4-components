<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HelloWorldTest extends TestCase
{
    public function testHelloWorld()
    {
        $this->assertSame('Hello, World!', 'Hello, World!');
    }
}
