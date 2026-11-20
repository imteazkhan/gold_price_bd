<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use App\Models\Visitor;
use App\Support\UserAgentParser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every public page view: one row per unique browser in `visitors`
 * (identified by a 1-year cookie) and one row per page view in `visits`.
 */
class TrackVisit
{
    public const COOKIE = 'gp_vid';

    public function handle(Request $request, Closure $next): Response
    {
        $visitor = null;

        if ($this->shouldTrack($request)) {
            try {
                $visitor = $this->record($request);
            } catch (\Throwable $e) {
                Log::warning('Visit tracking failed: '.$e->getMessage());
            }
        }

        $request->attributes->set('visitor', $visitor);

        $response = $next($request);

        if ($visitor && $request->cookie(self::COOKIE) !== $visitor->visitor_key) {
            $response->headers->setCookie(Cookie::make(self::COOKIE, $visitor->visitor_key, 60 * 24 * 365));
        }

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        return $request->isMethod('GET') && ! $request->ajax() && ! $request->expectsJson();
    }

    private function record(Request $request): Visitor
    {
        $key = $request->cookie(self::COOKIE);
        $visitor = ($key && Str::isUuid($key)) ? Visitor::where('visitor_key', $key)->first() : null;
        $ua = substr((string) $request->userAgent(), 0, 1000);
        $parsed = UserAgentParser::parse($ua);
        $now = now();
        $referrer = $this->externalReferrer($request);

        if (! $visitor) {
            $visitor = Visitor::create([
                'visitor_key' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => $ua,
                'browser' => $parsed['browser'],
                'platform' => $parsed['platform'],
                'device_type' => $parsed['device_type'],
                'is_bot' => $parsed['is_bot'],
                'language' => substr((string) $request->getPreferredLanguage(), 0, 20) ?: null,
                'first_referrer' => $referrer,
                'first_landing' => substr($request->getRequestUri(), 0, 500),
                'total_visits' => 0,
                'first_visit_at' => $now,
                'last_visit_at' => $now,
            ]);
        }

        $visitor->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => $ua,
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'device_type' => $parsed['device_type'],
            'is_bot' => $parsed['is_bot'],
            'last_visit_at' => $now,
            'total_visits' => $visitor->total_visits + 1,
        ])->save();

        Visit::create([
            'visitor_id' => $visitor->id,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'method' => $request->method(),
            'path' => substr('/'.ltrim($request->path(), '/'), 0, 500),
            'full_url' => substr($request->fullUrl(), 0, 1000),
            'referrer' => $referrer,
            'ip_address' => $request->ip(),
            'user_agent' => $ua,
            'device_type' => $parsed['device_type'],
            'visited_at' => $now,
        ]);

        return $visitor;
    }

    private function externalReferrer(Request $request): ?string
    {
        $ref = $request->headers->get('referer');
        if (! $ref) {
            return null;
        }
        $host = parse_url($ref, PHP_URL_HOST);

        return $host && $host === $request->getHost() ? null : substr($ref, 0, 500);
    }
}
