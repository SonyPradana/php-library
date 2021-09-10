<?php

use PHPUnit\Framework\TestCase;
use System\Router\Router;
use System\Router\RouteProvider;

class BasicRouteTest extends TestCase
{
  private function registerRouter()
  {
    Router::get('/test', function() {
      echo "render success";
    });

    Router::get('/test/number/(:id)', function($id) {
      echo "render success, with id is - " . $id;
    });

    Router::get('/test/text/(:text)', function($id) {
      echo "render success, with id is - " . $id;
    });

    Router::get('/test/any/(:any)', function($id) {
      echo "render success, with id is - " . $id;
    });

    Router::get('/test/any/(:all)', function($id) {
      echo "render success, with id is - " . $id;
    });
  }

  private function registerGroupRouter()
  {
    Router::prefix('/page/')->routes(function(RouteProvider $router) {
      $router->get('one', function() {
        echo 'page one';
      });
      $router->get('two', function() {
        echo 'page two';
      });
    });
  }

  private function registerRouterDiferentMethod()
  {
    Router::match( ['get'], '/get', function() {
      echo "render success using get";
    });
    Router::match( ['post'], '/post', function() {
      echo "render success using post";
    });
    Router::match( ['put'], '/put', function() {
      echo "render success using put";
    });
    Router::match( ['patch'], '/patch', function() {
      echo "render success using patch";
    });
    Router::match( ['delete'], '/delete', function() {
      echo "render success using delete";
    });
    Router::match( ['options'], '/options', function() {
      echo "render success using options";
    });
  }

  private function registerRouterMethodNotAlloed()
  {
    Router::methodNotAllowed(function() {
      echo 'method not allowed';
    });
  }

  private function registerRouterNotFound()
  {
    Router::pathNotFound(function() {
      echo 'page not found 404';
    });
  }

  private function getRespone(string $method, string $url)
  {
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $url;

    ob_start();
    Router::run('/');
    return ob_get_clean();
  }

  /**
   * @test
   */
  public function it_route_can_be_render(): void
  {
    $this->registerRouter();

    $route_basic = $this->getRespone('get', '/test');
    $route_number = $this->getRespone('get', '/test/number/123');
    $route_text = $this->getRespone('get', '/test/text/xyz');
    $route_any = $this->getRespone('get', '/test/any/xyz+123');
    $route_all = $this->getRespone('get', '/test/any/xyz 123'); // allow all charater

    $this->assertEquals(
      "render success",
      $route_basic,
      "the route must output same text"
    );
    $this->assertEquals(
      "render success, with id is - 123",
      $route_number,
      "the route must output same text"
    );
    $this->assertEquals(
      "render success, with id is - xyz",
      $route_text,
      "the route must output same text"
    );
    $this->assertEquals(
      "render success, with id is - xyz+123",
      $route_any,
      "the route must output same text"
    );
    $this->assertEquals(
      "render success, with id is - xyz 123",
      $route_all,
      "the route must output same text"
    );

  }

  /**
   * @test
   */
  public function it_route_can_be_render_using_group_prefix(): void
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
  public function it_route_can_be_render_diferent_method(): void
  {
    $this->registerRouterDiferentMethod();
    $get = $this->getRespone('get', '/get');
    $post = $this->getRespone('post', '/post');
    $put = $this->getRespone('put', '/put');
    $patch = $this->getRespone('patch', '/patch');
    $delete = $this->getRespone('delete', '/delete');
    $options = $this->getRespone('options', '/options');

    $this->assertEquals(
      "render success using get",
      $get,
      "render success using get"
    );
    $this->assertEquals(
      "render success using post",
      $post,
      "render success using post"
    );
    $this->assertEquals(
      "render success using put",
      $put,
      "render success using put"
    );
    $this->assertEquals(
      "render success using patch",
      $patch,
      "render success using patch"
    );
    $this->assertEquals(
      "render success using delete",
      $delete,
      "render success using delete"
    );
    $this->assertEquals(
      "render success using options",
      $options,
      "render success using options"
    );
  }

  /**
   * @test
   */
  public function it_route_is_method_not_allowed(): void
  {
    $this->registerRouterMethodNotAlloed();
    $get = $this->getRespone('post', '/get');
    $post = $this->getRespone('get', '/post');
    $put = $this->getRespone('get', '/put');
    $patch = $this->getRespone('get', '/patch');
    $delete = $this->getRespone('get', '/delete');
    $options = $this->getRespone('get', '/options');
    $this->assertEquals(
      "method not allowed",
      $get,
      "method not allowed"
    );
    $this->assertEquals(
      "method not allowed",
      $post,
      "method not allowed"
    );
    $this->assertEquals(
      "method not allowed",
      $put,
      "method not allowed"
    );
    $this->assertEquals(
      "method not allowed",
      $patch,
      "method not allowed"
    );
    $this->assertEquals(
      "method not allowed",
      $delete,
      "method not allowed"
    );
    $this->assertEquals(
      "method not allowed",
      $options,
      "method not allowed"
    );
  }

  /**
   * @test
   */
  public function it_page_is_not_found(): void
  {
    $this->registerRouterNotFound();
    $page = $this->getRespone('get', '/not-found');

    $this->assertEquals(
      'page not found 404',
      $page,
      'it must render "page is not found"'
    );
  }
}
