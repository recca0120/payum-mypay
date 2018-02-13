<?php

namespace PayumTW\Mypay\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Request\Api\CancelTransaction;
use PayumTW\Mypay\Action\Api\CancelTransactionAction;

class CancelTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CancelTransactionAction();
        $request = new CancelTransaction(new ArrayObject(['uid' => 'foo', 'key' => 'foo']));

        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );

        $api->shouldReceive('cancelTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        $action->execute($request);

        $this->assertSame(array_merge(['uid' => 'foo', 'key' => 'foo'], $params), (array) $request->getModel());
    }
}
