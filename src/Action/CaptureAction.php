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
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $token = $request->getToken();
        $afterUrl = $token->getAfterUrl();

        if (empty($model['success_returl']) === true) {
            $model['success_returl'] = $afterUrl;
        }

        if (empty($model['failure_returl']) === true) {
            $model['failure_returl'] = $afterUrl;
        }

        // $targetUrl = $token->getTargetUrl();
        // if (empty($model['success_returl']) === true) {
        //     $model['success_returl'] = $targetUrl;
        // }
        //
        // if (empty($model['failure_returl']) === true) {
        //     $model['failure_returl'] = $targetUrl;
        // }

        $notifyToken = $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        );

        $model['echo_0'] = $notifyToken->getHash();

        $result = $this->api->call($model->toUnsafeArray(), 'api/orders');
        $model->replace((array) $result);

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
