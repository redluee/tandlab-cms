<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Http\Router;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class RouterTest extends TestCase
{
    public function testMatchesStaticRoute(): void
    {
        $router = new Router();
        $called = false;
        $router->get('/tand', function () use (&$called): void {
            $called = true;
        });

        $router->dispatch('GET', '/tand');

        $this->assertTrue($called);
    }

    public function testMatchesRouteWithParams(): void
    {
        $router = new Router();
        $received = null;
        $router->get('/admin/bewerken/{page}', function (array $params) use (&$received): void {
            $received = $params;
        });

        $router->dispatch('GET', '/admin/bewerken/tand');

        $this->assertSame(['page' => 'tand'], $received);
    }

    public function testTrailingSlashIsNormalized(): void
    {
        $router = new Router();
        $called = false;
        $router->get('/team', function () use (&$called): void {
            $called = true;
        });

        $router->dispatch('GET', '/team/');

        $this->assertTrue($called);
    }

    public function testQueryStringIsIgnoredForMatching(): void
    {
        $router = new Router();
        $called = false;
        $router->get('/home', function () use (&$called): void {
            $called = true;
        });

        $router->dispatch('GET', '/home?foo=bar');

        $this->assertTrue($called);
    }

    public function testOnlyRouteWithMatchingMethodIsInvoked(): void
    {
        $router = new Router();
        $getCalled = false;
        $postCalled = false;
        $router->post('/contact', function () use (&$postCalled): void {
            $postCalled = true;
        });
        $router->get('/contact', function () use (&$getCalled): void {
            $getCalled = true;
        });

        $router->dispatch('GET', '/contact');

        $this->assertTrue($getCalled);
        $this->assertFalse($postCalled);
    }

    /**
     * De 404-fallback van dispatch() rendert de publieke view (inclusief een
     * databasequery voor site_settings), dus dat pad testen we hier niet —
     * alleen de pure matchlogica, via reflectie op de private methode.
     */
    public function testDifferentSegmentCountDoesNotMatch(): void
    {
        $router = new Router();
        $match = new ReflectionMethod(Router::class, 'match');

        $this->assertNull($match->invoke($router, '/tand/{slug}', '/tand'));
    }
}
