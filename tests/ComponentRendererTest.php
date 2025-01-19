<?php

namespace Tests;

use Dgvirtual\Components\Libraries\Component;
use Dgvirtual\Components\Libraries\ComponentRenderer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
final class ComponentRendererTest extends TestCase
{
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new ComponentRenderer();
        helper('cache');
    }

    protected function setCache($slotValue = null)
    {
        // Set the cache with key 'famous_quote'
        $quoteData = [
            'quote' => [
                'text'   => 'The only way to do great work is to love what you do',
                'author' => 'Steve Jobs',
            ],
            'seconds' => 60,
        ];
        if ($slotValue) {
            $quoteData['slot'] = $slotValue;
        }
        cache()->save('famous_quote', $quoteData, 60);
    }

    protected function emptyCache(): void
    {
        // Delete the cache key 'famous_quote'
        cache()->delete('famous_quote');
        parent::tearDown();
    }

    public function testRender()
    {
        $html   = '<div><x-button-green>Click me!</x-button-green></div>';
        $result = $this->renderer->render($html);
        $this->assertIsString($result);
    }

    public function testRenderEmpty()
    {
        $html   = '';
        $result = $this->renderer->render($html);
        $this->assertSame($result, '');
    }

    public function testRenderSelfClosingTags()
    {
        $html   = '<x-avatar src="https://example.com/myavatar" />';
        $result = $this->invokeMethod($this->renderer, 'renderSelfClosingTags', [$html]);
        $this->assertIsString($result);
        $this->assertStringContainsString('rounded-circle shadow-4', $result);
    }

    public function testRenderSelfClosingTagsControlledComponent()
    {
        $this->setCache();
        $html   = '<x-famous-quotes />'; // used as self-closing here
        $result = $this->invokeMethod($this->renderer, 'renderSelfClosingTags', [$html]);
        $this->assertIsString($result);
        $this->assertStringContainsString('blockquote-footer text-center', $result);
        $this->emptyCache();
    }

    public function testRenderPairedTags()
    {
        $html   = '<x-button-green>Click me!</x-button-green>';
        $result = $this->invokeMethod($this->renderer, 'renderPairedTags', [$html]);
        $this->assertIsString($result);
        $this->assertStringContainsString('<button', $result);
    }

    // public function testRenderPairedTagsRecursive()
    // {
    //     $html = '<div class="anything"><x-button-green><x-bootstrap-icon /> Click me!</x-button-green></div>';
    //     $result = $this->invokeMethod($this->renderer, 'renderPairedTags', [$html]);

    //     $this->assertIsString($result);
    //     $this->assertStringContainsString('<i class="bi ', $result);
    // }

    public function testRenderPairedTagsControlledComponent()
    {
        $this->setCache('Really Famous');
        $html   = '<x-famous-quotes seconds="5">Really Famous</x-famous-quotes>';
        $result = $this->invokeMethod($this->renderer, 'renderPairedTags', [$html]);
        $this->assertIsString($result);
        $this->assertStringContainsString('Really Famous', $result);
        cache()->delete('famous_quote');
        $this->emptyCache();
    }

    public function testParseAttributes()
    {
        $attributes = 'class="btn" type="button"';
        $result     = $this->invokeMethod($this->renderer, 'parseAttributes', [$attributes]);
        $this->assertIsArray($result);
        $this->assertContains('button', $result);
    }

    public function testRenderView()
    {
        $view = __DIR__ . '/testView.php';
        $data = ['buttonText' => 'Click me!'];

        // Create a temporary view file
        $phpCode = <<<'PHP'
            <button><?php echo $buttonText; ?></button>
            PHP;
        file_put_contents($view, $phpCode);

        $result = $this->invokeMethod($this->renderer, 'renderView', [$view, $data]);

        // Clean up the temporary view file
        unlink($view);

        $this->assertIsString($result);
    }

    public function testRenderViewWithMisplacedVariable()
    {
        $view = __DIR__ . '/testViewWithMisplacedVariable.php';
        $data = ['cardTextMisnamed' => 'What a nice card!'];

        // Create a temporary view file with a missing variable
        $phpCode = <<<'PHP'
            <div class="card"><?php echo $cardText; ?></div>
            PHP;
        file_put_contents($view, $phpCode);

        $this->expectException(Throwable::class);

        try {
            $this->invokeMethod($this->renderer, 'renderView', [$view, $data]);
        } finally {
            // Clean up the temporary view file
            unlink($view);
        }
    }

    public function testFactoryIsNotInstance()
    {
        $name   = 'button-green';
        $view   = __DIR__ . '/../src/Components/button-green.php';
        $result = $this->invokeMethod($this->renderer, 'factory', [$name, $view]);
        $this->assertNull($result);
    }

    public function testFactoryIsInstance()
    {
        $name   = 'famous-quotes';
        $view   = __DIR__ . '/../src/Components/famous-quotes.php';
        $result = $this->invokeMethod($this->renderer, 'factory', [$name, $view]);
        $this->assertInstanceOf(Component::class, $result);
    }

    public function testFactoryClassNotFound()
    {
        // do mismatch: component class is valid, view file exists, but
        // does not have a corresponding class
        $name   = 'button-green';
        $view   = __DIR__ . '/../src/Components/famous-quotes.php';
        $result = $this->invokeMethod($this->renderer, 'factory', [$name, $view]);
        $this->assertNull($result);
    }

    public function testLocateView()
    {
        $name   = 'button-green';
        $result = $this->invokeMethod($this->renderer, 'locateView', [$name]);
        $this->assertIsString($result);
        $this->assertFileExists($result);
    }

    public function testLocateViewNotFound()
    {
        $name = 'yellow-button';
        // Set the expectation for the exception BEFORE invoking the method
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View not found for component: yellow-button');
        $this->invokeMethod($this->renderer, 'locateView', [$name]);
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
        $this->assertSame($result, 'quoted string');
    }

    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
