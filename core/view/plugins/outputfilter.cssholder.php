<?php

function smarty_outputfilter_cssholder($output, &$smarty) {
	return preg_replace('/<!--tpl:cssholder-->/', Template::instance()->cssHolder(), $output, 1);
}
