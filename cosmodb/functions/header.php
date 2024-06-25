<?php
function get_columns_from_table($table_name) {
    global $conn;
    $stmt = $conn->query("SHOW COLUMNS FROM $table_name");
    $stmt = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (array_search('password', $stmt) !== false) {
        $key = array_search('password', $stmt);
        unset($stmt[$key]);
    }
    return $stmt;
}

function get_data_list ($column) {
    global $conn, $table;
    switch ($column) {
        case "people":
            $query = "SELECT `initials` FROM `people`";
            break;
        case "type_app":
            $query = "SELECT `type` FROM `app_types`";
            break;
        case "role":
        case "role_raise":
            $query = "SELECT `role_name` FROM `roles`";
            break;
        default:
            $query = "SELECT $column FROM $table";
            break;
    }
    $stmt = $conn->query($query);
    $stmt = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $stmt;
}
function change_table () {
    global $table, $user_name, $user_role, $user_loged_in, $user_role, $column_to_search;
    $logo = file_get_contents("../image/logo.svg");
    $settings_svg = file_get_contents("../image/settings.svg");
    $logout_svg = file_get_contents("../image/logout_2.svg");
    $user_roles = [
        "1" => "Статьи",
        "2" => "Модератор",
        "3" => "Редактирование базы данных",
        "4" => "Панель администратора",
    ];
    $user_roles_name = [
        "1" => "Пользователь",
        "2" => "-",
        "3" => "Оператор БД",
        "4" => "Администратор",
    ];
    $column_names = [
        "id" => "#",
        "type" => "Тип",
        "initials" => "Инициалы",
        "surename" => "Фамилия",
        "name" => "Имя",
        "last_name" => "Отчество",
        "role_name" => "Роль",
        "country" => "Страна",
        "people" => "Знаковые личности",
        "achiv_name" => "Наименование",
        "date" => "Дата",
        "text" => "Полнотекстовый поиск",
        "type_app" => "Тип аппарата",
        "nick_name" => "Логин",
        "role" => "Роль",
        "role_raise" => "Запрашиваемая роль",
        "email" => "Электронная почта",
    ];
    $columns = get_columns_from_table($table);
    switch (true) {
        case (!isset($_REQUEST['action'])&&$user_loged_in&&$user_role!=0&&!isset($_REQUEST['user_profile'])&&!isset($_REQUEST['article'])):
    ?>
            <header class="container-fluid row mt-1">
                <div class="col col-3 d-flex justify-content-start align-items-end px-0 ms-5">
                    <?= $logo ?>
                </div>
                <div class="col col-xxl-7 col-xl-6 col-lg-5 col-md-4 col-sm-6 d-flex justify-content-center align-items-center">
                    <form action="cosmodb.php?table=<?= $table ?>&column_name=<?= isset($_REQUEST['column_name']) ? $_REQUEST['column_name'] : $columns[0] ?>" method="get" class="row w-100 h-75 d-flex justify-content-center">
                    <input type="hidden" name="table" value="<?= $table ?>">
                    <input type="hidden" name="column_name" value="<?= $column_to_search ?>">
                        <div class="row pe-0 my-2">
                            <div class="col-12 input-group mb-0">
                                <input class="form-control col-10" 
                                type="search" 
                                name="search" 
                                placeholder="Поиск по статьям" 
                                aria-label="Search"
                                list ="column_names"
                                >
                                <?php
                                if ($column_to_search != "text" && $column_to_search != "id") {
                                    ?>
                                    <datalist id="column_names">
                                        <?php
                                        if ($column_to_search == "country") {
                                            $data_list = ["РОССИЯ", "СССР"];
                                        } else {
                                        $data_list = get_data_list($column_to_search);
                                        }
                                        var_dump($data_list);
                                        foreach ($data_list as $key => $value) {
                                            ?>
                                            <option value="<?= $value ?>"><?= $value ?></option>
                                            <?php
                                        }
                                        ?>
                                    </datalist>
                                    <?php
                                }
                                ?>
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php
                                    if (isset($_REQUEST['column_name'])) {
                                        echo $column_names[$_REQUEST['column_name']];
                                    } else {
                                        echo $column_names[$columns[0]];
                                    }
                                    ?>
                                </button>
                                <ul class="dropdown-menu">
                                <?php
                                foreach ($columns as $key => $value) { ?>
                                    <li>
                                        <a href="cosmodb.php?table=<?= $table ?>&column_name=<?= $value ?>" role="button" class="dropdown-item
                                        <?php
                                        if ((isset($_REQUEST['column_name']) && $_REQUEST['column_name'] == $value) || ($key == 0 && !isset($_REQUEST['column_name']))) {
                                            echo "btn-primary";
                                        } else {
                                            echo "btn-light";
                                        }
                                        ?>
                                        "><?= $column_names[$value]?></a>
                                    </li>
                                <?php
                                }
                                ?>
                                </ul>
                                <input 
                                class="btn btn-success"
                                type="submit" 
                                value="Поиск"
                                />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-1 d-flex justify-content-end align-items-center me-4 ms-5 px-0">

                        <button class="btn btn-outline-light dropdown-toggle px-1 py-1 border-dark border-2 rounded-circle no-caret" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $settings_svg ?>
                        </button>
                        <ul class="dropdown-menu dropdown-responsive">
                            <li class="row px-4">
                                <div class="col px-0 text-center">Логин: <?= $user_name ?></div>
                            </li>
                            <li class="row px-4">
                                <div class="col px-0 text-center">Роль: <?= $user_roles_name[$user_role] ?></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="row text-center">
                                <div class="col ps-3 pe-0 me-1">
                                    <a href="cosmodb.php?user_profile=<?= $user_name?><?= $user_role == 4 ? "&back_up=1" : ""?>"
                                    role="button" 
                                    class="btn btn-primary px-1 py-1 w-100 rounded-3">
                                        Настройки
                                    </a>
                                </div>
                                <div class="col ps-0 pe-3">
                                    <button
                                    class="btn btn-danger px-1 py-1 w-100 rounded-3"
                                    data-bs-toggle = "modal" 
                                    data-bs-target = "#logout_modal">
                                        Выйти
                                    </button>
                                </div>
                            </li>
                        </ul>

                </div>
            </header>
<div class="modal fade" id="logout_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="logout_modal_Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered mt-0 mb-5">
    <div class="modal-content border border-primary rounded-4 shadow">
      <!-- <div class="modal-header d-flex justify-content-center border-0">
        <p class="modal-title fs-5 h1" id="logout_modal_Label">Выход из профиля</p>
      </div> -->
      <div class="modal-body px-0 pt-3 pb-1">
        <p class="h3 text-center">Вы уверены, что хотите выйти?</p>
      </div>
      <div class="modal-footer d-flex justify-content-center border-0 px-0 pb-3 pt-1">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Отмена</button>
        <a href="cosmodb.php?logout=1"
        role="button" 
        class="btn btn-danger">Выйти</a>
      </div>
    </div>
  </div>
</div>
    <?php
        break;
        case (!isset($_REQUEST['action'])&&!$user_loged_in&&$user_role==0&&!isset($_REQUEST['user_profile'])&&!isset($_REQUEST['article'])):
            ?>
            <header class="container-fluid">
                <div class="row">
                    <div class="col mt-1 d-flex justify-content-center">
                        <img src="../image/logo.svg" class ="img-fluid" alt="logo">
                    </div>
                </div>
            </header>
            <?php
        break;
            ?>
            <!-- <header class="container mt-1">
                <div class="row">
                    <div class="col-3 d-flex justify-content-center align-items-center">
                        <img src="../image/logo.svg" class ="img-fluid" alt="logo">
                    </div>
                    <div class="col-6 d-flex justify-content-center align-items-center">
                        <p class="fs-2">"</p>
                    </div>
                    <div class="col d-flex justify-content-end align-items-end">
                        <a href="javascript:history.back()"
                        role="button"
                        class="btn btn-outline-primary fs-3 mb-3"
                        >Вернуться</a>
                    </div>
                </div>
            </header> -->
        <?php
        default:
        ?>
            <header class="container mt-1">
                <div class="row">
                    <div class="col"></div>
                    <div class="col d-flex justify-content-center align-items-center">
                        <img src="../image/logo.svg" class ="img-fluid" alt="logo">
                    </div>
                    <div class="col d-flex justify-content-end align-items-end">
                        <a href="cosmodb.php?table=<?=$table?>&column_name=<?=$column_to_search?>"
                        role="button"
                        class="btn btn-outline-primary fs-3 mb-3"
                        >Вернуться</a>
                    </div>
                </div>
            </header>
<?php
        break;
        }
}
?>