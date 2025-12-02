<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    /**
     * Handle the incoming request.
     * @throws ConnectionException
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->query('url');

        $response = Http::sink(null)->get($url);

        return response($response->body())
            ->header('Content-Type', $response->header('Content-Type'));
    }
}
