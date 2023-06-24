<?php

use PHPUnit\Framework\TestCase;
use System\Router\Router;

class BasicRouteTest extends TestCase
{
    private function registerRouter()
    {
        Router::get('/test', function () {
            echo 'render success';
        })->name('route.test');

        Router::get('/test/number/(:id)', function ($id) {
            echo 'render success, with id is - ' . $id;
        })->name('route.test.number');

        Router::get('/test/text/(:text)', function ($id) {
            echo 'render success, with id is - ' . $id;
        })->name('route.test.text');

        Router::get('/test/any/(:any)', function ($id) {
            echo 'render success, with id is - ' . $id;
        })->name('route.test.any');

        Router::get('/test/any/(:all)', function ($id) {
            echo 'render success, with id is - ' . $id;
        });
    }

    private function registerGroupRouter()
    {
        Router::prefix('/page/')->group(function () {
            Router::get('one', function () {
                echo 'page one';
            });
            Router::get('two', function () {
                echo 'page two';
            });
        });
    }

    private function registerRouterDiferentMethod()
    {
        Router::match(['get'], '/get', function () {
            echo 'render success using get';
        })->name('name_is_get');
        Router::match(['head'], '/head', function () {
            echo 'render success using get over head method';
        });
        Router::match(['post'], '/post', function () {
            echo 'render success using post';
        });
        Router::match(['put'], '/put', function () {
            echo 'render success using put';
        });
        Router::match(['patch'], '/patch', function () {
            echo 'render success using patch';
        });
        Router::match(['delete'], '/delete', function () {
            echo 'render success using delete';
        });
        Router::match(['options'], '/options', function () {
            echo 'render success using options';
        });
    }

    private function registerRouterMethodNotAlloed()
    {
        Router::methodNotAllowed(function () {
            echo 'method not allowed';
        });
    }

    private function registerRouterNotFound()
    {
        Router::pathNotFound(function () {
            echo 'page not found 404';
        });
    }

    private function getRespone(string $method, string $url)
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI']    = $url;

        ob_start();
        Router::run('/');

        return ob_get_clean();
    }

    /**
     * @test
     */
    public function itRouteCanBeRender(): void
    {
        $this->registerRouter();

        $route_basic  = $this->getRespone('get', '/test');
        $route_number = $this->getRespone('get', '/test/number/123');
        $route_text   = $this->getRespone('get', '/test/text/xyz');
        $route_any    = $this->getRespone('get', '/test/any/xyz+123');
        $route_all    = $this->getRespone('get', '/test/any/xyz 123'); // allow all charater

        $this->assertEquals(
            'render success',
            $route_basic,
            'the route must output same text'
        );
        $this->assertEquals(
            'render success, with id is - 123',
            $route_number,
            'the route must output same text'
        );
        $this->assertEquals(
            'render success, with id is - xyz',
            $route_text,
            'the route must output same text'
        );
        $this->assertEquals(
            'render success, with id is - xyz+123',
            $route_any,
            'the route must output same text'
        );
        $this->assertEquals(
            'render success, with id is - xyz 123',
            $route_all,
            'the route must output same text'
        );
    }

    /**
     * @test
     */
    public function itRouteCanBeRenderUsingGroupPrefix(): void
    {
        $this->registerGroupRouter();
        $get_one = $this->getRespone('get', '/page/one');
        $get_two = $this->getRespone('get', '/page/two');

        $this->assertEquals(
            'page one',
            $get_one,
            "gorup router with child is 'one'"
        );

        $this->assertEquals(
            'page two',
            $get_two,
            "gorup router with child is 'two'"
        );
    }

    /**
     * @test
     */
    public function itRouteCanBeRenderDiferentMethod(): void
    {
        $this->registerRouterDiferentMethod();
        $get     = $this->getRespone('get', '/get');
        $head    = $this->getRespone('head', '/head');
        $post    = $this->getRespone('post', '/post');
        $put     = $this->getRespone('put', '/put');
        $patch   = $this->getRespone('patch', '/patch');
        $delete  = $this->getRespone('delete', '/delete');
        $options = $this->getRespone('options', '/options');

        $this->assertEquals(
            'render success using get',
            $get,
            'render success using get'
        );
        $this->assertEquals(
            'render success using get over head method',
            $head,
            'render success using get over head method'
        );
        $this->assertEquals(
            'render success using post',
            $post,
            'render success using post'
        );
        $this->assertEquals(
            'render success using put',
            $put,
            'render success using put'
        );
        $this->assertEquals(
            'render success using patch',
            $patch,
            'render success using patch'
        );
        $this->assertEquals(
            'render success using delete',
            $delete,
            'render success using delete'
        );
        $this->assertEquals(
            'render success using options',
            $options,
            'render success using options'
        );
    }

    /**
     * @test
     */
    public function itRouteIsMethodNotAllowed(): void
    {
        $this->registerRouterMethodNotAlloed();

        $get     = $this->getRespone('post', '/get');
        $post    = $this->getRespone('get', '/post');
        $put     = $this->getRespone('get', '/put');
        $patch   = $this->getRespone('get', '/patch');
        $delete  = $this->getRespone('get', '/delete');
        $options = $this->getRespone('get', '/options');
        $this->assertEquals(
            'method not allowed',
            $get,
            'method not allowed'
        );
        $this->assertEquals(
            'method not allowed',
            $post,
            'method not allowed'
        );
        $this->assertEquals(
            'method not allowed',
            $put,
            'method not allowed'
        );
        $this->assertEquals(
            'method not allowed',
            $patch,
            'method not allowed'
        );
        $this->assertEquals(
            'method not allowed',
            $delete,
            'method not allowed'
        );
        $this->assertEquals(
            'method not allowed',
            $options,
            'method not allowed'
        );
    }

    /**
     * @test
     */
    public function itPageIsNotFound(): void
    {
        $this->registerRouterNotFound();
        $page = $this->getRespone('get', '/not-found');

        $this->assertEquals(
            'page not found 404',
            $page,
            'it must render "page is not found"'
        );
    }

    /**
     * @test
     */
    public function itCanPassGroupMiddleware(): void
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestMiddleware.php';

        Router::middleware([TestMiddleware::class])->group(function () {
            Router::get('/', fn () => true);
        });
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Router::run();
        Router::Reset();

        $this->assertEquals('oke', $_SERVER['middleware'], 'all route must pass global middleware');
    }

    /**
     * @test
     */
    public function itCanPassSingleMiddleware(): void
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestMiddleware.php';

        Router::get('/', fn () => true)->middleware([TestMiddleware::class]);
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Router::run();
        Router::Reset();

        $this->assertEquals('oke', $_SERVER['middleware'], 'all route must pass global middleware');
    }

    /**
     * @test
     */
    public function itCanPassMidlewareRunOnce(): void
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestMiddleware.php';

        TestMiddleware::$last = 0;
        Router::middleware([TestMiddleware::class])->group(function () {
            Router::get('/', fn () => true)->middleware([TestMiddleware::class]);
        });

        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Router::run();
        Router::Reset();

        $this->assertEquals(1, TestMiddleware::$last, 'all route must pass global middleware');

        TestMiddleware::$last = 0;
    }

    /**
     * @test
     */
    public function itRouteHasName(): void
    {
        $this->registerRouter();

        $this->assertTrue(Router::has('route.test'));
        $this->assertFalse(Router::has('route.success'));
    }
}
