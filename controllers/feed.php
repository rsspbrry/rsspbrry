<?php

// Refresh all feeds, used when Javascript is disabled
Router\get_action('refresh-all', function() {

    Model\Feed\refresh_all();
    Session\flash(t('Your subscriptions are updated'));
    Response\redirect('?action=unread');
});

// Edit feed form
Router\get_action('edit-feed', function() {

    $id = Request\int_param('feed_id');

    $values = Model\Feed\get($id);
    $values += array(
        'feed_group_ids' => Model\Group\get_feed_group_ids($id)
    );

    Response\html(Template\layout('edit_feed', array(
        'values' => $values,
        'errors' => array(),
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'groups' => Model\Group\get_all(),
        'menu' => 'feeds',
        'title' => t('Edit subscription')
    )));
});

// Submit edit feed form
Router\post_action('edit-feed', function() {

    $values = Request\values();
    $values += array(
        'enabled' => 0,
        'download_content' => 0,
        'rtl' => 0,
        'cloak_referrer' => 0,
        'feed_group_ids' => array(),
        'create_group' => ''
    );

    list($valid, $errors) = Model\Feed\validate_modification($values);

    if ($valid) {
        if (Model\Feed\update($values)) {
            Session\flash(t('Your subscription has been updated.'));
            Response\redirect('?action=feeds');
        }
        else {
            Session\flash_error(t('Unable to edit your subscription.'));
        }
    }

    Response\html(Template\layout('edit_feed', array(
        'values' => $values,
        'errors' => $errors,
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'groups' => Model\Group\get_all(),
        'menu' => 'feeds',
        'title' => t('Edit subscription')
    )));
});

// Confirmation box to remove a feed
Router\get_action('confirm-remove-feed', function() {

    $id = Request\int_param('feed_id');

    Response\html(Template\layout('confirm_remove_feed', array(
        'feed' => Model\Feed\get($id),
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'menu' => 'feeds',
        'title' => t('Confirmation')
    )));
});

// Remove a feed
Router\get_action('remove-feed', function() {

    $id = Request\int_param('feed_id');

    if ($id && Model\Feed\remove($id)) {
        Session\flash(t('This subscription has been removed successfully.'));
    }
    else {
        Session\flash_error(t('Unable to remove this subscription.'));
    }

    Response\redirect('?action=feeds');
});

// Refresh one feed and redirect to unread items
Router\get_action('refresh-feed', function() {

    $feed_id = Request\int_param('feed_id');
    $redirect = Request\param('redirect', 'unread');

    Model\Feed\refresh($feed_id);
    Response\redirect('?action='.$redirect.'&feed_id='.$feed_id);
});

// Ajax call to refresh one feed
Router\post_action('refresh-feed', function() {

    $feed_id = Request\int_param('feed_id', 0);

    Response\json(array(
        'feed_id' => $feed_id,
        'result' => Model\Feed\refresh($feed_id),
        'items_count' => Model\Feed\count_items($feed_id),
    ));
});

// Display all feeds
Router\get_action('feeds', function() {
    $nothing_to_read = Request\int_param('nothing_to_read');
    $nb_unread_items = Model\Item\count_by_status('unread');

    // possible with remember me function
    if ($nothing_to_read === 1 && $nb_unread_items > 0) {
        Response\redirect('?action=unread');
    }

    Response\html(Template\layout('feeds', array(
        'favicons' => Model\Favicon\get_all_favicons(),
        'feeds' => Model\Feed\get_all_item_counts(),
        'nothing_to_read' => $nothing_to_read,
        'nb_unread_items' => $nb_unread_items,
        'nb_failed_feeds' => Model\Feed\count_failed_feeds(),
        'menu' => 'feeds',
        'title' => t('Subscriptions')
    )));
});

// Display form to add one feed
Router\get_action('add', function() {

    $values = array(
        'download_content' => 0,
        'rtl' => 0,
        'cloak_referrer' => 0,
        'create_group' => '',
        'feed_group_ids' => array()
    );

    Response\html(Template\layout('add', array(
        'values' => $values + array('csrf' => Model\Config\generate_csrf()),
        'errors' => array(),
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'groups' => Model\Group\get_all(),
        'menu' => 'feeds',
        'title' => t('New subscription')
    )));
});

// Add a feed with the form or directly from the url, it can be used by a bookmarklet by example
Router\action('subscribe', function() {

    if (Request\is_post()) {
        $values = Request\values();
        Model\Config\check_csrf_values($values);
        $url = isset($values['url']) ? $values['url'] : '';
    }
    else {
        $values = array();
        $url = Request\param('url');
        $token = Request\param('token');

        if ($token !== Model\Config\get('bookmarklet_token')) {
            Response\text('Access Forbidden', 403);
        }
    }

    $values += array(
        'url' => trim($url),
        'download_content' => 0,
        'rtl' => 0,
        'cloak_referrer' => 0,
        'create_group' => '',
        'feed_group_ids' => array()
    );

    try {
        $feed_id = Model\Feed\create(
            $values['url'],
            $values['download_content'],
            $values['rtl'],
            $values['cloak_referrer'],
            $values['feed_group_ids'],
            $values['create_group']
        );
    }
    catch (UnexpectedValueException $e) {
        $error_message = t('This subscription already exists.');
    }
    catch (PicoFeed\Client\InvalidCertificateException $e) {
        $error_message = t('Invalid SSL certificate.');
    }
    catch (PicoFeed\Client\InvalidUrlException $e) {
        // picoFeed uses this exception for multiple reasons, but doesn't
        // provide an exception code to distinguish what exactly happend here
        $error_message = $e->getMessage();
    }
    catch (PicoFeed\Client\MaxRedirectException $e) {
        $error_message = t('Maximum number of HTTP redirections exceeded.');
    }
    catch (PicoFeed\Client\MaxSizeException $e) {
        $error_message = t('The content size exceeds to maximum allowed size.');
    }
    catch (PicoFeed\Client\TimeoutException $e) {
        $error_message = t('Connection timeout.');
    }
    catch (PicoFeed\Parser\MalformedXmlException $e) {
        $error_message = t('Feed is malformed.');
    }
    catch (PicoFeed\Reader\SubscriptionNotFoundException $e) {
        $error_message = t('Unable to find a subscription.');
    }
    catch (PicoFeed\Reader\UnsupportedFeedFormatException $e) {
        $error_message = t('Unable to detect the feed format.');
    }

    Model\Config\write_debug();

    if (isset($feed_id) && $feed_id !== false) {
        Session\flash(t('Subscription added successfully.'));
        Response\redirect('?action=feed-items&feed_id='.$feed_id);
    }
    else {
        if (! isset($error_message)) {
            $error_message = t('Error occured.');
        }

        Session\flash_error($error_message);
    }

    Response\html(Template\layout('add', array(
        'values' => $values + array('csrf' => Model\Config\generate_csrf()),
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'groups' => Model\Group\get_all(),
        'menu' => 'feeds',
        'title' => t('Subscriptions')
    )));
});

// OPML export
Router\get_action('export', function() {

    Response\force_download('feeds.opml');
    Response\xml(Model\Feed\export_opml());
});

// OPML import form
Router\get_action('import', function() {

    Response\html(Template\layout('import', array(
        'errors' => array(),
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'menu' => 'feeds',
        'title' => t('OPML Import')
    )));
});

// OPML importation
Router\post_action('import', function() {

    if (Model\Feed\import_opml(Request\file_content('file'))) {

        Session\flash(t('Your feeds have been imported.'));
        Response\redirect('?action=feeds');
    }
    else {

        Session\flash_error(t('Unable to import your OPML file.'));
        Response\redirect('?action=import');
    }
});
