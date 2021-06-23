<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 05.03.18
 * Time: 15:42
 */

namespace App\Http\Controllers;

use RatePAY;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as Controller;
use App\Http\Controllers\BaseController as BaseController;

class InstallmentController extends Controller
{
    var $_head;

    var $_content;

    var $_rb;

    var $_controller;

    var $_options;

    public function __construct(Request $request)
    {
        $this->_options = $request->json('options');
        $head = $request->json('head');
        $content = $request->json('content');

        $header = $request->server->getHeaders();
        if (!empty($header['PROFILE_ID'])) {
            $head['SystemId'] = $header['SYSTEM_ID'];
            $head['Credential']['ProfileId'] = $header['PROFILE_ID'];
            $head['Credential']['Securitycode'] = $header['SECURITY_CODE'];
        }

        $this->_controller = new BaseController();

        $this->_head = $this->_controller->prepareHead($head);

        $this->_rb = new RatePAY\RequestBuilder(filter_var($header['SANDBOX'], FILTER_VALIDATE_BOOLEAN));

        if (!empty($content)) {
            $this->_content = $this->_controller->prepareContent($content);
        }
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
        if (!empty($header['LOGGING'])) {
            $this->_controller->setLogging(filter_var($header['LOGGING'], FILTER_VALIDATE_BOOLEAN));
        }
    }

    /**
     * get configuration
     *
     * @return mixed
     */
    public function getConfiguration()
    {
        $configurationRequest = $this->_rb->callConfigurationRequest($this->_head);
        return $this->_controller->prepareResponse($configurationRequest, 'installment');
    }

    /**
     * calculation by time
     *
     * @return mixed
     */
    public function callCalculation()
    {
        $operation = "calculation-by-time";
        if ($this->_options['operation'] == 'calculation_by_rate') {
            $operation = "calculation-by-rate";
        }

        $calculationRequest = $this->_rb->callCalculationRequest($this->_head, $this->_content)->subtype($operation);
        return $this->_controller->prepareResponse($calculationRequest, 'calculator');
    }
}