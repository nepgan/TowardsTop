<?php
/**
 * 对输入进行检查
 *
 * @author 甘文涛
 * @date 2012-06-21
 */
class Input
{

    public static function filter($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = self::filter($val);
            }
        } else {
            $str = trim($str);
            $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); // 转换'与''与<与>与&这5个字符
            if (!get_magic_quotes_gpc()) {
                $str = addslashes($str); // 转义'与''与\与\0这4个字符
            }
        }
        return $str;
    }

    public static function clean($str, $type)
    {
        if ($type == 'int') $str = (int)$str;

        return $str;
    }

    public static function set($data, $field, $default = '')
    {
        return isset($data[$field]) ? $data[$field] : $default;
    }

    public static function get($name, $default = '', $type = 'str')
    {
        return isset($_GET[$name]) ? self::clean($_GET[$name], $type) : $default;
    }

    public static function post($name, $default = '', $type = 'str')
    {
        return isset($_POST[$name]) ? self::clean($_POST[$name], $type) : $default;
    }

    public static function session($name, $default = '', $type = 'str')
    {
        return isset($_SESSION[$name]) ? self::clean($_SESSION[$name], $type) : $default;
    }

    public static function cookie($name, $default = '', $type = 'str')
    {
        return isset($_COOKIE[$name]) ? self::clean($_COOKIE[$name], $type) : $default;
    }

    public static function server($name, $default = '', $type = 'str')
    {
        return isset($_SERVER[$name]) ? self::clean($_SERVER[$name], $type) : $default;
    }

    public static function msg($str, $field = '', $status = 0)
    {
        $data = array(
            'status' => $status,
            'field'  => $field,
            'msg'    => $str,
        );
        if (!$field) unset($data['field']);
        echo json_encode($data);
        exit;
    }

    public static function day($day)
    {
        if (!$day || strpos($day, '-') === FALSE || strlen($day) != 10 || !strtotime($day)) return FALSE;
        else return true;
    }

}
