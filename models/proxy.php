<?php

namespace Model\Proxy;

use Helper;
use Model\Config;
use PicoFeed\Config\Config as PicoFeedConfig;
use PicoFeed\Filter\Filter;
use PicoFeed\Client\Client;
use PicoFeed\Logging\Logger;

function rewrite_link($link)
{
    if (Helper\is_secure_connection() && strpos($link, 'http:') === 0) {
        $link = '?action=proxy&url='.rawurlencode($link);
    }

    return $link;
}

function rewrite_html($html, $website, $proxy_images, $cloak_referrer)
{
    if ($html === '' // no content, no proxy
        || (! $cloak_referrer && ! $proxy_images) // neither cloaking nor proxing enabled
        || (! $cloak_referrer && $proxy_images && ! Helper\is_secure_connection())) { // only proxy enabled, but not connected via HTTPS

        return $html;
    }

    $config = new PicoFeedConfig();
    $config->setFilterImageProxyUrl('?action=proxy&url=%s');

    if (! $cloak_referrer && $proxy_images) {
        // image proxy mode only: https links do not need to be proxied, since
        // they do not trigger mixed content warnings.
        $config->setFilterImageProxyProtocol('http');
    }
    elseif (! $proxy_images && $cloak_referrer && Helper\is_secure_connection()) {
        // cloaking mode only: if a request from a HTTPS connection to a HTTP
        // connection is made, the referrer will be omitted by the browser.
        // Only the referrer for HTTPS to HTTPs requests needs to be cloaked.
        $config->setFilterImageProxyProtocol('https');
    }

    $filter = Filter::html($html, $website);
    $filter->setConfig($config);

    return $filter->execute();
}

function download($url)
{
    try {

        if ((bool) Config\get('debug_mode')) {
            Logger::enable();
        }

        $client = Client::getInstance();
        $client->setUserAgent(Config\HTTP_USER_AGENT);
        $client->enablePassthroughMode();
        $client->execute($url);
    }
    catch (\PicoFeed\Client\ClientException $e) {}

    Config\write_debug();
}
