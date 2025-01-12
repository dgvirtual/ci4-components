<?php

namespace Tests;

use Dgvirtual\Components\Libraries\Component;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    /**
     * Test whether the view renderer method generates html from view and data
     *
     * @return void
     */
    public function testRenderView()
    {
        $component = new Component();
        $view = __DIR__ . '/testView.php';
        $data = ['cardText' => 'What a nice card!'];

        // Create a temporary view file
        $phpCode = <<<PHP
            <div class="card"><?php echo \$cardText; ?></div>
            PHP;
        file_put_contents($view, $phpCode);

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('renderView');
        $method->setAccessible(true);

        $result = $method->invokeArgs($component, [$view, $data]);

        // Clean up the temporary view file
        unlink($view);


        $expected = '<div class="card">What a nice card!</div>';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test whether the view renderer method catches throwable exceptions
     *
     * @return void
     */
    public function testRenderViewCatchesThrowable()
    {
        $component = new Component();
        $view = __DIR__ . '/testViewWithException.php';
        $data = ['cardTextMisnamed' => 'What a nice card!'];

        // Create a temporary view file with a missing variable
        $phpCode = <<<PHP
<div class="card"><?php echo \$cardText; ?></div>
PHP;
        file_put_contents($view, $phpCode);

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('renderView');
        $method->setAccessible(true);

        $this->expectException(\Throwable::class);

        try {
            $method->invokeArgs($component, [$view, $data]);
        } finally {
            // Clean up the temporary view file
            unlink($view);
        }
    }
}
