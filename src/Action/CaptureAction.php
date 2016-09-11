<?php

namespace PayumTW\Mypay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use PayumTW\Mypay\Api;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Sync;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException(sprintf('Not supported. Expected %s instance to be set as api.', Api::class));
        }

        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (isset($details['uid']) === true) {
            $this->gateway->execute(new Sync($details));

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

        $result = $this->api->createOrder($details->toUnsafeArray(), 'api/orders');
        $details->replace((array) $result);

        throw new HttpRedirect($result['url']);
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
