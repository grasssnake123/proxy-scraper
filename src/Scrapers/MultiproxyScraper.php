<?php declare(strict_types = 1);

namespace Vantoozz\ProxyScrapper\Scrapers;

/**
 * Class MultiproxyScraper
 * @package Vantoozz\ProxyScrapper\Scrapers
 */
final class MultiproxyScraper extends RemoteTextScraper
{
    /**
     * @return string
     */
    protected function remoteTextUrl(): string
    {
        return 'http://multiproxy.org/txt_all/proxy.txt';
    }
}