<?php

namespace Model\Favicon;

use Model\Config;
use Model\Group;
use Helper;
use PicoDb\Database;
use PicoFeed\Reader\Favicon;

// Create a favicons
function create_feed_favicon($feed_id, $site_url, $icon_link) {
    if (has_favicon($feed_id)) {
        return true;
    }

    $favicon = fetch($feed_id, $site_url, $icon_link);

    if ($favicon === false) {
        return false;
    }

    $favicon_id = store($favicon->getType(), $favicon->getContent());

    if ($favicon_id === false) {
        return false;
    }

    return Database::getInstance('db')
            ->table('favicons_feeds')
            ->save(array(
                'feed_id' => $feed_id,
                'favicon_id' => $favicon_id
            ));
}

// Download a favicon
function fetch($feed_id, $site_url, $icon_link)
{
    if (Config\get('favicons') == 1 && ! has_favicon($feed_id)) {
        $favicon = new Favicon;
        $favicon->find($site_url, $icon_link);
        return $favicon;
    }

    return false;
}

// Store the favicon (only if it does not exist yet)
function store($type, $icon)
{
    if ($icon === '') {
        return false;
    }

    $hash = sha1($icon);

    $favicon_id = get_favicon_id($hash);

    if ($favicon_id) {
        return $favicon_id;
    }

    $file = $hash.Helper\favicon_extension($type);

    if (file_put_contents(FAVICON_DIRECTORY.DIRECTORY_SEPARATOR.$file, $icon) === false) {
        return false;
    }

    $saved = Database::getInstance('db')
            ->table('favicons')
            ->save(array(
                'hash' => $hash,
                'type' => $type
            ));

    if ($saved === false) {
        return false;
    }

    return get_favicon_id($hash);
}

function get_favicon_id($hash) {
    return Database::getInstance('db')
          ->table('favicons')
          ->eq('hash', $hash)
          ->findOneColumn('id');
}

// Delete the favicon
function delete_favicon($favicon)
{
    unlink(FAVICON_DIRECTORY.DIRECTORY_SEPARATOR.$favicon['hash'].Helper\favicon_extension($favicon['type']));
    Database::getInstance('db')
        ->table('favicons')
        ->eq('hash', $favicon['hash'])
        ->remove();
}

// Purge orphaned favicons from database
function purge_favicons()
{
    $favicons = Database::getInstance('db')
                ->table('favicons')
                ->columns(
                    'favicons.type',
                    'favicons.hash',
                    'favicons_feeds.feed_id'
                )
                ->join('favicons_feeds', 'favicon_id', 'id')
                ->isNull('favicons_feeds.feed_id')
                ->findAll();

      foreach ($favicons as $favicon) {
         delete_favicon($favicon);
      }
}

// Return true if the feed has a favicon
function has_favicon($feed_id)
{
    return Database::getInstance('db')->table('favicons_feeds')->eq('feed_id', $feed_id)->count() === 1;
}

// Get favicons for those feeds
function get_favicons(array $feed_ids)
{
    if (Config\get('favicons') == 0) {
        return array();
    }

    $result = array();

    foreach ($feed_ids as $feed_id) {
        $result[$feed_id] = Database::getInstance('db')
              ->table('favicons')
              ->columns(
                  'favicons.type',
                  'favicons.hash'
              )
              ->join('favicons_feeds', 'favicon_id', 'id')
              ->eq('favicons_feeds.feed_id', $feed_id)
              ->findOne();
    }

    return $result;
}

// Get all favicons for a list of items
function get_item_favicons(array $items)
{
    $feed_ids = array();

    foreach ($items as $item) {
        $feed_ids[$item['feed_id']] = $item['feed_id'];
    }

    return get_favicons($feed_ids);
}

// Get all favicons
function get_all_favicons()
{
    if (Config\get('favicons') == 0) {
        return array();
    }

    $result = Database::getInstance('db')
            ->table('favicons')
            ->columns(
                'favicons_feeds.feed_id',
                'favicons.type',
                'favicons.hash'
            )
            ->join('favicons_feeds', 'favicon_id', 'id')
            ->findAll();

    $map = array();

    foreach ($result as $row) {
      $map[$row['feed_id']] = array(
        "type" => $row['type'],
        "hash" => $row['hash']
      );
    }

    return $map;
}
