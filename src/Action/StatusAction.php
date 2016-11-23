<?php

namespace PayumTW\Mypay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($details['prc']) === true) {

            /*
             * 290 交易成功，但資訊不符  交易成功，但資訊不符(包含金額、已逾期...等)
             */
            if (in_array($details['prc'], ['250', '600', '290'], true) === true) {
                $request->markCaptured();

                return;
            }

            /*
             * 280 儲值/WEBATM­線上待付款，但需要等到使用者線上確認交易
             */
            if (in_array($details['prc'], ['260', '270', '280', 'A0002'], true) === true) {
                $request->markPending();

                return;
            }

            if ($details['prc'] === '380') {
                $request->markExpired();

                return;
            }

            $request->markFailed();

            return;
        }

        if (isset($details['SysCode']) === true && isset($details['ResultCode']) === true && $details['ResultCode'] == '100') {
            $request->markFailed();

            return;
        }

        $request->markNew();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
