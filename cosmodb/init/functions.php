<?php
// require_once('db_connect.php');
session_start();

require_once('boot.php');
require_once('globals.php');
require_once('db_connect.php');

function database_eror () {
    global $db_exists;
    switch ($db_exists) {
        case null:
            $error_msg = "<article class=\"container-fluid\"><h1 align=\"center\">
            <strong class=\"pico-color-indigo-300\"><i>506</i></strong><hr />Система не настроена<br></h1>Система требует настройки. <a href=\"../init/db_connect.php\">Настроить</a>
            </article>";
            return $error_msg;
            break;
        default:
            $error_msg = "";
            return $error_msg;
            break;
    }
}

require_once('../functions/header.php');
require_once('../functions/view.php');
require_once('../functions/forms.php');
require_once('../functions/sign_in_n_up.php');
require_once('../functions/article.php');
require_once('../functions/user_profile.php');

function reset_session () {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    session_start(); // Start a new session
}



function main () {
    global $table, $db_exists, $users_set, $user_loged_in, $user_role, $article, $user_admin_exists, $tables, $column_to_search;
    // var_dump($_SESSION);
    // echo "<br>".$_SESSION['role']."<br>";
    switch (true) {
        case (isset($_REQUEST['action'])&&$db_exists&&$users_set&&$user_loged_in&&$user_role>0&&!isset($_REQUEST['logout'])&&!isset($_REQUEST['user_profile'])):
            change_table();
            $form_action = $_REQUEST['action'];
            forms($form_action);
            break;

        case (!isset($_REQUEST['action'])&&$db_exists&&$users_set&&$user_loged_in&&$user_role>1&&empty($article)&&!isset($_REQUEST['logout'])&&!isset($_REQUEST['user_profile'])): //!$_REQUEST['auto_reg_form']
            if(!in_array($_REQUEST['table'], $tables)) {
                header("Location: cosmodb.php?table=$table&column_name=id");
            }
            change_table();
            view_table($table);
            break;
        
        case (!isset($_REQUEST['action'])&&$db_exists&&$users_set&&$user_loged_in&&$user_role==1&&empty($article)&&!isset($_REQUEST['logout'])&&!isset($_REQUEST['user_profile'])): //!$_REQUEST['auto_reg_form']
            change_table();
            view_cards();
            break;

        case ($db_exists&&!$user_loged_in&&$user_role==0):
            if(isset($_REQUEST['auto_reg_form'])) {
                $auto_reg_form = $_REQUEST['auto_reg_form'];
            } else {
                $auto_reg_form = "Авторизоваться";
            }
            change_table();
            additional_set_up($auto_reg_form);
            break;

        case ($user_loged_in&&$db_exists&&$users_set&&$user_role>0&&!empty($article)&&!isset($_REQUEST['logout'])&&!isset($_REQUEST['user_profile'])):
            change_table();
            article($article);
            break;

        case (!isset($_REQUEST['action'])&&isset($_REQUEST['user_profile'])&&$db_exists&&$users_set&&$user_loged_in&&$user_role>0&&empty($article)&&!isset($_REQUEST['logout'])):
            change_table();
            user_profile();
            break;

        case (isset($_REQUEST['logout'])):
            session_unset();
            session_destroy();
            session_start();
            header ("Location: cosmodb.php?auto_reg_form=Авторизоваться");
            break;

        case (!$db_exists):
            create_cosmodb();
            database_eror();
            break;
        case (!isset($_REQUEST['action'])&&isset($_REQUEST['search'])&&$db_exists&&$users_set&&$user_loged_in&&$user_role>0&&empty($article)&&!isset($_REQUEST['logout'])&&!isset($_REQUEST['user_profile'])):
            var_dump($_REQUEST);
            break;
    }
}


?>