<?php

use Mockery as m;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\NotifyAction;

class NotifyActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_notify_success()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $api = m::spy('PayumTW\Mypay\Api');

        $response = [];

        $details = new ArrayObject($response);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($response) {
                $getHttpRequest->request = $response;

                return $getHttpRequest;
            });

        $api
            ->shouldReceive('verifyHash')->with($response, $details)->andReturn(true);

        $action = new NotifyAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(200, $e->getStatusCode());
            $this->assertSame('8888', $e->getContent());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('verifyHash')->with($response, $details)->once();
    }

    public function test_notify_when_checksum_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $api = m::spy('PayumTW\Mypay\Api');

        $response = [];

        $details = new ArrayObject($response);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($response) {
                $getHttpRequest->request = $response;

                return $getHttpRequest;
            });

        $api
            ->shouldReceive('verifyHash')->with($response, $details)->andReturn(false);

        $action = new NotifyAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertSame('key verify fail.', $e->getContent());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('verifyHash')->with($response, $details)->once();
    }
}
