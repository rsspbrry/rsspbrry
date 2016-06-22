<?php

require __DIR__.'/common.php';

Router\bootstrap(__DIR__.'/controllers', 'common', 'console', 'user', 'config', 'item', 'history', 'bookmark', 'feed');

// Page not found
Router\notfound(function() {
    Response\redirect('?action=unread');
});
