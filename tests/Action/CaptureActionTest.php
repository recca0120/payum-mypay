<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use PayumTW\Mypay\Action\CaptureAction;

class CaptureActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture([]));
        $request->shouldReceive('getToken')->once()->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );
        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo');
        $action->setGenericTokenFactory(
            $tokenFactory = m::mock('Payum\Core\Security\GenericTokenFactoryInterface')
        );
        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName = 'foo');
        $token->shouldReceive('getDetails')->once()->andReturn($details = ['foo' => 'bar']);
        $tokenFactory->shouldReceive('createNotifyToken')->once()->with($gatewayName, $details)->andReturn(
            $notifyToken = m::mock('Payum\Core\Security\TokenInterface')
        );
        $notifyToken->shouldReceive('getHash')->once()->andReturn($hash = md5(rand()));
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('PayumTW\Mypay\Request\Api\CreateTransaction'));
        $action->execute($request);
    }

    public function testCaptured()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture(['uid' => 1]));
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Sync'));
        $action->execute($request);
    }
}
