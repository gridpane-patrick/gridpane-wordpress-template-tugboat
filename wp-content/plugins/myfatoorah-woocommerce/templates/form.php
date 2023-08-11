<?php

if (!isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], get_permalink(wc_get_page_id('checkout'))) === false) {
    return;
}

if (empty($this->gateways['form']) && isset($this->appleRegistered) && !$this->appleRegistered) {
    return;
}

if (!empty($this->appleRegistered)) {
    include_once('appleButton.php');
}

if (!empty($this->gateways['form'])) {
    include_once('embedForm.php');
}