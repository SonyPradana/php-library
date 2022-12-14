<?php

use PHPUnit\Framework\TestCase;
use System\Router\Controller;
use System\Router\Router;

class RouteControllerTest extends TestCase
{
    protected $class_controller;
    protected $backup;

    public function __construct()
    {
        parent::__construct();
        // autoload asset class
        require_once __DIR__ . '/Assests/RouteClassController.php';
    }

    protected function setUp(): void
    {
        $this->class_controller = new class() extends Controller {
            public function index()
            {
                echo 'here';
            }
        };

        Router::pathNotFound(function () { echo 'not found'; });
        Router::methodNotAllowed(function () { echo 'not allowed'; });

        $this->backup['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];
        $this->backup['REQUEST_URI']    = $_SERVER['REQUEST_URI'];
    }

    protected function tearDown(): void
    {
        Router::Reset();

        $_SERVER['REQUEST_METHOD'] = $this->backup['REQUEST_METHOD'];
        $_SERVER['REQUEST_URI']    = $this->backup['REQUEST_URI'];
    }

    // /** @test */
    // public function itCanRouteUsingControllerWithoutMethod()
    // {
    //     Router::get('/', RouteClassController::class);

    //     ob_start();
    //     Router::run('/');
    //     $out = ob_get_clean();

    //     $this->assertEquals('works', $out);
    // }

    /** @test */
    public function itCanRouteUsingResourceControllerIndex()
    {
        $_SERVER['REQUEST_METHOD'] = 'get';

        Router::resource('/', RouteClassController::class);

        ob_start();
        Router::run('/');
        $out = ob_get_clean();

        $this->assertEquals('works', $out);
    }

    /** @test */
    public function itCanRouteUsingResourceControllerStore()
    {
        $_SERVER['REQUEST_METHOD'] = 'post';

        Router::resource('/', RouteClassController::class);

        ob_start();
        Router::run('/');
        $out = ob_get_clean();

        $this->assertEquals('works', $out);
    }
}
