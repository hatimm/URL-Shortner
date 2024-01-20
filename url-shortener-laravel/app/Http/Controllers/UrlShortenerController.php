<?php

namespace App\Http\Controllers;

use App\Models\ShortenedUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UrlShortenerController extends Controller
{
    public function index()
    {
        $shortenedUrls = ShortenedUrl::orderBy('click_count', 'desc')->get();
        return response()->json(['shortenedUrls' => $shortenedUrls]);
    }

    public function shorten(Request $request)
    {
        $request->validate([
            'longUrl' => 'required|url',
        ]);

        $longUrl = $request->input('longUrl');
        $shortUrl = $this->generateShortUrl();

        $shortenedUrl = ShortenedUrl::create([
            'long_url' => $longUrl,
            'short_url' => $shortUrl,
            'click_count' => 0,
        ]);

        return response()->json($shortenedUrl, 201);
    }

    private function generateShortUrl()
    {
        return Str::random(8);
    }

    public function redirect($shortUrl)
    {
        $url = ShortenedUrl::where('short_url', $shortUrl)->first();

        if ($url) {
            $url->increment('click_count');

            return redirect()->away($url->long_url);
        } else {
            abort(404, 'Shortened URL not found');
        }
    }


}
