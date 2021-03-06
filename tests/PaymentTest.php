<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 16.03.18
 * Time: 15:16
 */

class PaymentTest extends TestCase
{
    var $_positive_header;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $profile = getenv('TEST_PROFILE');
        $security = getenv('TEST_SECURITY_CODE');

        $this->_positive_header = array('profile-id' => $profile,
            'security-code' => $security,
            'system-id' => 'test',
            'sandbox' => true);
    }

    /**
     * positive test for payment request
     */
    public function testPositivePaymentRequest()
    {
        $this->json('POST', 'trx/', $this->getPositivePaymentRequest(), $this->_positive_header)
             ->seeJson(["successful" => true, "reason_message" => "Request successful"]);
    }

    /**
     * negative test for payment request
     */
    public function testNegativePaymentRequest()
    {
        $this->json('POST', 'trx/', $this->getNegativePaymentRequest(), $this->_positive_header)
             ->seeJson(["successful" => false, "reason_message" => "Request not successful"]);
    }

    /**
     * positive test for payment confirm
     */
    public function testPositivePaymentRequestConfirm()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx/', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['head']['external']['order_id'] = 'A12345';
        $data['options']['operation'] = 'payment_confirm';
        unset($data['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header)
             ->seeJson(["successful" => true, "reason_code" => 303,"reason_message" =>"No RMS reason code"]);
    }

    /**
     * positive test for payment request auto confirm
     */
    public function testPositiveAutoPaymentRequestConfirm()
    {
        $data = $this->getPositivePaymentRequest();
        $data['head']['external']['order_id'] = 'A12345';
        $this->json('POST', 'trx', $data, $this->_positive_header)
             ->seeJson(["successful" => true, "reason_message" => "Request successful"]);
    }

    /**
     * positive test for payment deliver
     */
    public function testPositiveDeliver()
    {
        $data = $this->getPositivePaymentRequest();
        $data['head']['external']['order_id'] = 'A12345';
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['options']['operation'] = 'confirmation_deliver';
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header)
             ->seeJson(["successful" => true, "reason_code" => 303,"reason_message" =>"No RMS reason code"]);
    }

    /**
     * negative test for confirmation deliver
     */
    public function testNegativeDeliver()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['options']['operation'] = 'confirmation_deliver';
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'] . 1, $data, $this->_positive_header)
             ->seeJson(["successful" => false, "reason_code" =>100, "reason_message" => "Internal server error: There is no regular TransactionId for the ProfileId "]);
    }

    /**
     * positive test for payment cancellation
     */
    public function testPositiveCancellation()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['options']['operation'] = 'cancellation';
        $data['head']['transaction_id'] = $res['transaction_id'];
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header)
            ->seeJson(["successful" => true, "reason_code" => 700, "reason_message" => "Request successful"]);
    }

    /**
     * negative test for payment cancellation
     */
    public function testNegativeCancellation()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['options']['operation'] = 'cancellation';
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'] . 1, $data, $this->_positive_header)
            ->seeJson(["successful" => false, "reason_code" =>100, "reason_message" => "Internal server error: There is no regular TransactionId for the ProfileId "]);
    }

    /**
     * negative test for payment return
     */
    public function testNegativeReturn()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['head']['external']['order_id'] = 'A12345';
        $data['options']['operation'] = 'confirmation_deliver';
        unset($data['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header);

        $data['options']['operation'] = 'return';
        $this->json('PUT', 'trx/' . $res['transaction_id'] . 1, $data, $this->_positive_header)
            ->seeJson(["successful" => false, "reason_code" =>100, "reason_message" => "Internal server error: There is no regular TransactionId for the ProfileId "]);
    }

    /**
     * positive test for payment return
     */
    public function testPositiveReturn()
    {
        $data = $this->getPositivePaymentRequest();
        $data['head']['external']['order_id'] = 'A12345';
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();
        $res = json_decode($res, true);

        $data['options']['operation'] = 'confirmation_deliver';
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header);

        $data['options']['operation'] = 'return';
        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header)
            ->seeJson(["successful" => true, "reason_code" => 700,"reason_message" => "Request successful"]);
    }

    /**
     * positive test for payment credit
     */
    public function testPositiveCredit()
    {
        $data = $this->getPositivePaymentRequest();
        $data['head']['external']['order_id'] = 'A12345';
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['options']['operation'] = 'confirmation_deliver';
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header);

        unset($data['content']['shopping_basket']['items']);
        unset($data['content']['shopping_basket']['shipping']);
        $data['content']['shopping_basket']['discount'] = array('description' => 'Discount',
                                                                'unit_price_gross' => -20,
                                                                'tax_rate' => 19);
        $data['options']['operation'] = 'credit';
        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header)
             ->seeJson(["successful" => true, "reason_code" => 700,"reason_message" => "Request successful"]);
    }

    /**
     * negative test for payment credit
     */
    public function testNegativeCredit()
    {
        $data = $this->getPositivePaymentRequest();
        $data['head']['external']['order_id'] = 'A12345';
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);

        $data['options']['operation'] = 'confirmation_deliver';
        $data['head']['transaction_id'] = $res['transaction_id'];
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'], $data, $this->_positive_header);

        unset($data['content']['shopping_basket']['items']);
        unset($data['content']['shopping_basket']['shipping']);
        $data['content']['shopping_basket']['discount'] = array('description' => 'Discount',
                                                                'unit_price_gross' => -20,
                                                                'tax_rate' => 19);
        $data['options']['operation'] = 'credit';
        $this->json('PUT', 'trx/' . $res['transaction_id'] . 1, $data, $this->_positive_header)
            ->seeJson(["successful" => false, "reason_code" =>100]);
    }

    /**
     * positive test for change order
     */
    public function testPositiveChange()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);
        $data['head']['transaction_id'] = $res['transaction_id'];

        $this->json('PUT', 'trx/' . $res['transaction_id'], $this->_getChangeOrder(), $this->_positive_header)
            ->seeJson(["successful" => true, "reason_code" => 700, "reason_message" => "Request successful"]);
    }

    /**
     * negative test for change order
     */
    public function testNegativeChange()
    {
        $data = $this->getPositivePaymentRequest();
        $this->json('POST', 'trx', $data, $this->_positive_header);
        $res = $this->response->getContent();

        $res = json_decode($res, true);
        $data['head']['transaction_id'] = $res['transaction_id'];

        $data['options']['operation'] = 'change_order';
        unset($data['content']['customer']);

        $this->json('PUT', 'trx/' . $res['transaction_id'] . 1, $this->_getChangeOrder(), $this->_positive_header)
            ->seeJson(["successful" => false, "reason_code" =>100, "reason_message" => "Internal server error: There is no regular TransactionId for the ProfileId "]);
    }

    private function getNegativePaymentRequest()
    {
        $data =  "{
                \"options\":{
                    \"operation\":\"payment_request\"
                },
                \"content\":{
                \"customer\":{
                        \"gender\":\"f\",
                        \"first_name\":\"Alice\",
                        \"last_name\":\"Ablehnung\",
                        \"date_of_birth\":\"1976-09-24\",
                        \"ip_address\":\"127.0.0.1\",
                        \"addresses\":[
                            {
                                \"type\":\"billing\",
                                \"street\":\"Nicht Versenden 2\",
                                \"zip_code\":\"12345\",
                                \"city\":\"Testhausen\",
                                \"country_code\":\"de\"
                            }
                        ],
                        \"contacts\":{
                            \"email\":\"alice@umbella.tld\",
                            \"phone\":{
                                \"direct_dial\":\"012345678\"
                            }
                        }
                    },
                    \"shopping_basket\":{
                        \"items\":[
                            {
                                \"description\":\"Test Product 2\",
                                \"article_number\":\"ArtNo2\",
                                \"quantity\":\"1\",
                                \"unit_price_gross\":\"300\",
                                \"tax_rate\":\"19\"
                            },
                            {
                                \"description\":\"Test Product 1\",
                                \"article_number\":\"ArtNo1\",
                                \"quantity\":\"2\",
                                \"unit_price_gross\":\"100\",
                                \"tax_rate\":\"19\"
                            }
                        ],
                        \"shipping\":{
                            \"description\":\"Shipping Costs\",
                            \"unit_price_gross\":\"4.95\",
                            \"tax_rate\":\"19\"
                        },
                        \"discount\":{
                            \"description\":\"Discount Costs\",
                            \"unit_price_gross\":\"20\",
                            \"tax_rate\":\"19\"
                        }
                    },
                    \"payment\":{
                        \"method\":\"installment\",
                        \"amount\":\"483.25\",
                        \"installment_details\":{
                            \"installment_number\":\"6\",
                            \"installment_amount\":\"80.55\",
                            \"lastInstallment_amount\":\"80.5\",
                            \"interest_rate\":\"9.8\",
                            \"payment_firstday\":\"28\"
                        },
                        \"debit_pay_type\":\"BANK-TRANSFER\"
                    }
                }
            }";
        return json_decode($data, true);
    }

    private function getPositivePaymentRequest()
    {
        $data = $this->getNegativePaymentRequest();
        $data['content']['customer']['last_name'] = 'Nobody';
        return $data;
    }

    private function _getChangeOrder()
    {
        $data =  "{
                \"options\":{
                    \"operation\":\"change_order\"
                },
                \"content\":{
                    \"shopping_basket\":{
                        \"items\":[
                            {
                                \"description\":\"Change Product \",
                                \"article_number\":\"Change 123\",
                                \"quantity\":\"1\",
                                \"unit_price_gross\":\"100\",
                                \"tax_rate\":\"19\"
                            }
                        ]
                    }
                }
            }";
        return json_decode($data, true);
    }

}