<?php

namespace Model\User;

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PicoDb\Database;
use Session;
use Model\Config;
use Model\RememberMe;
use Model\Database as DatabaseModel;

// Check if the user is logged in
function is_loggedin()
{
    return ! empty($_SESSION['loggedin']);
}

// Destroy the session and the rememberMe cookie
function logout()
{
    RememberMe\destroy();
    Session\close();
}

// Get the credentials from the current selected database
function getCredentials()
{
    return Database::getInstance('db')
        ->hashtable('settings')
        ->get('username', 'password');
}

// Validate authentication
function validate_login(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('username', t('The user name is required')),
        new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
        new Validators\Required('password', t('The password is required'))
    ));

    $result = $v->execute();
    $errors = $v->getErrors();

    if ($result) {

        $credentials = getCredentials();

        if ($credentials && $credentials['username'] === $values['username'] && password_verify($values['password'], $credentials['password'])) {

            $_SESSION['loggedin'] = true;
            $_SESSION['config'] = Config\get_all();

            // Setup the remember me feature
            if (! empty($values['remember_me'])) {
                $cookie = RememberMe\create(DatabaseModel\select(), $values['username'], Config\get_ip_address(), Config\get_user_agent());
                RememberMe\write_cookie($cookie['token'], $cookie['sequence'], $cookie['expiration']);
            }
        }
        else {

            $result = false;
            $errors['login'] = t('Bad username or password');
        }
    }

    return array(
        $result,
        $errors
    );
}
