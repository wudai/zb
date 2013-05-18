<?php

class BillApp extends FrontendApp {
	function addsingle() {
		if (!IS_POST) {
			$this->display('bill/addsingle.html');
		} else {
		}
	}
}
