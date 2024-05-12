<?php
function additional_set_up ($auto_reg_form) {
    global $conn, $user_admin_exists;
    switch ($auto_reg_form) {
        case "Регистрация":
            ?>
            <main class="container mt-5 border border-primary rounded-4 p-3">
            <form action="../main/cosmodb.php?auto_reg_form=Регистрация" method = "post">
                <?php if(!$user_admin_exists) { 
                    $value = [
                        "nick_name" => "value = \"admin\"",
                        "email" => "value = \"admin@cfdb.ru\"",
                        "password" => "value = \"admin1337_KU\"",
                    ];
                    $role_set_up = 4;
                    $role_raise_set_up = 4;
                    $have_account = "";
                ?>
                <legend class="h1 text-center text-primary">Регистрация администратора</legend>
                <?php } else {
                        $role_set_up = 1;
                        $role_raise_set_up = 1;
                        $have_account = "<p class=\"h5 text-center mt-3\">Есть аккаунт? <a href=\"cosmodb.php?auto_reg_form=Авторизоваться\">Авторизоваться</a></p>";
                        ?>
                <legend class="h1 text-center text-primary">Регистрация</legend>
                <?php } ?>
                
                <fieldset>
                <label for="nick_name" class="form-label h4">Имя пользователя</label>
                    <input
                        name="nick_name"
                        class="form-control form-control-lg mb-3"
                        id="nick_name"
                        placeholder="Имя пользователя"
                        <?= !$user_admin_exists ? $value['nick_name'] : "" ?>
                        <?= isset($_REQUEST['nick_name']) ? "value = \"".$_REQUEST['nick_name']."\"" : "" ?>
                    />
                <label for="email" class="form-label h4">Эл почта</label>
                    <input
                        type="email"
                        class="form-control form-control-lg mb-3"
                        name="email"
                        id="email"
                        placeholder="Эл почта"
                        <?= !$user_admin_exists ? $value['email'] : "" ?>
                        <?= isset($_REQUEST['email']) ? "value = \"".$_REQUEST['email']."\"" : "" ?>
                        aria-describedby="emailHelp"
                    />            
                <label for="password" class="form-label h4">Пароль</label>
                    <input 
                    type="password"
                    class="form-control form-control-lg mb-3"
                    name="password"
                    id="password"
                    placeholder="Пароль"
                    <?= !$user_admin_exists ? $value['password'] : "" ?>/>
                <label for="password_repeat" class="form-label h4">Повторите пароль</label>
                    <input
                    type="password"
                    class="form-control form-control-lg mb-3"
                    name="password_repeat"
                    id="password_repeat"
                    placeholder="Повторите пароль"
                    <?= !$user_admin_exists ? $value['password'] : "" ?>/>
                <input type="hidden" name="auto_reg_form" value="Регистрация" />


                <?php
            if(isset($_REQUEST['set_up'])&&!empty($_REQUEST['nick_name'])&&!empty($_REQUEST['email'])
            &&!empty($_REQUEST['password'])&&!empty($_REQUEST['password_repeat'])) {
                if($_REQUEST['password']==$_REQUEST['password_repeat']) {
                    $nick_name_set_up = $_REQUEST['nick_name'];
                    $email_set_up = $_REQUEST['email'];
                    try {
                        $stmt1 = $conn -> query("SELECT * FROM `users` WHERE `email` = '$email_set_up'");
                        $stmt1 = $stmt1 -> fetch();
                        $stmt2 = $conn -> query("SELECT * FROM `users` WHERE `nick_name` = '$nick_name_set_up'");
                        $stmt2 = $stmt2 -> fetch();
                        if(!empty($stmt1['id']) || !empty($stmt2['id'])) { ?>
                            <p class="h5 text-danger mt-3">Пользователь с такой почтой или логином уже существует</p>
                            <?php
                        } else {
                        $password_set_up = password_hash($_REQUEST['password'], PASSWORD_DEFAULT);
                        $conn -> exec("INSERT INTO `users` (`id`, `nick_name`, `role`, `role_raise`, `email`, `password`) VALUES ('','$nick_name_set_up', (SELECT `id` FROM `roles` WHERE `id`='$role_set_up'),(SELECT `id` FROM `roles` WHERE `id`='$role_raise_set_up'),'$email_set_up','$password_set_up')");
                        header("Location: ../main/cosmodb.php?auto_reg_form=Авторизоваться");
                        }
                    } catch(PDOException $e) {
                        echo $e->getMessage();
                    }
                } else { ?>
                    <p class="h5 text-danger mt-3">Пароли не совпадают</p>
                    <?php
                }
                } elseif (isset($_REQUEST['set_up'])) {?>
                    <p class="h5 text-danger mt-3">Заполните все поля</p>
                    <?php
                }
                ?>
                            <div class="row mt-3">
                    <div class="col d-flex justify-content-end">
                        <button type="button" onclick="showPassword()" class="btn btn-primary btn-lg">Показать пароль</button>
                    </div>
                    <div class="col d-flex justify-content-start">
                        <input
                            type="submit"
                            value="Зарегистрироваться"
                            name = "set_up"
                            class="btn btn-success btn-lg"
                        />
                    </div>
                </div>
            <?= $have_account ?>
            </fieldset>
            </form>
                <?php
        break;

        case "Авторизоваться":
            ?>
            <main class="container mt-5 border border-primary rounded-4 p-3">
            <form action="../main/cosmodb.php?auto_reg_form=Авторизоваться" method = "post">
                <legend class="h1 text-center text-primary">Вход в систему</legend>
                <fieldset>
                <label for="name_mail" class="form-label h4">Имя пользователя или эл почта</label>
                <input
                    id="name_mail"
                    name="name_mail"
                    placeholder="Имя пользователя или эл почта"
                    class="form-control form-control-lg mb-3"
                />
                <label for="password" class="form-label h4">Пароль</label>
                    <input 
                    type="password"
                    id="password" 
                    name="password" 
                    placeholder="Пароль"
                    class="form-control form-control-lg mb-3"
                    />
                <input type="hidden" name="auto_reg_form" value="Авторизоваться" />
                <div class="row">
        <?php
            if(isset($_REQUEST['set_up'])&&!empty($_REQUEST['name_mail'])&&!empty($_REQUEST['password'])) {
                $name_mail = $_REQUEST['name_mail'];
                $password = $_REQUEST['password'];
                $sql_search_user = "SELECT `nick_name`, `role`, `email`, `password` FROM `users` WHERE (`nick_name`='$name_mail' OR `email`='$name_mail')";
                $result_search_user = $conn->query($sql_search_user);
                if($result_search_user->rowCount() > 0) {
                    while($row = $result_search_user->fetch()) {
                        if(password_verify($password, $row["password"])) {
                            $_SESSION['user_loged_in'] = true;
                            $_SESSION['role'] = $row["role"];
                            $_SESSION['user_name'] = $row["nick_name"];
                            header("Location: cosmodb.php");
                        } else {?>
                        <p class="h5 text-danger mt-3">Неправильный пароль</p>
                            <?php
                        }
                    }
                } else { ?>
                        <p class="h5 text-danger mt-3">Неправильный пароль</p>
                <?php
                }
            } elseif (isset($_REQUEST['enter'])) { ?>
            <p class="h5 text-danger mt-3">Заполните все поля</p>
            <?php
                } ?>
                                    <div class="col d-flex justify-content-center">
                    <input
                        type="submit"
                        value="Войти"
                        name = "set_up"
                        class="btn btn-success btn-lg"
                    />
                    </div>
                </div>
            <p class="h5 text-center mt-3">Нет аккаунта? <a href="cosmodb.php?auto_reg_form=Регистрация">Зарегистрироваться</a></p>
            </fieldset>
            </form>	
			</main>
            <?php
            break;
            }
        ?>
			<script>
		function showPassword() {
			var x = document.getElementsByName("password")[0];
			if (x.type === "password") {
			x.type = "text";
			} else {
			x.type = "password";
			}
			var y = document.getElementsByName("password_repeat")[0];
			if (y.type === "password") {
			y.type = "text";
			} else {
			y.type = "password";
			}
		}
		</script>
		<?php

}