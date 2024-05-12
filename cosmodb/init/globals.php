<?php
if(isset($_REQUEST['id_change_form'])) {
    $id_change_form = $_REQUEST['id_change_form'];
} else {
    $id_change_form = "";
}

if(isset($_REQUEST['article'])) {
    $article = htmlspecialchars($_REQUEST['article']);
} else {
    $article = null;
}

if(isset($_SERVER['HTTP_REFERER'])) {
  $refer = $_SERVER['HTTP_REFERER']; 
   }
else
{
  $refer = "http://localhost/cosmodb/main/cosmodb.php";
}


if(isset($_SESSION['user_loged_in'])) {
  $user_loged_in = $_SESSION['user_loged_in']; 
   }
else
{
  $user_loged_in = false;
}

if(isset($_SESSION['role'])&&isset($_SESSION['user_name'])) {
  $user_role = $_SESSION['role'];
  $user_name = $_SESSION['user_name'];
} else {
  $user_role = 0;
  $user_name = "";
}

$tables = [
    'users',
    'roles',
    'app_types',
    'people',
    'space_achiv',
];

switch ($user_role) {
    case 1:
        unset($tables[0]);
        unset($tables[1]);
        unset($tables[2]);
        unset($tables[3]);
        break;
    case 2:
        unset($tables[2]);
        unset($tables[3]);
        break;
    case 3:
        unset($tables[0]);
        unset($tables[1]);
        break;
}

if(isset($_REQUEST['table'])&&in_array($_REQUEST['table'], $tables)) {
    $table = htmlspecialchars($_REQUEST['table']);
} elseif ($user_role == 2) {
    $table = 'users';
} else {
    $table = 'space_achiv';
}

$insert_query = "INSERT INTO `$table` ";
$select_query = " FROM $table";
$order_by = " ORDER BY `id`";
switch ($table) {
    case 'app_types':
        $select_query = "SELECT `id` AS `#`, `type` AS `Тип`".$select_query.$order_by;

        if(isset($_REQUEST['type'])&&!is_array($_REQUEST['type'])) {
            $type = $_REQUEST['type'];
            $insert_query = $insert_query."(`id`, `type`) VALUES ('$id_change_form', '$type')";
        } else {
        $type = "";
        }

        break;

    case 'people':
        $select_query = "SELECT `id` AS `#`, `initials` AS `Инициалы`, 
        CONCAT(`surename`,' ',`name`,' ',`last_name`) AS `ФИО`".$select_query.$order_by;
        
        if(isset($_REQUEST['initials'])&&isset($_REQUEST['surename'])&&isset($_REQUEST['name'])&&isset($_REQUEST['last_name'])&&!is_array($_REQUEST['initials'])&&!is_array($_REQUEST['surename'])&&!is_array($_REQUEST['name'])&&!is_array($_REQUEST['last_name'])) {
            $initials = $_REQUEST['initials'];
            $surename = $_REQUEST['surename'];
            $name = $_REQUEST['name'];
            $last_name = $_REQUEST['last_name'];
            $insert_query = $insert_query."(`id`,`initials`,`surename`,`name`,`last_name`) 
            VALUES ('$id_change_form', '$initials', '$surename', '$name', '$last_name')";
        } else {
            $initials = $surename = $name = $last_name = "";
        }

        break;
        
    case 'roles':
        $select_query = "SELECT `id` AS `#`, `role_name` AS `Роль`".$select_query.$order_by;

        if(isset($_REQUEST['role_name'])&&!is_array($_REQUEST['role_name'])) {
            $role_name = $_REQUEST['role_name'];
            $insert_query = $insert_query."(`id`,`role_name`) VALUES ('$id_change_form','$role_name')";
        } else {
        $role_name = "";
        }

        break;

    case 'space_achiv':
        $order_by = " ORDER BY space_achiv.id";
        $select_query = "SELECT space_achiv.id AS `#`, `country` AS `Страна`, people.initials AS `Причастное лицо`,
        `achiv_name` AS `Наименование`, `date` AS `Дата`, `text` AS `Текст`, app_types.type AS 'Тип аппарата'".$select_query."
        INNER JOIN people ON space_achiv.people = people.id
        INNER JOIN app_types ON space_achiv.type_app = app_types.id".$order_by;

        if(isset($_REQUEST['country'])&&isset($_REQUEST['people'])&&isset($_REQUEST['achiv_name'])&&isset($_REQUEST['date'])&&isset($_REQUEST['text'])&&isset($_REQUEST['type_app'])&&!is_array($_REQUEST['country'])&&!is_array($_REQUEST['people'])&&!is_array($_REQUEST['achiv_name'])&&!is_array($_REQUEST['date'])&&!is_array($_REQUEST['text'])&&!is_array($_REQUEST['type_app'])) {
            $country = $_REQUEST['country'];
            $people = $_REQUEST['people'];
            $achiv_name = $_REQUEST['achiv_name'];
            $date = $_REQUEST['date'];
            $text = $_REQUEST['text'];
            $type_app = $_REQUEST['type_app'];
            $insert_query = $insert_query."(`id`, `country`, `people`, `achiv_name`, `date`, `text`, `type_app`) 
        VALUES ('$id_change_form','$country',(SELECT `id` FROM `people` WHERE `id`='$people'),'$achiv_name','$date','$text',(SELECT `id` FROM `app_types` WHERE `id`='$type_app'))";
        } else {
            $country = $people = $achiv_name = $date = $text = $type_app = "";
        }
        
        
        break;

    case 'users':
        $order_by = " ORDER BY users.id";
        $select_query = "SELECT users.id AS `#`, `nick_name` AS `Логин`, roles.role_name AS `Роль пользователя`, 
        r1.role_name AS `Запрашиваемая роль`, `email` AS `Электронная почта`".$select_query."
        INNER JOIN roles ON users.role = roles.id
        LEFT JOIN roles r1 ON users.role_raise = r1.id".$order_by;

        if(isset($_REQUEST['nick_name'])&&isset($_REQUEST['role'])&&isset($_REQUEST['email'])&&isset($_REQUEST['password'])&&!is_array($_REQUEST['nick_name'])&&!is_array($_REQUEST['role'])&&!is_array($_REQUEST['email'])&&!is_array($_REQUEST['password'])) {
            $nick_name = $_REQUEST['nick_name'];
            $role = $_REQUEST['role'];
            $email = $_REQUEST['email'];
            $password = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
            $insert_query = $insert_query."(`id`, `nick_name`, `role`, `role_raise`, `email`, `password`) 
            VALUES ('$id_change_form','$nick_name', (SELECT `id` FROM `roles` WHERE `id`='$role'),
            (SELECT `id` FROM `roles` WHERE `id`='$role'),'$email','$password')";
        } else {
            $nick_name = $role = $role_raise = $email = $password = "";
        }


        break;

    default:
        header("Location: {$refer}");
        break;
}

try {
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password_host, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$db_exists = $conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'")->fetchColumn();
$result = $conn->query("SELECT id FROM users");
while ($row = $result->fetch()) {
    if (empty($row['id'])) {
        $users_set = null;
    } else {
        $users_set = true;
    }
}
$result = $conn->query("SELECT `role` FROM users");
while ($row = $result->fetch()) {
    if (!empty($users_set)&&$row['role']=="4") {
        $user_admin_exists = true;
    } else {
        $user_admin_exists = false;
    }
}
} catch(PDOException $e) {
    echo database_eror();
}
?>