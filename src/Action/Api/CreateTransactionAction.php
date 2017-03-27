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

        $params = $this->api->createTransaction((array) $details);

        $details->replace($params);

        if (isset($params['url']) === false) {
            throw new LogicException("Response content is not valid json: \n\n".urldecode(json_encode(array_map(function ($data) {
                return is_string($data) === true ? urlencode($data) : $data;
            }, $params))));
        } else {
            throw new HttpRedirect($params['url'].'?locale='.$this->locale(
                isset($details['locale']) === true ? $details['locale'] : 'zh-TW'
            ));
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

    /**
     * locale.
     *
     * @param string $locale
     * @return string
     */
    protected function locale($locale)
    {
        $map = [
            'zh-tw' => 'zh-TW',
            'tw' => 'zh-TW',
            'zh-cn' => 'zh-CN',
            'cn' => 'zh-CN',
            'en-us' => 'en',
            'en' => 'en',
        ];
        $locale = strtolower($locale);

        return isset($map[$locale]) === true ? $map[$locale] : 'zh-TW';
    }
}
