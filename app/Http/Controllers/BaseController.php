<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 14.03.18
 * Time: 09:41
 */

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller;
use RatePAY;

class BaseController extends Controller
{

    /**
     * prepare head
     *
     * @param array $head
     * @return object
     */
    public function prepareHead($head) {
        $head = $this->_changeKeyFormat($head);
        $mbHead = new RatePAY\ModelBuilder();
        $mbHead->setArray($head);
        return $mbHead;
    }

    /**
     * prepare content
     *
     * @param array $content
     * @return object
     */
    public function prepareContent($content) {
        $content = $this->_changeKeyFormat($content);
        $mbContent = new RatePAY\ModelBuilder('Content');

        if (is_array($content['ShoppingBasket']['Items'])) {
            foreach ($content['ShoppingBasket']['Items'] AS $item) {
                $basketItems[] = array('Item' => $item);
            }
            $content['ShoppingBasket']['Items'] = $basketItems;
        }

        if (is_array($content['Customer']['Addresses'])) {
            foreach ($content['Customer']['Addresses'] AS $address) {
                $addresses[] = array('Address' => $address);
            }
            $content['Customer']['Addresses'] = $addresses;
        }

        $mbContent->setArray($content);
        return $mbContent;
    }

    /**
     * @param object $sdk
     * @return string
     */
    public function prepareResponse($sdk, $type) {
        $response = array();

        $response['successful'] = $sdk->isSuccessful();
        $response['reason_code'] = $sdk->getReasonCode();
        $response['reason_message'] = $sdk->getReasonMessage();

        switch ($type) {
            case 'payment':
                $response['transaction_id'] = $sdk->getTransactionId();
                $response['customer_message'] = $sdk->getCustomerMessage();
                $response['retry_admitted'] = $sdk->isRetryAdmitted();
                $response['descriptor'] = $sdk->getDescriptor();
                break;
            case 'installment':
                $response['min_rate'] = $sdk->getMinRate();
                $response['allowed_months'] = $sdk->getAllowedMonths();
                break;
            case 'calculator':
                $response['payment_amount'] = $sdk->getPaymentAmount();
                $response['installment_number'] = $sdk->getInstallmentNumber();
                $response['installment_amount'] = $sdk->getInstallmentAmount();
                $response['last_installment_amount'] = $sdk->getLastInstallmentAmount();
                $response['interest_rate'] = $sdk->getInterestRate();
                $response['payment_firstday'] = $sdk->getPaymentFirstday();
                break;
        }

        $response['request'] = array($sdk->getRequestRaw());
        $response['response'] = array($sdk->getResponseRaw());

        return json_encode($response, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $content
     * @return array $content
     */
    protected function _changeKeyFormat($content) {
        foreach ($content AS $key => $value) {

            if (is_array($value)) {
                $value = $this->_changeKeyFormat($value);
            }
            unset($content[$key]);

            $key = ucwords($key, '_');
            $key = str_replace('_', '', $key);

            $content[ucfirst($key)] = $value;
        }
        return $content;
    }
}