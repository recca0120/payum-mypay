<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;
use PayumTW\Mypay\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new ConvertPaymentAction();
        $request = new Convert(
            $payment = m::mock('Payum\Core\Model\PaymentInterface'),
            $to = 'array'
        );
        $payment->shouldReceive('getDetails')->once()->andReturn([]);
        $payment->shouldReceive('getNumber')->once()->andReturn($number = 'foo');
        $payment->shouldReceive('getClientId')->once()->andReturn($clientId = 'foo');
        $payment->shouldReceive('getClientEmail')->once()->andReturn($clientEmail = 'foo');
        $payment->shouldReceive('getTotalAmount')->once()->andReturn($totalAmount = 'foo');
        $payment->shouldReceive('getCurrencyCode')->once()->andReturn($currenceCode = 'TWD');
        $action->execute($request);
        $this->assertSame([
            'order_id' => $number,
            'user_id' => $clientId,
            'user_email' => $clientEmail,
            'cost' => $totalAmount,
            'currency' => $currenceCode,
        ], $request->getResult());
    }
}
