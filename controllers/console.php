<?php

// Flush console messages
Router\get_action('flush-console', function() {

    @unlink(DEBUG_FILENAME);
    Response\redirect('?action=console');
});

// Display console
Router\get_action('console', function() {

    Response\html(Template\layout('console', array(
        'content' => @file_get_contents(DEBUG_FILENAME),
        'nb_unread_items' => Model\Item\count_by_status('unread'),
        'menu' => 'config',
        'title' => t('Console')
    )));
});
