<?php declare(strict_types = 1);

namespace Vantoozz\ProxyScraper\Scrapers;

use DateTimeImmutable;
use Vantoozz\ProxyScraper\Exceptions\HttpClientException;
use Vantoozz\ProxyScraper\Exceptions\InvalidArgumentException;
use Vantoozz\ProxyScraper\Exceptions\ScraperException;
use Vantoozz\ProxyScraper\HttpClient\HttpClientInterface;
use Vantoozz\ProxyScraper\Proxy;
use Vantoozz\ProxyScraper\ProxyString;

/**
 * Class CheckProxyScraper
 * @package Vantoozz\ProxyScraper\Scrapers
 */
final class CheckProxyScraper implements ScraperInterface
{
    private const URL = 'https://checkerproxy.net/api/archive/%s';
    private const ATTEMPTS = 3;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * FreeProxyListScraper constructor.
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return \Generator|Proxy[]
     * @throws \Vantoozz\ProxyScraper\Exceptions\ScraperException
     */
    public function get(): \Generator
    {
        $attempts = static::ATTEMPTS;
        $date = new DateTimeImmutable;

        $data = [];
        while ($attempts--) {
            $data = $this->getDailyData($date);
            if (count($data)) {
                break;
            }
            $date = $date->modify('-1 day');
        }

        foreach ($data as $item) {
            if (!array_key_exists('addr', $item)) {
                continue;
            }
            try {
                yield (new ProxyString($item['addr']))->asProxy();
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }
    }

    /**
     * @param \DateTimeInterface $date
     * @return array[]
     * @throws ScraperException
     */
    private function getDailyData(\DateTimeInterface $date): array
    {
        try {
            $json = $this->httpClient->get(sprintf(static::URL, $date->format('Y-m-d')));
        } catch (HttpClientException $e) {
            throw new ScraperException($e->getMessage(), $e->getCode(), $e);
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            $data = [];
        }

        $data = array_filter($data, function ($item) {
            return is_array($item);
        });

        return $data;
    }
}
