<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\CaptureAction;

class CaptureActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_redirect_to_mypay()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Mypay\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $token = m::spy('Payum\Core\Security\TokenInterface');
        $notifyToken = m::mock('Payum\Core\Security\TokenInterface');
        $details = new ArrayObject([]);
        $targetUrl = 'foo.target_url';
        $gatewayName = 'foo.gateway_name';
        $hash = 'foo.hash';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)
            ->shouldReceive('getToken')->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn($targetUrl)
            ->shouldReceive('getGatewayName')->andReturn($gatewayName)
            ->shouldReceive('getDetails')->andReturn($details);

        $tokenFactory
            ->shouldReceive('createNotifyToken')->with($gatewayName, $details)->andReturn($notifyToken);

        $notifyToken
            ->shouldReceive('getHash')->andReturn($hash);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $request->shouldHaveReceived('getToken')->once();
        $token->shouldHaveReceived('getTargetUrl')->once();
        $token->shouldHaveReceived('getGatewayName')->once();
        $token->shouldHaveReceived('getDetails')->once();
        $tokenFactory->shouldHaveReceived('createNotifyToken')->once();
        $notifyToken->shouldHaveReceived('getHash')->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Mypay\Request\Api\CreateTransaction'))->once();
    }

    public function test_captured_success()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Mypay\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $details = new ArrayObject([]);

        $response = [
            'uid' => 1,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($httpRequest) use ($response) {
                $httpRequest->request = $response;

                return $httpRequest;
            });

        $api
            ->shouldReceive('verifyHash')->with($response, $details)->andReturn(true);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('verifyHash')->with($response, $details)->once();
    }

    public function test_captured_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Mypay\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $details = new ArrayObject([]);

        $response = [
            'uid' => 1,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($httpRequest) use ($response) {
                $httpRequest->request = $response;

                return $httpRequest;
            });

        $api
            ->shouldReceive('verifyHash')->with($response, $details)->andReturn(false);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('verifyHash')->with($response, $details)->once();
    }
}
