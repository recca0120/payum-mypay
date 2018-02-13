<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\RefundAction;

class RefundActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new RefundAction();
        $request = new Refund(new ArrayObject([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Mypay\Request\Api\RefundTransaction');

        $this->assertNull($action->execute($request));
    }
}
