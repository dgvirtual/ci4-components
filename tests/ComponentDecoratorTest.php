<?php

namespace Tests;

use Dgvirtual\Components\Libraries\ComponentDecorator;
use Dgvirtual\Components\Libraries\ComponentRenderer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
final class ComponentDecoratorTest extends TestCase
{
    public function testDecorate()
    {
        $html   = '<div><x-button-green>Click me!</x-button-green></div>';
        $result = ComponentDecorator::decorate($html);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('Click me!', $result);
    }

    public function testDecoratePairedIncludesSelfClosing()
    {
        $html   = '<div><x-button-green><x-bootstrap-icon img="airplane" />Click me!</x-button-green></div>';
        $result = ComponentDecorator::decorate($html);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('<i class="bi ', $result);
        $this->assertStringContainsString('Click me!', $result);
    }

    public function testDecoratePairedIncludesPaired()
    {
        $html   = '<div><x-button-green><x-button-red>Click me!</x-button-red>Click me too!</x-button-green></div>';
        $result = ComponentDecorator::decorate($html);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('background-color: green', $result);
        $this->assertStringContainsString('background-color: red', $result);
        $this->assertStringContainsString('Click me!', $result);
        $this->assertStringContainsString('Click me too!', $result);
    }
    public function testDecoratePairedIncludesPairedAndSelfClosing()
    {
        // improbable example of course
        $html   = '<div><x-button-green><x-bootstrap-icon img="airplane" /><x-button-red onclick="alert(\'I was clicked!\')">Click me!</x-button-red>Click me too!</x-button-green></div>';
        $result = ComponentDecorator::decorate($html);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('background-color: green', $result); //green btn rendered
        $this->assertStringContainsString('background-color: red', $result); // red btn rendered
        $this->assertStringContainsString('<i class="bi ', $result); // icon rendered
        $this->assertStringContainsString('Click me!', $result);
        $this->assertStringContainsString('Click me too!', $result);
    }

    public function testFactory()
    {
        $reflection = new ReflectionClass(ComponentDecorator::class);
        $method     = $reflection->getMethod('factory');
        $method->setAccessible(true);
        $result = $method->invoke(null);
        $this->assertInstanceOf(ComponentRenderer::class, $result);
    }

    public function testFactorySingleton()
    {
        $reflection = new ReflectionClass(ComponentDecorator::class);
        $method     = $reflection->getMethod('factory');
        $method->setAccessible(true);
        $firstInstance  = $method->invoke(null);
        $secondInstance = $method->invoke(null);
        $this->assertSame($firstInstance, $secondInstance);
    }
}
