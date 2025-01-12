<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Dgvirtual\Components\Libraries\ComponentDecorator;
use Dgvirtual\Components\Libraries\ComponentRenderer;

class ComponentDecoratorTest extends TestCase
{
    public function testDecorate()
    {
        $html = '<div><x-green-button>Click me!</x-green-button></div>';
        $result = ComponentDecorator::decorate($html);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('Click me!', $result);
    }

    public function testFactory()
    {
        $reflection = new \ReflectionClass(ComponentDecorator::class);
        $method = $reflection->getMethod('factory');
        $method->setAccessible(true);
        $result = $method->invoke(null);
        $this->assertInstanceOf(ComponentRenderer::class, $result);
    }

    public function testFactorySingleton()
    {
        $reflection = new \ReflectionClass(ComponentDecorator::class);
        $method = $reflection->getMethod('factory');
        $method->setAccessible(true);
        $firstInstance = $method->invoke(null);
        $secondInstance = $method->invoke(null);
        $this->assertSame($firstInstance, $secondInstance);
    }
}