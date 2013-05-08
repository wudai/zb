<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {combo} block plugin
 *
 * Type:     block<br>
 * Name:     combo<br>
 * Purpose:  combo css/js url

 * @author yancan <yancan at aspire-tech dot com>
 * @param array
 * @param Smarty
 */
function smarty_block_combo($params, $content, &$smarty)
{
    $urls = explode("\n", trim($content));
    //$urls = array_filter($urls, create_function('$a', 'return !empty($a);'));
    $urls = array_map('trim', $urls);
    $urls = Asset::getComboUrl($urls);

    return $urls;
}

/* vim: set expandtab: */

?>
