<?php

namespace PayumTW\Mypay\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Request\Api\CreateTransaction;
use PayumTW\Mypay\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException Payum\Core\Reply\HttpRedirect
     */
    public function testExecute()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction($details = new ArrayObject(['foo' => 'bar']));
        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );
        $api->shouldReceive('createTransaction')->once()->with((array) $details)->andReturn(['url' => 'foo']);

        $action->execute($request);

        $this->assertSame([
            'foo' => 'bar',
            'url' => 'foo',
        ], (array) $request->getModel());
    }

    /**
     * @expectedException LogicException
     */
    public function testExecuteFail()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction($details = new ArrayObject(['foo' => 'bar']));
        $action->setApi(
            $api = m::mock('PayumTW\Mypay\Api')
        );
        $api->shouldReceive('createTransaction')->once()->with((array) $details)->andReturn(['foo' => 'foo']);

        $action->execute($request);

        $this->assertSame([
            'foo' => 'bar',
            'foo' => 'foo',
        ], (array) $request->getModel());
    }
}
