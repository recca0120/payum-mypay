<?php

namespace PayumTW\Mypay\Action\Api;

use LogicException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Request\Api\CreateTransaction;
use Payum\Core\Exception\RequestNotSupportedException;

class CreateTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request CreateTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $result = $this->api->createTransaction((array) $details);

        if (isset($result['url']) === false) {
            throw new LogicException("Response content is not valid json: \n\n".json_encode($result));
        } else {
            $details->replace($result);
            throw new HttpRedirect($details['url']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
