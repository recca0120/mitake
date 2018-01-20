<?php

namespace Recca0120\Mitake\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\Mitake\MitakeMessage;

class MitakeMessageTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testConstruct()
    {
        $message = new MitakeMessage(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testContent()
    {
        $message = new MitakeMessage();
        $message->content(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testCreate()
    {
        $message = MitakeMessage::create(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }
}
