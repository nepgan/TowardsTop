<?php
/**
 * 视图类
 *
 * @author Matt Gan 甘文涛
 * @email qyjazz@qq.com
 * @date 2014-05-09
 */
function output($data, $field, $default = '')
{
    if (isset($data[$field])) $value = $data[$field];
    else $value = $default;

    return $value;
}

class View
{

    public static function option($fields, $list)
    {
        $html = '';
        foreach ($fields as $key => $val) {
            $temp = '<label for="{id}"><input type="checkbox" id="{id}" name="show_option" value="{value}"{checked} /> {name}</label>';
            $temp = strtr($temp, array(
                '{id}' => 'show_option_' . $val['name'],
                '{name}' => $val['name'],
                '{value}' => $key,
                '{checked}' => in_array($key, $list) ? ' checked="checked"' : '',
            ));
            $html .= $temp;
        }
        return $html;
    }

    public static function header($fields)
    {
        echo '<thead>';
        echo '<tr>';
        foreach ($fields as $field => $val) {
            $width = output($val, 'width');
            $class = output($val, 'class');
            $by = output($val, 'by');
            $title = output($val, 'title');

            $extend = '';
            if ($width) $extend .= ' width="' . $width . '"';
            if ($class) $extend .= ' class="' . $class . '"';
            if ($by) $extend .= ' by="' . $by . '"';
            if ($title) $extend .= ' title="' . $title . '"';
            if (strpos($class, 'order') !== FALSE) $extend .= ' id="order_' . $field . '"';
            echo '<th' . $extend . '>';

            $value = $val['name'];
            $son = output($val, 'son');
            if ($son) {
                echo '<table class="table3">';
                echo '<tr>';
                foreach ($son as $k => $v) {
                    $extend2 = '';

                    $width2 = output($v, 'width');
                    if ($width2) $extend2 .= ' width="' . $width2 . '"';

                    $class2 = output($v, 'class');
                    if ($class2) $extend2 .= ' class="' . $class2 . '"';

                    echo '<th' . $extend2 . '>';
                }
                echo '</tr>';
                echo '</table>';
            } else {
                echo $value;
                if ($by == 'desc') {
                    echo '<span class="fa fa-sort-desc"></span>';
                } else if ($by == 'asc') {
                    echo '<span class="fa fa-sort-asc"></span>';
                } else if ($class == 'order') {
                    echo '<span class="fa fa-sort"></span>';
                }
            }

            echo '</th>';
        }
        echo '</tr>';
        echo '</thead>';
    }

    public static function body($fields, $list)
    {
        echo '<tbody>';

        if ($list) {
            foreach ($list as $key => $row) {
                echo '<tr>';
                foreach ($fields as $field => $val) {
                    if ($field == 'handle') echo '<td class="center" id="option' . $row['id'] . '">';
                    else if ($field == 'status' && isset($row['id'])) echo '<td id="status' . $row['id'] . '">';
                    else if (isset($val['class']) && $val['class'] && $val['class'] != 'order') echo '<td class="' . $val['class'] . '">';
                    else echo '<td>';
                    echo $row[$field];
                    echo '</td>';
                }
                echo '</tr>';
            }
        }

        echo '</tbody>';
    }

    public static function table($fields, $list, $pageStr, $title = '')
    {
        if (!$title) $title = 'Table List';
        if ($title != 'none') {
            echo '<div class="title2">';
            echo '<div class="my_pager">' . $pageStr . '</div>';
            echo '<h3><i class="fa fa-align-justify"></i>' . $title . '</h3>';
            echo '</div>';
        }
        echo '<div class="table_wrap">';
        echo '<table class="table">';
        self::header($fields);
        self::body($fields, $list);
        echo '</table>';
        echo '<div class="my_pager">' . $pageStr . '</div>';
        echo '</div>';
    }

    public static function field($data, $row, $view = 'add') {
        $required = isset($data['required']) ? '<i>*</i>' : '';
        $field = isset($data['field']) ? $data['field'] : '';
        $value = isset($data['value']) ? $data['value'] : (isset($row[$field]) ? $row[$field] : '');
        $name = isset($data['name']) ? $data['name'] : '';
        $list = isset($data['list']) ? $data['list'] : array();
        $type = isset($data['type']) ? $data['type'] : 'text';
        $maxlength = isset($data['maxlength']) ? ' maxlength="' . $data['maxlength'] . '"' : '';
        $class = isset($data['class']) ? ' class="' . $data['class'] . '"' : '';
        $extend = isset($data['extend']) ? ' ' . $data['extend'] : '';
        $tip = isset($data['tip']) ? ' ' . $data['tip'] : '';

        if ($view != 'add') {
            $value = isset($_GET[$field]) ? $_GET[$field] : (isset($data['value']) ? $data['value'] : '');
        }

        if (strpos($extend, 'id="') === FALSE) {
            $extend .= ' id="' . trim($field, '[]') . '"';
        }

        $html = '';
        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="{field}"{class}{extend}>{value}</textarea>';
                break;

            case 'select':
                $html .= '<select name="{field}"{class}{extend}>';
                if ($view != 'add') {
                    $html .= '<option value=""> - ' . $name . ' - </option>';
                }
                if ($value) $value = explode(',', $value);
                foreach ($list as $key => $val) {
                    $selected = (is_array($value) && in_array($key, $value)) ? ' selected="selected"' : '';
                    $html .= '<option value="' . $key . '"' . $selected . '>' . $val . '</option>';
                }
                $html .= '</select>';
                break;

            case 'custom':
                $html .= $data['html'];
                break;

            default:
                $html .= '<input type="{type}" name="{field}" value="{value}"{maxlength}{class}{extend} />';
                break;
        }

        if ($view == 'add') {
            $html = '<tr><th>{required} {name}</th><td>' . $html . '<span class="tips">{tip}</span><span id="msg_{field}" class="red"></span></td></tr>';
        } else {
            if (isset($_GET[$field]) && $_GET[$field] != $name && $_GET[$field]) {
                if ($class) $class = trim($class, '"') . ' focus"';
                else $class = ' class="focus"';

                if (strpos($html, 'multiple') !== FALSE) $class = 'chosen-select focus';
            }
        }

        $html = strtr(
            $html,
            array(
                '{required}' => $required,
                '{type}' => $type,
                '{field}' => $field,
                '{name}' => $name,
                '{value}' => $value,
                '{maxlength}' => $maxlength,
                '{class}' => $class,
                '{extend}' => $extend,
                '{tip}' => $tip,
            )
        );

        return $html;
    }

    public static function add($fields, $row, $view = 'add') {
        $html = '';
        foreach ($fields as $field => $val) {
            $val['field'] = $field;
            $html .= self::field($val, $row, $view);
        }
        return $html;
    }

    public static function search($fields, $row) {
        return self::add($fields, $row, '');
    }

    public static function js($name, $path = 'admin', $version = '')
    {
        if (!$version) $version = VIEW_VERSION;
        if (strpos($name, '?') !== FALSE) {
            $version = substr($name, strpos($name, '?') + 1);
            $name = substr($name, 0, strpos($name, '?'));
        }
        return '<script src="/js/' . $path . '/' . $name . '.js?v=' . $version . '"></script>';
    }

}
