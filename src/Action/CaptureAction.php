<?php

namespace PayumTW\Mypay\Action;

use PayumTW\Mypay\Api;
use Payum\Core\Request\Capture;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Mypay\Action\Api\BaseApiAwareAction;
use PayumTW\Mypay\Request\Api\CreateTransaction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;

class CaptureAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->request['uid']) === true) {
            if ($this->api->verifyHash($httpRequest->request, $details) === false) {
                $httpRequest->request['code'] = '290';
            }
            $details->replace($httpRequest->request);

            return;
        }

        $token = $request->getToken();

        $targetUrl = $token->getTargetUrl();
        if (empty($details['success_returl']) === true) {
            $details['success_returl'] = $targetUrl;
        }

        if (empty($details['failure_returl']) === true) {
            $details['failure_returl'] = $targetUrl;
        }

        $notifyToken = $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        );

        $details[Api::NOTIFY_TOKEN_FIELD] = $notifyToken->getHash();

        $this->gateway->execute(new CreateTransaction($details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
