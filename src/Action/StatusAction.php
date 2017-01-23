<?php

namespace PayumTW\Mypay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;

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

        $code = null;
        if (isset($details['prc']) === true) {
            $code = $details['prc'];
        } elseif (isset($details['code']) === true) {
            $code = $details['code'];
        }

        if (is_null($code) === false) {

            /*
             * 290 交易成功，但資訊不符  交易成功，但資訊不符(包含金額、已逾期...等)
             */
            if (in_array($code, ['250', '290', '600'], true) === true) {
                $request->markCaptured();

                return;
            }

            /*
             * 280 儲值/WEBATM­線上待付款，但需要等到使用者線上確認交易
             */
            if (in_array($code, ['200', '260', '270', '280', 'A0002'], true) === true) {
                $request->markPending();

                return;
            }

            if ($code === '380') {
                $request->markExpired();

                return;
            }

            $request->markFailed();

            return;
        }

        if (
            isset($details['SysCode']) === true &&
            isset($details['ResultCode']) === true &&
            $details['ResultCode'] == '100'
        ) {
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
