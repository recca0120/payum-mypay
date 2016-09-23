<?php

namespace PayumTW\Mypay\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use PayumTW\Mypay\Request\Api\VerifyHash;

class VerifyHashAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request VerifyHash
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['key'] !== $httpRequest->request['key']) {
            throw new HttpResponse('key verify fail.', 400, ['Content-Type' => 'text/plain']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof VerifyHash &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
