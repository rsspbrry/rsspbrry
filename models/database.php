<?php

namespace Model\Database;

use Schema;
use DirectoryIterator;
use Model\Config;
use SimpleValidator\Validator;
use SimpleValidator\Validators;

// Create a new database for a new user
function create($filename, $username, $password)
{
    $filename = DATA_DIRECTORY.DIRECTORY_SEPARATOR.$filename;

    if (ENABLE_MULTIPLE_DB && ! file_exists($filename)) {

        $db = new \PicoDb\Database(array(
            'driver' => 'sqlite',
            'filename' => $filename,
        ));

        if ($db->schema()->check(Schema\VERSION)) {
            $credentials = array(
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT)
            );

            $db->hashtable('settings')->put($credentials);

            return true;
        }
    }

    return false;
}

// Get or set the current database
function select($filename = '')
{
    static $current_filename = DB_FILENAME;

    // function gets called with a filename at least once the database
    // connection is established
    if (! empty($filename)) {
        if (ENABLE_MULTIPLE_DB && in_array($filename, get_all())) {
            $current_filename = $filename;

            // unset the authenticated flag if the database is changed
            if (empty($_SESSION['database']) || $_SESSION['database'] !== $filename) {
                if (isset($_SESSION)) {
                    unset($_SESSION['loggedin']);
                }

                $_SESSION['database'] = $filename;
                $_SESSION['config'] = Config\get_all();
            }
        }
        else {
            return false;
        }
    }

    return $current_filename;
}

// Get database path
function get_path()
{
    return DATA_DIRECTORY.DIRECTORY_SEPARATOR.select();
}

// Get the list of available databases
function get_all()
{
    $listing = array();

    $dir = new DirectoryIterator(DATA_DIRECTORY);

    foreach ($dir as $fileinfo) {
        if ($fileinfo->getExtension() === 'sqlite') {
            $listing[] = $fileinfo->getFilename();
        }
    }

    return $listing;
}

// Get the formated db list
function get_list()
{
    $listing = array();

    foreach (get_all() as $filename) {

        if ($filename === DB_FILENAME) {
            $label = t('Default database');
        }
        else {
            $label = ucfirst(substr($filename, 0, -7));
        }

        $listing[$filename] = $label;
    }

    return $listing;
}

// Validate database form
function validate(array $values)
{
    $v = new Validator($values, array(
        new Validators\Required('name', t('The database name is required')),
        new Validators\AlphaNumeric('name', t('The name must have only alpha-numeric characters')),
        new Validators\Required('username', t('The user name is required')),
        new Validators\MaxLength('username', t('The maximum length is 50 characters'), 50),
        new Validators\Required('password', t('The password is required')),
        new Validators\MinLength('password', t('The minimum length is 6 characters'), 6),
        new Validators\Required('confirmation', t('The confirmation is required')),
        new Validators\Equals('password', 'confirmation', t('Passwords don\'t match')),
    ));

    return array(
        $v->execute(),
        $v->getErrors()
    );
}
