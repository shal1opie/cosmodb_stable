<?php
function change_table () {
    global $table, $user_name, $user_role, $user_loged_in, $user_role;
    $logo = file_get_contents("../image/logo.svg");
    $settings_svg = file_get_contents("../image/settings.svg");
    $logout_svg = file_get_contents("../image/logout_2.svg");
    $user_roles = [
        "1" => "Статьи",
        "2" => "Модератор",
        "3" => "Редактирование базы данных",
        "4" => "Панель администратора",
    ];
    switch (true) {
        case (!isset($_REQUEST['action'])&&$user_loged_in&&$user_role!=0&&!isset($_REQUEST['user_profile'])&&!isset($_REQUEST['article'])):
    ?>
            <header class="container-fluid row mt-1">
                <div class="col d-flex justify-content-start align-items-end mx-4">
                    <?= $logo ?>
                </div>
                <div class="col d-flex justify-content-center align-items-end mx-4">
                    <strong class="h1 pb-2"><?= $user_roles[$user_role] ?></strong>
                </div>
                <div class="col d-flex justify-content-end align-items-end mx-4">
                    <strong class="h1 pb-2"><?= $user_name ?>
                        <a href="cosmodb.php?user_profile=<?= $user_name?><?= $user_role == 4 ? "&back_up=1" : ""?>"
                        role="button" 
                        class="btn btn-outline-light px-1 py-1 d-inline-flex align-items-center justify-content-center border-dark border-2 rounded-circle"><?= $settings_svg ?></a>
                        <button
                        class="btn btn-danger px-1 py-1 
                        d-inline-flex align-items-center justify-content-center 
                        border-3 rounded-circle"
                        data-bs-toggle = "modal" 
                        data-bs-target = "#logout_modal"><?= $logout_svg ?></button>
                    </strong>
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
                        <a href="<?= $user_role==1?"cosmodb.php":"cosmodb.php?table=$table"?>"
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