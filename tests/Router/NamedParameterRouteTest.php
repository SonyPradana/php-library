<?php

use PHPUnit\Framework\TestCase;
use System\Router\Router;

class NamedParameterRouteTest extends TestCase
{
    private function registerRouter()
    {
        Router::get('/test', function () {
            echo 'render success';
        })->name('route.test');

        Router::get('/test/number/(someid:id)', function ($someid) {
            echo 'render success, with id is - ' . $someid;
        })->name('route.test.number');
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

    private function registerRouterWithMultipleParams()
    {
        Router::get('/users/(userId:num)/(type:text)', function ($userId, $type) {
            echo "User {$userId} is of type {$type}";
        })->name('users.type');

        Router::get('/blog/(year:num)/(month:num)/(slug:any)', function ($year, $month, $slug) {
            echo "Blog post from {$month}/{$year}: {$slug}";
        })->name('blog.post');

        Router::post('/api/users/(id:num)/posts/(postId:num)', function ($id, $postId) {
            echo "Post {$postId} for user {$id}";
        })->name('api.user.post');
    }

    /**
     * @test
     */
    public function itRouteCanBeRender(): void
    {
        $this->registerRouter();
        $this->registerRouterMethodNotAlloed();
        $this->registerRouterNotFound();

        $route_basic  = $this->getRespone('get', '/test');
        $route_number = $this->getRespone('get', '/test/number/123');

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
    }

    /** @test */
    public function itHandlesMultipleNamedParameters(): void
    {
        $this->registerRouterWithMultipleParams();

        $response = $this->getRespone('get', '/users/123/admin');
        $this->assertEquals(
            'User 123 is of type admin',
            $response,
            'Should handle multiple named parameters correctly'
        );

        $response = $this->getRespone('get', '/blog/2023/05/my-awesome-post');
        $this->assertEquals(
            'Blog post from 05/2023: my-awesome-post',
            $response,
            'Should handle three named parameters with different types'
        );
    }

    /** @test */
    public function itRespectsParameterTypes(): void
    {
        Router::get('/test/(age:num)/(name:text)', function ($age, $name) {
            echo "Name: {$name}, Age: {$age}";
        });

        $response = $this->getRespone('get', '/test/25/john');
        $this->assertEquals(
            'Name: john, Age: 25',
            $response,
            'Should match valid parameter types'
        );

        $response = $this->getRespone('get', '/test/abc/john');
        $this->assertEquals(
            'page not found 404',
            $response,
            'Should not match invalid number type'
        );

        $response = $this->getRespone('get', '/test/25/john123');
        $this->assertEquals(
            'page not found 404',
            $response,
            'Should not match invalid text type'
        );
    }

    /** @test */
    public function itHandlesMethodNotAllowedWithNamedParams(): void
    {
        Router::post('/api/users/(id:num)', function ($id) {
            echo "Create user {$id}";
        });

        $response = $this->getRespone('get', '/api/users/123');
        $this->assertEquals(
            'method not allowed',
            $response,
            'Should return method not allowed for wrong HTTP method'
        );
    }

    /** @test */
    public function itHandlesOptionalParameters(): void
    {
        Router::get('/products', function () {
            echo 'All products';
        });

        Router::get('/products/(category:text)', function ($category) {
            echo "Category: {$category}";
        });

        Router::get('/products/(category:text)/(id:num)', function ($category, $id) {
            echo "Product {$id} in {$category}";
        });

        $response = $this->getRespone('get', '/products');
        $this->assertEquals(
            'All products',
            $response,
            'Should work with base route'
        );

        $response = $this->getRespone('get', '/products/electronics');
        $this->assertEquals(
            'Category: electronics',
            $response,
            'Should work with category parameter'
        );

        $response = $this->getRespone('get', '/products/electronics/123');
        $this->assertEquals(
            'Product 123 in electronics',
            $response,
            'Should work with both category and id parameters'
        );
    }

    /** @test */
    public function itHandlesSpecialCharactersInParameters(): void
    {
        Router::get('/search/(query:all)', function ($query) {
            echo "Searching for: {$query}";
        });

        $response = $this->getRespone('get', '/search/php+routing+system');
        $this->assertEquals(
            'Searching for: php+routing+system',
            $response,
            'Should handle special characters in parameters'
        );
    }
}
