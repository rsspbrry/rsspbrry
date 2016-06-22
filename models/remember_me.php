<?php

namespace Model\RememberMe;

use PicoDb\Database;
use Model\Config;
use Model\Database as DatabaseModel;

const TABLE = 'remember_me';
const COOKIE_NAME = '_R_';
const EXPIRATION = 5184000;

/**
 * Get a remember me record
 *
 * @access public
 * @return mixed
 */
function find($token, $sequence)
{
    return Database::getInstance('db')
                ->table(TABLE)
                ->eq('token', $token)
                ->eq('sequence', $sequence)
                ->gt('expiration', time())
                ->findOne();
}

/**
 * Get all sessions
 *
 * @access public
 * @return array
 */
function get_all()
{
    return Database::getInstance('db')
                ->table(TABLE)
                ->desc('date_creation')
                ->columns('id', 'ip', 'user_agent', 'date_creation', 'expiration')
                ->findAll();
}

/**
 * Authenticate the user with the cookie
 *
 * @access public
 * @return bool
 */
function authenticate()
{
    $credentials = read_cookie();

    if ($credentials !== false) {

        $record = find($credentials['token'], $credentials['sequence']);

        if ($record) {

            // Update the sequence
            write_cookie(
                $record['token'],
                update($record['token']),
                $record['expiration']
            );

            // mark user as sucessfull logged in
            $_SESSION['loggedin'] = true;

            return true;
        }
    }

    return false;
}

/**
 * Update the database and the cookie with a new sequence
 *
 * @access public
 */
function refresh()
{
    $credentials = read_cookie();

    if ($credentials !== false) {

        $record = find($credentials['token'], $credentials['sequence']);

        if ($record) {

            // Update the sequence
            write_cookie(
                $record['token'],
                update($record['token']),
                $record['expiration']
            );
        }
    }
}

/**
 * Remove the current RememberMe session and the cookie
 *
 * @access public
 */
function destroy()
{
    $credentials = read_cookie();

    if ($credentials !== false) {

        Database::getInstance('db')
             ->table(TABLE)
             ->eq('token', $credentials['token'])
             ->remove();
    }

    delete_cookie();
}

/**
 * Create a new RememberMe session
 *
 * @access public
 * @param  integer  $dbname      Database name
 * @param  integer  $username    Username
 * @param  string   $ip          IP Address
 * @param  string   $user_agent  User Agent
 * @return array
 */
function create($dbname, $username, $ip, $user_agent)
{
    $token = hash('sha256', $dbname.$username.$user_agent.$ip.Config\generate_token());
    $sequence = Config\generate_token();
    $expiration = time() + EXPIRATION;

    cleanup();

    Database::getInstance('db')
         ->table(TABLE)
         ->insert(array(
            'username' => $username,
            'ip' => $ip,
            'user_agent' => $user_agent,
            'token' => $token,
            'sequence' => $sequence,
            'expiration' => $expiration,
            'date_creation' => time(),
         ));

    return array(
        'token' => $token,
        'sequence' => $sequence,
        'expiration' => $expiration,
    );
}

/**
 * Remove old sessions
 *
 * @access public
 * @return bool
 */
function cleanup()
{
    return Database::getInstance('db')
                ->table(TABLE)
                ->lt('expiration', time())
                ->remove();
}

/**
 * Return a new sequence token and update the database
 *
 * @access public
 * @param  string   $token        Session token
 * @return string
 */
function update($token)
{
    $new_sequence = Config\generate_token();

    Database::getInstance('db')
         ->table(TABLE)
         ->eq('token', $token)
         ->update(array('sequence' => $new_sequence));

    return $new_sequence;
}

/**
 * Encode the cookie
 *
 * @access public
 * @param  string   $token        Session token
 * @param  string   $sequence     Sequence token
 * @return string
 */
function encode_cookie($token, $sequence)
{
    return implode('|', array(base64_encode(DatabaseModel\select()), $token, $sequence));
}

/**
 * Decode the value of a cookie
 *
 * @access public
 * @param  string   $value    Raw cookie data
 * @return array
 */
function decode_cookie($value)
{
    @list($database, $token, $sequence) = explode('|', $value);

    if (ENABLE_MULTIPLE_DB && ! DatabaseModel\select(base64_decode($database))) {
        return false;
    }

    return array(
        'token' => $token,
        'sequence' => $sequence,
    );
}

/**
 * Return true if the current user has a RememberMe cookie
 *
 * @access public
 * @return bool
 */
function has_cookie()
{
    return ! empty($_COOKIE[COOKIE_NAME]);
}

/**
 * Write and encode the cookie
 *
 * @access public
 * @param  string   $token        Session token
 * @param  string   $sequence     Sequence token
 * @param  string   $expiration   Cookie expiration
 */
function write_cookie($token, $sequence, $expiration)
{
    setcookie(
        COOKIE_NAME,
        encode_cookie($token, $sequence),
        $expiration,
        BASE_URL_DIRECTORY,
        null,
        \Helper\is_secure_connection(),
        true
    );
}

/**
 * Read and decode the cookie
 *
 * @access public
 * @return mixed
 */
function read_cookie()
{
    if (empty($_COOKIE[COOKIE_NAME])) {
        return false;
    }

    return decode_cookie($_COOKIE[COOKIE_NAME]);
}

/**
 * Remove the cookie
 *
 * @access public
 */
function delete_cookie()
{
    setcookie(
        COOKIE_NAME,
        '',
        time() - 3600,
        BASE_URL_DIRECTORY,
        null,
        \Helper\is_secure_connection(),
        true
    );
}
