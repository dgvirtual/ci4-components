<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Dgvirtual\Components\Libraries\Component;
use Dgvirtual\Components\Libraries\ComponentRenderer;

#[CoversClass(ComponentRenderer::class)]
class ComponentRendererTest extends TestCase
{
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new ComponentRenderer();
    }

    public function testRender()
    {
        $output = '<div><x-green-button>Click me!</x-green-button></div>';
        $result = $this->renderer->render($output);
        $this->assertIsString($result);

    }

    public function testRenderSelfClosingTags()
    {
        $output = '<x-avatar src="https://example.com/myavatar" />';
        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('renderSelfClosingTags');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$output]);
        $this->assertIsString($result);
        $this->assertStringContainsString('rounded-circle shadow-4', $result);
    }

    public function testRenderPairedTags()
    {
        $output = '<x-green-button>Click me!</x-green-button>';
        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('renderPairedTags');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$output]);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
    }

    public function testParseAttributes()
    {
        $attributes = 'class="btn" type="button"';
        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('parseAttributes');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$attributes]);
        $this->assertIsArray($result);
        $this->assertContains('button', $result);
    }

    public function testRenderView()
    {
        $view = __DIR__ . '/testView.php';
        $data = ['buttonText' => 'Click me!'];

        // Create a temporary view file
        $phpCode = <<<PHP
            <button><?php echo \$buttonText; ?></button>
            PHP;
        file_put_contents($view, $phpCode);

        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('renderView');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$view, $data]);

        // Clean up the temporary view file
        unlink($view);

        $this->assertIsString($result);
    }

    public function testFactoryIsNotInstance()
    {
        $name = 'green-button';
        $view = __DIR__ . '/../src/Views/Components/green-button.php';
        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('factory');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$name, $view]);
        // result should be null, since the component is not cotrolled
        // = does not have a corresponding class
        $this->assertNull($result);
    }

    public function testFactoryIsInstance()
    {
        $name = 'famous-quotes';
        $view = __DIR__ . '/../src/Views/Components/famous-quotes.php';
        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('factory');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$name, $view]);
        $this->assertInstanceOf(Component::class, $result);
    }

    public function testLocateView()
    {
        $name = 'green-button';
        $reflection = new \ReflectionClass($this->renderer);
        $method = $reflection->getMethod('locateView');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->renderer, [$name]);
        $this->assertIsString($result);
        $this->assertFileExists($result);
    }

    public function testGetComponentsLookupPaths()
    {
        $result = $this->invokeMethod($this->renderer, 'getComponentsLookupPaths', []);
        $this->assertIsArray($result);
        if (is_array($result)) {
            foreach ($result as $path) {
                $this->assertStringContainsString('Components', $path);
            }
        }
    }

    public function testStripQuotes()
    {
        $string = '"quoted string"';
        $result = $this->invokeMethod($this->renderer, 'stripQuotes', [$string]);
        $this->assertEquals($result, 'quoted string');
    }


    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
