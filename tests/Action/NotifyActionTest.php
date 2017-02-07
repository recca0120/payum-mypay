<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Payum\Core\Reply\ReplyInterface;
use PayumTW\Mypay\Action\NotifyAction;

class NotifyActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new NotifyAction();
        $request = new Notify(['key' => 'key']);
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) {
            $getHttpRequest->request = ['key' => 'key'];

            return $getHttpRequest;
        });

        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );
        $api->shouldReceive('verifyHash')->once()->with(['key' => 'key'], ['key' => 'key'])->andReturn(true);

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(200, $e->getStatusCode());
            $this->assertSame('8888', $e->getContent());
        }
    }

    public function testExecuteVerifyHashFail()
    {
        $action = new NotifyAction();
        $request = new Notify(['key' => 'key']);
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) {
            $getHttpRequest->request = ['key' => 'key'];

            return $getHttpRequest;
        });

        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );
        $api->shouldReceive('verifyHash')->once()->with(['key' => 'key'], ['key' => 'key'])->andReturn(false);

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertSame('key verify fail.', $e->getContent());
        }
    }
}
