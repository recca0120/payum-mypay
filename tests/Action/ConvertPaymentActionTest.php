<?php

use Mockery as m;
use PayumTW\Mypay\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_convert()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new ConvertPaymentAction();
        $request = m::mock('Payum\Core\Request\Convert');
        $payment = m::mock('Payum\Core\Model\PaymentInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getSource')->twice()->andReturn($payment)
            ->shouldReceive('getTo')->once()->andReturn('array');

        $payment
            ->shouldReceive('getDetails')->andReturn([])
            ->shouldReceive('getNumber')->andReturn('fooNumber')
            ->shouldReceive('getClientId')->andReturn('fooClientId')
            ->shouldReceive('getClientEmail')->andReturn('fooClientEmail')
            ->shouldReceive('getTotalAmount')->andReturn('fooTotalAmount');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $request->shouldReceive('setResult')->once()->andReturnUsing(function ($data) {
            $this->assertSame([
                'order_id' => 'fooNumber',
                'user_id' => 'fooClientId',
                'user_email' => 'fooClientEmail',
                'cost' => 'fooTotalAmount',
            ], $data);
        });

        $action->execute($request);
    }
}
