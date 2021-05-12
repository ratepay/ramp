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

    var $_headArray;

    var $_sandbox;

    public function __construct(Request $request)
    {
        $this->_options = $request->json('options');
        $head = $request->json('head');
        $this->_content = $request->json('content');


        $header = $request->server->getHeaders();
        if (!empty($header['PROFILE_ID'])) {
            $head['SystemId'] = $header['SYSTEM_ID'];
            $head['Credential']['ProfileId'] = $header['PROFILE_ID'];
            $head['Credential']['Securitycode'] = $header['SECURITY_CODE'];
        }

        $this->_head = $head;
        $this->_headArray = $header;
        $this->_sandbox = filter_var($header['SANDBOX'], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * prepare requests
     *
     * @param $transactionId
     * @return mixed
     */
    public function prepareRequest($transactionId = null)
    {
        $head = $this->_head;
        if (!empty($transactionId)) {
            $head['transaction_id'] = $transactionId;
        }

        $this->_controller = new BaseController();

        $this->_content = $this->_controller->prepareContent($this->_content);
        $this->_head = $this->_controller->prepareHead($head);

        $this->_rb = new RatePAY\RequestBuilder($this->_sandbox);

        if (!empty($this->_options['connection_timeout'])) {
            $this->_rb->setConnectionTimeout($this->_options['connection_timeout']);
        }
        if (!empty($this->_options['execution_timeout'])) {
            $this->_rb->setConnectionTimeout($this->_options['execution_timeout']);
        }
        if (!empty($this->_options['connection_retries'])) {
            $this->_rb->setConnectionTimeout($this->_options['connection_retries']);
        }
        if (!empty($this->_options['retry_delay'])) {
            $this->_rb->setConnectionTimeout($this->_options['retry_delay']);
        }

        $header = $this->_headArray;
        if (!empty($header['LOGGING'])) {
            $this->_controller->setLogging(filter_var($header['LOGGING'], FILTER_VALIDATE_BOOLEAN));
        }

        switch ($this->_options['operation']) {
            case 'payment_request':
                return $this->_callPaymentRequest();
                break;
            case 'payment_confirm':
                return $this->_callPaymentConfirm();
                break;
            case 'confirmation_deliver':
                return $this->_callConfirmationDeliver();
                break;
            case 'return':
            case 'cancellation':
            case 'change_order':
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
        $head = $this->_headArray;
        $response = $this->_controller->prepareResponse($paymentRequest, 'payment');

        if ($paymentRequest->isSuccessful() && !empty($head['external']['order_id'])) {
            $head['transaction_id'] = $paymentRequest->getTransactionId();
            $this->_head = $this->_controller->prepareHead($head);
            $this->_callPaymentConfirm();
        }

        return $response;
    }

    /**
     * call auto payment confirm
     *
     * @return mixed
     */
    private function _callPaymentConfirm()
    {
        $paymentConfirm = $this->_rb->callPaymentConfirm($this->_head);
        return $this->_controller->prepareResponse($paymentConfirm, 'paymentconfirm');
    }

    /**
     * call confirmation deliver
     *
     * @return mixed
     */
    private function _callConfirmationDeliver()
    {
        $confirmationDeliver = $this->_rb->callConfirmationDeliver($this->_head, $this->_content);
        return $this->_controller->prepareResponse($confirmationDeliver, 'paymentdeliver');
    }

    /**
     * call payment return
     *
     * @return mixed
     */
    private function _callPaymentChange()
    {
        if ($this->_options['operation'] == 'change_order') {
            $this->_options['operation'] = 'change-order';
        }

        $paymentChange = $this->_rb->callPaymentChange($this->_head, $this->_content)->subtype($this->_options['operation']);
        return $this->_controller->prepareResponse($paymentChange, 'paymentchange');
    }

    /**
     * call payment credit
     *
     * @return mixed
     */
    private function _callPaymentDiscount()
    {
        $paymentChange = $this->_rb->callPaymentChange($this->_head, $this->_content)->subtype($this->_options['operation']);
        return $this->_controller->prepareResponse($paymentChange, 'paymentcredit');
    }

}
