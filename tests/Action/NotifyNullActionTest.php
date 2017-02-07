<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use PayumTW\Mypay\Api;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use PayumTW\Mypay\Action\NotifyNullAction;

class NotifyNullActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new NotifyNullAction();
        $request = new Notify(null);
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) {
            $getHttpRequest->request = [Api::NOTIFY_TOKEN_FIELD => 'key'];

            return $getHttpRequest;
        });
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetToken'));
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Notify'));
        $action->execute($request);
    }
}
