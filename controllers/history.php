<?php

// Display history page
Router\get_action('history', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\Item\count_by_status('read');
    $items = Model\Item\get_all_by_status(
        'read',
        array(),
        $offset,
        Model\Config\get('items_per_page'),
        'updated',
        Model\Config\get('items_sorting_direction')
    );

    Response\html(Template\layout('history', array(
        'favicons' => Model\Favicon\get_item_favicons($items),
        'original_marks_read' => Model\Config\get('original_marks_read'),
        'items' => $items,
        'order' => '',
        'direction' => '',
        'display_mode' => Model\Config\get('items_display_mode'),
        'nb_items' => $nb_items,
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'offset' => $offset,
        'items_per_page' => Model\Config\get('items_per_page'),
        'nothing_to_read' => Request\int_param('nothing_to_read'),
        'menu' => 'history',
        'title' => t('History').' ('.$nb_items.')'
    )));
});

// Confirmation box to flush history
Router\get_action('confirm-flush-history', function() {

    Response\html(Template\layout('confirm_flush_items', array(
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'menu' => 'history',
        'title' => t('Confirmation')
    )));
});

// Flush history
Router\get_action('flush-history', function() {

    Model\Item\mark_all_as_removed();
    Response\redirect('?action=history');
});
