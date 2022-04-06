<?php
use PHPUnit\Framework\TestCase;
use System\View\View;

class RenderViewTest extends TestCase
{

  /**
   * @test
   */
  public function it_can_render_using_view_classes(): void
  {
    $test_html = dirname(__DIR__) . '\View\sample\sample.html';
    $test_php = dirname(__DIR__) . '\View\sample\sample.php';

    ob_start();
    View::render($test_html);
    $render_html = ob_get_clean();

    ob_start();
    View::render($test_php, ["contents" => ["say" => "hay"]]);
    $render_php = ob_get_clean();

    // view: view-html
    $this->assertEquals(
      "<html><head></head><body></body></html>\n",
      $render_html,
      'it must sameoutput with template html'
    );

    // view: view-php
    $this->assertEquals(
      "<html><head></head><body><h1>hay</h1></body></html>\n",
      $render_php,
      'it must sameoutput with template html'
    );
  }
}
