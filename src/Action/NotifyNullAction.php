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
        $_REQUEST = [
            'uid' => '3198',
            'key' => 'f640b06e8ac800749851f7fc334f7093',
            'prc' => '250',
            'finishtime' => '20160923110105',
            'cardno' => '4311********6200',
            'acode' => 'AB1234',
            'order_id' => '57e49a82dc5b9',
            'user_id' => '88b9b4f6d25a50d2cbc92769b38b197e',
            'cost' => '1',
            'love_cost' => '0',
            'retmsg' => '付款完成',
            'pfn' => 'CREDITCARD',
            'echo_0' =>'',
            'echo_1' =>'',
            'echo_2' =>'',
            'echo_3' =>'',
            'echo_4' => 'VjkC0XKh0aYeOLuaGznQ8_bYDY2FUA-x_QCnn8MmIOE',
        ];
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
