<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    static function do($request)
    {
        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', $request);

        return $browser->getResponse();
    }

    /**
     * Do a http request
     * @param mixed $request
     * @return mixed
     */
    static function request($request)
    {
        return self::do($request)->getContent();
    }

    /**
     * Do a http request
     * @param mixed $request
     * @return mixed
     */
    static function status($request)
    {
        return self::do($request)->getStatusCode();
    }

    static function downloadFileFromUrl($url, $destinationPath)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();

            File::ensureDirectoryExists(Str::beforeLast(storage_path($destinationPath), '/'));
            Storage::put($destinationPath, $content);
            Storage::setVisibility($destinationPath, 'public');
            Storage::disk('public')->setVisibility($destinationPath, 'public');

            return true; // File downloaded successfully
        } else {
            return false; // Failed to download file
        }
    }
}
