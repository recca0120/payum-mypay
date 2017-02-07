<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use PayumTW\Mypay\Action\SyncAction;

class SyncActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new SyncAction();
        $request = new Sync([]);
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('PayumTW\Mypay\Request\Api\GetTransactionData'));
        $action->execute($request);
    }
}
