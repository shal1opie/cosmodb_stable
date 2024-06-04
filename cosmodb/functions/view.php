<?php

function user_search () {
    global $table, $select_query, $column_to_search, $order_by;
    $search_query = str_replace($order_by, '', $select_query);
    $search_term = "";
    switch ($column_to_search) {
        case "text":
            $search_query .= " WHERE MATCH(`text`) AGAINST ('*".$_REQUEST['search']."*' IN NATURAL LANGUAGE MODE)";
            break;
        case "people":
            $related_column = "people.initials";
            $search_query .= " WHERE ".$related_column." LIKE '%".$_REQUEST['search']."%'";
            break;
        case "type_app":
            $related_column = "app_types.type";
            $search_query .= " WHERE ".$related_column." LIKE '%".$_REQUEST['search']."%'";
            break;
        case "country":
            $search_term = mb_strtoupper($_REQUEST['search'], 'UTF-8');
            if ($search_term == "РОССИЯ") {
                $search_term = "2";
            } elseif ($search_term == "СССР") {
                $search_term = "1";
            }
            $search_query .= " WHERE ".$table.".".$column_to_search." LIKE '%".$search_term."%'";
            break;
        case "role":
        case "role_raise":
            $related_column = "roles.role_name";
            $search_query .= " WHERE ".$related_column." LIKE '%".$_REQUEST['search']."%'";
            break;
        default:
            $search_query .= " WHERE ".$table.".".$column_to_search." LIKE '%".$_REQUEST['search']."%'";
            break;
    }
    return $search_query.$order_by;
}

function view_table ($table) {
    global $conn, $select_query, $user_role, $column_to_search, $order_by;
    $delete_ids = [];
    $table_names = [
        "app_types" => "Тип аппарата",
        "people" => "Знаковые личности",
        "roles" => "Роли",
        "space_achiv" => "Космические достижения",
        "users" => "Пользователи",
    ];
    switch ($user_role) {
        case "2":
            unset($table_names["app_types"]);
            unset($table_names["people"]);
            break;
        case "3":
            unset($table_names["users"]);
            unset($table_names["roles"]);
            break;
    } 
    ?>
    <main class="container-fluid h-100 mb-4">
        <div class="row row-cols-2 mx-5 mt-4">
            <div class="col px-0 d-flex justify-content-start mt-1">
    <?php
    foreach($table_names as $key => $value) {
        switch ($key) {
            case $table:
                echo "<a href=\"cosmodb.php?table=".$key."&column_name=id\" role=\"button\" class=\"btn rounded-0 rounded-top-4 btn-primary\">".$value."</a>";
                $current_table_name = $value;
                break;
            default:
                echo "<a href=\"cosmodb.php?table=".$key."&column_name=id\" role=\"button\" class=\"btn rounded-0 rounded-top-4 btn-light\">".$value."</a>";
                break;
        }
    }
    ?>
            </div>
            <div class="col d-flex justify-content-end px-0 mb-1">
                <a href="cosmodb.php?table=<?= $table ?>&action=Добавить+строку" role="button" class="btn btn-outline-primary rounded-3">+</a>
                <?php
                if($table == 'users') {
                    $modal_users = [];
                    $sql_search_pending_users = "SELECT `role`, `role_raise` FROM `users` WHERE `role` != `role_raise`";
                    try {
                        $result_search_pending_users = $conn -> query($sql_search_pending_users);
                        $pending_users = $result_search_pending_users -> fetch();
                        if ($pending_users) {?>
                            <button class="btn btn-outline-primary rounded-3 ms-2" data-bs-toggle="modal" data-bs-target="#pending_users_modal">Заявки на повышение роли</button>
                        <?php
                        }
                    } catch (Exception $e) {
                        echo $e -> getMessage();
                    }
                }
                ?>
            </div>
        </div>
    <?php
    if ($table == 'space_achiv') {
        $select_query = str_replace('`text` AS `Текст`, ', '', $select_query);
    }
    if(isset($_REQUEST['search'])) {
        $search_query = user_search();
        $final_query = $search_query;
    } else {
        $final_query = $select_query;
    }
    // var_dump($final_query);
try {
    $result = $conn -> query($final_query);
    ?>
    <div class="row mx-4">
    <?php
    echo "<input type=\"hidden\" name=\"table\" value=\"".$table."\" />";
    ?>
    <div class="table-responsive border border-primary rounded-4 px-0" data-simplebar>
        <table class="table table-hover table-striped mb-0">
    <?php
    if ($result->rowCount() == 0) {
        echo "<th class=\"text-center\">По вашему запросу ничего не найдено</th>";
    }
    $once = 0;
    while ($row = $result -> fetch()) {
        if($once < 1) {echo "<thead><tr>";}
        foreach($row as $key => $value) {
            if (is_string($key) && $once < 1) {echo "<th class=\"text-center\">$key</th>";}
        }
        if($once < 1) {echo "<th class=\"text-center\">Действия</th></tr></thead><tbody>";}
        echo "</tr><tr class=\"align-middle\">";
        $once = 1;
        foreach($row as $key => $value) {
            $length = strlen($value);
            switch (true) {
                case (is_string($key)&&$key=='#'):
                    $id=$value;
                    // if($table=='users'&&$id==1) {
                        echo "<td class=\"text-center\">$value</td>";
                    // } else {
                    //     echo "<td class=\"px-0 text-center form-check-input\"><input class=\"input_m\" type=\"checkbox\" name=\"id_change_form[]\" value=\"$value\" readonly />$value</td>";
                    // }
                    break;
                case (is_string($key)&&$length<150&&$key!='Страна'&&$key!='Дата'):
                    if($key =='Наименование') {
                        echo "<td>$value</td>";
                    } elseif ($key == 'Запрашиваемая роль' &&$value==$row['Роль пользователя'] && isset($row['Роль пользователя'])) {
                        ?>
                        <td class="text-center"><em>Повышение роли не требуется</em></td>
                        <?php
                    } else {
                        echo "<td class=\"text-center\">$value</td>";
                    }
                    break;
                // case (is_string($key)&&$length>150):
                //     $substr=mb_substr($value,0,30,'UTF-8');
                //     echo "<td >$substr... <a href=\"cosmodb.php?article=$id\">Прочесть статью</a></td>";
                //     break;
                case ($table=='space_achiv'&&is_string($key)&&$key=="Страна"):
                    if($value==1) {
                        echo "<td class=\"text-center\">СССР</td>";
                    } elseif($value==2) {
                        echo "<td class=\"text-center\">Россия</td>";
                    }
                    break;
                case ($table=='space_achiv'&&is_string($key)&&$key=="Дата"):
                    $date_text = date("d.m.Y", strtotime($value));
                    echo "<td class=\"text-center\">$date_text</td>";
                    break;
            }
        }
        ?>
        <td class="text-center">
            <!-- если не работает, поменяй [] на %5B%5D -->
            <?php
            if (isset($row['Роль пользователя']) && $row['Роль пользователя'] == "Администратор") {
                ?>
                Нет доступных действий
                <?php
            } else {
                if ($table == 'space_achiv') {
            ?>
            <a href="cosmodb.php?article=<?=$id?>" 
            role="button" 
            class="btn btn-success px-1 py-1 d-inline-flex align-items-center justify-content-center px-2 py-2 me-1">
                <svg width="24" height="20" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 6C16 6 13 0.5 8 0.5C3 0.5 0 6 0 6C0 6 3 11.5 8 11.5C13 11.5 16 6 16 6ZM1.1727 6C1.22963 5.91321 1.29454 5.81677 1.36727 5.71242C1.70216 5.23193 2.19631 4.5929 2.83211 3.95711C4.12103 2.66818 5.88062 1.5 8 1.5C10.1194 1.5 11.879 2.66818 13.1679 3.95711C13.8037 4.5929 14.2978 5.23193 14.6327 5.71242C14.7055 5.81677 14.7704 5.91321 14.8273 6C14.7704 6.08679 14.7055 6.18323 14.6327 6.28758C14.2978 6.76807 13.8037 7.4071 13.1679 8.04289C11.879 9.33182 10.1194 10.5 8 10.5C5.88062 10.5 4.12103 9.33182 2.83211 8.04289C2.19631 7.4071 1.70216 6.76807 1.36727 6.28758C1.29454 6.18323 1.22963 6.08679 1.1727 6Z" stroke-width="2" fill="#F9F9F9"/>
                    <path d="M8 3.5C6.61929 3.5 5.5 4.61929 5.5 6C5.5 7.38071 6.61929 8.5 8 8.5C9.38071 8.5 10.5 7.38071 10.5 6C10.5 4.61929 9.38071 3.5 8 3.5ZM4.5 6C4.5 4.067 6.067 2.5 8 2.5C9.933 2.5 11.5 4.067 11.5 6C11.5 7.933 9.933 9.5 8 9.5C6.067 9.5 4.5 7.933 4.5 6Z" stroke-width="2" fill="#F9F9F9"/>
                </svg>
            </a>
            <?php
            } elseif ($table == 'users' && $row['Роль пользователя'] != $row['Запрашиваемая роль']) {
                $modal_users[] = [
                    'id' => $id,
                    'login' => $row['Логин'],
                    'role' => $row['Роль пользователя'],
                    'role_new' => $row['Запрашиваемая роль'],
                ];
                ?>
                <a href="cosmodb.php?table=users&id_change_form%5B%5D=<?=$id?>&action=Одобрить+роль"
                role="button"
                class="btn btn-success d-inline-flex align-items-center justify-content-center px-2 me-1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 24C21.3137 24 24 21.3137 24 18C24 14.6863 21.3137 12 18 12C14.6863 12 12 14.6863 12 18C12 21.3137 14.6863 24 18 24ZM18.6061 13.9653L21.1775 16.5368C21.5123 16.8715 21.5123 17.4142 21.1775 17.7489C20.8428 18.0837 20.3001 18.0837 19.9653 17.7489L18.8571 16.6408V21.4286C18.8571 21.902 18.4734 22.2857 18 22.2857C17.5266 22.2857 17.1429 21.902 17.1429 21.4286V16.6408L16.0347 17.7489C15.6999 18.0837 15.1572 18.0837 14.8225 17.7489C14.4877 17.4142 14.4877 16.8715 14.8225 16.5368L17.3939 13.9653C17.5547 13.8046 17.7727 13.7143 18 13.7143C18.2273 13.7143 18.4453 13.8046 18.6061 13.9653Z" fill="#f9f9f9"/>
                        <path d="M15.4286 5.14286C15.4286 7.98318 13.126 10.2857 10.2857 10.2857C7.44539 10.2857 5.14286 7.98318 5.14286 5.14286C5.14286 2.30254 7.44539 0 10.2857 0C13.126 0 15.4286 2.30254 15.4286 5.14286ZM10.2857 8.57143C12.1793 8.57143 13.7143 7.0364 13.7143 5.14286C13.7143 3.24931 12.1793 1.71429 10.2857 1.71429C8.39217 1.71429 6.85714 3.24931 6.85714 5.14286C6.85714 7.0364 8.39217 8.57143 10.2857 8.57143Z" fill="#f9f9f9"/>
                        <path d="M10.7247 20.5714C10.5311 20.0238 10.3976 19.4477 10.3321 18.8512H1.71429C1.71673 18.4281 1.97787 17.1608 3.14074 15.9979C4.25889 14.8797 6.36151 13.7143 10.2857 13.7143C10.7323 13.7143 11.1552 13.7294 11.5559 13.7578C11.9419 13.1726 12.4054 12.6432 12.9319 12.184C12.1321 12.0654 11.2525 12 10.2857 12C1.71429 12 0 17.1429 0 18.8571C0 20.5714 1.71429 20.5714 1.71429 20.5714H10.7247Z" fill="#f9f9f9"/>
                    </svg>
                </a>
                <a href="cosmodb.php?table=users&id_change_form%5B%5D=<?=$id?>&action=Отклонить+роль"
                role="button"
                class="btn btn-danger d-inline-flex align-items-center justify-content-center px-2 me-1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.3637 14.4241C18.6997 13.3216 16.4357 13.5034 14.9695 14.9695C13.5034 16.4357 13.3216 18.6997 14.4241 20.3637L20.3637 14.4241ZM21.5759 15.6363L15.6363 21.5759C17.3003 22.6784 19.5643 22.4966 21.0305 21.0305C22.4966 19.5643 22.6784 17.3003 21.5759 15.6363ZM13.7574 13.7574C16.1005 11.4142 19.8995 11.4142 22.2426 13.7574C24.5858 16.1005 24.5858 19.8995 22.2426 22.2426C19.8995 24.5858 16.1005 24.5858 13.7574 22.2426C11.4142 19.8995 11.4142 16.1005 13.7574 13.7574Z" fill="#f9f9f9"/>
                        <path d="M15.4286 5.14286C15.4286 7.98318 13.126 10.2857 10.2857 10.2857C7.44539 10.2857 5.14286 7.98318 5.14286 5.14286C5.14286 2.30254 7.44539 0 10.2857 0C13.126 0 15.4286 2.30254 15.4286 5.14286ZM10.2857 8.57143C12.1793 8.57143 13.7143 7.0364 13.7143 5.14286C13.7143 3.24931 12.1793 1.71429 10.2857 1.71429C8.39217 1.71429 6.85714 3.24931 6.85714 5.14286C6.85714 7.0364 8.39217 8.57143 10.2857 8.57143Z" fill="#f9f9f9"/>
                        <path d="M10.7247 20.5714C10.5311 20.0238 10.3976 19.4477 10.3321 18.8512H1.71429C1.71673 18.4281 1.97787 17.1608 3.14074 15.9979C4.25889 14.8797 6.36151 13.7143 10.2857 13.7143C10.7323 13.7143 11.1552 13.7294 11.5559 13.7578C11.9419 13.1726 12.4054 12.6432 12.9319 12.184C12.1321 12.0654 11.2525 12 10.2857 12C1.71429 12 0 17.1429 0 18.8571C0 20.5714 1.71429 20.5714 1.71429 20.5714H10.7247Z" fill="#f9f9f9"/>
                    </svg>
                </a>

                <?php
            }
            ?>
            <a href="cosmodb.php?table=<?= $table ?>&id_change_form%5B%5D=<?=$id?>&action=Редактировать+выбранное"
            role="button"
            class="btn btn-warning d-inline-flex align-items-center justify-content-center px-2 me-1">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.5364 19.2428L13.2787 18.4109C14.1894 17.3902 15.8235 17.5229 16.5576 18.6772C17.2415 19.7525 18.7273 19.9563 19.6755 19.105L21.021 17.8969M2.97876 19.4699L7.34474 18.5902C7.57652 18.5435 7.78934 18.4293 7.95648 18.2621L17.7302 8.48307C18.1988 8.01422 18.1984 7.25423 17.7294 6.78577L15.659 4.71769C15.1902 4.24942 14.4306 4.24974 13.9622 4.7184L4.18752 14.4985C4.02071 14.6654 3.90681 14.8777 3.86006 15.109L2.97876 19.4699Z" stroke="#f9f9f9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <button class="btn btn-danger d-inline-flex align-items-center justify-content-center px-2" data-bs-toggle="modal" data-bs-target="#delete_modal_<?=$id?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6.17647H20M10 16.7647V10.4118M14 16.7647V10.4118M16 21H8C6.89543 21 6 20.0519 6 18.8824V7.23529C6 6.65052 6.44772 6.17647 7 6.17647H17C17.5523 6.17647 18 6.65052 18 7.23529V18.8824C18 20.0519 17.1046 21 16 21ZM10 6.17647H14C14.5523 6.17647 15 5.70242 15 5.11765V4.05882C15 3.47405 14.5523 3 14 3H10C9.44772 3 9 3.47405 9 4.05882V5.11765C9 5.70242 9.44772 6.17647 10 6.17647Z" stroke="#f9f9f9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <?php
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
            $delete_ids[] += $id;
            ?>
        </td>

        <?php
            }
        echo "</tr>";

    }
    ?>
        </tbody>
        </table>
    </div>
    </div>
</div>
    <?php
    if (isset($pending_users)) {
    ?>
    <div class="modal fade" id="pending_users_modal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="pending_users_modal_Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border border-primary rounded-4 shadow">
            <div class="modal-header d-flex justify-content-center border-0 pb-0 pt-3">
                <p class="modal-title fs-5 h1" id="pending_users_modal_Label">Заявки на повышение ролей</p>
            </div>
            <div class="modal-body px-0 pt-3 pb-1">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col" class="text-center">Логин</th>
                            <th scope="col" class="text-center">Роль</th>
                            <th scope="col" class="text-center">Запрашиваемая роль</th>
                            <th scope="col" class="text-center">Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                    foreach ($modal_users as $value) {
                        ?>
                        <tr>
                        <?php
                        foreach ($value as $key => $val) { ?>
                            <td class="text-center"><?=$val?></td>
                            <?php
                        } ?>
                            <td class="text-center">
                                <a href="cosmodb.php?table=users&id_change_form%5B%5D=<?=$value['id']?>&action=Одобрить+роль"
                                role="button"
                                class="btn btn-success d-inline-flex align-items-center justify-content-center px-2 me-1">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18 24C21.3137 24 24 21.3137 24 18C24 14.6863 21.3137 12 18 12C14.6863 12 12 14.6863 12 18C12 21.3137 14.6863 24 18 24ZM18.6061 13.9653L21.1775 16.5368C21.5123 16.8715 21.5123 17.4142 21.1775 17.7489C20.8428 18.0837 20.3001 18.0837 19.9653 17.7489L18.8571 16.6408V21.4286C18.8571 21.902 18.4734 22.2857 18 22.2857C17.5266 22.2857 17.1429 21.902 17.1429 21.4286V16.6408L16.0347 17.7489C15.6999 18.0837 15.1572 18.0837 14.8225 17.7489C14.4877 17.4142 14.4877 16.8715 14.8225 16.5368L17.3939 13.9653C17.5547 13.8046 17.7727 13.7143 18 13.7143C18.2273 13.7143 18.4453 13.8046 18.6061 13.9653Z" fill="#f9f9f9"/>
                                        <path d="M15.4286 5.14286C15.4286 7.98318 13.126 10.2857 10.2857 10.2857C7.44539 10.2857 5.14286 7.98318 5.14286 5.14286C5.14286 2.30254 7.44539 0 10.2857 0C13.126 0 15.4286 2.30254 15.4286 5.14286ZM10.2857 8.57143C12.1793 8.57143 13.7143 7.0364 13.7143 5.14286C13.7143 3.24931 12.1793 1.71429 10.2857 1.71429C8.39217 1.71429 6.85714 3.24931 6.85714 5.14286C6.85714 7.0364 8.39217 8.57143 10.2857 8.57143Z" fill="#f9f9f9"/>
                                        <path d="M10.7247 20.5714C10.5311 20.0238 10.3976 19.4477 10.3321 18.8512H1.71429C1.71673 18.4281 1.97787 17.1608 3.14074 15.9979C4.25889 14.8797 6.36151 13.7143 10.2857 13.7143C10.7323 13.7143 11.1552 13.7294 11.5559 13.7578C11.9419 13.1726 12.4054 12.6432 12.9319 12.184C12.1321 12.0654 11.2525 12 10.2857 12C1.71429 12 0 17.1429 0 18.8571C0 20.5714 1.71429 20.5714 1.71429 20.5714H10.7247Z" fill="#f9f9f9"/>
                                    </svg>
                                </a>
                                <a href="cosmodb.php?table=users&id_change_form%5B%5D=<?=$value['id']?>&action=Отклонить+роль"
                                role="button"
                                class="btn btn-danger d-inline-flex align-items-center justify-content-center px-2 me-1">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.3637 14.4241C18.6997 13.3216 16.4357 13.5034 14.9695 14.9695C13.5034 16.4357 13.3216 18.6997 14.4241 20.3637L20.3637 14.4241ZM21.5759 15.6363L15.6363 21.5759C17.3003 22.6784 19.5643 22.4966 21.0305 21.0305C22.4966 19.5643 22.6784 17.3003 21.5759 15.6363ZM13.7574 13.7574C16.1005 11.4142 19.8995 11.4142 22.2426 13.7574C24.5858 16.1005 24.5858 19.8995 22.2426 22.2426C19.8995 24.5858 16.1005 24.5858 13.7574 22.2426C11.4142 19.8995 11.4142 16.1005 13.7574 13.7574Z" fill="#f9f9f9"/>
                                        <path d="M15.4286 5.14286C15.4286 7.98318 13.126 10.2857 10.2857 10.2857C7.44539 10.2857 5.14286 7.98318 5.14286 5.14286C5.14286 2.30254 7.44539 0 10.2857 0C13.126 0 15.4286 2.30254 15.4286 5.14286ZM10.2857 8.57143C12.1793 8.57143 13.7143 7.0364 13.7143 5.14286C13.7143 3.24931 12.1793 1.71429 10.2857 1.71429C8.39217 1.71429 6.85714 3.24931 6.85714 5.14286C6.85714 7.0364 8.39217 8.57143 10.2857 8.57143Z" fill="#f9f9f9"/>
                                        <path d="M10.7247 20.5714C10.5311 20.0238 10.3976 19.4477 10.3321 18.8512H1.71429C1.71673 18.4281 1.97787 17.1608 3.14074 15.9979C4.25889 14.8797 6.36151 13.7143 10.2857 13.7143C10.7323 13.7143 11.1552 13.7294 11.5559 13.7578C11.9419 13.1726 12.4054 12.6432 12.9319 12.184C12.1321 12.0654 11.2525 12 10.2857 12C1.71429 12 0 17.1429 0 18.8571C0 20.5714 1.71429 20.5714 1.71429 20.5714H10.7247Z" fill="#f9f9f9"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer d-flex justify-content-center border-0 px-0 pb-3 pt-1">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Закрыть</button>

            </div>
            </div>
        </div>
    </div>
    <?php
        foreach ($delete_ids as $id) {?>
                    <div class="modal fade" id="delete_modal_<?=$id?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="delete_modal_<?=$id?>_Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered mt-0 mb-5">
                        <div class="modal-content border border-primary rounded-4 shadow">
                            <div class="modal-header d-flex justify-content-center border-0 pb-0 pt-3">
                                <p class="modal-title fs-5 h1" id="delete_modal_<?=$id?>_Label">
                                    <?= $legend ?>
                                </p>
                            </div>
                            <div class="modal-body px-0 pt-3 pb-1">
                                <p class="h3 text-center">Вы действительно хотите удалить строку?</p>
                            </div>
                            <div class="modal-footer d-flex justify-content-center border-0 px-0 pb-3 pt-1">
                                <button class="btn btn-primary" data-bs-dismiss="modal">Отмена</button>
                                <a href="cosmodb.php?table=<?=$table?>&id_change_form%5B%5D=<?=$id?>&action=Удалить+выбранное&delete=Удалить"
                                type="button" 
                                class="btn btn-danger">
                                Удалить
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
    <?php
        }
    }
} catch(PDOException $e) {
    echo database_eror();
}
    echo "</main>";
}


function view_cards () {
    global $conn, $column_to_search;
    ?>
    <main class="container-fluid mb-5">
        <div class="row mt-3 row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mx-4">
    <?php
    if(isset($_REQUEST['search'])) {
        $sql = user_search();
    } else {
        $sql = "SELECT `id` AS `#`, `achiv_name` AS `Наименование`, `text` FROM space_achiv";
    }
    try {
        $result = $conn -> query($sql);
        while ($row = $result -> fetch()) {
            ?>
            <div class="col d-flex justify-content-center">
            <?php
            // $card_text = explode(" ",$row['text'], 9);
            // unset($card_text[8]);
            // $card_text = implode(" ",$card_text)."...";
            ?>

                    <div class="card w-100" style="height: 18rem;">
                        <img src="../image/placeholder.jpg" 
                        class="card-img-top 
                        overflow-hidden 
                        object-fit-cover
                        h-75 
                        w-100" 
                        alt="...">
                        <div class="card-body d-flex flex-column h-100 mt-auto">
                            <p class="card-title fs-5"><?=$row['Наименование']?></p>
                            <a href="cosmodb.php?article=<?=$row['#']?>" class="btn btn-primary stretched-link mt-auto">Прочесть статью</a>
                        </div>
                    </div>

            </div>
            <?php
        }
    } catch(PDOException $e) {
        echo database_eror();
    }
    ?>
        </div>
    </main>
    <?php
}