<?php

namespace PayumTW\Mypay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use PayumTW\Mypay\Api;

class NotifyNullAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $notifyToken = $httpRequest->request[Api::NOTIFY_TOKEN_FIELD];
        $getToken = new GetToken($notifyToken);
        $this->gateway->execute($getToken);
        $this->gateway->execute(new Notify($getToken->getToken()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            null === $request->getModel();
    }
}
