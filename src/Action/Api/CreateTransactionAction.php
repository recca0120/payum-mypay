<?php

namespace PayumTW\Mypay\Action\Api;

use LogicException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use PayumTW\Mypay\Request\Api\CreateTransaction;

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
        $details->replace($result);

        if (isset($result['url']) === false) {
            throw new LogicException("Response content is not valid json: \n\n".json_encode($result));
        } else {
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
