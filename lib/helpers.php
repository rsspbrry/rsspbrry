<?php

namespace Helper;

function is_secure_connection()
{
    return ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

/*
 * get Version number from git archive output
 */
function parse_app_version($refnames, $commithash)
{
    $version = 'master';

    if ($refnames !== '$Format:%d$') {
        $tag = preg_replace('/\s*\(.*tag:\sv([^,]+).*\)/i', '\1', $refnames);

        if (!is_null($tag) && $tag !== $refnames) {
            return $tag;
        }
    }

    if ($commithash !== '$Format:%H$') {
        $version .= '.'.$commithash;
    }

    return $version;
}

/*
 * get Image extension from mime type
 */
function favicon_extension($type)
{
    $types = array(
        'image/png' => '.png',
        'image/gif' => '.gif',
        'image/x-icon' => '.ico',
        'image/jpeg' => '.jpg',
        'image/jpg' => '.jpg'
    );

    if (array_key_exists($type, $types)) {
        return $types[$type];
    } else {
        return '.ico';
    }
}

function favicon(array $favicons, $feed_id)
{
    if (! empty($favicons[$feed_id])) {
        return '<img src="'.FAVICON_URL_PATH.'/'.$favicons[$feed_id]['hash'].favicon_extension($favicons[$feed_id]['type']).'" class="favicon"/>';
    }

    return '';
}

function is_rtl(array $item)
{
    return ! empty($item['rtl']) || \PicoFeed\Parser\Parser::isLanguageRTL($item['language']);
}

function css()
{
    $theme = \Model\Config\get('theme');

    if ($theme !== 'original') {

        $css_file = THEME_DIRECTORY.'/'.$theme.'/css/app.css';

        if (file_exists($css_file)) {
            return $css_file.'?version='.filemtime($css_file);
        }
    }

    return 'assets/css/app.css?version='.filemtime('assets/css/app.css');
}

function get_current_base_url()
{
    $url = is_secure_connection() ? 'https://' : 'http://';
    $url .= $_SERVER['SERVER_NAME'];
    $url .= $_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443 ? '' : ':'.$_SERVER['SERVER_PORT'];
    $url .= str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) !== '/' ? str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])).'/' : '/';

    return $url;
}

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
}

function flash($type, $html)
{
    $data = '';

    if (isset($_SESSION[$type])) {
        $data = sprintf($html, escape($_SESSION[$type]));
        unset($_SESSION[$type]);
    }

    return $data;
}

function format_bytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', 'k', 'M', 'G', 'T');

    return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
}

function get_host_from_url($url)
{
    return escape(parse_url($url, PHP_URL_HOST)) ?: $url;
}

function summary($value, $min_length = 5, $max_length = 120, $end = '[...]')
{
    $length = strlen($value);

    if ($length > $max_length) {
        $max = strpos($value, ' ', $max_length);
        if ($max === false) {
            $max = $max_length;
        }
        return substr($value, 0, $max).' '.$end;
    }
    else if ($length < $min_length) {
        return '';
    }

    return $value;
}

function in_list($id, array $listing)
{
    if (isset($listing[$id])) {
        return escape($listing[$id]);
    }

    return '?';
}

function relative_time($timestamp, $fallback_date_format = '%e %B %Y %k:%M')
{
    $diff = time() - $timestamp;

    if ($diff < 0) return \dt($fallback_date_format, $timestamp);

    if ($diff < 60) return \t('%d second ago', $diff);

    $diff = floor($diff / 60);
    if ($diff < 60) return \t('%d minute ago', $diff);

    $diff = floor($diff / 60);
    if ($diff < 24) return \t('%d hour ago', $diff);

    $diff = floor($diff / 24);
    if ($diff < 7) return \t('%d day ago', $diff);

    $diff = floor($diff / 7);
    if ($diff < 4) return \t('%d week ago', $diff);

    $diff = floor($diff / 4);
    if ($diff < 12) return \t('%d month ago', $diff);

    return \dt($fallback_date_format, $timestamp);
}

function error_class(array $errors, $name)
{
    return ! isset($errors[$name]) ? '' : ' form-error';
}

function error_list(array $errors, $name)
{
    $html = '';

    if (isset($errors[$name])) {

        $html .= '<ul class="form-errors">';

        foreach ($errors[$name] as $error) {
            $html .= '<li>'.escape($error).'</li>';
        }

        $html .= '</ul>';
    }

    return $html;
}

function form_value($values, $name)
{
    if (isset($values->$name)) {
        return 'value="'.escape($values->$name).'"';
    }

    return isset($values[$name]) ? 'value="'.escape($values[$name]).'"' : '';
}

function form_hidden($name, $values = array())
{
    return '<input type="hidden" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).'/>';
}

function form_default_select($name, array $options, $values = array(), array $errors = array(), $class = '')
{
    $options = array('' => '?') + $options;
    return form_select($name, $options, $values, $errors, $class);
}

function form_select($name, array $options, $values = array(), array $errors = array(), $class = '')
{
    $html = '<select name="'.$name.'" id="form-'.$name.'" class="'.$class.'">';

    foreach ($options as $id => $value) {

        $html .= '<option value="'.escape($id).'"';

        if (isset($values->$name) && $id == $values->$name) $html .= ' selected="selected"';
        if (isset($values[$name]) && $id == $values[$name]) $html .= ' selected="selected"';

        $html .= '>'.escape($value).'</option>';
    }

    $html .= '</select>';
    $html .= error_list($errors, $name);

    return $html;
}

function form_radios($name, array $options, array $values = array())
{
    $html = '';

    foreach ($options as $value => $label) {
        $html .= form_radio($name, $label, $value, isset($values[$name]) && $values[$name] == $value);
    }

    return $html;
}

function form_radio($name, $label, $value, $checked = false, $class = '')
{
    return '<label><input type="radio" name="'.$name.'" class="'.$class.'" value="'.escape($value).'" '.($checked ? 'checked' : '').'>'.escape($label).'</label>';
}

function form_checkbox($name, $label, $value, $checked = false, $class = '')
{
    return '<label><input type="checkbox" name="'.$name.'" class="'.$class.'" value="'.escape($value).'" '.($checked ? 'checked="checked"' : '').'><span>'.escape($label).'</span></label>';
}

function form_label($label, $name, $class = '')
{
    return '<label for="form-'.$name.'" class="'.$class.'">'.escape($label).'</label>';
}

function form_textarea($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<textarea name="'.$name.'" id="form-'.$name.'" class="'.$class.'" ';
    $html .= implode(' ', $attributes).'>';
    $html .= isset($values->$name) ? escape($values->$name) : isset($values[$name]) ? $values[$name] : '';
    $html .= '</textarea>';
    $html .= error_list($errors, $name);

    return $html;
}

function form_input($type, $name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    $class .= error_class($errors, $name);

    $html = '<input type="'.$type.'" name="'.$name.'" id="form-'.$name.'" '.form_value($values, $name).' class="'.$class.'" ';
    $html .= implode(' ', $attributes).'/>';
    $html .= error_list($errors, $name);

    return $html;
}

function form_text($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('text', $name, $values, $errors, $attributes, $class);
}

function form_password($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('password', $name, $values, $errors, $attributes, $class);
}

function form_email($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('email', $name, $values, $errors, $attributes, $class);
}

function form_date($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('date', $name, $values, $errors, $attributes, $class);
}

function form_number($name, $values = array(), array $errors = array(), array $attributes = array(), $class = '')
{
    return form_input('number', $name, $values, $errors, $attributes, $class);
}
