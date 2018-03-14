<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use RatePAY;

class ProfileController extends BaseController
{
    /**
     * get ratepay profile
     *
     * @param Request $request
     * @return mixed
     */
    public function getProfile(Request $request)
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
        $profileRequest = $rb->callProfileRequest($mbHead);
        return $profileRequest->getResult();
    }
}
