<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Text\Regex;
use System\Text\Str;

final class RegexStrTest extends TestCase
{
    public function testRegexEmail()
    {
        $res = Str::isMatch('sony@mail.com', Regex::EMAIL, 'cek email');
        $this->assertTrue($res);

        $res = Str::isMatch('sony.com', Regex::EMAIL, 'cek email');
        $this->assertFalse($res);
    }

    public function testRegexUsername()
    {
        $res = Str::isMatch('sony', Regex::USER, 'cek user');
        $this->assertTrue($res);

        $res = Str::isMatch('1sony', Regex::USER, 'cek user');
        $this->assertFalse($res);

        $res = Str::isMatch('son', Regex::USER, 'cek user less that 3');
        $this->assertFalse($res);

        $res = Str::isMatch('test_regex_username', Regex::USER, 'cek user more that 16');
        $this->assertFalse($res);
    }

    public function testRegexPlainText()
    {
        $res = Str::isMatch('php generators explained', Regex::PLAIN_TEXT, 'cek plain text');
        $this->assertTrue($res);

        $res = Str::isMatch('php generators explained!', Regex::PLAIN_TEXT, 'cek plain text');
        $this->assertFalse($res);
    }

    public function testRegexSlug()
    {
        $res = Str::isMatch('php-generators-explained', Regex::SLUG, 'cek plain text');
        $this->assertTrue($res);

        $res = Str::isMatch('php generators explained', Regex::SLUG, 'cek plain text');
        $this->isFalse($res);

        $res = Str::isMatch('php/generators/explained', Regex::SLUG, 'cek plain text');
        $this->isFalse($res);
    }

    public function testRegexHtmlTag()
    {
        $res = Str::isMatch('<script>alert(1)</alert>', Regex::HTML_TAG, 'cek html tag');
        $this->assertTrue($res);

        $res = Str::isMatch('&lt;script&gt;alert(1)&lt;/alert&gt;', Regex::HTML_TAG, 'cek html tag');
        $this->assertFalse($res);
    }

    public function testRegexJsInline()
    {
        $res = Str::isMatch('<img src="foo.jpg" onload=function_xyz />', Regex::JS_INLINE, 'cek html tag');
        $this->assertTrue($res);
    }

    public function testRegexPassword()
    {
        $res = Str::isMatch('Password123@', Regex::PASSWORD_COMPLEX, 'cek password');
        $this->assertTrue($res);

        $res = Str::isMatch('Password123', Regex::PASSWORD_COMPLEX, 'cek password');
        $this->assertFalse($res);
    }

    public function testRegexPasswordModerate()
    {
        $res = Str::isMatch('Password123', Regex::PASSWORD_MODERATE, 'cek password');
        $this->assertTrue($res);

        $res = Str::isMatch('password123', Regex::PASSWORD_MODERATE, 'cek password');
        $this->assertFalse($res);

        $res = Str::isMatch('Passwordddd', Regex::PASSWORD_MODERATE, 'cek password');
        $this->assertFalse($res);

        $res = Str::isMatch('Pwd123', Regex::PASSWORD_MODERATE, 'cek password');
        $this->assertFalse($res);
    }

    public function testRegexDateYyyymmdd()
    {
        $res = Str::isMatch('2022-12-31', Regex::DATE_YYYYMMDD);
        $this->assertTrue($res);

        $res = Str::isMatch('2022-31-12', Regex::DATE_YYYYMMDD);
        $this->assertFalse($res);
    }

    public function testRegexDateDdmmyyyy()
    {
        // use -

        $res = Str::isMatch('31-12-2022', Regex::DATE_DDMMYYYY);
        $this->assertTrue($res);

        $res = Str::isMatch('12-31-2022', Regex::DATE_DDMMYYYY);
        $this->assertFalse($res);

        // use .

        $res = Str::isMatch('31.12.2022', Regex::DATE_DDMMYYYY);
        $this->assertTrue($res);

        $res = Str::isMatch('12.31.2022', Regex::DATE_DDMMYYYY);
        $this->assertFalse($res);

        // use /

        $res = Str::isMatch('31/12/2022', Regex::DATE_DDMMYYYY);
        $this->assertTrue($res);

        $res = Str::isMatch('12/31/2022', Regex::DATE_DDMMYYYY);
        $this->assertFalse($res);
    }

    public function testRegexDateDdmmmyyyy()
    {
        // use -

        $res = Str::isMatch('01-Jun-2022', Regex::DATE_DDMMMYYYY);
        $this->assertTrue($res);

        $res = Str::isMatch('Jun-01-2022', Regex::DATE_DDMMMYYYY);
        $this->assertFalse($res);

        // use /

        $res = Str::isMatch('01/Jun/2022', Regex::DATE_DDMMMYYYY);
        $this->assertTrue($res);

        $res = Str::isMatch('Jun/01/2022', Regex::DATE_DDMMMYYYY);
        $this->assertFalse($res);

        // use .

        $res = Str::isMatch('01.Jun.2022', Regex::DATE_DDMMMYYYY);
        $this->assertTrue($res);

        $res = Str::isMatch('Jun.01.2022', Regex::DATE_DDMMMYYYY);
        $this->assertFalse($res);
    }

    public function testRegexIpv4()
    {
        $test = '0.0.0.0';
        $this->assertTrue(Str::isMatch($test, Regex::IPV4));
    }

    public function testRegexIpv6()
    {
        $test = '1200:0000:AB00:1234:0000:2552:7777:1313';
        $this->assertTrue(Str::isMatch($test, Regex::IPV6));

        $test = '1200:0000:AB00:1234:O000:2552:7777:1313';
        $this->assertFalse(Str::isMatch($test, Regex::IPV6));
    }

    public function testRegexIpv4OrIpv6()
    {
        $test = '0.0.0.0';
        $this->assertTrue(Str::isMatch($test, Regex::IPV4_6));

        $test = '1200:0000:AB00:1234:0000:2552:7777:1313';
        $this->assertTrue(Str::isMatch($test, Regex::IPV4_6));

        $test = '1200:0000:AB00:1234:O000:2552:7777:1313';
        $this->assertFalse(Str::isMatch($test, Regex::IPV4_6));
    }

    public function testRegexUrl()
    {
        $test = 'https://stackoverflow.com/questions/206059/php-validation-regex-for-url';
        $this->assertTrue(Str::isMatch($test, Regex::URL));

        $test = 'http://stackoverflow.com/questions/206059/php-validation-regex-for-url';
        $this->assertTrue(Str::isMatch($test, Regex::URL));
    }
}
