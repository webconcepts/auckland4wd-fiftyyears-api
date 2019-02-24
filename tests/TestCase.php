<?php

use App\Exceptions\Handler;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Disable lumen's default exception handling during a test.
     * Copied from Laravel.
     */
    protected function withoutExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Exception $e) {}
            public function render($request, Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * Get the original data value returned to the response from the controller
     *
     * @param mixed $key single key, or . separated for checking multi level
     * @return mixed value
     */
    protected function responseData($key)
    {
        $data = $this->response->getOriginalContent();

        foreach (explode('.', $key) as $k) {
            $data = $data[$k];
        }

        return $data;
    }

    /**
     * Assert that the response content json contains the given key
     *
     * @param string $key
     */
    protected function assertJsonHasKey($key)
    {
        $this->assertObjectHasAttribute($key, json_decode($this->response->getContent()));
    }

    /**
     * Assert that a collection's contents equal an expected array of values
     *
     * Blatant copy of Adam Wathan's crafy use of the zip method :)
     *
     * @param array $expected
     * @param Collection $collection
     */
    protected function assertCollectionEquals($expected, Collection $collection)
    {
        $this->assertEquals(count($expected), count($collection));

        $collection->zip($expected)->each(function ($pair) {
            list($a, $b) = $pair;
            $this->assertTrue($a->is($b), 'Failed asserting that the collection equals the expected contents.');
        });
    }
}
