<?php

namespace PayumTW\Mypay;

use LogicException;
use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Exception\Http\HttpException;

class Api
{
    const NOTIFY_TOKEN_FIELD = 'echo_4';

    // 1  CREDITCARD  信用卡
    const CREDITCARD = 'CREDITCARD';
    // 2  RECHARGE  儲值卡
    const RECHARGE = 'RECHARGE';
    // 3  CSTORECODE  超商代碼
    const CSTORECODE = 'CSTORECODE';
    // 4  WEBATM  WEBATM
    const WEBATM = 'WEBATM';
    // 5  TELECOM  電信小額
    const TELECOM = 'TELECOM';
    // 6  E_COLLECTION  虛擬帳號
    const E_COLLECTION = 'E_COLLECTION';
    // 7  UNIONPAY  銀聯卡
    const UNIONPAY = 'UNIONPAY';
    // 8  SVC  點數卡
    const SVC = 'SVC';
    // 9  ABROAD  海外信用卡
    const ABROAD = 'ABROAD';
    // 10  ALIPAY  支付寶
    const ALIPAY = 'ALIPAY';
    // 11  SMARTPAY  Smart Pay
    const SMARTPAY = 'SMARTPAY';

    /**
     * @var HttpClientInterface
     */
    protected $client;
    /**
     * @var MessageFactory
     */
    protected $messageFactory;
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory, Encrypter $encrypter = null)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->encrypter = $encrypter ?: new Encrypter($this->options['key']);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint(), [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], http_build_query($fields));

        $response = $this->client->send($request);

        $statusCode = $response->getStatusCode();
        if (false == ($statusCode >= 200 && $statusCode < 300)) {
            throw HttpException::factory($request, $response);
        }

        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);
        if (null === $result) {
            throw new LogicException("Response content is not valid json: \n\n{$body}");
        }

        return $result;
    }

    /**
     * getApiEndpoint.
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->options['sandbox'] === false ? 'https://mypay.tw/api/init' : 'https://pay.usecase.cc/api/init';
    }

    /**
     * createTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function createTransaction(array $params)
    {
        $supportedParams = [
            // 次特店商務代號  必要  必要
            'store_uid' => $this->options['store_uid'],
            // 消費者帳號(請代入於貴特店中該消費者登記的帳號)  必要  必要
            'user_id' => null,
            // 消費者姓名(請代入於貴特店中該消費者登記的名稱)
            'user_name' => null,
            // 消費者真實姓名
            'user_real_name' => null,
            // 消費者帳單地址
            'user_address' => null,
            // 消費者身份證字號
            'user_sn' => null,
            // 消費者家用電話(白天電話)
            'user_phone' => null,
            // 消費者行動電話
            'user_cellphone' => null,
            // 消費者 E­Mail
            'user_email' => null,
            // 消費者生日(格式為 YYYYMMDD，如 20090916)
            'user_birthday' => null,
            // 訂單總金額(如為定期定額付費，此為一期的金額) = 物品 之總價加總 ­ 折價  必要  必要
            'cost' => null,
            // 訂單編號(訂單編號建議不要重覆)  必要  必要
            'order_id' => null,
            // 消費者來源 IP  必要  必要
            'ip' => $this->options['ip'],
            // /**
            //  * 定期定額付費，期數單位：
            //  * W 為每週定期一次扣款；
            //  * M 為每月定期一次扣款；
            //  * S 為每季定期
            //  * 一次扣款。如未使用到定期定額付費，不需傳此參數
            //  */
            'regular' => null,
            /*
             * 總期數(如為 12 期即代入 12，如果為不限期數，請代入  0，如非定期定額付費，不需傳此參數
             */
            'regular_total' => null,
            // 訂單內物品數  必要
            'item' => null,
            // 預選付費方法，如 pfn=CREDITCARD 即為信用卡付 費。多種類型可用逗號隔開，其他參數請參照附錄一。  必要  必要
            'pfn' => static::CREDITCARD,
            // 交易成功後的轉址(若動態網址可以使用此方式傳遞)
            'success_returl' => null,
            // 交易失敗後的轉址(若動態網址可以使用此方式傳遞)
            'failure_returl' => null,
            // 折價
            'discount' => null,
        ];

        $supportedItemParams = ['id', 'name', 'cost', 'amount'];
        if (isset($params['items']) === true) {
            $params['item'] = count($params['items']);
            $total = 0;
            foreach ($params['items'] as $key => $item) {
                if (empty($item['cost']) === true) {
                    $item['cost'] = $item['price'];
                }
                if (empty($item['amount']) === true) {
                    $item['amount'] = $item['quantity'];
                }
                foreach ($supportedItemParams as $name) {
                    $params['i_'.$key.'_'.$name] = $item[$name];
                }
                $params['i_'.$key.'_total'] = $item['cost'] * $item['amount'];
                $total += $params['i_'.$key.'_total'];
            }
            if (empty($params['cost'])) {
                $params['cost'] = $total;
            }
        }

        // // 名目總金額    必要
        // 'voucher_total_price' => '',
        // // 票券物品數    必要
        // 'voucher_item' => '',
        // // 票券張數     必要
        // 'v_[n]_count' => '',
        // // 面額     必要
        // 'v_[n]_price' => '',
        // // 每張票券實際交易金額    必要
        // 'v_[n]_cost' => '',
        // // 履約保證起始     必要
        // 'v_[n]_assure_start' => '',
        // // 履約保證結束     必要
        // 'v_[n]_assure_end' => '',
        // // 票券有效起始時間    必要
        // 'v_[n]_validity_start' => '',
        // // 票券有效結束時間     必要
        // 'v_[n]_validity_end' => '',
        // // 票券總產生張數    必要
        // 'voucher_total_count' => '',

        foreach ($params as $key => $value) {
            if (preg_match('/(i|v|echo)_\d+/', $key)) {
                $supportedParams[$key] = null;
            }
        }

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        return $this->call($params, 'api/orders');
    }

    /**
     * getTransactionData.
     *
     * @param mixed $params
     *
     * @return array
     */
    public function getTransactionData(array $params)
    {
        $supportedParams = [
            'uid' => null,
            'key' => null,
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        return $this->call($params, 'api/queryorder');
    }

    /**
     * verifyHash.
     *
     * @param  array $params
     * @param  array $details
     *
     * @return bool
     */
    public function verifyHash(array $params, $details)
    {
        return $params['key'] === $details['key'];
    }

    /**
     * call.
     *
     * @param array $params
     *
     * @return array
     */
    protected function call($params, $cmd)
    {
        return $this->doRequest([
            'store_uid' => $this->options['store_uid'],
            'service' => $this->calculateHash([
                'service_name' => 'api',
                'cmd' => $cmd,
            ]),
            'encry_data' => $this->calculateHash($params),
        ]);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function calculateHash(array $params)
    {
        return $this->encrypter->encrypt(json_encode($params));
    }
}
