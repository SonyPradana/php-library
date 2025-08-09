<?php

use PHPUnit\Framework\TestCase;
use System\Router\Route;
use System\Router\Router;

class UrlBuilderInvalidArgumentTest extends TestCase
{
    protected function setUp(): void
    {
        Router::$patterns = [
            '(:num)' => '\d+',
            '(:any)' => '.+',
        ];
    }

    protected function tearDown(): void
    {
        Router::$patterns = [
            '(:id)'   => '(\d+)',
            '(:num)'  => '([0-9]*)',
            '(:text)' => '([a-zA-Z]*)',
            '(:any)'  => '([0-9a-zA-Z_+-]*)',
            '(:slug)' => '([0-9a-zA-Z_-]*)',
            '(:all)'  => '(.*)',
        ];
    }

    public static function invalidArgumentCases(): array
    {
        return [
            // 1. Unknown pattern type
            'Unknown pattern type' => [
                [
                    'uri'      => '/user/(id:unknown)',
                    'patterns' => [],
                ],
                ['id' => 1],
                'Unknown pattern type: (:unknown)',
            ],

            // 2. Missing named parameter (assoc)
            'Missing named parameter (assoc)' => [
                [
                    'uri'      => '/user/(id:num)',
                    'patterns' => [],
                ],
                ['slug' => 123],
                'Missing named parameter: id',
            ],

            // 3. Missing named parameter (indexed)
            'Missing named parameter (indexed)' => [
                [
                    'uri'      => '/user/(id:num)',
                    'patterns' => [],
                ],
                [],
                'Missing parameter at index 0 for named parameter id',
            ],

            // 4. Named parameter value not match regex (assoc)
            'Named parameter value not match regex' => [
                [
                    'uri'      => '/user/(id:num)',
                    'patterns' => [],
                ],
                ['id' => 'abc'], // assoc, agar masuk jalur named param
                "Named parameter 'id' with value 'abc' doesn't match pattern (:num) (\d+)",
            ],

            // 5. Missing parameter for pattern (assoc)
            'Missing parameter for pattern (assoc)' => [
                [
                    'uri'      => '/user/(:num)',
                    'patterns' => [],
                ],
                ['foo' => 'bar'],
                "Missing parameter for pattern (:num). Provide either numeric index 0 or key 'num'",
            ],

            // 6. Missing parameter for pattern (indexed)
            'Missing parameter for pattern (indexed)' => [
                [
                    'uri'      => '/user/(:num)',
                    'patterns' => [],
                ],
                [],
                'Missing parameter at index 0 for pattern (:num)',
            ],

            // 7. Parameter not match regex for pattern (indexed)
            'Parameter not match regex for pattern' => [
                [
                    'uri'      => '/user/(:num)',
                    'patterns' => [],
                ],
                ['abc'], // indexed, agar masuk pattern loop
                "Parameter 'abc' doesn't match pattern (:num) (\d+)",
            ],

            // 8. Unreplaced named parameter left in URL → fail fast di missing param
            'Unreplaced named parameter left in URL' => [
                [
                    'uri'      => '/user/(id:num)/(slug:any)',
                    'patterns' => [],
                ],
                ['id' => 1],
                'Missing named parameter: slug',
            ],

            // 9. Unreplaced pattern left in URL → fail fast di missing param
            'Unreplaced pattern left in URL' => [
                [
                    'uri'      => '/user/(:num)/(:any)',
                    'patterns' => [],
                ],
                [1],
                'Missing parameter at index 1 for pattern (:any)',
            ],
        ];
    }

    /**
     * @dataProvider invalidArgumentCases
     */
    public function testInvalidArguments(array $route, array $parameters, string $expectedMessage)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $route = new Route($route);

        Router::routeToUrl($route, $parameters);
    }
}
