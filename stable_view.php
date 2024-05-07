<?php
function view_table ($table) {
    global $conn, $select_query, $user_role;
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
    <main class="container-fluid h-100 mb-5">
        <div class="row row-cols-auto mx-5 mt-5">
            <div class="col px-0">
    <?php
    foreach($table_names as $key => $value) {
        switch ($key) {
            case $table:
                echo "<a href=\"cosmodb.php?table=".$key."\" role=\"button\" class=\"btn rounded-0 rounded-top-4 btn-primary\">".$value."</a>";
                $current_table_name = $value;
                break;
            default:
                echo "<a href=\"cosmodb.php?table=".$key."\" role=\"button\" class=\"btn rounded-0 rounded-top-4 btn-light\">".$value."</a>";
                break;
        }
    }
    ?>
        </div>

        </div>
    <?php
try {
    $result = $conn -> query($select_query);
    ?>
    <form action="cosmodb.php" method="get" class="row mx-4">
    <?php
    echo "<input type=\"hidden\" name=\"table\" value=\"".$table."\" />";
    ?>
    <div class="table-responsive col-11 border border-primary rounded-4 px-0" data-simplebar>
        <table class="table table-hover table-striped mb-0">
    <?php
    $once = 0;
    while ($row = $result -> fetch()) {
        if($once < 1) {echo "<thead><tr>";}
        foreach($row as $key => $value) {
            if (is_string($key) && $once < 1) {echo "<th class=\"text-center\">$key</th>";}
        }
        if($once < 1) {echo "</tr></thead><tbody>";}
        echo "<tr>";
        $once = 1;
        foreach($row as $key => $value) {
            $length = strlen($value);
            switch (true) {
                case (is_string($key)&&$key=='#'):
                    $id=$value;
                    if($table=='users'&&$id==1) {
                        echo "<td>$value</td>";
                    } else {
                        echo "<td class=\"px-0 text-center form-check-input\"><input class=\"input_m\" type=\"checkbox\" name=\"id_change_form[]\" value=\"$value\" readonly />$value</td>";
                    }
                    break;
                case (is_string($key)&&$length<150&&$key!='Страна'&&$key!='Дата'):
                    if($key =='Наименование') {
                        echo "<td>$value</td>";
                    } else {
                        echo "<td class=\"text-center\">$value</td>";
                    }
                    break;
                case (is_string($key)&&$length>150):
                    $substr=mb_substr($value,0,30,'UTF-8');
                    echo "<td >$substr... <a href=\"cosmodb.php?article=$id\">Прочесть статью</a></td>";                    break;
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
        
        echo "</tr>";

    }
    ?>
        </tbody>
        </table>
    </div>
    <div class="col-1 px-4">
        <div class="row">
        <input 
            type="submit" 
            value="Добавить строку" 
            name="action" 
            class="btn btn-outline-primary rounded-0 rounded-top-4 border-bottom-0" 
        />
        </div>

        <div class="row">
        <input 
            type="submit" 
            value="Редактировать выбранное" 
            name="action" 
            class="btn btn-outline-primary rounded-0 border-bottom-0"
        />
        </div>

        <div class="row">
        <input 
            type="submit" 
            value="Удалить выбранное" 
            name="action" 
            class="btn btn-outline-primary rounded-0 rounded-bottom-4"
        />
        </div>

        <!-- <div class="row">
        <input 
            type="submit" 
            value="SQL" 
            name="action"
            class="btn btn-outline-primary rounded-0 rounded-bottom-4"
        />
        </div> -->
    </div>
    </form>
    <?php
} catch(PDOException $e) {
    echo database_eror();
}
    echo "</main>";
}


function view_cards () {
    global $conn;
    ?>
    <main class="container-fluid mb-5">
        <div class="row mt-5 row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mx-4">
    <?php
    $sql = "SELECT `id`, `achiv_name`, `text` FROM space_achiv";
    try {
        $result = $conn -> query($sql);
        while ($row = $result -> fetch()) {
            ?>
            <div class="col d-flex justify-content-center">
            <?php
            $card_text = explode(" ",$row['text'], 9);
            unset($card_text[8]);
            $card_text = implode(" ",$card_text)."...";
            ?>
                <div class="card w-100" style="height: 18rem;">
                    <img src="../image/placeholder.jpg" 
                    class="card-img-top 
                    overflow-hidden 
                    object-fit-cover
                    h-75 
                    w-100" 
                    alt="...">
                    <div class="card-body d-flex flex-column h-100">
                        <p class="card-title fs-5"><?=$row['achiv_name']?></p>
                        <!-- <p class="card-text"><?=$card_text?></p> -->
                        <a href="cosmodb.php?article=<?=$row['id']?>" class="btn btn-primary stretched-link mt-auto">Прочесть статью</a>
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