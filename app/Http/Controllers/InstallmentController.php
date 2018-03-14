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

    public function __construct(Request $request)
    {
        $head = $request->json('head');
        $content = $request->json('content');

        $header = $request->server->getHeaders();
        if (!empty($header['PROFILE'])) {
            $head['Credential']['ProfileId'] = $header['PROFILE'];
            $head['Credential']['Securitycode'] = $header['SECURITY'];
        }

        $controller = new BaseController();

        $this->_head = $controller->prepareHead($head);
        $this->_content = $controller->prepareContent($content);

        $this->_rb = new RatePAY\RequestBuilder(true);
    }

    /**
     * get configuration
     *
     * @param Request $request
     * @return mixed
     */
    public function getConfiguration(Request $request)
    {
        $header = $request->server->getHeaders();
        $mbHead = new RatePAY\ModelBuilder();

        $mbHead->setArray([
            'SystemId' => "Example",
            'Credential' => [
                    'ProfileId' => $header['PROFILE'],
                    'Securitycode' => $header['SECURITY']
            ]
        ]);

        $rb = new RatePAY\RequestBuilder(true);
        $configurationRequest = $rb->callProfileRequest($mbHead);
        return $configurationRequest->getResult();
    }

    /**
     * calculation by time
     *
     * @return mixed
     */
    public function callCalculationByTime()
    {
        $calculationRequest = $this->_rb->callCalculationRequest($this->_head, $this->_content)->subtype('calculation-by-time');
        return $calculationRequest->getResult();
    }

    /**
     * calculation by rate
     *
     * @return mixed
     */
    public function callCalculationByRate()
    {
        $calculationRequest = $this->_rb->callCalculationRequest($this->_head, $this->_content)->subtype('calculation-by-time');
        return $calculationRequest->getResult();
    }
}