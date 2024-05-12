<?php
function user_profile () {
    global $user_name, $user_role, $conn;
    $goback_svg = file_get_contents("../image/goback.svg");
    $user_roles = [
        "1" => "Пользователь",
        "2" => "Модератор",
        "3" => "Оператор БД",
        "4" => "Администратор",
    ];
    $tables = [
        "users" => "Пользователи",
        "space_achiv" => "Космические достижения",
        "app_types" => "Типы аппаратов",
        "roles" => "Роли",
        "people" => "Люди",
    ]
    ?>

<main class="container mt-5">
    <?php
    if ($user_role == 4 && !isset($_REQUEST['edit_profile'])) {
        $dir = '../back_up';
        $files = scandir($dir);
        $files = array_diff($files, array('..', '.'));
    ?>
        <div class="row row-cols-2 mx-3 mt-4">
            <div class="col px-0 d-flex justify-content-start mt-1">
                <a href="cosmodb.php?user_profile=<?= $user_name?>&back_up=1" role="button" class="btn rounded-0 rounded-top-4 <?= isset($_REQUEST['back_up']) ? 'btn-primary' : 'btn-light' ?>">
                    Резервные копии
                </a>
                <a href="cosmodb.php?user_profile=<?= $user_name?>&logs=1" role="button" class="btn rounded-0 rounded-top-4 <?= isset($_REQUEST['logs']) ? 'btn-primary' : 'btn-light' ?>">
                    Логи
                </a>
            </div>
            <?php
            if (isset($_REQUEST['back_up'])) {
            ?>
            <div class="col d-flex justify-content-end px-0 mb-1">
                <a href="../init/create_database_back_up.php"
                role="button"
                class="btn btn-success btn">
                    Создать резервную копию
                </a>
            </div>
            <?php
            }
            ?>
        </div>
        <div class="row">
            <div class="table-responsive border border-primary rounded-4 px-0" data-simplebar>
                <table class="table table-hover table-striped mb-0">
                    <?php
                    if (isset($_REQUEST['back_up'])) {
                    ?>
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Дата создания</th>
                            <th scope="col" class="text-center">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($files as $key => $value) {
                        ?>
                        <tr>
                        <?php
                        $file_date = date("d.m.Y", filemtime("../back_up/$value"));
                        ?>
                            <td class="text-center"><?= $file_date ?></td>
                            <td class="text-center">
                                Поставить базу данных
                                <a href="../back_up/<?= $value ?>" 
                                download="<?= $value ?>" 
                                class="text-decoration-none text-dark">
                                    <?= $value ?>
                                </a>
                                Удалить
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                    <?php
                    } elseif (isset($_REQUEST['logs'])) {
                    ?>
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Пользователь</th>
                            <th scope="col" class="text-center">Роль</th>
                            <th scope="col" class="text-center">Таблица</th>
                            <th scope="col" class="text-center">Действие</th>
                            <th scope="col" class="text-center">Номер строки</th>
                            <th scope="col" class="text-center">Время</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $logs = [];
                        $dir = '../init/logs.json';
                        $files = json_decode(file_get_contents($dir), true);
                        foreach ($files as $key_files => $value) {
                            foreach ($value as $key => $value) {
                                switch ($key) {
                                    case 'user':
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $value ?></td>
                                        <?php
                                        break;
                                    case 'role':
                                        ?>
                                            <td class="text-center"><?= $user_roles[$value] ?></td>
                                        <?php
                                        break;
                                    case 'table':
                                        ?>
                                            <td class="text-center"><?= $tables[$value] ?></td>
                                        <?php
                                        break;
                                    case 'action':
                                        if (is_null($value)) {
                                            ?>
                                                <td class="text-center">Добавить запись</td>
                                                <td class="text-center">-</td>
                                            <?php
                                        } else {
                                        ?>
                                            <td class="text-center"><?= $value ?></td>
                                        <?php
                                        }
                                        break;
                                    case 'id':
                                        ?>
                                            <td class="text-center"><?= $value ?></td>
                                        <?php
                                        break;
                                    case 'time':
                                        ?>
                                            <td class="text-center"><?= $value ?></td>
                                        </tr>
                                        <?php
                                        break;
                                }
                            }
                        }
                    }
                    ?>
                </table>
        </div>
    </div>
    <?php
    } elseif (!isset($_REQUEST['edit_profile'])) {
                $sql_profile_search = "SELECT `email` FROM `users` WHERE `nick_name` = '$user_name'";
                try {
                $profile_search = $conn->query($sql_profile_search)->fetch();
                $profile_search_email = $profile_search['email'];
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                $sql_profile_role_select = "SELECT `id`, `role_name` FROM `roles`";
                $result_profile_role_select = $conn -> query($sql_profile_role_select);
                $sql_profile_role_raise_select = "SELECT `role_raise` FROM `users` WHERE `nick_name` = '$user_name'";
                try {
                $result_profile_role_raise_select = $conn -> query($sql_profile_role_raise_select)->fetch();
                $profile_role_raise = $result_profile_role_raise_select['0'];
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
    ?>
    <div class="row">
        <div class="col-3">
            <div class="card border border-primary rounded-4">
                <!-- <img src="../profile_imgs/photo_2024-04-05_21-35-52.jpg" class="mt-2 object-fit-cover mx-auto rounded-circle" id="profile_pic" alt="..."> -->
                <div class="card-body">
                    <p class="h4 card-title">Логин: <?= $user_name ?></p>
                    <p class="card-text">Роль: <?= $user_roles[$user_role] ?></p>
                    <a href="cosmodb.php?logout=1"
                    role="button" 
                    class="btn btn-danger"
                    >Выйти <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.04844 1.30005H3.65138C3.13655 1.30005 2.6428 1.50286 2.27876 1.86387C1.91472 2.22488 1.71021 2.71451 1.71021 3.22505V14.775C1.71021 15.2856 1.91472 15.7752 2.27876 16.1362C2.6428 16.4972 3.13655 16.7 3.65138 16.7H7.04844M7.2897 9.00005H18.2897M18.2897 9.00005L14.0866 4.60005M18.2897 9.00005L14.0866 13.4" stroke="#F9F9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg></a>
                </div>
            </div>
        </div>
        <div class="col-9 border border-primary rounded-4 px-0">
            <form action="cosmodb.php?user_profile=<?= $user_name ?>" method="post">
                <div class="row">
                    <div class="col">
                        <p class ="form-control form-control-plaintext fs-3 text-center">
                            <strong>Редактирование профиля</strong>
                        </p>
                    </div>
                </div>
                <div class="row mt-3 mb-3 px-4">
                    <label class="col-sm-3 col-form-label text-end" for="profile_edit_login">Логин</label>
                    <div class="col">
                        <input type="text"
                        class="form-control"
                        id="profile_edit_login"
                        name="profile_edit_login"
                        value="<?= $user_name ?>"/>
                    </div>
                </div>

                <div class="row mb-3 px-4">
                    <label class="col-sm-3 col-form-label text-end" for="profile_edit_email">Эл почта</label>
                    <div class="col">
                        <input type="text"
                        class="form-control"
                        id="profile_edit_email"
                        name="profile_edit_email"
                        value="<?php echo $profile_search_email ?>"/>
                    </div>
                </div>
                <div class="row mb-3 px-4">
                    <label class="col-sm-3 col-form-label text-end" for="profile_edit_password">Изменить пароль</label>
                    <div class="col">
                        <input type="password"
                        class="form-control"
                        id="profile_edit_password"
                        name="profile_edit_password"
                        aria-describedby="help_block"/>
                        <div id="help_block" class="form-text">
                        Смена логина, почты и/или пароля приводит к окончанию текущей сессии, придется заходить заново
                        </div>
                    </div>
                </div>
                <?php
                if ($profile_role_raise == $user_role) {
                ?>
                <div class="row mb-3 px-4">
                    <label class="col-sm-3 col-form-label text-end" for="profile_edit_role">Запросить роль</label>
                    <div class="col">
                        <select type="password"
                        class="form-select"
                        id="profile_edit_role"
                        name="profile_edit_role">
                <?php
                while ($row_profile_role_select = $result_profile_role_select -> fetch()) {
                    switch (true) {
                        case ($user_role == $row_profile_role_select['id']):
                            echo "<option value='$row_profile_role_select[id]' selected>$row_profile_role_select[role_name]</option>";
                            break;
                        
                        case ($user_role < $row_profile_role_select['id']):
                            echo "<option value='$row_profile_role_select[id]'>$row_profile_role_select[role_name]</option>";
                            break;
                    }
                }
                ?>
                        </select>
                    </div>
                </div>
                <?php
                } else {
                ?>
                <div class="row mb-3 px-4">
                    <div class="col">
                        <p class="text-center">Запрошена роль: <?= $user_roles[$profile_role_raise] ?></p>
                    </div>
                </div>
                <?php
                }
                if (!empty($not_stated)) {
                    echo $not_stated;
                }
                ?>
                <div class="col d-flex justify-content-center">
                    <input
                        type="submit"
                        value="Изменить профиль"
                        name = "edit_profile"
                        class="btn btn-success mb-3"
                    />
                </div>
            </form>
        </div>
    </div>
                <?php

            } elseif (isset($_REQUEST['edit_profile'])&&($_REQUEST['profile_edit_login']!=$user_name || !empty($_REQUEST['profile_edit_password']) || $_REQUEST['profile_edit_email']!=$profile_search_email || $_REQUEST['profile_edit_role']!=$user_role)) {
                    $_SESSION['test'] = true;
                $relog = false;
                $sql_profile_edit = [];
                $i = 0;
                if(isset($_REQUEST['profile_edit_login'])&&$_REQUEST['profile_edit_login']!=$user_name) {
                    $profile_edit_login = $_REQUEST['profile_edit_login'];
                    try{
                        $stmt2 = $conn -> query("SELECT * FROM `users` WHERE `nick_name` = '$profile_edit_login'");
                        $stmt2 = $stmt2 -> fetch();
                        if(!empty($stmt2['id'])) { ?>
                            <p class="h5 text-danger mt-3 text-center mb-3">Пользователь с таким логином уже существует</p>
                            <?php
                        } else {
                            $sql_profile_edit[$i] = "`nick_name` = '$profile_edit_login'";
                            $i++;
                            $relog = true;
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                if (isset($_REQUEST['profile_edit_password'])&&!empty($_REQUEST['profile_edit_password'])) {
                    $profile_edit_password = password_hash($_REQUEST['profile_edit_password'], PASSWORD_DEFAULT);
                    $sql_profile_edit[$i] = "`password` = '$profile_edit_password'";
                    $i++;
                    $relog = true;
                }
                if (isset($_REQUEST['profile_edit_email'])&&$_REQUEST['profile_edit_email']!=$profile_search_email) {
                    $profile_edit_email = $_REQUEST['profile_edit_email'];
                    try {
                        $stmt1 = $conn -> query("SELECT * FROM `users` WHERE `email` = '$profile_edit_email'");
                        $stmt1 = $stmt1 -> fetch();
                        if(!empty($stmt1['id'])) { ?>
                            <p class="h5 text-danger mt-3 text-center mb-3">Пользователь с такой почтой уже существует</p>
                            <?php
                        } else {
                            $sql_profile_edit[$i] = "`email` = '$profile_edit_email'";
                            $i++;
                            $relog = true;
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                if (isset($_REQUEST['profile_edit_role'])&&$_REQUEST['profile_edit_role']!=$user_role) {
                    $profile_edit_role = $_REQUEST['profile_edit_role'];
                    $sql_profile_edit[$i] = "`role_raise` = '$profile_edit_role'";
                    $i++;
                    if ($relog) {
                        $relog = true;
                    } else {
                        $relog = false;
                    }

                }
                if (!empty($sql_profile_edit)) {
                    $update_sql = "";
                }
                    $count = count($sql_profile_edit);
                    foreach ($sql_profile_edit as $key => $value) {
                        if ($key != $count - 1) {
                        $update_sql .= $value.", ";
                        } else {
                        $update_sql .= $value;
                        }
                    }
                    $update_profile = "UPDATE `users` SET $update_sql WHERE `nick_name` = '$user_name'";
                    try {
                        $conn -> query($update_profile);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    if ($relog) {
                        session_unset();
                        session_destroy();
                        header("Location: cosmodb.php?auto_reg_form=Авторизоваться");
                    } else {
                        header("Refresh:0");
                    }
                } elseif (isset($_REQUEST['edit_profile'])&&$_REQUEST['profile_edit_login']==$user_name&&empty($_REQUEST['profile_edit_password'])&&$_REQUEST['profile_edit_email']==$profile_search_email&&$_REQUEST['profile_edit_role']==$user_role) {
                    $not_stated = "<p class=\"h5 text-danger mt-3 text-center mb-3\">Измените хотя бы одно поле</p>";
                } else {
                    $not_stated = "";
                }
            ?>

    </main>
    <?php
}
    ?>
