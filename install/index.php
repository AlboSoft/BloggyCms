<?php

session_start();

ob_start();

if (isset($_SESSION['install_complete']) && $_SESSION['install_complete'] === true &&
    isset($_SESSION['install_step']) && $_SESSION['install_step'] >= 4 &&
    isset($_SESSION['viewed_step4']) && $_SESSION['viewed_step4'] === true &&
    file_exists('../system/config/config.php') && 
    file_exists('../system/config/database.php')) {
    
    try {
        include '../system/config/database.php';
        $testDb = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $testDb = null;
        header('Location: ../');
        exit;
    } catch (Exception $e) {

    }
}

$configsExistButNotComplete = false;
if ((!isset($_SESSION['install_complete']) || $_SESSION['install_complete'] !== true) &&
    file_exists('../system/config/config.php') && 
    file_exists('../system/config/database.php')) {
    $configsExistButNotComplete = true;
}

if (!isset($_SESSION['install_step'])) {
    $_SESSION['install_step'] = 1;
}

if (isset($_GET['restart'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$step = $_SESSION['install_step'];
$stepFile = "step{$step}.php";

if (!file_exists($stepFile)) {
    $stepFile = "step1.php";
    $_SESSION['install_step'] = 1;
}

include 'templates/layout.php';
?>