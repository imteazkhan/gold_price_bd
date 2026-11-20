<?php

namespace App\Support;

/**
 * Lightweight user-agent parser (no external package needed).
 */
class UserAgentParser
{
    public static function parse(?string $ua): array
    {
        $ua = (string) $ua;
        $isBot = (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|bingpreview|mediapartners|headless|lighthouse|curl|wget|python-requests|httpclient|axios|postman/i', $ua);

        return [
            'browser' => self::browser($ua),
            'platform' => self::platform($ua),
            'device_type' => $isBot ? 'bot' : self::device($ua),
            'is_bot' => $isBot,
        ];
    }

    private static function browser(string $ua): string
    {
        return match (true) {
            $ua === '' => 'Unknown',
            (bool) preg_match('/Edg(e|A|iOS)?\//i', $ua) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/i', $ua) => 'Opera',
            (bool) preg_match('/SamsungBrowser/i', $ua) => 'Samsung Internet',
            (bool) preg_match('/UCBrowser/i', $ua) => 'UC Browser',
            (bool) preg_match('/Firefox|FxiOS/i', $ua) => 'Firefox',
            (bool) preg_match('/Chrome|CriOS/i', $ua) => 'Chrome',
            (bool) preg_match('/Safari/i', $ua) => 'Safari',
            (bool) preg_match('/MSIE|Trident/i', $ua) => 'Internet Explorer',
            default => 'Other',
        };
    }

    private static function platform(string $ua): string
    {
        return match (true) {
            $ua === '' => 'Unknown',
            (bool) preg_match('/Android/i', $ua) => 'Android',
            (bool) preg_match('/iPhone|iPad|iPod/i', $ua) => 'iOS',
            (bool) preg_match('/Windows/i', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/i', $ua) => 'macOS',
            (bool) preg_match('/CrOS/i', $ua) => 'Chrome OS',
            (bool) preg_match('/Linux/i', $ua) => 'Linux',
            default => 'Other',
        };
    }

    private static function device(string $ua): string
    {
        return match (true) {
            (bool) preg_match('/iPad|Tablet|(Android(?!.*Mobile))/i', $ua) => 'tablet',
            (bool) preg_match('/Mobile|iPhone|iPod|Android|Opera Mini|IEMobile/i', $ua) => 'mobile',
            default => 'desktop',
        };
    }
}
