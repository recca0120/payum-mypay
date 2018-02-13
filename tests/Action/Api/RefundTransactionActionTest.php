<?php

namespace PayumTW\Mypay\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Request\Api\RefundTransaction;
use PayumTW\Mypay\Action\Api\RefundTransactionAction;

class RefundTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new RefundTransactionAction();
        $request = new RefundTransaction(new ArrayObject(['uid' => 'foo_uid', 'key' => 'foo_key', 'cost' => 'foo_cost']));

        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );

        $api->shouldReceive('refundTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        $action->execute($request);

        $this->assertSame(array_merge(['uid' => 'foo_uid', 'key' => 'foo_key', 'cost' => 'foo_cost'], $params), (array) $request->getModel());
    }
}
