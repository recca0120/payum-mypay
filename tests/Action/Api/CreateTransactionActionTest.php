<?php

use Mockery as m;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends PHPUnit_Framework_TestCase
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
        $request = m::spy('PayumTW\Mypay\Request\Api\CreateTransaction');
        $details = new ArrayObject([
            'url' => 'foo.url'
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->twice()->andReturn($details);

        $api
            ->shouldReceive('createTransaction')->once()->andReturn($details->toUnsafeArray());

        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertInstanceOf('Payum\Core\Reply\HttpRedirect', $response);
        }

        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('createTransaction')->once();
    }

    /**
     * @expectedException LogicException
     */
    public function test_redirect_to_mypay_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Mypay\Api');
        $request = m::spy('PayumTW\Mypay\Request\Api\CreateTransaction');
        $details = new ArrayObject([
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->twice()->andReturn($details);

        $api
            ->shouldReceive('createTransaction')->once()->andReturn($details->toUnsafeArray());

        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('createTransaction')->once();
    }
}
