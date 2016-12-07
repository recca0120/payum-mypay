<?php

use Mockery as m;
use PayumTW\Mypay\Api;
use PayumTW\Mypay\Action\NotifyNullAction;

class NotifyNullActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_notify_execute()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new NotifyNullAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Notify');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once()->andReturnUsing(function ($request) {
                $request->request[Api::NOTIFY_TOKEN_FIELD] = 'fooTokenField';

                return $request;
            })
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetToken'))->once()
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\Notify'))->once();

        $request->shouldReceive('getModel')->once()->andReturnNull();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->execute($request);
    }
}
