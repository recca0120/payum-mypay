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
        $this->validate([
        ], 'markNew');
    }

    public function test_request_mark_captured()
    {
        $this->validate([
            'prc' => '250',
        ], 'markCaptured');

        $this->validate([
            'code' => '250',
        ], 'markCaptured');
    }

    public function test_request_mark_pending()
    {
        $this->validate([
            'prc' => '260',
        ], 'markPending');

        $this->validate([
            'code' => '260',
        ], 'markPending');
    }

    public function test_request_mark_expired()
    {
        $this->validate([
            'prc' => '380',
        ], 'markExpired');

        $this->validate([
            'code' => '380',
        ], 'markExpired');
    }

    public function test_request_mark_failed()
    {
        $this->validate([
            'prc' => '-1',
        ], 'markFailed');

        $this->validate([
            'code' => '-1',
        ], 'markFailed');

        $this->validate([
            'prc' => '400',
        ], 'markFailed');

        $this->validate([
            'code' => '400',
        ], 'markFailed');
    }

    public function test_request_syscode_mark_failed()
    {
        $this->validate([
            'SysCode' => '-1',
            'ResultCode' => '100',
        ], 'markFailed');
    }

    protected function validate($input, $type)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject($input);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived($type)->once();
    }
}
