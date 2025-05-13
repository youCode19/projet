<?php

// bootstrap.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_init.php';


// Initialisation de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autres initialisations...