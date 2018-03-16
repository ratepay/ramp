<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as Controller;
use App\Http\Controllers\BaseController as BaseController;
use RatePAY;

class PaymentController extends Controller
{
    var $_trx;

    var $_head;

    var $_content;

    var $_rb;

    var $_options;

    var $_controller;

    public function __construct(Request $request, $transactionId = null)
    {
        $this->_options = $request->json('options');
        $head = $request->json('head');
        $content = $request->json('content');

        if (!empty($transactionId)) {
            $head['TransactionId'] = $transactionId;
        }

        $header = $request->server->getHeaders();
        if (!empty($header['PROFILE_ID'])) {
            $head['SystemId'] = $header['SYSTEM_ID'];
            $head['Credential']['ProfileId'] = $header['PROFILE_ID'];
            $head['Credential']['Securitycode'] = $header['SECURITY_CODE'];
        }

        $this->_controller = new BaseController();

        $this->_content = $this->_controller->prepareContent($content);
        $this->_head = $this->_controller->prepareHead($head);

        $this->_rb = new RatePAY\RequestBuilder($header['SANDBOX']);

    }

    /**
     * prepare requests
     *
     * @return mixed
     */
    public function prepareRequest()
    {
        switch ($this->_options['operation']) {
            case 'purchase':
                return $this->_callPaymentRequest();
                break;
            case 'confirm':
                return $this->_callPaymentConfirm();
                break;
            case 'delivery':
                return $this->_callConfirmationDeliver();
                break;
            case 'return':
            case 'cancellation':
                return $this->_callPaymentChange();
                break;
            case 'credit':
            case 'debit':
                return $this->_callPaymentDiscount();
                break;
        }
    }

    /**
     * call payment request
     *
     * @return mixed
     */
    private function _callPaymentRequest()
    {
        $paymentRequest = $this->_rb->callPaymentRequest($this->_head, $this->_content);

        if ($paymentRequest->isSuccessful()) {
            $this->_trx = $paymentRequest->getTransactionId();

            if (isset($head['External']['OrderId'])) {
                    $this->_callPaymentConfirm();
            }

            return $this->_controller->prepareResponse($paymentRequest, 'payment');
        }
    }

    /**
     * call auto payment confirm
     *
     * @return mixed
     */
    private function _callPaymentConfirm()
    {
        if (!empty($this->_trx)) {
            $head['TransactionId'] = $this->_trx;
        }

        $paymentConfirm = $this->_rb->callPaymentConfirm($this->_head);
        return $this->_controller->prepareResponse($paymentConfirm, 'payment');
    }

    /**
     * call confirmation deliver
     *
     * @return mixed
     */
    private function _callConfirmationDeliver()
    {
        $confirmationDeliver = $this->_rb->callConfirmationDeliver($this->_head, $this->_content);
        return $this->_controller->prepareResponse($confirmationDeliver, 'payment');
    }

    /**
     * call payment return
     *
     * @return mixed
     */
    private function _callPaymentChange()
    {
        $paymentChange = $this->_rb->callPaymentChange($this->_head, $this->_content)->subtype($this->_options['operation']);
        return $this->_controller->prepareResponse($paymentChange, 'payment');
    }

    /**
     * call payment credit
     *
     * @return mixed
     */
    private function _callPaymentDiscount()
    {
        $paymentChange = $this->_rb->callPaymentChange($this->_head, $this->_content)->subtype($this->_options['operation']);
        return $this->_controller->prepareResponse($paymentChange, 'payment');
    }

}
