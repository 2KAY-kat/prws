<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class RuleEngine
{
    protected Client $client;
    protected string $baseUrl;
    protected ?string $homepageHtml = null;
    protected ?Crawler $crawler = null;

    public function __construct(string $url)
    {
        $this->baseUrl = rtrim($url, '/');
        $this->client = new Client([
            'timeout' => 8,
            'http_errors' => false,
            'headers' => ['User-Agent' => 'PRWS-Auditor/1.0'],
        ]);
    }

    public function run(): array
    {
        $this->fetchHomepage();

        $results = [];
        foreach (config('prws_rules') as $rule) {
            $method = 'check' . str_replace('-', '', $rule['rule_id']);
            $passed = method_exists($this, $method) ? $this->$method() : false;

            $results[] = array_merge($rule, [
                'passed' => $passed,
                'points_earned' => $passed ? $rule['points'] : 0,
            ]);
        }

        return $results;
    }

    protected function fetchHomepage(): void
    {
        try {
            $response = $this->client->get($this->baseUrl);
            $this->homepageHtml = (string) $response->getBody();
            $this->crawler = new Crawler($this->homepageHtml);
        } catch (GuzzleException $e) {
            \Log::error('PRWS fetch failed: ' . $e->getMessage());
            $this->homepageHtml = '';
        }
    }

    protected function pathExists(string $path): bool
    {
        try {
            $response = $this->client->get($this->baseUrl . $path);
            return $response->getStatusCode() < 400;
        } catch (GuzzleException) {
            return false;
        }
    }

    protected function linkTextContains(array $needles): bool
    {
        if (!$this->crawler) return false;

        foreach ($this->crawler->filter('a')->extract(['href', '_text']) as [$href, $text]) {
            foreach ($needles as $needle) {
                if (str_contains(strtolower($href), $needle) || str_contains(strtolower($text), $needle)) {
                    return true;
                }
            }
        }
        return false;
    }

    // --- LEG-001: Privacy Policy ---
    protected function checkLEG001(): bool
    {
        return $this->pathExists('/privacy')
            || $this->pathExists('/privacy-policy')
            || $this->linkTextContains(['privacy']);
    }

    // --- LEG-002: Terms of Service ---
    protected function checkLEG002(): bool
    {
        return $this->pathExists('/terms')
            || $this->pathExists('/terms-of-service')
            || $this->linkTextContains(['terms']);
    }

    // --- LEG-004: Copyright in footer ---
    protected function checkLEG004(): bool
    {
        if (!$this->crawler) return false;
        $footer = $this->crawler->filter('footer')->count()
            ? $this->crawler->filter('footer')->text('')
            : $this->homepageHtml;

        return (bool) preg_match('/©|copyright|\(c\)\s?\d{4}|all rights reserved|creative commons|licen[cs]e/i', $footer);
    }

    // --- CON-001: Contact page ---
    protected function checkCON001(): bool
    {
        return $this->pathExists('/contact') || $this->linkTextContains(['contact']);
    }

    // --- CON-002: Support email ---
    protected function checkCON002(): bool
    {
        // Check homepage first: mailto link or plain-text email pattern
        if ($this->hasEmailSignal($this->homepageHtml)) {
            return true;
        }

        // Fallback: check the contact page
        try {
            $response = $this->client->get($this->baseUrl . '/contact');
            if ($response->getStatusCode() < 400) {
                return $this->hasEmailSignal((string) $response->getBody());
            }
        } catch (GuzzleException) {
            // ignore, fall through to false
        }

        return false;
    }

    protected function hasEmailSignal(string $html): bool
    {
        if ($html === '') return false;

        $crawler = new Crawler($html);

        // 1. mailto link
        if ($crawler->filter('a[href^="mailto:"]')->count() > 0) {
            return true;
        }

        // 2. plain-text email address anywhere in the visible page
        return (bool) preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $html);
    }

    // --- REL-001: Custom 404 ---
    protected function checkREL001(): bool
    {
        try {
            $response = $this->client->get($this->baseUrl . '/prws-nonexistent-' . uniqid());
            if ($response->getStatusCode() !== 404) return false;
            return strlen((string) $response->getBody()) > 200;
        } catch (GuzzleException) {
            return false;
        }
    }

    // --- REL-004: Favicon ---
    protected function checkREL004(): bool
    {
        if ($this->crawler && $this->crawler->filter('link[rel*="icon"]')->count() > 0) {
            return true;
        }
        return $this->pathExists('/favicon.ico');
    }

    // --- SEO-001: robots.txt ---
    protected function checkSEO001(): bool
    {
        return $this->pathExists('/robots.txt');
    }

    // --- SEO-002: sitemap.xml ---
    protected function checkSEO002(): bool
    {
        return $this->pathExists('/sitemap.xml');
    }

    // --- SEO-003: Title tag ---
    protected function checkSEO003(): bool
    {
        if (!$this->crawler) return false;
        return $this->crawler->filter('title')->count() > 0
            && trim($this->crawler->filter('title')->text('')) !== '';
    }

    // --- SEO-004: Meta description ---
    protected function checkSEO004(): bool
    {
        if (!$this->crawler) return false;
        $meta = $this->crawler->filter('meta[name="description"]');
        return $meta->count() > 0 && trim($meta->attr('content') ?? '') !== '';
    }

    // --- ACC-003: Semantic HTML ---
    protected function checkACC003(): bool
    {
        if (!$this->crawler) return false;
        foreach (['header', 'main', 'footer', 'nav'] as $tag) {
            if ($this->crawler->filter($tag)->count() === 0) return false;
        }
        return true;
    }

    // --- ACC-004: Language declaration ---
    protected function checkACC004(): bool
    {
        if (!$this->crawler) return false;
        $html = $this->crawler->filter('html');
        return $html->count() > 0 && trim($html->attr('lang') ?? '') !== '';
    }
}
