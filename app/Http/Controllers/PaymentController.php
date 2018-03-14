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

    public function __construct(Request $request, $transactionId = null)
    {
        $this->_options = $request->json('options');
        $head = $request->json('head');
        $content = $request->json('content');

        if (!empty($transactionId)) {
            $head['TransactionId'] = $transactionId;
        }

        $header = $request->server->getHeaders();
        if (!empty($header['PROFILE'])) {
            $head['Credential']['ProfileId'] = $header['PROFILE'];
            $head['Credential']['Securitycode'] = $header['SECURITY'];
        }

        $controller = new BaseController();

        $this->_content = $controller->prepareContent($content);
        $this->_head = $controller->prepareHead($head);

        $this->_rb = new RatePAY\RequestBuilder(true);

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

            return $paymentRequest->getResponseRaw();
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
        return $paymentConfirm->getResponseRaw();
    }

    /**
     * call confirmation deliver
     *
     * @return mixed
     */
    private function _callConfirmationDeliver()
    {
        $confirmationDeliver = $this->_rb->callConfirmationDeliver($this->_head, $this->_content);
        return $confirmationDeliver->getResponseRaw();
    }

    /**
     * call payment return
     *
     * @return mixed
     */
    private function _callPaymentChange()
    {
        $paymentChange = $this->_rb->callPaymentChange($this->_head, $this->_content)->subtype($this->_options['operation']);
        return $paymentChange->getResponseRaw();
    }

    /**
     * call payment credit
     *
     * @return mixed
     */
    private function _callPaymentDiscount()
    {
        $paymentChange = $this->_rb->callPaymentChange($this->_head, $this->_content)->subtype($this->_options['operation']);
        return $paymentChange->getResponseRaw();
    }

}
