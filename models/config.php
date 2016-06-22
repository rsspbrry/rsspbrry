<?php

namespace Model\Config;

use Translator;
use DirectoryIterator;
use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoDb\Database;
use PicoFeed\Config\Config as ReaderConfig;
use PicoFeed\Logging\Logger;

const HTTP_USER_AGENT = 'RSSPBRRY (http://rsspbrry.com)';

// Get PicoFeed config
function get_reader_config()
{
    $config = new ReaderConfig;
    $config->setTimezone(get('timezone'));

    // Client
    $config->setClientTimeout(HTTP_TIMEOUT);
    $config->setClientUserAgent(HTTP_USER_AGENT);
    $config->setMaxBodySize(HTTP_MAX_RESPONSE_SIZE);

    // Grabber
    $config->setGrabberRulesFolder(RULES_DIRECTORY);

    // Proxy
    $config->setProxyHostname(PROXY_HOSTNAME);
    $config->setProxyPort(PROXY_PORT);
    $config->setProxyUsername(PROXY_USERNAME);
    $config->setProxyPassword(PROXY_PASSWORD);

    // Filter
    $config->setFilterIframeWhitelist(get_iframe_whitelist());

    if ((bool) get('debug_mode')) {
        Logger::enable();
    }

    // Parser
    $config->setParserHashAlgo('crc32b');

    return $config;
}

function get_iframe_whitelist()
{
    return array(
        'http://www.youtube.com',
        'https://www.youtube.com',
        'http://player.vimeo.com',
        'https://player.vimeo.com',
        'http://www.dailymotion.com',
        'https://www.dailymotion.com',
    );
}

// Send a debug message to the console
function debug($line)
{
    Logger::setMessage($line);
    write_debug();
}

// Write PicoFeed debug output to a file
function write_debug()
{
    if ((bool) get('debug_mode')) {
        file_put_contents(DEBUG_FILENAME, implode(PHP_EOL, Logger::getMessages()));
    }
}

// Get available timezone
function get_timezones()
{
    $timezones = timezone_identifiers_list();
    return array_combine(array_values($timezones), $timezones);
}

// Returns true if the language is RTL
function is_language_rtl()
{
    $languages = array(
        'ar_AR'
    );

    return in_array(get('language'), $languages);
}

// Get all supported languages
function get_languages()
{
    return array(
        'ar_AR' => 'عربي',
        'cs_CZ' => 'Čeština',
        'de_DE' => 'Deutsch',
        'en_US' => 'English',
        'es_ES' => 'Español',
        'fr_FR' => 'Français',
        'it_IT' => 'Italiano',
        'pt_BR' => 'Português',
        'zh_CN' => '简体中国',
        'sr_RS' => 'српски',
        'sr_RS@latin' => 'srpski',
        'ru_RU' => 'Русский',
        'tr_TR' => 'Türkçe',
    );
}

// Get all skins
function get_themes()
{
    $themes = array(
        'original' => t('Default')
    );

    if (file_exists(THEME_DIRECTORY)) {

        $dir = new DirectoryIterator(THEME_DIRECTORY);

        foreach ($dir as $fileinfo) {

            if (! $fileinfo->isDot() && $fileinfo->isDir()) {
                $themes[$dir->getFilename()] = ucfirst($dir->getFilename());
            }
        }
    }

    return $themes;
}

// Sorting direction choices for items
function get_sorting_directions()
{
    return array(
        'asc' => t('Older items first'),
        'desc' => t('Most recent first'),
    );
}

// Display summaries or full contents on lists
function get_display_mode()
{
	return array(
		'summaries' => t('Summaries'),
		'full' => t('Full contents')
	);
}

// Autoflush choices for read items
function get_autoflush_read_options()
{
    return array(
        '0' => t('Never'),
        '-1' => t('Immediately'),
        '1' => t('After %d day', 1),
        '5' => t('After %d day', 5),
        '15' => t('After %d day', 15),
        '30' => t('After %d day', 30)
    );
}

// Autoflush choices for unread items
function get_autoflush_unread_options()
{
    return array(
        '0' => t('Never'),
        '15' => t('After %d day', 15),
        '30' => t('After %d day', 30),
        '45' => t('After %d day', 45),
        '60' => t('After %d day', 60),
    );
}

// Number of items per pages
function get_paging_options()
{
    return array(
        10 => 10,
        20 => 20,
        30 => 30,
        50 => 50,
        100 => 100,
        150 => 150,
        200 => 200,
        250 => 250,
    );
}

// Get redirect options when there is nothing to read
function get_nothing_to_read_redirections()
{
    return array(
        'feeds' => t('Subscriptions'),
        'history' => t('History'),
        'bookmarks' => t('Bookmarks'),
    );
}

// Create a CSRF token
function generate_csrf()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = array();
    }

    $token = generate_token();
    $_SESSION['csrf'][$token] = true;

    return $token;
}

// Check CSRF token (form values)
function check_csrf_values(array &$values)
{
    if (empty($values['csrf']) || ! isset($_SESSION['csrf'][$values['csrf']])) {
        $values = array();
    }
    else {

        unset($_SESSION['csrf'][$values['csrf']]);
        unset($values['csrf']);
    }
}

// Check CSRF token
function check_csrf($token)
{
    if (isset($_SESSION['csrf'][$token])) {
        unset($_SESSION['csrf'][$token]);
        return true;
    }

    return false;
}

// Generate a token from /dev/urandom or with uniqid() if open_basedir is enabled
function generate_token()
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(30));
    }
    else if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes(30));
    }
    else if (ini_get('open_basedir') === '' && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        return hash('sha256', file_get_contents('/dev/urandom', false, null, 0, 30));
    }

    return hash('sha256', uniqid(mt_rand(), true));
}

// Regenerate tokens for the API and bookmark feed
function new_tokens()
{
    $values = array(
        'api_token' => generate_token(),
        'feed_token' => generate_token(),
        'bookmarklet_token' => generate_token(),
        'fever_token' => substr(generate_token(), 0, 8),
    );

    return Database::getInstance('db')->hashtable('settings')->put($values);
}

// Get a config value from the DB or from the session
function get($name)
{
    if (! isset($_SESSION)) {
        return current(Database::getInstance('db')->hashtable('settings')->get($name));
    }
    else {

        if (! isset($_SESSION['config'][$name])) {
            $_SESSION['config'] = get_all();
        }

        if (isset($_SESSION['config'][$name])) {
            return $_SESSION['config'][$name];
        }
    }

    return null;
}

// Get all config parameters
function get_all()
{
    $config = Database::getInstance('db')->hashtable('settings')->get();
    unset($config['password']);
    return $config;
}

// Validation for edit action
function validate_modification(array $values)
{
    $rules = array(
        new Validators\Required('username', t('The user name is required')),
        new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
        new Validators\Required('autoflush', t('Value required')),
        new Validators\Required('autoflush_unread', t('Value required')),
        new Validators\Required('items_per_page', t('Value required')),
        new Validators\Integer('items_per_page', t('Must be an integer')),
        new Validators\Required('theme', t('Value required')),
        new Validators\Integer('frontend_updatecheck_interval', t('Must be an integer')),
        new Validators\Integer('debug_mode', t('Must be an integer')),
        new Validators\Integer('nocontent', t('Must be an integer')),
        new Validators\Integer('favicons', t('Must be an integer')),
        new Validators\Integer('original_marks_read', t('Must be an integer')),
    );

    if (ENABLE_AUTO_UPDATE) {
        $rules[] = new Validators\Required('auto_update_url', t('Value required'));
    }

    if (! empty($values['password'])) {
        $rules[] = new Validators\Required('password', t('The password is required'));
        $rules[] = new Validators\MinLength('password', t('The minimum length is 6 characters'), 6);
        $rules[] = new Validators\Required('confirmation', t('The confirmation is required'));
        $rules[] = new Validators\Equals('password', 'confirmation', t('Passwords don\'t match'));
    }

    $v = new Validator($values, $rules);

    return array(
        $v->execute(),
        $v->getErrors()
    );
}

// Save config into the database and update the session
function save(array $values)
{
    // Update the password if needed
    if (! empty($values['password'])) {
        $values['password'] = password_hash($values['password'], PASSWORD_BCRYPT);
    } else {
        unset($values['password']);
    }

    unset($values['confirmation']);

    // If the user does not want content of feeds, remove it in previous ones
    if (isset($values['nocontent']) && (bool) $values['nocontent']) {
        Database::getInstance('db')->table('items')->update(array('content' => ''));
    }

    if (Database::getInstance('db')->hashtable('settings')->put($values)) {
        reload();
        return true;
    }

    return false;
}

// Reload the cache in session
function reload()
{
    $_SESSION['config'] = get_all();
    Translator\load(get('language'));
}

// Get the user agent of the connected user
function get_user_agent()
{
    return empty($_SERVER['HTTP_USER_AGENT']) ? t('Unknown') : $_SERVER['HTTP_USER_AGENT'];
}

// Get the real IP address of the connected user
function get_ip_address($only_public = false)
{
    $keys = array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );

    foreach ($keys as $key) {

        if (isset($_SERVER[$key])) {

            foreach (explode(',', $_SERVER[$key]) as $ip_address) {

                $ip_address = trim($ip_address);

                if ($only_public) {

                    // Return only public IP address
                    if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip_address;
                    }
                }
                else {

                    return $ip_address;
                }
            }
        }
    }

    return t('Unknown');
}
