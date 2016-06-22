<?php

namespace Model\Item;

use Model\Service;
use Model\Config;
use Model\Group;
use PicoDb\Database;
use PicoFeed\Logging\Logger;
use PicoFeed\Scraper\Scraper;

// Get all items without filtering
function get_all()
{
    return Database::getInstance('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.language',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->orderBy('updated', 'desc')
        ->findAll();
}

// Get everthing since date (timestamp)
function get_all_since($timestamp)
{
    return Database::getInstance('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.language',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->gte('updated', $timestamp)
        ->orderBy('updated', 'desc')
        ->findAll();
}

function get_latest_feeds_items()
{
    return Database::getInstance('db')
        ->table('feeds')
        ->columns(
            'feeds.id',
            'MAX(items.updated) as updated',
            'items.status'
        )
        ->join('items', 'feed_id', 'id')
        ->groupBy('feeds.id')
        ->orderBy('feeds.id')
        ->findAll();
}

// Get a list of [item_id => status,...]
function get_all_status()
{
    return Database::getInstance('db')
        ->hashtable('items')
        ->in('status', array('read', 'unread'))
        ->orderBy('updated', 'desc')
        ->getAll('id', 'status');
}

// Get all items by status
function get_all_by_status($status, $feed_ids = array(), $offset = null, $limit = null, $order_column = 'updated', $order_direction = 'desc')
{
    return Database::getInstance('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.language',
            'items.author',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->eq('status', $status)
        ->in('feed_id', $feed_ids)
        ->orderBy($order_column, $order_direction)
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}

// Get the number of items per status
function count_by_status($status, $feed_ids = array())
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('status', $status)
        ->in('feed_id', $feed_ids)
        ->count();
}

// Get the number of bookmarks
function count_bookmarks()
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('bookmark', 1)
        ->in('status', array('read', 'unread'))
        ->count();
}

// Get all bookmarks
function get_bookmarks($offset = null, $limit = null)
{
    return Database::getInstance('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.bookmark',
            'items.status',
            'items.content',
            'items.feed_id',
            'items.language',
            'items.author',
            'feeds.site_url',
            'feeds.title AS feed_title',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('read', 'unread'))
        ->eq('bookmark', 1)
        ->orderBy('updated', Config\get('items_sorting_direction'))
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}

// Get the number of items per feed
function count_by_feed($feed_id)
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('feed_id', $feed_id)
        ->in('status', array('unread', 'read'))
        ->count();
}

// Get all items per feed
function get_all_by_feed($feed_id, $offset = null, $limit = null, $order_column = 'updated', $order_direction = 'desc')
{
    return Database::getInstance('db')
        ->table('items')
        ->columns(
            'items.id',
            'items.title',
            'items.updated',
            'items.url',
            'items.enclosure',
            'items.enclosure_type',
            'items.feed_id',
            'items.status',
            'items.content',
            'items.bookmark',
            'items.language',
            'items.author',
            'feeds.site_url',
            'feeds.rtl'
        )
        ->join('feeds', 'id', 'feed_id')
        ->in('status', array('unread', 'read'))
        ->eq('feed_id', $feed_id)
        ->orderBy($order_column, $order_direction)
        ->offset($offset)
        ->limit($limit)
        ->findAll();
}

// Get one item by id
function get($id)
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('id', $id)
        ->findOne();
}

// Get item naviguation (next/prev items)
function get_nav($item, $status = array('unread'), $bookmark = array(1, 0), $feed_id = null, $group_id = null)
{
    $query = Database::getInstance('db')
        ->table('items')
        ->columns('id', 'status', 'title', 'bookmark')
        ->neq('status', 'removed')
        ->orderBy('updated', Config\get('items_sorting_direction'));

    if ($feed_id) $query->eq('feed_id', $feed_id);

    if ($group_id) $query->in('feed_id', Group\get_feeds_by_group($group_id));

    $items = $query->findAll();

    $next_item = null;
    $previous_item = null;

    for ($i = 0, $ilen = count($items); $i < $ilen; $i++) {

        if ($items[$i]['id'] == $item['id']) {

            if ($i > 0) {

                $j = $i - 1;

                while ($j >= 0) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $previous_item = $items[$j];
                        break;
                    }

                    $j--;
                }
            }

            if ($i < ($ilen - 1)) {

                $j = $i + 1;

                while ($j < $ilen) {

                    if (in_array($items[$j]['status'], $status) && in_array($items[$j]['bookmark'], $bookmark)) {
                        $next_item = $items[$j];
                        break;
                    }

                    $j++;
                }
            }

            break;
        }
    }

    return array(
        'next' => $next_item,
        'previous' => $previous_item
    );
}

// Change item status to removed and clear content
function set_removed($id)
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'removed', 'content' => ''));
}

// Change item status to read
function set_read($id)
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'read'));
}

// Change item status to unread
function set_unread($id)
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('id', $id)
        ->save(array('status' => 'unread'));
}

// Change item status to "read", "unread" or "removed"
function set_status($status, array $items)
{
    if (! in_array($status, array('read', 'unread', 'removed'))) return false;

    return Database::getInstance('db')
        ->table('items')
        ->in('id', $items)
        ->save(array('status' => $status));
}

// Enable/disable bookmark flag
function set_bookmark_value($id, $value)
{
    if ($value == 1) {
        Service\push($id);
    }

    return Database::getInstance('db')
        ->table('items')
        ->eq('id', $id)
        ->in('status', array('read', 'unread'))
        ->save(array('bookmark' => $value));
}

// Mark all unread items as read
function mark_all_as_read()
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('status', 'unread')
        ->save(array('status' => 'read'));
}

// Mark all read items to removed
function mark_all_as_removed()
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('status', 'read')
        ->eq('bookmark', 0)
        ->save(array('status' => 'removed', 'content' => ''));
}

// Mark all items of a feed as read
function mark_feed_as_read($feed_id)
{
    return Database::getInstance('db')
        ->table('items')
        ->eq('status', 'unread')
        ->eq('feed_id', $feed_id)
        ->update(array('status' => 'read'));
}

// Mark all items of a group as read
function mark_group_as_read($group_id)
{
    // workaround for missing update with join
    $feed_ids = Group\get_feeds_by_group($group_id);

    return Database::getInstance('db')
        ->table('items')
        ->eq('status', 'unread')
        ->in('feed_id', $feed_ids)
        ->update(array('status' => 'read'));
}

// Mark all read items to removed after X days
function autoflush_read()
{
    $autoflush = (int) Config\get('autoflush');

    if ($autoflush > 0) {

        // Mark read items removed after X days
        Database::getInstance('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'read')
            ->lt('updated', strtotime('-'.$autoflush.'day'))
            ->save(array('status' => 'removed', 'content' => ''));
    }
    else if ($autoflush === -1) {

        // Mark read items removed immediately
        Database::getInstance('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'read')
            ->save(array('status' => 'removed', 'content' => ''));
    }
}

// Mark all unread items to removed after X days
function autoflush_unread()
{
    $autoflush = (int) Config\get('autoflush_unread');

    if ($autoflush > 0) {

        // Mark read items removed after X days
        Database::getInstance('db')
            ->table('items')
            ->eq('bookmark', 0)
            ->eq('status', 'unread')
            ->lt('updated', strtotime('-'.$autoflush.'day'))
            ->save(array('status' => 'removed', 'content' => ''));
    }
}

// Update all items
function update_all($feed_id, array $items)
{
    $nocontent = (bool) Config\get('nocontent');

    $items_in_feed = array();

    $db = Database::getInstance('db');
    $db->startTransaction();

    foreach ($items as $item) {

        Logger::setMessage('Item => '.$item->getId().' '.$item->getUrl());

        // Item parsed correctly?
        if ($item->getId() && $item->getUrl()) {

            Logger::setMessage('Item parsed correctly');

            // Get item record in database, if any
            $itemrec = $db
                ->table('items')
                ->columns('enclosure')
                ->eq('id', $item->getId())
                ->findOne();

            // Insert a new item
            if ($itemrec === null) {

                Logger::setMessage('Item added to the database');

                $db->table('items')->save(array(
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'url' => $item->getUrl(),
                    'updated' => $item->getDate()->getTimestamp(),
                    'author' => $item->getAuthor(),
                    'content' => $nocontent ? '' : $item->getContent(),
                    'status' => 'unread',
                    'feed_id' => $feed_id,
                    'enclosure' => $item->getEnclosureUrl(),
                    'enclosure_type' => $item->getEnclosureType(),
                    'language' => $item->getLanguage(),
                ));
            }
            else if (! $itemrec['enclosure'] && $item->getEnclosureUrl()) {

                Logger::setMessage('Update item enclosure');

                $db->table('items')->eq('id', $item->getId())->save(array(
                    'status' => 'unread',
                    'enclosure' => $item->getEnclosureUrl(),
                    'enclosure_type' => $item->getEnclosureType(),
                ));
            }
            else {
                Logger::setMessage('Item already in the database');
            }

            // Items inside this feed
            $items_in_feed[] = $item->id;
        }
    }

    // Cleanup old items
    cleanup($feed_id, $items_in_feed);

    $db->closeTransaction();
}

// Remove from the database items marked as "removed"
// and not present inside the feed
function cleanup($feed_id, array $items_in_feed)
{
    if (! empty($items_in_feed)) {

        $db = Database::getInstance('db');

        $removed_items = $db
            ->table('items')
            ->columns('id')
            ->notin('id', $items_in_feed)
            ->eq('status', 'removed')
            ->eq('feed_id', $feed_id)
            ->desc('updated')
            ->findAllByColumn('id');

        // Keep a buffer of 2 items
        // It's workaround for buggy feeds (cache issue with some Wordpress plugins)
        if (is_array($removed_items)) {

            $items_to_remove = array_slice($removed_items, 2);

            if (! empty($items_to_remove)) {

                $nb_items = count($items_to_remove);
                Logger::setMessage('There is '.$nb_items.' items to remove');

                // Handle the case when there is a huge number of items to remove
                // Sqlite have a limit of 1000 sql variables by default
                // Avoid the error message "too many SQL variables"
                // We remove old items by batch of 500 items
                $chunks = array_chunk($items_to_remove, 500);

                foreach ($chunks as $chunk) {

                    $db->table('items')
                        ->in('id', $chunk)
                        ->eq('status', 'removed')
                        ->eq('feed_id', $feed_id)
                        ->remove();
                }
            }
        }
    }
}

// Download content from an URL
function download_content_url($url)
{
    $content = '';

    $grabber = new Scraper(Config\get_reader_config());
    $grabber->setUrl($url);
    $grabber->execute();

    if ($grabber->hasRelevantContent()) {
        $content = $grabber->getFilteredContent();
    }

    return $content;
}

// Download content from item ID
function download_content_id($item_id)
{
    $item = get($item_id);
    $content = download_content_url($item['url']);

    if (! empty($content)) {

        if (! Config\get('nocontent')) {

            // Save content
            Database::getInstance('db')
                ->table('items')
                ->eq('id', $item['id'])
                ->save(array('content' => $content));
        }

        Config\write_debug();

        return array(
            'result' => true,
            'content' => $content
        );
    }

    Config\write_debug();

    return array(
        'result' => false,
        'content' => ''
    );
}
