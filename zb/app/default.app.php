<?php

class DefaultApp extends FrontendApp{
    function index() {
        $this->display('index.html');
    }
}
