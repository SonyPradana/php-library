<?php

use PHPUnit\Framework\TestCase;
use System\File\UploadFile;
use System\Http\Request;
use Validator\Rule\FilterPool;
use Validator\Rule\ValidPool;
use Validator\Validator;

class RequestTest extends TestCase
{
    /** @var Request */
    private $request;

    /** @var Request */
    private $request_post;

    /** @var Request */
    private $request_put;

    protected function setUp(): void
    {
        $this->request = new Request(
            'http://localhost/',
            ['query_1'   => 'query'],
            ['post_1'    => 'post'],
            ['costume'   => 'costume'],
            ['cookies'   => 'cookies'],
            [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
            ],
            ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
            'GET',
            '127:0:0:1',
            '{"respone":"ok"}'
        );

        $this->request_post = new Request(
            'http://localhost/',
            ['query_1'   => 'query'],
            ['post_1'    => 'post'],
            ['costume'   => 'costume'],
            ['cookies'   => 'cookies'],
            [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
                'file_2' => [
                    'name'      => 'test123.txt',
                    'type'      => 'file',
                    'tmp_name'  => dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test123.tmp',
                    'error'     => 0,
                    'size'      => 1,
                ],
            ],
            ['header_1'  => 'header', 'header_2' => 123, 'foo' => 'bar'],
            'POST',
            '127:0:0:1',
            '{"respone":"ok"}'
        );

        $this->request_put = new Request('test.test', [], [], [], [], [], [
            'content-type' => 'app/json',
        ], '', '', '{"respone":"ok"}');
    }

    /**
     * @test
     */
    public function hasSameUrl()
    {
        $this->assertEquals('http://localhost/', $this->request->getUrl());
    }

    /**
     * @test
     */
    public function hasSameQuery()
    {
        $this->assertEquals('query', $this->request->getQuery('query_1'));
        $this->assertEquals('query', $this->request->query()->get('query_1'));
    }

    /**
     * @test
     */
    public function hasSamePost()
    {
        $this->assertEquals('post', $this->request->getPost('post_1'));
        $this->assertEquals('post', $this->request->post()->get('post_1'));
    }

    /**
     * @test
     */
    public function hasSameCookies()
    {
        $this->assertEquals('cookies', $this->request->getCookie('cookies'));
    }

    /**
     * @test
     */
    public function hasSameFile()
    {
        $file = $this->request->getFile('file_1');
        $this->assertEquals(
            'file_name',
            $file['name']
        );
        $this->assertEquals(
            'text',
            $file['type']
        );
        $this->assertEquals(
            'tmp_name',
            $file['tmp_name']
        );
        $this->assertEquals(
            0,
            $file['error']
        );
        $this->assertEquals(
            0,
            $file['size']
        );
    }

    /**
     * @test
     */
    public function hasSameHeader()
    {
        $this->assertEquals('header', $this->request->getHeaders('header_1'));
    }

    /**
     * @test
     */
    public function hasSameMethod()
    {
        $this->assertEquals('GET', $this->request->getMethod());
    }

    /**
     * @test
     */
    public function hasSameIp()
    {
        $this->assertEquals('127:0:0:1', $this->request->getRemoteAddress());
    }

    /**
     * @test
     */
    public function hasSameBody()
    {
        $this->assertEquals('{"respone":"ok"}', $this->request->getRawBody());
    }

    /**
     * @test
     */
    public function hasSameBodyJson()
    {
        $this->assertEquals(
            ['respone' => 'ok'],
            $this->request->getJsonBody());
    }

    /**
     * @test
     */
    public function itNotSecureRequest()
    {
        $this->assertFalse($this->request->isSecured());
    }

    /**
     * @test
     */
    public function hasHeader()
    {
        $this->assertTrue($this->request->hasHeader('header_2'));
    }

    /**
     * @test
     */
    public function isHeaderContains()
    {
        $this->assertTrue($this->request->isHeader('foo', 'bar'));
    }

    /**
     * @test
     */
    public function itCanGetAllProperty()
    {
        $this->assertEquals($this->request->all(), [
            'header_1'          => 'header',
            'header_2'          => 123,
            'foo'               => 'bar',
            'query_1'           => 'query',
            'costume'           => 'costume',
            'x-raw'             => '{"respone":"ok"}',
            'x-method'          => 'GET',
            'cookies'           => 'cookies',
            'files'             => [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function itCanThrowErrorWhenBodyEmpty()
    {
        $request = new Request('test.test', [], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');

        try {
            $request->all();
        } catch (Throwable $th) {
            $this->assertEquals('Request body is empty.', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanThrowErrorWhenBodyCantDecode()
    {
        $request = new Request('test.test', [], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', 'nobody');

        try {
            $request->all();
        } catch (Throwable $th) {
            $this->assertEquals('Could not decode request body.', $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanAccessAsArrayGet()
    {
        $this->assertEquals('query', $this->request['query_1']);
        $this->assertEquals(null, $this->request['query_x']);
    }

    /**
     * @test
     */
    public function itCanAccessAsArrayHas()
    {
        $this->assertTrue(isset($this->request['query_1']));
        $this->assertFalse(isset($this->request['query_x']));
    }

    /**
     * @test
     */
    public function itCanAccessUsingGetter()
    {
        $this->assertEquals('query', $this->request->query_1);
    }

    /**
     * @test it can detect Ajax Request
     */
    public function itCanDetectAjaxRequest()
    {
        $req = new Request('test.test', [], [], [], [], [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertTrue($req->isAjax());
    }

    /** @test */
    public function itCanGetItemFromAttribute()
    {
        $this->assertEquals('costume', $this->request->getAttribute('costume', 'fixed'));
        $this->assertEquals('fixed', $this->request->getAttribute('fixed', 'fixed'));
    }

    /** @test */
    public function itCanUseForeaceRequest()
    {
        foreach ($this->request as $key => $value) {
            $this->assertEquals($this->request[$key], $value);
        }
    }

    /** @test */
    public function itCanDetectRequestJsonRequest()
    {
        $this->assertFalse($this->request->isJson());
        $this->assertTrue($this->request_put->isJson());
    }

    /** @test */
    public function itCanReturnBodyIfRequestComeFromJsonRequest()
    {
        $this->assertEquals('ok', $this->request_put->json()->get('respone', 'bad'));
        $this->assertEquals('ok', $this->request_put->all()['respone']);
        $this->assertEquals('ok', $this->request_put['respone']);
    }

    /** @test */
    public function itCanGetAllPropertyIfMethodPost()
    {
        $this->assertEquals($this->request_post->all(), [
            'header_1'          => 'header',
            'header_2'          => 123,
            'foo'               => 'bar',
            'query_1'           => 'query',
            'post_1'            => 'post',
            'costume'           => 'costume',
            'x-raw'             => '{"respone":"ok"}',
            'x-method'          => 'POST',
            'cookies'           => 'cookies',
            'files'             => [
                'file_1' => [
                    'name'      => 'file_name',
                    'type'      => 'text',
                    'tmp_name'  => 'tmp_name',
                    'error'     => 0,
                    'size'      => 0,
                ],
                'file_2' => [
                    'name'      => 'test123.txt',
                    'type'      => 'file',
                    'tmp_name'  => dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test123.tmp',
                    'error'     => 0,
                    'size'      => 1,
                ],
            ],
        ]);
    }

    /** @test */
    public function itCanUseValidateMacro()
    {
        Request::macro(
            'validate',
            fn (?Closure $rule = null, ?Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );

        // get
        $v = $this->request->validate();
        $v->field('query_1')->required();
        $this->assertTrue($v->is_valid());

        // post
        $v = $this->request_post->validate();
        $v->field('query_1')->required();
        $v->field('post_1')->required();
        $this->assertTrue($v->is_valid());

        // file
        $v = $this->request_post->validate();
        $v->field('query_1')->required();
        $v->field('post_1')->required();
        $v->field('files.file_1')->required();
        $this->assertTrue($v->is_valid());

        // put
        $v = $this->request_put->validate();
        $v->field('respone')->required();
        $this->assertTrue($v->is_valid());

        // get (filter)
        $v = $this->request->validate(
            fn (ValidPool $vr) => $vr('query_1')->required(),
            fn (FilterPool $fr) => $fr('query_1')->upper_case()
        );
        $this->assertTrue($v->is_valid());
        $this->assertEquals('QUERY', $v->filters->get('query_1'));
    }

    /** @test */
    public function itCanUseUploadMacro()
    {
        Request::macro(
            'upload',
            function ($file_name) {
                $files = $this->{'getFile'}();

                return (new UploadFile($files[$file_name]))->markTest(true);
            }
        );

        $upload = $this->request_post->upload('file_2');
        $upload
            ->setFileName('success')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file'])
        ;

        $upload->upload();

        $upload->delete(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'success.txt');

        $this->assertTrue($upload->success());
    }

    /**
     * @test
     */
    public function itCanModifeRequest()
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');
        $request2 = $request->duplicate(['query' => 'new']);

        $this->assertEquals('old', $request->getQuery('query'));
        $this->assertEquals('new', $request2->getQuery('query'));
    }

    /**
     * @test
     */
    public function itCanGetMimeType()
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');

        $mimetypes = $request->getMimeTypes('html');
        $this->assertEquals(['text/html', 'application/xhtml+xml'], $mimetypes);

        $mimetypes = $request->getMimeTypes('php');
        $this->assertEquals([], $mimetypes, 'php format is not exists');
    }

    /**
     * @test
     */
    public function itCanGetFormat()
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'app/json'], 'PUT', '::1', '');

        $format = $request->getFormat('text/html');
        $this->assertEquals('html', $format);

        $format = $request->getFormat('text/php');
        $this->assertNull($format, 'php format not exist');
    }

    /**
     * @test
     */
    public function itCanGetRequestFormat()
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], ['content-type' => 'application/json'], 'PUT', '::1', '');

        $this->assertEquals('json', $request->getRequestFormat());
    }

    /**
     * @test
     */
    public function itCanNotGetRequestFormat()
    {
        $request  = new Request('test.test', ['query' => 'old'], [], [], [], [], [], 'PUT', '::1', '');

        $this->assertNull($request->getRequestFormat());
    }

    /**
     * @test
     */
    public function itCanGetHeaderAuthorization(): void
    {
        $request = new Request('test.test', headers: ['Authorization' => '123']);

        $this->assertEquals('123', $request->getAuthorization());
    }

    /**
     * @test
     */
    public function itCanGetHeaderBearerAuthorization(): void
    {
        $request = new Request('test.test', headers: ['Authorization' => 'Bearer 123']);

        $this->assertEquals('123', $request->getBearerToken());
    }
}
