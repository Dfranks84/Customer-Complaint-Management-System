<?php

session_start();

require_once('util/security.php');

// Log the current user out.
Security::logout();