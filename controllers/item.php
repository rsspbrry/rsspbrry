<?php

// Display unread items
Router\get_action('unread', function() {

    Model\Item\autoflush_read();
    Model\Item\autoflush_unread();

    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\Config\get('items_sorting_direction'));
    $offset = Request\int_param('offset', 0);
    $group_id = Request\int_param('group_id', null);
    $feed_ids = array();

    if (! is_null($group_id)) {
        $feed_ids = Model\Group\get_feeds_by_group($group_id);
    }

    $items = Model\Item\get_all_by_status(
        'unread',
        $feed_ids,
        $offset,
        Model\Config\get('items_per_page'),
        $order,
        $direction
    );

    $nb_items = Model\Item\count_by_status('unread', $feed_ids);
    $nb_unread_items = Model\Item\count_by_status('unread');

    if ($nb_unread_items === 0) {
        $action = Model\Config\get('redirect_nothing_to_read');
        Response\redirect('?action='.$action.'&nothing_to_read=1');
    }

    Response\html(Template\layout('unread_items', array(
        'favicons' => Model\Favicon\get_item_favicons($items),
        'original_marks_read' => Model\Config\get('original_marks_read'),
        'order' => $order,
        'direction' => $direction,
        'display_mode' => Model\Config\get('items_display_mode'),
        'group_id' => $group_id,
        'items' => $items,
        'nb_items' => $nb_items,
        'nb_unread_items' => $nb_unread_items,
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'title' => 'RSSPBRRY ('.$nb_items.')',
        'menu' => 'unread',
        'groups' => Model\Group\get_all()
    )));
});

// Show item
Router\get_action('show', function() {

    $id = Request\param('id');
    $menu = Request\param('menu');
    $item = Model\Item\get($id);
    $feed = Model\Feed\get($item['feed_id']);
    $group_id = Request\int_param('group_id', null);

    Model\Item\set_read($id);
    $item['status'] = 'read';

    switch ($menu) {
        case 'unread':
            $nav = Model\Item\get_nav($item, array('unread'), array(1, 0), null, $group_id);
            break;
        case 'history':
            $nav = Model\Item\get_nav($item, array('read'));
            break;
        case 'feed-items':
            $nav = Model\Item\get_nav($item, array('unread', 'read'), array(1, 0), $item['feed_id']);
            break;
        case 'bookmarks':
            $nav = Model\Item\get_nav($item, array('unread', 'read'), array(1));
            break;
    }

    $image_proxy = (bool) Model\Config\get('image_proxy');

    // add the image proxy if requested and required
    $item['content'] = Model\Proxy\rewrite_html($item['content'], $item['url'], $image_proxy, $feed['cloak_referrer']);

    if ($image_proxy && strpos($item['enclosure_type'], 'image') === 0) {
        $item['enclosure'] = Model\Proxy\rewrite_link($item['enclosure']);
    }

    Response\html(Template\layout('show_item', array(
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'item' => $item,
        'feed' => $feed,
        'item_nav' => isset($nav) ? $nav : null,
        'menu' => $menu,
        'title' => $item['title'],
        'group_id' => $group_id,
    )));
});

// Display feed items page
Router\get_action('feed-items', function() {

    $feed_id = Request\int_param('feed_id', 0);
    $offset = Request\int_param('offset', 0);
    $nb_items = Model\Item\count_by_feed($feed_id);
    $feed = Model\Feed\get($feed_id);
    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', Model\Config\get('items_sorting_direction'));
    $items = Model\Item\get_all_by_feed($feed_id, $offset, Model\Config\get('items_per_page'), $order, $direction);

    Response\html(Template\layout('feed_items', array(
        'favicons' => Model\Favicon\get_favicons(array($feed['id'])),
        'original_marks_read' => Model\Config\get('original_marks_read'),
        'order' => $order,
        'direction' => $direction,
        'display_mode' => Model\Config\get('items_display_mode'),
        'feed' => $feed,
        'items' => $items,
        'nb_items' => $nb_items,
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'menu' => 'feed-items',
        'title' => '('.$nb_items.') '.$feed['title']
    )));
});

// Ajax call to download an item (fetch the full content from the original website)
Router\post_action('download-item', function() {
    $id = Request\param('id');

    $item = Model\Item\get($id);
    $feed = Model\Feed\get($item['feed_id']);

    $download = Model\Item\download_content_id($id);
    $download['content'] = Model\Proxy\rewrite_html($download['content'], $item['url'], Model\Config\get('image_proxy'), $feed['cloak_referrer']);

    Response\json($download);
});

// Ajax call to mark item read
Router\post_action('mark-item-read', function() {

    Model\Item\set_read(Request\param('id'));
    Response\json(array('Ok'));
});

// Ajax call to mark item as removed
Router\post_action('mark-item-removed', function() {

    Model\Item\set_removed(Request\param('id'));
    Response\json(array('Ok'));
});

// Ajax call to mark item unread
Router\post_action('mark-item-unread', function() {

    Model\Item\set_unread(Request\param('id'));
    Response\json(array('Ok'));
});

// Mark unread items as read
Router\get_action('mark-all-read', function() {

    $group_id = Request\int_param('group_id', null);

    if (!is_null($group_id)) {
        Model\Item\mark_group_as_read($group_id);
    }
    else {
        Model\Item\mark_all_as_read();
    }

    Response\redirect('?action=unread');
});

// Mark all unread items as read for a specific feed
Router\get_action('mark-feed-as-read', function() {

    $feed_id = Request\int_param('feed_id');

    Model\Item\mark_feed_as_read($feed_id);

    Response\redirect('?action=feed-items&feed_id='.$feed_id);
});

// Mark all unread items as read for a specific feed (Ajax request) and return
// the number of unread items. It's not possible to get the number of items
// that where marked read from the frontend, since the number of unread items
// on page 2+ is unknown.
Router\post_action('mark-feed-as-read', function() {

    Model\Item\mark_feed_as_read(Request\int_param('feed_id'));
    $nb_items = Model\Item\count_by_status('unread');

    Response\raw($nb_items);
});

// Mark item as read and redirect to the listing page
Router\get_action('mark-item-read', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'unread');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_read($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});

// Mark item as unread and redirect to the listing page
Router\get_action('mark-item-unread', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_unread($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id.'#item-'.$id);
});

// Mark item as removed and redirect to the listing page
Router\get_action('mark-item-removed', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);
    $feed_id = Request\int_param('feed_id', 0);

    Model\Item\set_removed($id);

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'&feed_id='.$feed_id);
});

Router\post_action('latest-feeds-items', function() {
    $items = Model\Item\get_latest_feeds_items();
    $nb_unread_items = Model\Item\count_by_status('unread');

    $feeds = array_reduce($items, function ($result, $item) {
        $result[$item['id']] = array(
            'time' => $item['updated'] ?: 0,
            'status' => $item['status']
        );
        return $result;
    }, array());

    Response\json(array(
        'feeds' => $feeds,
        'nbUnread' => $nb_unread_items
    ));
});
