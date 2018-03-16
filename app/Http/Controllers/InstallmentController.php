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

    public function __construct(Request $request)
    {
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
        if (!empty($content)) {
            $this->_content = $this->_controller->prepareContent($content);
        }

        $this->_rb = new RatePAY\RequestBuilder($header['SANDBOX']);
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
    public function callCalculationByTime()
    {
        $calculationRequest = $this->_rb->callCalculationRequest($this->_head, $this->_content)->subtype('calculation-by-time');
        return $this->_controller->prepareResponse($calculationRequest, 'calculator');
    }

    /**
     * calculation by rate
     *
     * @return mixed
     */
    public function callCalculationByRate()
    {
        $calculationRequest = $this->_rb->callCalculationRequest($this->_head, $this->_content)->subtype('calculation-by-time');
        return $this->_controller->prepareResponse($calculationRequest, 'calculator');
    }
}