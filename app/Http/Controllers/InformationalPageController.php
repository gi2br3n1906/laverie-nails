<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ShippingRateService;
use Illuminate\View\View;

class InformationalPageController extends Controller
{
    public function about(): View
    {
        return view('about');
    }

    public function faq(): View
    {
        return view('faq');
    }

    public function shippingPolicy(ShippingRateService $shippingRates): View
    {
        return view('shipping-policy', [
            'shippingRates' => $shippingRates->rates(),
        ]);
    }

    public function guidance(): View
    {
        return view('guidance', [
            'guidanceVideoUrl' => $this->youtubeEmbedUrl((string) Setting::get('guidance.video_url', '')),
        ]);
    }

    private function youtubeEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === 'youtu.be') {
            $videoId = trim((string) ($parts['path'] ?? ''), '/');
        } elseif ($host === 'youtube.com' || str_ends_with($host, '.youtube.com')) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $videoId = isset($query['v']) ? (string) $query['v'] : '';

            if ($videoId === '' && preg_match('#^/(?:embed|shorts)/([^/?]+)#', (string) ($parts['path'] ?? ''), $matches)) {
                $videoId = $matches[1];
            }
        } else {
            return null;
        }

        return preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) === 1
            ? 'https://www.youtube.com/embed/'.$videoId
            : null;
    }
}
