<?php

/**
 *    控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class BaseApp extends Object
{
    /* 建立到视图的链接 */
    var $_view = null;

    function __construct()
    {
        $this->BaseApp();
    }

    function BaseApp()
    {
        /* 初始化Session */
        $this->_init_session();
    }

    /**
     *    运行指定的动作
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function do_action($action)
    {
        if ($action && $action{0} != '_' && method_exists($this, $action))
        {
            $this->_curr_action  = $action;
            $this->_run_action();            //运行动作
        }
        else
        {
            exit('missing_action');
        }
    }
    function index()
    {
        echo 'Hello! ECMall';
    }

    /**
     *    给视图传递变量
     *
     *    @author    Garbin
     *    @param     string $k
     *    @param     mixed  $v
     *    @return    void
     */
    function assign($k, $v = null)
    {
        if (is_array($k))
        {
            $args  = func_get_args();
            foreach ($args as $arg)     //遍历参数
            {
                foreach ($arg as $key => $value)    //遍历数据并传给视图
                {
                    $this->_view->assign($key, $value);
                }
            }
        }
        else
        {
            $this->_view->assign($k, $v);
        }
    }

    /**
     *    显示视图
     *
     *    @author    Garbin
     *    @param     string $n
     *    @return    void
     */
    function display($n)
    {
        $this->_view->display($n);
    }

    function fetch($n)
    {
        return $this->_view->fetch($n);
    }

    /**
     *    初始化视图连接
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _init_view()
    {
        if ($this->_view === null)
        {
            $this->_view =& v();
            $this->_config_view();  //配置
        }
    }

    /**
     *    配置视图
     *
     *    @author    Garbin
     *    @return    void
     */
    function _config_view()
    {
        # code...
    }

    /**
     *    运行动作
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function _run_action()
    {
        $action = $this->_curr_action;
        $this->$action();
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
		session_start();
    }

    /**
     *  控制器结束运行后执行
     *
     *  @author Garbin
     *  @return void
     */
    function destruct()
    {
    }

}
