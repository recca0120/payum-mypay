<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Cancel;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\CancelAction;

class CancelActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CancelAction();
        $request = new Cancel(new ArrayObject([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Mypay\Request\Api\CancelTransaction');

        $this->assertNull($action->execute($request));
    }
}
