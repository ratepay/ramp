<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as Controller;
use RatePAY;

class ProfileController extends Controller
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
            'SystemId' => $header['SYSTEM_ID'],
            'Credential' => [
                'ProfileId' => $header['PROFILE_ID'],
                'Securitycode' => $header['SECURITY_CODE']
            ]
        ]);

        $rb = new RatePAY\RequestBuilder($header['SANDBOX']);
        $profileRequest = $rb->callProfileRequest($mbHead);

        if ($profileRequest->isSuccessful()) {
            return $profileRequest->getResult();
        } else {
            return "ProfileRequest not successful";
        }

    }
}
