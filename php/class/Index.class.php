<?php
/**
 * 首页类
 *
 * @author Matt Gan 甘文涛
 * @email qyjazz@qq.com
 * @date 2014-05-13
 */
class Index {

    public function page($total, $size) {
        // 分页
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if (!$page) $page = 1;
        $offset = ($page - 1) * $size;

        $pager = new Pager();
        $url = $_SERVER['REQUEST_URI'];
        $url = strtr($url, array('?json=1' => '', '&json=1' => ''));
        $pageStr = $pager->show($page, $total, $url, $size, 1, 2);

        return array($page, $offset, $size, $pageStr);
    }

    public function list_fields() {
        return array();
    }

    public function search_fields($model) {
        return array();
    }

    public function vars($model) {
        return array();
    }

    public function search($field, $name, $type, $param = array(), $prefix = '', $value, $search_result) {
        $result = '';
        $field = trim($field, '[]');
        $value = $value ? $value : (isset($_GET[$field]) ? ($_GET[$field]) : '');
        if ($prefix) $field = $prefix . '.' . "`$field`";
        else $field = "`$field`";
        if ($value) {
            if ($type == 'str' && $value != $name) {
                $result = " AND $field = '$value'";
            } else if ($type == 'int') {
                $value = (int)$value;
                $result = " AND $field = " . $value;
            } else if ($type == '%%' && $value != $name) {
                $result = " AND $field LIKE '%$value%'";
            } else if ($type == 'int_zero' && (int)$value) {
                $result = " AND $field = " . ((int)$value - 1);
            } else if ($type == 'country') {
                $value = ',' . trim($value, ',') . ',';
                $countryList = common::getCountryList();
                $temp = '';
                foreach ($countryList as $key => $val) {
                    if (strpos($value, ',' . $val . ',') !== FALSE) {
                        $temp .= " OR $field LIKE '%\"$key\"%'";
                    }
                }
                $temp = trim($temp, ' OR');
                if ($temp) $result = " AND ($temp)";
            }
        } else {
            if ($value === 0) {
                $result = " AND $field = 0";
            }
        }
        if ($type == 'between') {
            if ($param[0]) $result = " AND $field >= " . $param[0];
            if ($param[1]) $result .= " AND $field <= " . $param[1];
        } else if ($type == 'between_str') {
            $result = " AND $field BETWEEN '" . $param[0] . "' AND '" . $param[1] . "'";
        } else if ($type == 'in') {
            if (strpos($value, ',') === FALSE && $value) $value = (int)$value;
            if ($search_result) $value = 0;
            if ($value !== '') {
                // if (is_array($value)) $value = implode(',', $value);
                $result = " AND $field IN ($value)";
            }
        }
        return $result;
    }

    public function where($search_fields) {
        $where = '';
        foreach ($search_fields as $key => $val) {
            $temp_param = Input::set($val, 'search_param', array());
            $temp_prefix = Input::set($val, 'search_prefix', 'a');
            $temp_type = Input::set($val, 'search_type');
            $where .= $this->search($key, $val['name'], $temp_type, $temp_param, $temp_prefix, Input::set($val, 'value'), Input::set($val, 'search_result'));
        }
        // if ($where) $where = 'WHERE ' . trim($where, ' AND');
        return $where;
    }

    public function rate($install, $click) {
        $install = strtr($install, array(',' => ''));
        $click = strtr($click, array(',' => ''));
        $result = $click ? round($install / $click * 100, 2) : 0;
        if ($result >= 50) {
            $result = '<span class="red">' . $result . '%</span>';
        } else if ($result) {
            $result .= '%';
        } else {
            $result = '-';
        }
        return $result;
    }

    public function margin($profit, $revenue) {
        if ($profit < 0) {
            $profit = -1 * $profit;
        }
        if ($profit && $revenue) return round($profit / $revenue * 100, 1) . '%';
        else return '-';
    }

    public function ecpm($cost, $impression) {
        $cost = strtr($cost, array(',' => ''));
        $impression = strtr($impression, array(',' => ''));
        return ($impression && $cost) ? round($cost / $impression * 1000, 3) : '-';
    }

    public function link($name, $link, $pop = '', $extend = '') {
        $html = '<a href="{link}"{pop}{extend}>{name}</a>';
        $html = strtr($html, array('{link}' => $link, '{name}' => $name, '{extend}' => $extend));
        if ($pop) $pop = ' target="_blank"';
        $html = strtr($html, array('{pop}' => $pop));
        return $html;
    }

    public function money($money) {
        if (!$money) {
            $result = '-';
        } else {
            $result = '<span style="color:#e4393c;">';
            if ($money < 0) $result .= '- $' . number_format(-1 * $money, 2);
            else $result .= '$' . number_format($money, 2);
            $result .= '</span>';

            if (number_format($money, 2) == '0.00') $result = '-';
        }
        return $result;
    }

    public function formatList($list, $model) {
        return $list;
    }

    public function outputGraph($list, $model) {
        return array();
    }

    public function export() {

    }

    public function export_excel($list_fields, $list) {
        $data = $this->export();
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $data['name'] . ".xls");
        header('Cache-Control: no-cache, must-revalidate');
        header("Expires: 0");

        // 导出xls 开始
        foreach ($list_fields as $key => $val) {
            echo iconv('UTF-8', 'GB2312//ignore', strtr($val['name'], array('<br/>' => ' '))) . "\t";
        }
        echo "\n";

        $i = 0;
        foreach ($list as $key => $val) {
            foreach ($list_fields as $field => $v) {
                echo iconv('UTF-8', 'GB2312//ignore', strip_tags($val[$field])) . "\t";
            }
            echo "\n";

            $i++;
            if ($i >= 5000) break;
        }
        echo "\n";
        echo "\n";
    }

    public function export_excel_new($list_fields, $list,$need_fields) {
    	$data = $this->export();
    	header("Content-type:application/octet-stream");
    	header("Accept-Ranges:bytes");
    	header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition:attachment;filename=" . $data['name'] . ".xls");
    	header('Cache-Control: no-cache, must-revalidate');
    	header("Expires: 0");

    	// 导出xls 开始
    	foreach ($list_fields as $key => $val) {
    		echo iconv('UTF-8', 'GB2312//ignore', strtr($val, array('<br/>' => ' '))) . "\t";
    	}
    	echo "\n";

    	$i = 0;
    	foreach ($list as $key => $val) {
    		foreach ($need_fields as $field => $v) {
    			echo iconv('UTF-8', 'GB2312//ignore', strip_tags($val[$v])) . "\t";
    		}
    		echo "\n";

    		$i++;
    		if ($i >= 5000) break;
    	}
    	echo "\n";
    	echo "\n";
    	exit;
    }

    public function hide_html() {

    }

    public function view($title, $model, $html = 'admin/common/module_index', $template = '') {
        $search_fields = $this->search_fields($model);
        $list_fields = $this->list_fields();

        $size = 10;

        $vars = $this->vars($model);
        if ($vars) extract($vars);

        if (!isset($error)) $error = '';
        if (!$error) {

            $where = $this->where($search_fields);

            if (Input::get('export')) {
                $list = $model->getList($where, 0, 5000);
                if (Input::get('export') == 'campaign') {
                    $list_fields2 = $this->list_fields2($model);
                    $list_fields3 = $this->list_fields3($model);
                    if ($list) {
                        $list2 = $this->formatList2($list, $model, $where, $list_fields2);
                        $list3 = $this->formatList3($list, $model, $where, $list_fields3);

                        $this->export_excel($list_fields2, $list2);
                        $this->export_excel($list_fields3, $list3);
                    }
                } else {
                    if ($list) $list = $this->formatList($list, $model, $where, $list_fields);

                    $this->export_excel($list_fields, $list);
                }
                exit;
            } else {
                $total = $model->getTotal($where);

                // 分页
                if ($size) list($page, $offset, $size, $pageStr) = $this->page($total, $size);
                if (!isset($offset)) $offset = 0;
                if (!isset($pageStr)) $pageStr = '';

                if (isset($no_page)) $pageStr = '';

                $list = array();
                if ($total) $list = $model->getList($where, $offset, $size);

                $graph = $this->outputGraph($list, $model);
                $graph = json_encode($graph);

                if ($list) $list = $this->formatList($list, $model, $where, $list_fields);
            }
        }

        if (Input::get('json') == 1) {
            $data = array('list' => $list, 'page' => $pageStr);
            if ($this->error) $data['error'] = $this->error;
            exit(json_encode($data));
        }

        $positions = array();
        foreach ($list_fields as $key => $val) {
            $positions[] = Input::set($val, 'class');
        }

        $userInfo = $GLOBALS['userInfo'];

        $hide_html = $this->hide_html();

        if (!$template) $template = LOGIN_TYPE . '/common/index';
        include template($template);
    }


    public function newPage($total, $size) {

    	// 分页
    	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    	if (!$page) $page = 1;
    	$offset = ($page - 1) * $size;
    	$pager = new Pager();
    	$url = $_SERVER['REQUEST_URI'];
    	$url = strtr($url, array('?json=1' => '', '&json=1' => ''));
    	$pageStr = $pager->show($page, $total, $url, $size, 1, 1);

    	return array($page, $offset, $size, $pageStr);
    }

    public function commonLogin($model) {
    	return array();
    }

    public function newView($title, $model, $html = 'admin/common/module_index', $template = '') {

    	$vars = $this->vars($model);
    	if ($vars) extract($vars);

    	if (!$template) $template = LOGIN_TYPE . '/common/index';
    	include template($template);
    }

}
