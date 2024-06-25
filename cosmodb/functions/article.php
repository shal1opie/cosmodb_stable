<?php
function article ($article) {
    global $conn, $refer;
    $sql = "SELECT `text`, `achiv_name` FROM `space_achiv` WHERE `id`=$article";
    $stmt = $conn->query($sql);
    $row = $stmt->fetch();
    $article_text = nl2br($row['text']);
    $article_text = "<p class=\"article-indent h5\">".str_replace("\n", "</p><p class=\"article-indent h5\">", $article_text);
    ?>
    <main class="container mt-5 mb-5">
        <div class="card rounded-top-4 position-relative" style="height: 18rem;">
            <img src="../image/placeholder_<?=$article?>.jpg" class="bg-dark bg-opacity-10 card-img-top object-fit-cover overflow-hidden shadow hover-zoom" alt="...">
            <div class="position-absolute top-0 start-0 end-0 bottom-0 bg-black bg-opacity-50 bg-gradient"></div>
            <div class="card-img-overlay d-flex align-items-end justify-content-center py-0 px-0">
                <div class="bg-black bg-opacity-75 rounded-top-4 w-100 d-flex align-items-end justify-content-center px-1 py-1">
                <p class="card-title fs-1 text-white"><?=$row['achiv_name']?></p>
                </div>
            </div>
        </div>
        <div class="mt-3">
    <?php
    echo $article_text;
    ?>
    </div>
    </main>
    <?php
}