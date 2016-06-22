<?php

// Logout and destroy session
Router\get_action('logout', function() {

    Model\User\logout();
    Response\redirect('?action=login');
});

// Display form login
Router\get_action('login', function() {

    if (Model\User\is_loggedin()) {
        Response\redirect('?action=unread');
    }

    Response\html(Template\load('login', array(
        'errors' => array(),
        'values' => array(
            'csrf' => Model\Config\generate_csrf(),
        ),
        'databases' => Model\Database\get_list(),
        'current_database' => Model\Database\select()
    )));
});

// Check credentials and redirect to unread items
Router\post_action('login', function() {

    $values = Request\values();
    Model\Config\check_csrf_values($values);
    list($valid, $errors) = Model\User\validate_login($values);

    if ($valid) {
        Response\redirect('?action=unread');
    }

    Response\html(Template\load('login', array(
        'errors' => $errors,
        'values' => $values + array('csrf' => Model\Config\generate_csrf()),
        'databases' => Model\Database\get_list(),
        'current_database' => Model\Database\select()
    )));
});
