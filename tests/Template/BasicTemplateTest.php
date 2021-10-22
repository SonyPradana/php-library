<?php

use PHPUnit\Framework\TestCase;
use System\Template\Constant;
use System\Template\ConstPool;
use System\Template\Generate;
use System\Template\Method;
use System\Template\MethodPool;
use System\Template\Property;
use System\Template\PropertyPool;
use System\Template\Providers\NewConst;
use System\Template\Providers\NewFunction;
use System\Template\Providers\Newproperty;

class BasicTemplateTest extends TestCase
{
  private function getExpected(string $expected): string
  {
    $file_name = dirname(__DIR__) . '/Template/expected/' . $expected;
    return file_get_contents($file_name);
  }

  /** @test */
  public function it_can_generate_basic_class():void
  {
    $class = new Generate('NewClass');

    $class
      ->use(System\Template\Generate::class)
      ->extend(\PHPUnit\Framework\TestCase::class)
      ->implement('testInterface')
      ->setEndWithNewLine();

    $this->assertEquals(
      $this->getExpected('basic_class'),
      $class,
      "this class have perent and interface"
    );
  }

  /** @test */
  public function it_can_generate_class_with_trait_property_and_method()
  {
    $class = new Generate('NewClass');

    $class
      ->use(System\Template\Generate::class)
      ->extend(\PHPUnit\Framework\TestCase::class)
      ->implement('testInterface')
      ->traits([
        PhpParser\Builder\TraitUseAdaptation::class,
        PhpParser\Builder\TraitUse::class,
      ])
      ->consts(NewConst::name('TEST'))
      ->propertys(Newproperty::name('test'))
      ->methods(NewFunction::name('test'))
      ->setEndWithNewLine();

    $this->assertEquals(
      $this->getExpected('class_with_trait_property_method'),
      $class->generate(),
      "this class have traits propety and method"
    );
  }

  /** @test */
  public function it_can_generate_class_with_trait_property_and_method_from_template()
  {
    $class = new Generate('NewClass');

    $class
      ->customizeTemplate("<?php\n{{before}}{{comment}}\n{{rule}}class\40{{head}} {\n\n{{body}}\n}\n?>{{end}}")
      ->tabIndent("\t")
      ->tabSize(2)

      ->use(System\Template\Generate::class)
      ->extend(\PHPUnit\Framework\TestCase::class)
      ->implement('testInterface')
      ->traits([
        PhpParser\Builder\TraitUseAdaptation::class,
        PhpParser\Builder\TraitUse::class,
      ])
      ->consts(NewConst::name('TEST'))
      ->propertys(Newproperty::name('test'))
      ->methods(
        NewFunction::name('test')
          ->customizeTemplate("{{comment}}{{before}}function {{name}}({{params}}){{return type}} {{{new line}}{{body}}{{new line}}}")
      )
      ->setEndWithNewLine();

    $this->assertEquals(
      $this->getExpected('class_wtih_custume_template'),
      $class->generate(),
      "this class have trait property and method from template"
    );
  }

  /** @test */
  public function it_can_generate_class_with_complex_propertys()
  {

    $class = new Generate('NewClass');

    $class
    ->propertys(
      Newproperty::name('test')
        ->visibility(Property::PRIVATE_)
        ->addComment('Test')
        ->addLineComment()
        ->addVaribaleComment('string')
        ->expecting('= "works"')
    )
    ->propertys(function(PropertyPool $property) {
      // multype property
      for ($i=0; $i < 10; $i++) {
        $property->name('test_' . $i);
      }
    })
    ->setEndWithNewLine();

    // add property using addPoperty
    $class
      ->addProperty('some_property')
      ->visibility(Property::PUBLIC_)
      ->expecting("= 1")
      ->addVaribaleComment('int');

    $this->assertEquals(
      $this->getExpected('class_with_complex_property'),
      $class->generate(),
      "this class have complex property"
      );
  }

  /** @test */
  public function it_can_generate_class_with_complex_methods()
  {
    $class = new Generate('NewClass');

    $class
    ->methods(
      NewFunction::name('test')
        ->addComment('A method')
        ->addLineComment()
        ->addReturnComment('string', '$name', 'Test')
        ->params(['string $name = "test"'])
        ->setReturnType('string')
        ->body(['return $name;'])
    )
    ->methods(function(MethodPool $method) {
      // multy funtion
      for ($i=0; $i<3; $i++) {
        $method
          ->name('test_' . $i)
          ->params(['$param_' . $i])
          ->setReturnType('int')
          ->body(['return $param_' . $i . ';']);
      }
    })
    ->setEndWithNewLine();

    // add property using method
    $class
      ->addMethod('someTest')
      ->visibility(Method::PUBLIC_)
      ->isFinal()
      ->isStatic()
      ->params(['string $case', 'int $number'])
      ->setReturnType('bool')
      ->body([
        '$bool = true;',
        'return $bool;'
      ])
      ->addReturnComment('bool', 'true if true');

    $this->assertEquals(
      $this->getExpected('class_with_complex_methods'),
      $class->generate(),
      "this class have complex methods"
    );
  }

  /** @test */
  public function it_can_generate_class_with_complex_consts()
  {
    $class = new Generate('NewClass');

    $class
    ->consts(
      Constant::new('COMMENT')
        ->addComment('a const with Comment')
    )
    ->consts(function(ConstPool $const) {
      for ($i=0; $i < 10; $i++) {
        $const
          ->name("CONST_" . $i)
          ->equal($i);
      }
    })
    ->setEndWithNewLine();

    $class
      ->addConst('A_CONST')
      ->visibility(Constant::PRIVATE_)
      ->expecting('= true');

    $this->assertEquals(
      $this->getExpected('class_with_complex_const'),
      $class->generate(),
      "this class have complex methods"
    );
  }

  /** @test */
  public function it_can_generate_class_with_complex_comments()
  {
    $class = new Generate('NewClass');

    $class
    ->addComment('A class with comment')
    ->addLineComment()
    ->addComment('@auth sonypradana@gmail.com')
    ->consts(
      Constant::new('COMMENT')
        ->addComment('a const with Comment')
    )
    ->propertys(
      Property::new('_property')
        ->addVaribaleComment('string', 'String property')
    )
    ->methods(
      Method::new('someTest')
        ->addComment('a funtion with commnet')
        ->addLineComment()
        ->addVaribaleComment('string', 'sample')
        ->addParamComment('string', '$test', 'Test')
        ->addReturnComment('bool', 'true if true')
    )
    ->setEndWithNewLine();

    $this->assertEquals(
      $this->getExpected('class_with_complex_comment'),
      $class->generate(),
      "this class have complex methods"
    );
  }
}
