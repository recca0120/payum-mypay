<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\StatusAction;

class StatusActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_request_mark_new()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markNew')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_captured()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'prc' => '250',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markCaptured')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_pending()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'prc' => '260',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markPending')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_expired()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'prc' => '380',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markExpired')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_failed()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'prc' => '-1',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markFailed')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_syscode_mark_failed()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'SysCode' => '-1',
            'ResultCode' => '100',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markFailed')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }
}
