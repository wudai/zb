<?php

/**
 *    MallBaseApp
 *
 *    @author    Garbin
 *    @usage    none
 */
class MallBaseApp extends BaseApp
{
    var $outcall;
    function __construct()
    {
        $this->MallBaseApp();
    }
    function MallBaseApp()
    {
        parent::__construct();
        if (!defined('MODULE')) //没看懂
        {

            /* 载入配置项 */
            $setting =& af('settings');
            Conf::load($setting->getAll());

            /* 初始化访问者(放在此可能产生问题) */
            $this->_init_visitor();

        }

    }
    function _init_visitor()
    {
    }

    /**
     *    初始化Session
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _init_session()
    {
		parent::_init_session();
    }
    function _config_view()
    {
		parent::_config_view();
    }

    /**
     *    转发至模块
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {
        /* 指定了要运行的模块则调用模块控制器 */
        (!empty($_GET['module']) && !defined('MODULE')) && $action = 'run_module';
        parent::do_action($action);
    }

    function run_module()
    {
        $module_name = empty($_REQUEST['module']) ? false : strtolower(trim(str_replace('/', '', $_REQUEST['module'])));
        if (!$module_name)
        {
            $this->show_warning('no_such_module');

            return;
        }
        $file = defined('IN_BACKEND') ? 'admin' : 'index';
        $module_class_file = ROOT_PATH . '/external/modules/' . $module_name . '/' . $file . '.module.php';
        require(ROOT_PATH . '/includes/module.base.php');
        require($module_class_file);
        define('MODULE', $module_name);
        $module_class_name = ucfirst($module_name) . 'Module';

        /* 判断模块是否启用 */
        $model_module =& m('module');
        $find_data = $model_module->find('index:' . $module_name);
        if (empty($find_data))
        {
            /* 没有安装 */
            $this->show_warning('no_such_module');

            return;
        }
        $info = current($find_data);
        if (!$info['enabled'])
        {
            /* 尚未启用 */
            $this->show_warning('module_disabled');

            return;
        }

        /* 加载模块配置 */
        Conf::load(array($module_name . '_config' => unserialize($info['module_config'])));

        /* 运行模块 */
        $module = new $module_class_name();
        c($module);
        $module->do_action(ACT);
        $module->destruct();
    }


    function logout()
    {
        $this->visitor->logout();
    }

    function display($f)
    {
        $this->assign('site_url', SITE_URL);

        /* 用户信息 */
        $this->assign('visitor', $this->visitor->has_login ? $this->visitor->info : array());
        parent::display($f);
    }

	function fetch($f)
	{
        $this->assign('site_url', SITE_URL);

        /* 用户信息 */
        $this->assign('visitor', $this->visitor->has_login ? $this->visitor->info : array());
        return parent::fetch($f);
	}


    /**
     *    显示错误警告
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function show_warning()
    {
		$args = func_get_args();
		if (defined('IS_AJAX') && IS_AJAX) {
			$this->json_out(1, $args[0]);
		} else {
			call_user_func_array('show_warning', $args);
			die;
		}
    }


    /**
     *    显示提示消息
     *
     *    @author    Garbin
     *    @return    void
     */
    function show_message()
    {
        $args = func_get_args();
		if (defined('IS_AJAX') && IS_AJAX) {
			$this->json_out(0, $args[0]);
		} else {
			call_user_func_array('show_message', $args);
			die;
		}
    }

    /**
     * Make a json message
     *
     * @param   mixed   $retval
     * @param   string  $msg
     *
     * @return  void
     */
    function json_out($code, $msg = null, $data = null)
    {
		$this->json_header();
        $json = json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data));
		$jqremote = isset($_GET['jsoncallback']) ? trim($_GET['jsoncallback']) : false;
        if ($jqremote) {
            $json = $jqremote . '(' . $json . ')';
        }
        die($json);
    }

    /**
     * Send a Header
     *
     * @author weberliu
     *
     * @return  void
     */
    function json_header()
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type:text/plain;charset=" . CHARSET, true);
    }

    /**
     *    验证码
     *
     *    @author    Garbin
     *    @return    void
     */
    function _captcha($width, $height)
    {
        import('captcha.lib');
        $word = generate_code();
        $_SESSION['captcha'] = base64_encode($word);
        $code = new Captcha(array(
            'width' => $width,
            'height'=> $height,
        ));
        $code->display($word);
    }

    /**
     *    获取分页信息
     *
     *    @author    Garbin
     *    @return    array
     */
    function _get_page($page_per = 10)
    {
        $page = empty($_REQUEST['p']) ? 1 : intval($_REQUEST['p']);
        $start = ($page -1) * $page_per;

        return array('limit' => "{$start},{$page_per}", 'curr_page' => $page, 'pageper' => $page_per);
    }

    /**
     * 格式化分页信息
     * @param   array   $page
     * @param   int     $num    显示几页的链接
     */
    function _format_page(&$page, $num = 7)
    {
        $page['page_count'] = ceil($page['item_count'] / $page['pageper']);
        $mid = ceil($num / 2) - 1;
        if ($page['page_count'] <= $num)
        {
            $from = 1;
            $to   = $page['page_count'];
        }
        else
        {
            $from = $page['curr_page'] <= $mid ? 1 : $page['curr_page'] - $mid + 1;
            $to   = $from + $num - 1;
            $to > $page['page_count'] && $to = $page['page_count'];
        }

        /*
        if (preg_match('/[&|\?]?page=\w+/i', $_SERVER['REQUEST_URI']) > 0)
        {
            $url_format = preg_replace('/[&|\?]?page=\w+/i', '', $_SERVER['REQUEST_URI']);
        }
        else
        {
            $url_format = $_SERVER['REQUEST_URI'];
        }
        */

        /* 生成app=goods&act=view之类的URL */
		$query_string = str_replace('#38;', '', $_SERVER['QUERY_STRING']);
        if (preg_match('/[&|\?]p=\w+$/i', $query_string) > 0)
        {
            $url_format = preg_replace('/[&|\?]p=\w+$/i', '', $query_string);
        }
        else
        {
            $url_format = $query_string;
        }

        $page['page_links'] = array();
        $first_url = url($url_format);
        for ($i = $from; $i <= $to; $i++)
        {
            $page['page_links'][$i] = url("{$url_format}&p={$i}");
        }
        $page['prev_link'] = $page['curr_page'] > $from ? url("{$url_format}&p=" . ($page['curr_page'] - 1)) : "";
        $page['next_link'] = $page['curr_page'] < $to ? url("{$url_format}&p=" . ($page['curr_page'] + 1)) : "";
		$page['last_link'] = url("{$url_format}&p=" . ($page['page_count']));
		$page['first_link'] = $first_url;
		$page['query_string'] = $first_url;
    }

    /**
     *    使用编辑器
     *
     *    @author    Garbin
     *    @param     array $params
     *    @return    string
     */
    function _build_editor($params = array())
    {
        $name = isset($params['name']) ?  $params['name'] : null;
        $theme = isset($params['theme']) ?  $params['theme'] : 'normal';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $if_media = false;
        $visit = $this->visitor->get('manage_store');
        $store_id = isset($visit) ? intval($visit) : 0;
        $privs = $this->visitor->get('privs');
        if (!empty($privs))
        {
            if ($privs == 'all')
            {
                $if_media = true;
            }
            else
            {
                $privs_array = explode(',', $privs);
                if (in_array('article|all', $privs_array))
                {
                    $if_media = true;
                }
            }
        }
        if (!empty($store_id) && !$if_media)
        {
            $store_mod =& m('store');
            $store = $store_mod->get_info($store_id);
            $sgrade_mod =& m('sgrade') ;
            $sgrade = $sgrade_mod->get_info($store['sgrade']);
            $functions = explode(',', $sgrade['functions']);
            if (in_array('editor_multimedia', $functions))
            {
                $if_media = true;
            }
        }

        $include_js = $ext_js ? '<script type="text/javascript" src="{lib file="tiny_mce/tiny_mce.js"}"></script>' : '';

        /* 指定哪个(些)textarea需要编辑器 */
        if ($name === null)
        {
            $mode = 'mode:"textareas",';
        }
        else
        {
            $mode = 'mode:"exact",elements:"' . $name . '",';
        }

        /* 指定使用哪种主题 */
        $themes = array(
            'normal'    =>  'plugins:"inlinepopups,preview,fullscreen,paste'.($if_media ? ',media' : '' ).'",
            theme:"advanced",
            theme_advanced_buttons1:"code,fullscreen,preview,removeformat,|,bold,italic,underline,strikethrough,|," +
                "formatselect,fontsizeselect,|,forecolor,backcolor",
            theme_advanced_buttons2:"bullist,numlist,|,outdent,indent,blockquote,|,justifyleft,justifycenter," +
                "justifyright,justifyfull,|,link,unlink,charmap,image,|,pastetext,pasteword,|,undo,redo,|,media",
            theme_advanced_buttons3 : "",',
            'simple'    =>  'theme:"simple",',
        );
        switch ($theme)
        {
            case 'simple':
                $theme_config = $themes['simple'];
            break;
            case 'normal':
                $theme_config = $themes['normal'];
            break;
            default:
                $theme_config = $themes['normal'];
            break;
        }

        /* 输出 */
        $str = <<<EOT
$include_js
<script type="text/javascript">
    tinyMCE.init({
        {$mode}
        {$theme_config}
        relative_urls : false,
        remove_script_host : false,
        theme_advanced_toolbar_location:"top",
        theme_advanced_toolbar_align:"left"
});
</script>
EOT;

        return $this->_view->fetch('str:' . $str);;
    }

    /**
     *    使用swfupload
     *
     *    @author    Hyber
     *    @param     array $params
     *    @return    string
     */
    function _build_upload($params = array())
    {
        $belong = isset($params['belong']) ? $params['belong'] : 0; //上传文件所属模型
        $item_id = isset($params['item_id']) ? $params['item_id']: 0; //所属模型的ID
        $file_size_limit = isset($params['file_size_limit']) ? $params['file_size_limit']: '2 MB'; //默认最大2M
        $button_text = '上传'; //上传按钮文本
        $image_file_type = isset($params['image_file_type']) ? $params['image_file_type'] : IMAGE_FILE_TYPE;
        $upload_url = isset($params['upload_url']) ? $params['upload_url'] : 'index.php?app=swfupload';
        $button_id = isset($params['button_id']) ? $params['button_id'] : 'spanButtonPlaceholder';
        $progress_id = isset($params['progress_id']) ? $params['progress_id'] : 'divFileProgressContainer';
        $if_multirow = isset($params['if_multirow']) ? $params['if_multirow'] : 0;
        $define = isset($params['obj']) ? 'var ' . $params['obj'] . ';' : '';
        $assign = isset($params['obj']) ? $params['obj'] . ' = ' : '';
        $ext_js = isset($params['ext_js']) ? $params['ext_js'] : true;
        $ext_css = isset($params['ext_css']) ? $params['ext_css'] : true;

        $include_js = $ext_js ? '<script type="text/javascript" charset="utf-8" src="{lib file="swfupload/swfupload.js"}"></script>
<script type="text/javascript" charset="utf-8" src="{lib file="swfupload/js/handlers.js"}"></script>' : '';
        $include_css = $ext_css ? '<link type="text/css" rel="stylesheet" href="{lib file="swfupload/css/default.css"}"/>' : '';
        /* 允许类型 */
        $file_types = '';
        $image_file_type = explode('|', $image_file_type);
        foreach ($image_file_type as $type)
        {
            $file_types .=  '*.' . $type . ';';
        }
        $file_types = trim($file_types, ';');
        $str = <<<EOT

{$include_js}
{$include_css}
<script type="text/javascript">
{$define}
$(function(){
    {$assign}new SWFUpload({
        upload_url: "{$upload_url}",
        flash_url: "{lib file="swfupload/swfupload.swf"}",
        post_params: {
            "HTTP_USER_AGENT":"{$_SERVER['HTTP_USER_AGENT']}",
            'belong': {$belong},
            'item_id': {$item_id},
            'ajax': 1
        },
        file_size_limit: "{$file_size_limit}",
        file_types: "{$file_types}",
        custom_settings: {
            upload_target: "{$progress_id}",
            if_multirow: {$if_multirow}
        },

        // Button Settings
        button_image_url: "{lib file="swfupload/images/SmallSpyGlassWithTransperancy_17x18.png"}",
        button_width: 86,
        button_height: 18,
        button_text: '<span class="button">{$button_text}</span>',
        button_text_style: '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; font-weight: bold; color: #3F3D3E; } .buttonSmall { font-size: 10pt; }',
        button_text_top_padding: 0,
        button_text_left_padding: 18,
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        button_cursor: SWFUpload.CURSOR.HAND,

        // The event handler functions are defined in handlers.js
        file_queue_error_handler: fileQueueError,
        file_dialog_complete_handler: fileDialogComplete,
        upload_progress_handler: uploadProgress,
        upload_error_handler: uploadError,
        upload_success_handler: uploadSuccess,
        upload_complete_handler: uploadComplete,
        button_placeholder_id: "{$button_id}",
        file_queued_handler : fileQueued
    });
});
</script>
EOT;
        return $this->_view->fetch('str:' . $str);
    }

    /**
     *    发送邮件
     *
     *    @author    Garbin
     *    @param     mixed  $to
     *    @param     string $subject
     *    @param     string $message
     *    @param     int    $priority
     *    @return    void
     */
    function _mailto($to, $subject, $message, $priority = MAIL_PRIORITY_LOW)
    {
        /* 加入邮件队列，并通知需要发送 */
        $model_mailqueue =& m('mailqueue');
        $mails = array();
        $to_emails = is_array($to) ? $to : array($to);
        foreach ($to_emails as $_to)
        {
            $mails[] = array(
                'mail_to'       => $_to,
                'mail_encoding' => CHARSET,
                'mail_subject'  => $subject,
                'mail_body'     => $message,
                'priority'      => $priority,
                'add_time'      => TIME,
            );
        }

        $model_mailqueue->add($mails);

        /* 默认采用异步发送邮件，这样可以解决响应缓慢的问题 */
        $this->_sendmail();
    }

    /**
     *    发送邮件
     *
     *    @author    Garbin
     *    @param     bool $is_sync
     *    @return    void
     */
    function _sendmail($is_sync = false)
    {
        if (!$is_sync)
        {
            /* 采用异步方式发送邮件，与模板引擎配合达到目的 */
            $_SESSION['ASYNC_SENDMAIL'] = true;

            return true;
        }
        else
        {
            /* 同步发送邮件，将异步发送的命令去掉 */
            unset($_SESSION['ASYNC_SENDMAIL']);
            $model_mailqueue =& m('mailqueue');

            return $model_mailqueue->send(5);
        }
    }

    /**
     *     获取异步发送邮件代码
     *
     *    @author    Garbin
     *    @return    string
     */
    function _async_sendmail()
    {
        $script = '';
        if (isset($_SESSION['ASYNC_SENDMAIL']) && $_SESSION['ASYNC_SENDMAIL'])
        {
            /* 需要异步发送 */
            $async_sendmail = SITE_URL . '/index.php?app=sendmail';
            $script = '<script type="text/javascript">sendmail("' . $async_sendmail . '");</script>';
        }

        return $script;
    }

    /**
     *    计划任务守护进程
     *
     *    @author    Garbin
     *    @return    void
     */
    function _run_cron()
    {
/*
        register_shutdown_function(create_function('', '
            if (!is_file(ROOT_PATH . "/data/tasks.inc.php"))
            {
                $default_tasks = array(
                    "cleanup" =>
                        array (
                            "cycle" => "custom",
                            "interval" => 3600,     //每一个小时执行一次清理
                        ),
                );
                file_put_contents(ROOT_PATH . "/data/tasks.inc.php", "<?php\r\n\r\nreturn " . var_export($default_tasks, true) . ";\r\n\r\n", LOCK_EX);
            }
            import("cron.lib");
            $cron = new Crond(array(
                "task_list" => ROOT_PATH . "/data/tasks.inc.php",
                "task_path" => ROOT_PATH . "/includes/tasks",
                "lock_file" => ROOT_PATH . "/data/crond.lock"
            ));                     //计划任务实例
            $cron->execute();       //执行
        '));
 */
    }

    /**
     * 发送Feed
     *
     * @author Garbin
     * @param
     * @return void
     **/
    function send_feed($event, $data)
    {
        $ms = &ms();
        if (!$ms->feed->feed_enabled())
        {
            return;
        }

        $feed_config = $this->visitor->get('feed_config');
        $feed_config = empty($feed_config) ? Conf::get('default_feed_config') : unserialize($feed_config);
        if (!$feed_config[$event])
        {
            return;
        }

        $ms->feed->add($event, $data);
    }

}

/**
 *    访问者基础类，集合了当前访问用户的操作
 *
 *    @author    Garbin
 *    @return    void
 */
class BaseVisitor extends Object
{
    var $has_login = false;
    var $info      = null;
    var $privilege = null;
    var $_info_key = '';
    function __construct()
    {
        $this->BaseVisitor();
    }
    function BaseVisitor()
    {
        if (!empty($_SESSION[$this->_info_key]['user_id']))
        {
            $this->info         = $_SESSION[$this->_info_key];
            $this->has_login    = true;
        }
        else
        {
            $this->info         = array(
                'user_id'   => 0,
                'user_name' => '访客',
            );
            $this->has_login    = false;
        }
		if (!empty($_SESSION[$this->_info_key]['ou_id'])) {
            $this->info         = $_SESSION[$this->_info_key];
			$this->has_out = true;
		} else {
			$this->has_out = false;
		}
    }
    function assign($user_info)
    {
        $_SESSION[$this->_info_key]   =   $user_info;
		if ($user_info['user_id']) {
			$this->has_login = true;
            $this->info   = $_SESSION[$this->_info_key];
		}
    }

    /**
     *    获取当前用户的指定信息
     *
     *    @author    Garbin
     *    @param     string $key  指定用户信息
     *    @return    string  如果值是字符串的话
     *               array   如果是数组的话
     */
    function get($key = null)
    {
        $info = null;

        if (empty($key))
        {
            /* 未指定key，则返回当前用户的所有信息：基础信息＋详细信息 */
            $info = array_merge((array)$this->info, (array)$this->get_detail());
        }
        else
        {
            /* 指定了key，则返回指定的信息 */
            if (isset($this->info[$key]))
            {
                /* 优先查找基础数据 */
                $info = $this->info[$key];
            }
            else
            {
                /* 若基础数据中没有，则查询详细数据 */
                $detail = $this->get_detail();
                $info = isset($detail[$key]) ? $detail[$key] : null;
            }
        }

        return $info;
    }

    /**
     *    登出
     *
     *    @author    Garbin
     *    @return    void
     */
    function logout()
    {
		setcookie('sooker_uid', '', TIME - 86400*7, '/', COOKIE_DOMAIN);
		setcookie('sooker_auth', '',TIME - 86400*7, '/', COOKIE_DOMAIN);
		setcookie('sooker_username', '', TIME - 86400*7, '/', COOKIE_DOMAIN);
		setcookie('sooker_store', '', TIME - 86400*7, '/', COOKIE_DOMAIN);
        unset($_SESSION[$this->_info_key]);
    }
    function i_can($event, $privileges = array())
    {
        $fun_name = 'check_' . $event;

        return $this->$fun_name($privileges);
    }

    function check_do_action($privileges)
    {

        if ($privileges == 'all')
        {
            // 拥有所有权限
            return true;
        }
        else
        {
            /* 查看当前操作是否在白名单中，如果在，则允许，否则不允许 */
            $privs = explode(',', $privileges);
			$setting = include(ROOT_PATH . '/admin/includes/priv.inc.php');
			$appinfo = $setting[APP];
			if (!$appinfo) return true;//没有配置的默认不限制权限
			if (in_array($appinfo['group'] . '|all', $privs)){//拥有全部权限的账号
				return true;
			}
			if (!$appinfo['privs'] || !in_array(ACT, $appinfo['privs'])) {//未加限制的功能
				return true;
			}
			if ( in_array($appinfo['group'] . '|'. ACT, $privs)) {
                return true;
            }

            return false;
        }
    }
     /**
     *    获取用户角色-->权限
     *
     *    @author    zhaowei
     *    @return    string
     */
    function get_role_privs()
    {
        $info = null;

        $_admin_role_mod = & m('admin_role');
        $role_info = $_admin_role_mod->find(array(
	            'conditions' => 'userid='.$this->info['user_id'],
	        ));
	    if ($role_info){
	    	$arr = array();
	    	foreach ($role_info as $value){
	    		if ($value['roleid'] == 1){
	    			return 'all';//roleid=1时为超级管理员
	    		}
	    		$arr[] = $value['roleid'];
	    	}
	    	$roleids = implode(',',$arr);
	    	$_role_mod = & m('role');
	        $privs = $_role_mod->find(array(
	                'fields' => 'privs',
		            'conditions' => 'disabled=0 AND roleid IN ('.$roleids.')',
		        ));
		    if ($privs){
		    	$arr = array();
		    	foreach ($privs as $val){
		    		$arr[] = $val['privs'];
		    	}
		    	$info = implode(',',$arr);
		    }
	    }
        return $info;
    }

     /**
     *    获取用户-->数据权限
     *
     *    @author    zhaowei
     *    @return    string
     */
    function get_data_priv()
    {
        $info = null;

        $_data_priv_mod = & m('data_priv');
        $info = $_data_priv_mod->get($this->info['user_id']);
        if ($info && $info['privs']){
            $info['privs'] = explode(',', $info['privs']);
        }
        return $info;
    }

    /**
     *    获取用户角色
     *
     *    @author    zhaowei
     *    @return    string
     */
    function get_role($user_id)
    {
        $role_info = null;

        $_admin_role_mod = & m('admin_role');
        $role_info = $_admin_role_mod->find(array(
	            'conditions' => 'userid='.$user_id.' and region=1',
	        ));

        return $role_info;
    }

}
?>
