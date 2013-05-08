<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {combocss} block plugin
 *
 * Type:     block<br>
 * Name:     combocss<br>
 * Purpose:  combo css

 * @author yancan <yancan at aspire-tech dot com>
 * @param array
 * @param Smarty
 */
function smarty_block_combocss($params, $content, &$smarty)
{
    if(empty($content))
        return '';
    $urls = explode("\n", trim($content));
    $urls = array_map('trim', $urls);
    return Template::cssToString($urls);
}

/* vim: set expandtab: */
