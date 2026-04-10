<?php
require_once 'includes/config.php';
$userClass = new User();
$userClass->logout();
redirect('index.php');
