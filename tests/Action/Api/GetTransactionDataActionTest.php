<?php

namespace PayumTW\Mypay\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Request\Api\GetTransactionData;
use PayumTW\Mypay\Action\Api\GetTransactionDataAction;

class GetTransactionDataActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new GetTransactionDataAction();
        $request = new GetTransactionData($details = new ArrayObject(['url' => 'foo']));
        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );
        $api->shouldReceive('getTransactionData')->once()->with((array) $details)->andReturn($result = ['url' => 'bar']);
        $action->execute($request);
        $this->assertSame($result, (array) $request->getModel());
    }
}
