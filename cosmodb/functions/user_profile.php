<?php
function user_profile () {
    global $user_name, $user_role;
    $logout_svg = file_get_contents("../image/logout_2.svg");
    $goback_svg = file_get_contents("../image/goback.svg");
    $user_roles = [
        "1" => "Пользователь",
        "2" => "Модератор",
        "3" => "Оператор БД",
        "4" => "Администратор",
    ];

    ?>

<main class="container mt-5">
    <?php
    if ($user_role == 4) {
        $dir = '../back_up';
        $files = scandir($dir);
        $files = array_diff($files, array('..', '.'));
    ?>
    <div class="row">
        <div class="col-3">
            <div class="card">
                <!-- <img src="../profile_imgs/photo_2024-04-05_21-35-52.jpg" class="mt-2 object-fit-cover mx-auto rounded-circle" id="profile_pic" alt="..."> -->
                <div class="card-body">
                    <p class="h4 card-title">Логин: <?= $user_name ?></p>
                    <p class="card-text">Роль: <?= $user_roles[$user_role] ?></p>
                    <a href="cosmodb.php?logout=1"
                    role="button" 
                    class="btn btn-danger"
                    >Выйти <?= $logout_svg ?></a>
                </div>
            </div>
        </div>
        <div class="col-9 table-responsive border border-primary rounded-4 px-0" data-simplebar>

                <table class="table table-hover table-striped mb-0">
                    <p class="h2 text-center">Резервные копии</p>
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">Дата создания</th>
                            <th scope="col" class="text-center">Файл</th>
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
                            <td class="text-right"><a href="../back_up/<?= $value ?>" download="<?= $value ?>" class="text-decoration-none text-dark"><?= $value ?></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>

                <a href="../init/create_database_back_up.php" role="button" class="btn btn-primary mt-1 w-100">Создать резервную копию</a>
        </div>
    </div>
    <?php
    } else {
    ?>
    <div class="row">
        <div class="col d-flex justify-content-center">
            <div class="card">
                <div class="card-body">
                    <p class="h4 card-title">Логин: <?= $user_name ?></p>
                    <p class="card-text">Роль: <?= $user_roles[$user_role] ?></p>
                    <a href="cosmodb.php?logout=1"
                    role="button" 
                    class="btn btn-danger"
                    >Выйти <?= $logout_svg ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
</main>
    <?php
}

?>