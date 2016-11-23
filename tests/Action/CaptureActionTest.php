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
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Capture');
        $tokenFactory = m::mock('Payum\Core\Security\GenericTokenFactoryInterface');
        $token = m::mock('stdClass');
        $notifyToken = m::mock('stdClass');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with(m::type('PayumTW\Mypay\Request\Api\CreateTransaction'))->once();

        $request
            ->shouldReceive('getModel')->twice()->andReturn($details)
            ->shouldReceive('getToken')->once()->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->once()->andReturn('fooMerchanturl')
            ->shouldReceive('getGatewayName')->once()->andReturn('fooGatewayName')
            ->shouldReceive('getDetails')->once()->andReturn([
                'foo' => 'bar',
            ]);

        $notifyToken
            ->shouldReceive('getHash')->once()->andReturn('fooHash');

        $tokenFactory
            ->shouldReceive('createNotifyToken')->once()->andReturn($notifyToken);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);
    }

    public function test_mypay_response()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Capture');
        $tokenFactory = m::mock('Payum\Core\Security\GenericTokenFactoryInterface');
        $token = m::mock('stdClass');
        $notifyToken = m::mock('stdClass');
        $details = new ArrayObject([
            'uid' => 'foo.uid',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->twice()->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\Sync'))->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);
    }
}
