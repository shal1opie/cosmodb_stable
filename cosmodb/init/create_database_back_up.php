<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cosmodb";

$table_name = [
    0 => 'people',
    1 => 'app_types',
    2 => 'roles',
    3 => 'space_achiv',
    4 => 'users',
];

$dir = "../back_up/cosmodb_back_up_" . date("d-m-Y_H-i-s") . ".sql";

$super_end = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

    foreach($table_name as $key => $value) {
        $sql = "SHOW CREATE TABLE $value";
        $result = $conn->query($sql);
        while ($row = $result->fetch()) {
        $code = $row['Create Table'];
        $exc = str_replace('DEFAULT CHARSET=cp1251', 'DEFAULT CHARSET=utf8mb4', $code);
        $exc = str_replace('COLLATE=cp1251_general_ci', 'COLLATE=utf8mb4_general_ci', $exc);
        $exc = $exc.";";
        
        $sql = "SELECT * FROM $value";
        $result = $conn->query($sql);
        $end = "";
        while ($row = $result->fetch()) {
            switch($value) {
                case 'people':
                    $columns = "INSERT INTO `people` (`id`, `initials`, `surename`, `name`, `last_name`) VALUES \n";
                    $inserted_values = "(".$row['id'].", '".$row['initials']."', '".$row['surename']."', '".$row['name']."', '".$row['last_name']."'),\n";
                    $end.=$inserted_values;
                    break;
                case 'app_types':
                    $columns = "INSERT INTO `app_types` (`id`, `type`) VALUES \n";
                    $inserted_values = "(".$row['id'].", '".$row['type']."'),\n";
                    $end.=$inserted_values;
                    break;
                case 'roles':
                    $columns = "INSERT INTO `roles` (`id`, `role_name`) VALUES \n";
                    $inserted_values = "(".$row['id'].", '".$row['role_name']."'),\n";
                    $end.=$inserted_values;
                    break;
                case 'space_achiv':
                    $columns = "INSERT INTO `space_achiv` (`id`, `country`, `people`, `achiv_name`, `date`, `text`, `type_app`) VALUES \n";
                    $inserted_values = "(".$row['id'].", '".$row['country']."', '".$row['people']."', '".$row['achiv_name']."', '".$row['date']."', '".$row['text']."', '".$row['type_app']."'),\n";
                    $end.=$inserted_values;
                    break;
                case 'users':
                    $columns = "INSERT INTO `users` (`id`, `nick_name`, `role`, `role_raise`, `email`, `password`) VALUES \n";
                    $inserted_values = "(".$row['id'].", '".$row['nick_name']."', '".$row['role']."','".$row['role_raise']."', '".$row['email']."', '".$row['password']."'),\n";
                    $end.=$inserted_values;
                    break;
            }
        }
        $super_end.=$exc."\n";
        $end = $columns.substr($end, 0, -2).";";
        $super_end .= $end."\n\n";
        
    }
    }
    header("Location: ../main/cosmodb.php");
} catch(PDOException $e) {
    echo $e;
}

file_put_contents($dir, $super_end);

?>