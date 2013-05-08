<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {combojs} block plugin
 *
 * Type:     block<br>
 * Name:     combojs<br>
 * Purpose:  combo js 

 * @author yancan <yancan at aspire-tech dot com>
 * @param array
 * @param Smarty
 */
function smarty_block_combojs($params, $content, &$smarty)
{
    if(empty($content))
        return '';
    $urls = explode("\n", trim($content));
    $urls = array_map('trim', $urls);
    return Template::jsToString($urls);
}

