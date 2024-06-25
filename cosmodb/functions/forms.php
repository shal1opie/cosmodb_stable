<?php
function add_log ($log_data) {
    $json_log_dir = '../init/logs.json';
    if(file_exists($json_log_dir)) {
        $log_data_end = json_decode(file_get_contents($json_log_dir), true);
        $log_data_end[] = $log_data;
    } else {
        $log_data_end[] = $log_data;
    }
    $log_data = json_encode($log_data_end, JSON_UNESCAPED_UNICODE |JSON_PRETTY_PRINT);       
    file_put_contents($json_log_dir, $log_data);
}

function edit () {
    global $conn, $table, $id_change_form, $insert_query, $action, $user_role, $user_name, $column_to_search;
    $log_data = [
        'user' => $user_name,
        'role' => $user_role,
        'table' => $table,
        'action' => $action,
        'time' => date('d.m.Y H:i:s')
    ];

    switch (true) {
        case (empty($id_change_form)&&$table=='space_achiv'):
            ?>
            <legend class="h1 text-center text-primary mt-3">Добавление космического достижения</legend>
            <input type="hidden" name="table" value="space_achiv"/>
            <div class="row mb-3 px-3">
            <label class="form-label" for="achiv_name">Наименование</label>
            <input
            class="form-control"
            id="achiv_name" 
            type="text" 
            name="achiv_name" 
            placeholder="Наименование"/>
            </div>
            <div class="row mb-3 px-3">
            <label class="form-label" for="country">Страна</label>
            <select 
            class="form-select" 
            id = "country"
            name="country">
                <option value="1">СССР</option>
                <option value="2">Россия</option>
            </select>
            </div>
            <div class="row mb-3 px-3">
            <label class="form-label" for="people">Знаковая личность</label>
            <select 
            class="form-select"
            id = "people"
            name="people">
            <?php
            $sql = "SELECT `id`, `initials` FROM `people`";
            $result = $conn -> query($sql);
            while($row = $result -> fetch()) {?>
                <option value="<?=$row['id']?>"><?=$row['initials']?></option><?php
            }?>
            </select>
            </div>
            <div class="row mb-3 px-3">
            <label class="form-label" for="date">Дата</label>
            <input 
            class="form-control"
            id = "date"
            type="date" 
            name="date"/>
            </div>
            <div class="row mb-3 px-3">
            <label class="form-label" for="text">Текст</label>
            <textarea
            class="form-control"
            id = "text"
            name="text"
            rows="10"></textarea>
            </div>
            <div class="row mb-3 px-3">
            <label class="form-label" for="type_app">Тип аппарата</label>
            <select 
            class="form-select"
            id = "type_app"
            name="type_app">
            <?php
            $sql = "SELECT `id`, `type` FROM `app_types`";
            $result = $conn -> query($sql);
            while($row = $result -> fetch()) {?>
                <option value="<?=$row['id']?>"><?=$row['type']?></option><?php
            }
            ?></select>
            </div>
            <?php
            if(!empty($_REQUEST['achiv_name'])&&!empty($_REQUEST['country'])&&!empty($_REQUEST['date'])&&!empty($_REQUEST['text'])&&!empty($_REQUEST['type_app'])&&!empty($_REQUEST['people'])) {
                    try {
                        $conn -> exec($insert_query);         
                    } catch(PDOException $e) {
                        echo $e->getMessage();
                    }
                add_log($log_data);
                header("Location: cosmodb.php");
            } elseif(isset($_REQUEST['add_row'])) {?>
                    <p class="h5 text-danger mt-3">Заполните все поля</p>
            <?php
            }
            ?>
            <div class="col d-flex justify-content-center">
            <input 
            type="submit" 
            value="Добавить"
            name="add_row"
            class="btn btn-success mb-3 btn-lg" />
            </div>
            <?php
            break;
        case (empty($id_change_form)&&$table=='users'):
            ?>
            <legend class="h1 text-center text-primary mt-3">Добавление пользователя</legend>
            <input type="hidden" name="table" value="users"/>
            <div class="row mb-3 px-3">
                <label class="form-label" for="nick_name">Имя пользователя</label>
                    <input 
                    class="form-control"
                    id = "nick_name"
                    type="text" 
                    name="nick_name" 
                    placeholder="Имя пользователя"/>
            </div>
            <?php
            $sql = "SELECT `id`, `role_name` FROM `roles`";
            $result = $conn -> query($sql);
            $ss = 0;
            ?>
            <div class="row mb-3 px-3">
                <label class="form-label" for="role">Роль</label>
                    <select 
                    class = "form-select"
                    id = "role"
                    name="role">
            <?php
            while($row = $result -> fetch()) {
                ?><option value ="<?=$row['id']?>"><?=$row['role_name']?></option><?php
                $s[$ss] = "<option value =\"".$row['id']."\">".$row['role_name']."</option>";
                $ss++;
            }
            $max = $ss+1;
            ?></select>
            </div>
            <div class="row mb-3 px-3">
                <label class="form-label" for="email">Эл почта</label>
                    <input 
                    class="form-control"
                    id = "email"
                    type="email" 
                    name="email" 
                    placeholder="Эл почта"/>
            </div>
            <div class="row mb-3 px-3">
                <label class="form-label" for="password">Пароль</label>
                    <input 
                    class="form-control"
                    id = "password"
                    type="password" 
                    name="password" 
                    placeholder="Пароль"/>
            </div>
            <?php
            if(!empty($_REQUEST['role'])&&!empty($_REQUEST['nick_name'])&&!empty($_REQUEST['email'])&&!empty($_REQUEST['password'])) {
                $sql_search_user = "SELECT `nick_name`, `role`, `email`, `password` FROM `users` WHERE (`nick_name`='".$_REQUEST['nick_name']."' OR `email`='".$_REQUEST['email']."')";
                $result = $conn -> query($sql_search_user);
                $row = $result -> fetch();
                if($result->rowCount()==0) {
                    try {
                        $conn -> exec($insert_query);         
                    } catch(PDOException $e) {
                        echo $e->getMessage();
                    }
                    add_log($log_data);
                    header("Location: ../main/cosmodb.php?table=users&change_column=$column_to_search");
                } else {
                    echo "<small class=\"pico-color-red-500\">Такой пользователь уже существует!</small>";
                }
                
            } elseif (isset($_REQUEST['add_row'])) {?>
                    <p class="h5 text-danger mt-3">Заполните все поля</p>
            <?php
            }
            ?>
            <div class="col d-flex justify-content-center">
                <input 
                type="submit" 
                value="Добавить"
                name="add_row"
                class="btn btn-success mb-3 btn-lg" />
            </div>
            <?php
            break;
        case (empty($id_change_form)&&$table=='app_types'):
            ?>
            <legend class="h1 text-center text-primary mt-3">Добавление типа аппарата</legend>
            <input type="hidden" name="table" value="app_types"/>
            <div class="row mb-3 px-3">
                <label class="form-label" for="type">Название типа</label>
                    <input 
                    class="form-control"
                    id = "type"
                    type="text" 
                    name="type" 
                    placeholder="Название типа"/>
            </div>
            <?php
            if(!empty($_REQUEST['type'])) {
                try {
                    $conn -> exec($insert_query);
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
                add_log($log_data);
                header("Location: cosmodb.php?table=app_types&change_column=$column_to_search");
            } elseif (isset($_REQUEST['add_row'])) {?>
                    <p class="h5 text-danger mt-3">Заполните все поля</p>
            <?php
            }
            ?>
            <div class="col d-flex justify-content-center">
                <input 
                type="submit" 
                value="Добавить"
                name="add_row"
                class="btn btn-success mb-3 btn-lg" />
            </div>
            <?php
            break;
        case (empty($id_change_form)&&$table=='roles'):
            ?>
            <legend class="h1 text-center text-primary mt-3">Добавление роли</legend>
            <input type="hidden" name="table" value="roles"/>
            <div class="row mb-3 px-3">
                <label class="form-label" for="role_name">Название роли</label>
                    <input 
                    class="form-control"
                    id = "role_name"
                    type="text" 
                    name="role_name" 
                    placeholder="Название роли"/>
            </div>
            <?php
            if(!empty($_REQUEST['role_name'])) {
                try {
                    $conn -> exec($insert_query);
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
                add_log($log_data);
                header("Location: cosmodb.php?table=roles&change_column=$column_to_search"); 
            } elseif (isset($_REQUEST['add_row'])) {?>
                    <p class="h5 text-danger mt-3">Заполните все поля</p>
            <?php
            }
            ?>
            <div class="col d-flex justify-content-center">
                <input 
                type="submit" 
                value="Добавить"
                name="add_row"
                class="btn btn-success mb-3 btn-lg" />
            </div><?php

            break;
        case (empty($id_change_form)&&$table=='people'):
            ?>
            <legend class="h1 text-center text-primary mt-3">Добавление знаковой личности</legend>
            <input type="hidden" name="table" value="people"/>
            <div class="row mb-3 px-3">
                <label class="form-label" for="initials">Инициалы персоны</label>
                    <input 
                    class="form-control"
                    id = "initials"
                    type="text" 
                    name="initials" 
                    placeholder="Инициалы"/>
            </div>
            <div class="row mb-3 px-3">
                <label class="form-label" for="name">Имя</label>
                    <input 
                    class="form-control"
                    id = "name"
                    type="text" 
                    name="name" 
                    placeholder="Имя"/>
            </div>
            <div class="row mb-3 px-3">
                <label class="form-label" for="surename">Фамилия</label>
                    <input 
                    class="form-control"
                    id = "surename"
                    type="text" 
                    name="surename" 
                    placeholder="Фамилия"/>
            </div>
            <div class="row mb-3 px-3">
                <label class="form-label" for="last_name">Отчество</label>
                    <input 
                    class="form-control"
                    id = "last_name"
                    type="text" 
                    name="last_name" 
                    placeholder="Отчество"/>
            </div>
            <?php
            if(!empty($_REQUEST['initials'])&&!empty($_REQUEST['name'])&&!empty($_REQUEST['surename'])&&!empty($_REQUEST['last_name'])) {
                try {
                    $conn -> exec($insert_query);
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
                add_log($log_data);
                header("Location: cosmodb.php?table=people&change_column=$column_to_search");
            } elseif (isset($_REQUEST['add_row'])) {?>
                    <p class="h5 text-danger mt-3">Заполните все поля</p>
            <?php
            }
            ?>
            <div class="col d-flex justify-content-center">
                <input 
                type="submit" 
                value="Добавить"
                name="add_row"
                class="btn btn-success mb-3 btn-lg" />
            </div>
            <?php
            break;
    }
}
function forms ($action) {
    global $id_change_form, $table, $refer, $conn, $select_query, $user_role, $user_name, $column_to_search;
    if($action == 'Добавить строку') {
        $html_query = "table=".$table."action=".$action;
    } else {
        $html_query = "table=".$table."&id_change_form%5B%5D=".$id_change_form[0]."&action=".$action;
    }
    if(!empty($id_change_form)) {
        $log_data = [
            'user' => $user_name,
            'role' => $user_role,
            'table' => $table,
            'action' => $action,
            'id' => $id_change_form[0],
            'time' => date('d.m.Y H:i:s')
        ];
    }
    ?>
<main class="container mb-5 mt-5 border border-primary rounded-4 ">
    <form action="cosmodb.php?<?= $html_query?>&action=<?=$action?>" method="post">
        <input type="hidden" name="action" value="<?=$action?>"/>
    <?php
    switch (true) {
        case ($action=='Добавить строку'):
            edit();
            break;
        case ($action=='Редактировать выбранное'&&!empty($id_change_form)):
            if(!isset($_REQUEST['edit'])) {
                switch ($table) {
                    case 'users':
                        $legend = "Редактирование данных пользователя";
                        break;
                    case 'roles':
                        $legend = "Редактирование роли";
                        break;
                    case 'people':
                        $legend = "Редактирование знаковых личностей";
                        break;
                    case 'app_types':
                        $legend = "Редактирование типа аппарата";
                        break;
                    case 'space_achiv':
                        $legend = "Редактирование статьи";
                        break;
                }
                ?>
                <legend class="h1 text-center text-primary mt-3"><?=$legend?></legend>
                    <input type="hidden" name="table" value="<?=$table?>"/>
                <?php
            $num_ids = count($id_change_form);
            foreach ($id_change_form as $ids){
                if($table=='users'&&$ids==1) {
                    echo "<article class=\"container-fluid\"><h1 align=\"center\">
                    <strong class=\"pico-color-indigo-300\"><i>403</i></strong><hr />Редактирование запрещено<br></h1>Попытка редактирования профиля администратора.<a href=\"javascript:history.back()\">Вернуться</a>
                    </article>";
                    break;
                } else {
                    $select_where = str_replace('ORDER BY', 'WHERE '.$table.'.id = '.$ids.' ORDER BY', $select_query);
                    $input_names = [
                        "Логин" => "login[]",
                        "Роль пользователя" => "role[]",
                        "Запрашиваемая роль" => "role_raise[]",
                        "Электронная почта" => "email[]",
                        "Пароль" => "password[]",
                        "Инициалы" => "initials[]",
                        "Имя" => "name[]",
                        "Фамилия" => "surename[]",
                        "Отчество" => "last_name[]",
                        "Роль" => "role_name[]",
                        "Тип" => "type[]",
                        "Страна" => "country[]",
                        "Знаковая личность" => "people[]",
                        "Наименование" => "achiv_name[]",
                        "Дата" => "date[]",
                        "Текст" => "text[]",
                        "Тип аппарата" => "type_app[]"
                    ];
                    $word_value = [];
                    $words_to_find = [
                        "Роль" => "roles",
                        "Страна" => "country",
                        "Знаковая личность" => "people",
                        "Запрашиваемая роль" => "roles",
                        "Роль пользователя" => "roles",
                        "Тип аппарата" => "app_types"
                    ];
                    foreach ($words_to_find as $key_name => $word) {
                        $pos = strpos($select_query, $word);
                        if ($pos !== false) {
                            $word_value[$key_name] = $word;
                        }
                    }
                    $row = $conn->query($select_where)->fetch();
                    unset($row[0]);
                    foreach($row as $key => $value) {
                    switch ($key) {
                        case (is_string($key)&&$key!='Текст'&&!isset($word_value[$key])&&$key!='#'&&!is_numeric($value)&&$key!=0&&$key!='Дата'):
                            ?>
                            <div class="row mb-3 px-3">
                            <label class="form-label" for="<?=$input_names[$key]?>"><?=$key?></label>
                            <input 
                            type="text" 
                            class="form-control"
                            id="<?=$input_names[$key]?>"
                            name="<?=$input_names[$key]?>" 
                            value="<?=$value?>"/>
                            </div>
                            <?php
                            break;
                        case (is_string($key)&&is_numeric($value)&&!isset($word_value[$key])):
                            ?>
                            <div class="row mb-3 px-3">
                            <label class="form-label" for="id_change_form[]"><?=$key?></label>
                            <input type="text" 
                            class="form-control"
                            id="id_change_form[]"
                            name="id_change_form[]" 
                            value="<?=$value?>" 
                            readonly/>
                            </div>
                            <?php
                            break;
                        case (is_string($key)&&$key=='Текст'&&!isset($word_value[$key])):
                            ?>
                            <div class="row mb-3 px-3">
                            <label class="form-label" for="<?=$input_names[$key]?>"><?=$key?></label>
                            <textarea
                            class="form-control"
                            id="<?=$input_names[$key]?>"
                            name="<?=$input_names[$key]?>"
                            rows="10">
                            <?=trim($value)?>
                            </textarea>
                            </div>
                            <?php
                            break;
                        case (is_string($key)&&$key=='Дата'&&!isset($word_value[$key])):
                            ?>
                            <div class="row mb-3 px-3">
                            <label class="form-label" for="<?=$input_names[$key]?>"><?=$key?></label>
                            <input type="date"
                            class="form-control"
                            id="<?=$input_names[$key]?>"
                            name="<?=$input_names[$key]?>"
                            value="<?=$value?>"/>
                            </div>
                            <?php
                            break;
                        case (is_string($key)&&isset($word_value[$key])):
                            ?>
                            <div class="row mb-3 px-3">
                            <label class="form-label" for="<?=$input_names[$key]?>"><?=$key?></label>
                            <select name="<?=$input_names[$key]?>" class="form-select">
                            <?php
                            if ($key!='Страна') {?>
                            <option value="0"><?=$value?></option><?php
                            }
                            switch ($word_value[$key]) {
                                case "roles":
                                    $sql = "SELECT * FROM roles";
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch()) {
                                        if ($row['role_name']!=$value) {?>
                                            <option value="<?=$row['id']?>"><?=$row['role_name']?></option><?php
                                        }
                                    }
                                    break;
                                case "country":
                                    if($value==1) {?>
                                        <option value="0">СССР</option><option value="2">Россия</option><?php
                                    } else {?>
                                        <option value="0">Россия</option><option value="1">СССР</option><?php
                                    }
                                    break;
                                case "people":
                                    $sql = "SELECT * FROM people";
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch()) {
                                        if ($row['initials']!=$value) {?>
                                            <option value="<?=$row['id']?>"><?=$row['initials']?></option><?php
                                        }
                                    }
                                    break;
                                case "app_types":
                                    $sql = "SELECT * FROM app_types";
                                    $result = $conn->query($sql);
                                    while ($row = $result->fetch()) {
                                        if ($row['type']!=$value) {?>
                                            <option value="<?=$row['id']?>"><?=$row['type']?></option><?php
                                        }
                                    }
                                    break;
                            }?>
                            </select>
                        </label>
                        </div>
                            <?php
                            break;
                        }
                    }
                }
            }?>
            <div class="col d-flex justify-content-center">
            <input type="submit" 
            value="Редактировать" 
            name="edit" 
            class="btn btn-success mb-3 btn-lg"/>
            </div>
            <?php
        } else {
                $ii = 0;
                foreach ($_REQUEST as $key_1 => $value) {
                    foreach ($_REQUEST["id_change_form"] as $key_2 => $value2) {
                        if($key_1!='id_change_form'&&$key_1!='table'&&$key_1!='action'&&$key_1!='edit') {
                            foreach ($_REQUEST[$key_1] as $key_3 => $value3) {
                                $sql_select_compare = "SELECT * FROM ".$table." WHERE id = $value2";
                                $ssttmmtt = $conn->query($sql_select_compare);
                                $row_compare = $ssttmmtt->fetch();
                                if($key_2==$key_3&&$row_compare[$key_1]!=$value3&&$value3!=0) {
                                    $update[$ii] = "UPDATE $table SET $key_1 = '$value3' WHERE $table.id = $value2";
                                    $ii++;
                                }
                            }
                        }
                    }
                }
                if(!empty($update)) {
                foreach ($update as $key_update => $value_update) {
                    $_SESSION['update'] = $value_update;
                    try {
                        $conn->query($value_update);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
                add_log($log_data);
                header("Location: cosmodb.php?table=$table&column_name=$column_to_search");
                exit();
            }
            
            break;
        case ($action=='Удалить выбранное'&&!empty($id_change_form)):
            $sql = "DELETE FROM ".$table." WHERE id IN (";
            $num_ids = count($id_change_form);
            foreach ($id_change_form as $ids){
                if($table=='users'&&$ids==1) {
                    echo "<article class=\"container-fluid\"><h1 align=\"center\">
                    <strong class=\"pico-color-indigo-300\"><i>403</i></strong><hr />Редактирование запрещено<br></h1>Попытка редактирования профиля администратора.<a href=\"javascript:history.back()\">Вернуться</a>
                    </article>";
                    break;
                } else {
                    echo "<input type=\"hidden\" name=\"id_change_form[]\" value=\"$ids\"/>";
                    $sql .= $ids.", ";
                }
            }
            $sql = substr($sql, 0, -2);
            $sql .= ");";
            switch ($table) {
                case "users":
                    $legend = "Удаление пользователя";
                    break;
                case "people":
                    $legend = "Удаление знаковой личности";
                    break;
                case "app_types":
                    $legend = "Удаление типа аппарата";
                    break;
                case "roles":
                    $legend = "Удаление роли";
                    break;
                case "space_achiv":
                    $legend = "Удаление статьи";
                    break;
            }
            ?>
            <legend class="h1 text-center text-primary mt-3"><?=$legend?></legend>
            <input type="hidden" name="table" value="<?=$table?>"/>
            <p class="h1 text-center mb-3">Вы действительно хотите удалить <?=$num_ids > 1 ? 'строки' : 'строку'?>?</p>
            <div class="row mb-3">
                <div class="col d-flex justify-content-end">
                    <a role="button" class="btn btn-primary btn-lg" href="javascript:history.back()">Отменить</a>
                </div>
                <div class="col d-flex justify-content-start">
                    <input type="submit" value="Удалить" class="btn btn-danger btn-lg" name="delete"/>
                </div>
            </div>
            <?php
            if(isset($_REQUEST['delete'])) {
                try {
                    $conn -> exec($sql);
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
                add_log($log_data);
                header("Location: cosmodb.php?table=$table&column_name=$column_to_search");
                exit();
            } 
            break;
        case ($action=='SQL'):
            if (!isset($_REQUEST['sql'])) {
                echo "<div class=\"col-auto\"><input class=\"form-control-plaintext\" name=\"table\" value=\"".$table."\"/></div></div>
                <textarea name=\"sql\" placeholder=\"SQL запрос\" rows=\"10\" cols=\"50\"></textarea>
                <input type=\"submit\" value=\"Выполнить\" name=\"sql\"/>";
            } else {
                $sql = $_REQUEST['sql'];
                $conn -> exec($sql);
                header("Location: cosmodb.php?table=$table&column_name=$column_to_search");
                exit();
            }
            break;
        case ($action=='Одобрить роль'):
            $sql_role_approve_search_user = "SELECT `nick_name`, `role_raise` FROM `users` WHERE `id` = $id_change_form[0]";
            $stmt_role_approve_search_user = $conn->query($sql_role_approve_search_user)->fetch();
            $sql_role_approve_search_role = "SELECT `role_name` FROM `roles` WHERE `id` = ".$stmt_role_approve_search_user['role_raise'];
            $stmt_role_approve_search_role = $conn->query($sql_role_approve_search_role)->fetch();
            ?>
            <input type="hidden" name="table" value="users"/>
            <p class="h2 text-center mb-3 mt-3">Вы действительно хотите изменить роль пользователю <b><?=$stmt_role_approve_search_user['nick_name']?></b> на <b><?=$stmt_role_approve_search_role['role_name']?></b>?</p>
            <div class="row mb-3 mt-3">
                <div class="col d-flex justify-content-end">
                    <a role="button" class="btn btn-primary btn-lg" href="javascript:history.back()">Отменить</a>
                </div>
                <div class="col d-flex justify-content-start">
                    <input type="submit" value="Изменить роль" class="btn btn-success btn-lg" name="change_user_role"/>
                </div>
            </div>
            <?php
            if(isset($_REQUEST['change_user_role'])) {
                $sql_role_approve = "UPDATE `users` SET `role` = ".$stmt_role_approve_search_user['role_raise']." WHERE `id` = $id_change_form[0]";
                try {
                $conn -> exec($sql_role_approve);
                } catch(PDOException $e) {
                    echo $e->getMessage();
                }
                add_log($log_data);
                header("Location: cosmodb.php?table=users&column_name=$column_to_search");
                exit();
            }
            break;
        case ($action=='Отклонить роль'):
            $sql_role_decline_search_user = "SELECT `nick_name`, `role` FROM `users` WHERE `id` = $id_change_form[0]";
            $stmt_role_decline_search_user = $conn->query($sql_role_decline_search_user)->fetch();
            $sql_role_decline = "UPDATE `users` SET `role_raise` = ".$stmt_role_decline_search_user['role']." WHERE `id` = $id_change_form[0]";
            try {
                $conn -> exec($sql_role_decline);
            } catch(PDOException $e) {
                echo $e->getMessage();
            }
            add_log($log_data);
            header("Location: cosmodb.php?table=users&column_name=$column_to_search");
            exit();
            break;
        default:
            header("Location: cosmodb.php?table=$table&column_name=$column_to_search");
            break;
    }
?></form></main><?php
}
