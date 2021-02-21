<?php

namespace AutozNetwork\Tests\Authentication;

use AutozNetwork\AutozNetworkClient;
use AutozNetwork\AutozNetworkRequest;
use AutozNetwork\AutozNetworkResponse;

class FooAutozNetworkClientForOAuth2Test extends AutozNetworkClient
{
    protected $response = '';

    public function setMetadataResponse()
    {
        $this->response = '{"data":{"user_id":"444"}}';
    }

    public function setAccessTokenResponse()
    {
        $this->response = '{"access_token":"my_access_token","expires":"1422115200"}';
    }

    public function setCodeResponse()
    {
        $this->response = '{"code":"my_neat_code"}';
    }

    public function sendRequest(AutozNetworkRequest $request)
    {
        return new AutozNetworkResponse(
            $request,
            $this->response,
            200,
            []
        );
    }
}
