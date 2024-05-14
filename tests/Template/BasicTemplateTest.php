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
use System\Template\Providers\NewProperty;

class BasicTemplateTest extends TestCase
{
    private function getExpected(string $expected): string
    {
        $file_name = __DIR__ . DIRECTORY_SEPARATOR . 'expected' . DIRECTORY_SEPARATOR . $expected;

        $file_content = file_get_contents($file_name);

        return str_replace("\r\n", "\n", $file_content);
    }

    /** @test */
    public function itCanGenerateBasicClass(): void
    {
        $class = new Generate('NewClass');

        $class
      ->use(Generate::class)
      ->extend(TestCase::class)
      ->implement('testInterface')
      ->setEndWithNewLine();

        $this->assertEquals(
            $this->getExpected('basic_class'),
            $class,
            'this class have perent and interface'
        );
    }

    /** @test */
    public function itCanGenerateClassWithTraitPropertyAndMethod()
    {
        $class = new Generate('NewClass');

        $class
      ->use(Generate::class)
      ->extend(TestCase::class)
      ->implement('testInterface')
      ->traits([
          PhpParser\Builder\TraitUseAdaptation::class,
          PhpParser\Builder\TraitUse::class,
      ])
      ->consts(NewConst::name('TEST'))
      ->propertys(NewProperty::name('test'))
      ->methods(NewFunction::name('test'))
      ->setEndWithNewLine();

        $this->assertEquals(
            $this->getExpected('class_with_trait_property_method'),
            $class->generate(),
            'this class have traits propety and method'
        );
    }

    /** @test */
    public function itCanGenerateClassWithTraitPropertyAndMethodFromTemplate()
    {
        $class = new Generate('NewClass');

        $class
      ->customizeTemplate("<?php\n{{before}}{{comment}}\n{{rule}}class\40{{head}} {\n\n{{body}}\n}\n?>{{end}}")
      ->tabIndent("\t")
      ->tabSize(2)

      ->use(Generate::class)
      ->extend(TestCase::class)
      ->implement('testInterface')
      ->traits([
          PhpParser\Builder\TraitUseAdaptation::class,
          PhpParser\Builder\TraitUse::class,
      ])
      ->consts(NewConst::name('TEST'))
      ->propertys(NewProperty::name('test'))
      ->methods(
          NewFunction::name('test')
            ->customizeTemplate('{{comment}}{{before}}function {{name}}({{params}}){{return type}} {{{new line}}{{body}}{{new line}}}')
      )
      ->setEndWithNewLine();

        $this->assertEquals(
            $this->getExpected('class_wtih_custume_template'),
            $class->generate(),
            'this class have trait property and method from template'
        );
    }

    /** @test */
    public function itCanGenerateClassWithComplexPropertys()
    {
        $class = new Generate('NewClass');

        $class
    ->propertys(
        NewProperty::name('test')
          ->visibility(Property::PRIVATE_)
          ->addComment('Test')
          ->addLineComment()
          ->addVaribaleComment('string')
          ->expecting('= "works"')
    )
    ->propertys(function (PropertyPool $property) {
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
      ->dataType('array')
      ->expecting(
          [
              '= array(',
              '  \'one\'    => 1,',
              '  \'two\'    => 2,',
              '  \'bool\'   => false,',
              '  \'string\' => \'string\'',
              ')',
          ]
      )
      ->addVaribaleComment('array');

        // add property using propertypool
        $pool = new PropertyPool();
        for ($i=1; $i < 6; $i++) {
            $pool
        ->name('from_pool_' . $i)
        ->visibility(Property::PUBLIC_)
        ->dataType('string')
        ->expecting('= \'pools_' . $i . '\'')
        ->addVaribaleComment('string')
            ;
        }
        $class->propertys($pool);

        $this->assertEquals(
            $this->getExpected('class_with_complex_property'),
            $class->generate(),
            'this class have complex property'
        );
    }

    /** @test */
    public function itCanGenerateClassWithComplexMethods()
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
    ->methods(function (MethodPool $method) {
        // multy funtion
        for ($i=0; $i < 3; $i++) {
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
          'return $bool;',
      ])
      ->addReturnComment('bool', 'true if true');

        // add property using propertypool
        $pool = new MethodPool();
        for ($i=1; $i < 3; $i++) {
            $pool
          ->name('function_' . $i)
          ->visibility(Property::PUBLIC_)
          ->params(['string $param'])
          ->setReturnType('string')
          ->body([
              'return $param;',
          ])
          ->addParamComment('string', '$param', 'String param')
          ->addReturnComment('string', 'Same as param')
            ;
        }
        $class->methods($pool);

        $this->assertEquals(
            $this->getExpected('class_with_complex_methods'),
            $class->generate(),
            'this class have complex methods'
        );
    }

    /** @test */
    public function itCanGenerateClassWithComplexConsts()
    {
        $class = new Generate('NewClass');

        $class
    ->consts(
        Constant::new('COMMENT')
          ->addComment('a const with Comment')
    )
    ->consts(function (ConstPool $const) {
        for ($i=0; $i < 10; $i++) {
            $const
          ->name('CONST_' . $i)
          ->equal($i);
        }
    })
    ->setEndWithNewLine();

        $class
      ->addConst('A_CONST')
      ->visibility(Constant::PRIVATE_)
      ->expecting('= true');

        // add property using propertypool
        $pool = new ConstPool();
        for ($i=1; $i < 4; $i++) {
            $pool
        ->name('CONSTPOOL_' . $i)
        ->expecting('= true')
            ;
        }
        $class->consts($pool);

        $this->assertEquals(
            $this->getExpected('class_with_complex_const'),
            $class->generate(),
            'this class have complex methods'
        );
    }

    /** @test */
    public function itCanGenerateClassWithComplexComments()
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
            'this class have complex methods'
        );
    }

    /**
     * @test
     */
    public function itCanGenerateReplacedTemplate(): void
    {
        // pre replace
        $class = new Generate('old_class');

        $class->preReplace('class', 'trait');

        $this->assertEquals(
            "<?php\n\ntrait old_class\n{\n\n}",
            $class->generate()
        );

        // replace
        $class->replace(['old_class'], ['new_class']);

        $this->assertEquals(
            "<?php\n\ntrait new_class\n{\n\n}",
            $class->generate()
        );
    }
}
