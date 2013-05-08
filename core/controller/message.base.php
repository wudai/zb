<?php

/**
 * ECMALL: 消息控制器
 * ============================================================================
 * 版权所有 (C) 2005-2008 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.shopex.cn
 * -------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Id: message.base.php 10365 2009-12-22 07:36:08Z chengweidong $
 */
if (!defined('IN_MALL'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


function _trigger_message ($arr)
{
    if (count($arr) < 2) {
        $arr[] = '返回';
    }
    if (count($arr) < 3) {
        $arr[] = 'javascript:history.back()';
    }
    $m = '';
    if (!empty($arr[0]))
    {
        if (is_array($arr[0]))
        {
            $m = '出错';
            foreach ($arr[0] as $key => $err)
            {
                $m .= $err['msg'] . ($err['obj'] ? '[' . $err['obj'] . ']' : '') . '<br />';
            }
        }
        else
        {
            $m = $arr[0];
        }
    }
    $a = array('content' => $m, 'links' => array());
    $n = count($arr);
    for ($i = 1; $i < $n; $i += 2) {
        $href = (($i + 1) >= $n) ? 'javascript:history.back()' : $arr[$i + 1];
        //$redirect = (($i + 2) >= $n) ? false : $arr[$i + 2];
        $a['links'][] = array('href' => $href , 'text' => $arr[$i]);
    }

    return $a;
}
/**
    * send a system notice message
    *
    * @author wj
    * @param string $msg
    * @return void
    */
function show_message ($msg)
{
    $a = _trigger_message(func_get_args());

    _message(serialize($a), E_USER_NOTICE);
}

/**
    * send a system warning message
    *
    * @param string $msg
    */
function show_warning ($msg)
{
    $a = _trigger_message(func_get_args());

    _message(serialize($a), E_USER_WARNING);
}


/**
    * send a system message
    *
    * @author  weberliu
    * @param   string  $msg
    * @param   int     $type
    */
function _message($msg, $type)
{
    $msg = new Message($msg, $type);
    $msg->display();
}

/**
 * 写入 log 文件
 *
 * @param   string  $msg
 * @param   string  $file
 * @param   string  $line
 */
function put_log($err, $msg, $file, $line)
{
    $filename = ROOT_PATH . "/temp/logs/" .date("Ym"). ".log";

    if (!is_dir('temp/logs'))
    {
        mkdir(ROOT_PATH . '/' . 'temp/logs');
    }

    $handler = null;

    if (($handler = fopen($filename, 'ab+')) !== false)
    {
        fwrite($handler, date('r') . "\t[$err]$msg\t$file\t$line\n");
        fclose($handler);
    }
}

class Message extends MessageBase
{
    var $visitor    = null;
    var $caption    = '';
    var $icon       = '';
    var $links      = array();
    var $redirect   = '';
    var $err_line   = '';
    var $err_file   = '';

    function __construct($str='', $errno=null)
    {
        $this->Message($str, $errno);
    }
    function Message($str, $errno=null)
    {
        parent::__construct();
        if ($errno == E_USER_ERROR || $errno == E_ERROR || $errno == E_WARNING)
        {
            $this->icon = "error";
        }
        else if ($errno == E_USER_WARNING)
        {
            $this->icon = "warning";
        }
        else
        {
            $this->icon = "notice";
        }
        $this->_init_view();
        $this->handle_message($str);
        $this->visitor =& env('visitor');
        $this->_session = &env('session');
    }
    function handle_message($msg)
    {
        /* decode message */
        $arr = @unserialize($msg);

        if ($arr === false)
        {
            $this->message = nl2br($msg);
        }
        else
        {
            foreach ($arr['links'] AS $key=>$val)
            {
                $this->add_link($val['text'], $val['href']);
            }
            $this->message = nl2br($arr['content']);
        }
    }
    /**
     * 生成bug报告链接
     *
     * @author wj
     * @param string $err  错误类型
     * @param string $msg 错误信息
     * @param string $file   出错文件
     * @param string $line   出错行号
     * @return  void
     */
    function report_link($err, $msg, $file, $line)
    {
        if (strncmp($msg, 'MySQL Error[', 12) == 0)
        {
            $tmp_arr = explode("\n", $msg, 2);
            $tmp_param = strtr($tmp_arr[0], array('MySQL Error['=>'dberrno=', ']: '=>'&dberror='));
            parse_str($tmp_param, $tmp_arr);
            $url = 'http://ecmall.shopex.cn/help/faq.php?type=mysql&dberrno=' . $tmp_arr['dberrno'] . '&dberror=' .  urlencode($tmp_arr['dberror']);

            $this->add_link('数据库错误日志', $url);
        }
        else
        {
            $arr_report = array('err'=>$err, 'msg'=>$msg, 'file'=>$file, 'line'=>$line, 'query_string'=>$_SERVER['QUERY_STRING'], 'occur_date'=>date('Y-m-d H:i:s'));
            foreach ($arr_report as $k=>$v)
            {
                $arr_report[$k] = $k . chr(9) . $v;
            }
            $str_report = str_replace('=', '', base64_encode(implode(chr(8), $arr_report)));
            $url = 'index.php?app=issue&data=' . $str_report . '&amp;sign=' . md5($str_report . ECM_KEY);

            $this->add_link('错误信息', $url);
        }

        $this->add_link('返回');
    }

    /**
     * 添加一个链接到消息页面
     *
     * @author  weberliu
     * @param   string  $text
     * @param   string  $href
     * @return  void
     */
    function add_link($text, $href='javascript:history.back()')
    {
        $this->links[] = array('text' => $text, 'href' => $href);

        if ($this->icon == 'notice' && $this->redirect == '')
        {
            $this->redirect = (strstr($href, 'javascript:') !== false) ? $href : "location.href='{$href}'";
        }
    }

    /**
     * 显示消息页面
     *
     * @author  wj
     * @return  void
     */
    function display()
    {
        $this->message = str_replace(ROOT_PATH, '', $this->message);

        if (defined('IS_AJAX') && IS_AJAX)
        {
            $error_line = empty($this->err_file[$this->err_line]) ? '' : "\n\nFile: $this->err_file[$this->err_line]";
            if ($this->icon == "notice")
            {
                $this->json_result('', $this->message . $error_line);
                return;
            }
            else
            {
                $this->json_error($this->message . $error_line);
                return;
            }
        }
        else
        {
            if ($this->redirect)
            {
                $this->redirect = str_replace('&amp;', '&', $this->redirect); //$this->redirect 是给js使用的,不能包含&amp;
            }
            $this->assign('page_title', '系统信息' . '-- Powered by ECMall');
            $this->assign('message',    $this->message);
            $this->assign('links',      $this->links);
            $this->assign('icon',       $this->icon);
            $this->assign('err_line',   $this->err_line);
            $this->assign('err_file',   $this->err_file);
            $this->assign('redirect',   $this->redirect);
            restore_error_handler(); //错误提示时将错误捕捉关掉,以免display出错时出现死循环
            parent::display('message.html');
        }
    }
}
?>
