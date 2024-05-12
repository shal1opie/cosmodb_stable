<?php
if(file_exists('../init/database_settings.json')) {
$database_settings = json_decode(file_get_contents('../init/database_settings.json'), JSON_OBJECT_AS_ARRAY);
	foreach ($database_settings as $key => $value) {
		switch ($key) {
			case 'servername':
				$servername = $value;
				break;
			
			case 'username':
				$username = $value;
				break;
			
			case 'password':
				$password_host = $value;
				break;
			
			case 'dbname':
				$dbname = $value;
				break;
			case 'db_backup':
				$db_backup = $value;
				break;
		}
	}
} else {
	echo 'Нет файла database_settings.json';
}
?>