<?php
session_start();

require_once('boot.php');

try {
    $conn = new PDO("mysql:host=$servername", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    // $conn -> exec("set names utf8");
    // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbExists = $conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'")->fetchColumn();
    $_SESSION['db_maded'] = false;
    if (!$dbExists) {
        $_SESSION['db_exists'] = false;
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
        $conn->exec($sql);

        $conn = null;
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = file_get_contents('../back_up/cosmodb.sql');
        $conn->exec($sql);

        $_SESSION['db_maded'] = true;
    } else {$_SESSION['db_exists'] = true;}
    if ($_SESSION['db_maded']) {
        unset($_SESSION['db_maded']);
        $_SESSION['db_exists'] = true;
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
        session_start(); // Start a new session
        header("Location: ../main/cosmodb.php?auto_reg_form=Регистрация");
        exit();
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null;
?>