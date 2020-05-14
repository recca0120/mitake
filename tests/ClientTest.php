<?php

namespace Recca0120\Mitake\Tests;

use Carbon\Carbon;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Mitake\Client;

class ClientTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testQuery()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'msgid' => '265078525',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'msgid' => $params['msgid'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://smexpress.mitake.com.tw:9600/SmQueryGet.asp',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            '1010079522	1	20170101010010
1010079523	4	20170101010011'
        );

        $this->assertSame([[
            'to' => '1010079522',
            'credit' => '1',
            'time' => '20170101010010',
        ], [
            'to' => '1010079523',
            'credit' => '4',
            'time' => '20170101010011',
        ]], $client->query($params));
    }

    public function testCredit()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], []));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://smexpress.mitake.com.tw:9600/SmQueryGet.asp',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            'AccountPoint=1221'
        );

        $this->assertSame(1221, $client->credit());
    }

    public function testSend()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => '中文字',
            'subject' => 'subject',
            'sendTime' => '20180120171048',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'DestName' => $params['subject'],
            'divtime' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
            'smbody' => mb_convert_encoding($params['text'], 'big5', 'utf8'),
            'dstaddr' => $params['to'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://smexpress.mitake.com.tw:9600/SmSendGet.asp',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '
[1]
msgid=0892448417
statuscode=1
AccountPoint=97
            '
        );

        $this->assertSame([
            'msgid' => '0892448417',
            'statuscode' => '1',
            'AccountPoint' => '97',
        ], $client->send($params));
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionCode 500
     * @expectedExceptionMessage 帳號、密碼錯誤
     */
    public function testSendFail()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => '中文字',
            'subject' => 'subject',
            'sendTime' => '20180120171048',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'DestName' => $params['subject'],
            'divtime' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
            'smbody' => mb_convert_encoding($params['text'], 'big5', 'utf8'),
            'dstaddr' => $params['to'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://smexpress.mitake.com.tw:9600/SmSendGet.asp',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = mb_convert_encoding('
[1]
statuscode=p
Error=帳號、密碼錯誤
            ',
        'big5', 'utf8'));

        $client->send($params);
    }
}
