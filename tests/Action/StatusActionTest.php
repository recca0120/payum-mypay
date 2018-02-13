<?php

namespace PayumTW\Mypay\Tests\Action;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\StatusAction;

class StatusActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testRequestMarkNew()
    {
        $this->validate([
        ], 'markNew');
    }

    public function testRequestMarkCaptured()
    {
        $this->validate([
            'prc' => '250',
        ], 'markCaptured');

        $this->validate([
            'code' => '250',
        ], 'markCaptured');
    }

    public function testRequestMarkPending()
    {
        $this->validate([
            'prc' => '260',
        ], 'markPending');

        $this->validate([
            'code' => '260',
        ], 'markPending');
    }

    public function testRequestMarkExpired()
    {
        $this->validate([
            'prc' => '380',
        ], 'markExpired');

        $this->validate([
            'code' => '380',
        ], 'markExpired');
    }

    public function testRequestMarkFailed()
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

    public function testRequestSyscodeMarkFailed()
    {
        $this->validate([
            'SysCode' => '-1',
            'ResultCode' => '100',
        ], 'markFailed');
    }

    protected function validate($input, $type)
    {
        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $request->shouldReceive('getModel')->andReturn($details = new ArrayObject($input));
        $request->shouldReceive($type)->once();

        $this->assertNull($action->execute($request));
    }
}
