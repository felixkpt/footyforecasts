<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    /**
     * Do a http request
     * @param mixed $request
     * @return mixed
     */
    static function request($request)
    {
        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', $request);

        $html = $browser->getResponse()->getContent();

        return $html;
    }
}
