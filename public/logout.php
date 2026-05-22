<?php
require_once __DIR__ . '/../includes/functions.php';
logout_user();
flash('success', 'You have been logged out.');
redirect(SITE_URL . '/index.php');
