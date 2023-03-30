<?php session_start();
?>
<!DOCTYPE html>
<html lang ="ja">
    <head>
        <meta charset="UTF-8">
        <title>トップ</title>
        <link rel="icon" href="favicon.ico">
        <link rel="stylesheet" href="top.css">
        <link rel="stylesheet" href="root.css">
        <script src="function.js"></script>
        <script>
            onload = (function () {
                batsu();
            })
        </script>
    </head>
    <body>
        <?php
        

        //ログアウト押下時セッション内容を消す
        if (isset($_POST["logout"])) {
            $_SESSION = array();
            setcookie("count", "");
            setcookie("name", "");
            header('Location:./login_form.php');
        }
        //ログイン判定
        if (isset($_SESSION["user_id"])) {
            if (!isset($_COOKIE["name"]) || $_COOKIE["name"] != $_SESSION["user_name"]) {
                $user_name = $_SESSION["user_name"];
                setcookie("name", $user_name);
                $user_name = htmlspecialchars($user_name,ENT_QUOTES, "UTF-8");
                echo <<<EOM
                <script>window.onload = function() {confirm_pop("welcome")};</script>
                <div id="welcome">
                    $user_name さんようこそ!<br>
                    <button id="hiddenpop" onclick="nofunc('welcome')">OK</button>
                </div>
                EOM;
            }
            $login = true;
        } else {
            $login = false;
        }  
        ?>
        <header>
            <span id="logo"><img src="TsuZukiV.png" id="logo_image" oncontextmenu="return false;" onclick="gotop()"></span>
            <form method="post" action="" name="searchform" id="search">
            <input type="hidden" name="sort" value="<?php if (isset($_POST["sort"])) print $_POST["sort"];?>">
                <select name="category" id="select">
                    <option class="category" value="title">タイトル</option>
                    <option class="category" value="tag" <?php if (isset($_POST["category"]) && $_POST["category"] == "tag") print "selected";?>>タグ</option>
                </select>
                <input type="text" id="textbox" name="search" placeholder="検索" autocomplete="off" oninput="batsu()" value=<?php if (isset($_POST["search"])) print htmlspecialchars($_POST["search"],ENT_QUOTES,"UTF-8");?>>
                <span class="batsu" id="clear" onclick="searchclear()"></span>
                <img src="虫眼鏡.png" id="glass" onclick="javascript:searchform.submit()" oncontextmenu="return false;">
            </form>
            <?php
                if ($login) {
                    //ログイン時
                    print <<<EOM
                    <input type=button class=button id=post onclick=location.href="./image_post.php" value="投稿">
                    <input type=button class=button id=tag onclick=location.href='./tag.php' value=タグ>
                    <form method="post" action="">
                    <input type=submit class=button id=logout name=logout value=ログアウト>
                    </form>
                    EOM;
                } else {
                    //ログアウト時
                    print <<<EOM
                    <input type=submit class=button id=login onclick=location.href="login_form.php" value=ログイン>
                    EOM;
                }
            ?>
        </header>
        <main>
            <div class = "sort">
                <form class = "sort" action="" method="POST" name="sortform">
                    <select name = "sort" onchange="javascript:sortform.submit()">
                        <option class="sort" value="descend" <?php if (isset($_POST["sort"]) && $_POST["sort"] == "descend") print "selected";?>>投稿日が新しい順</option>
                        <option class="sort" value="ascend" <?php if (isset($_POST["sort"]) && $_POST["sort"] == "ascend") print "selected";?>>投稿日が古い順</option>
                        <option class="sort" value="comde" <?php if (isset($_POST["sort"]) && $_POST["sort"] == "comde") print "selected";?>>コメントが多い順</option>
                        <option class="sort" value="comas" <?php if (isset($_POST["sort"]) && $_POST["sort"] == "comas") print "selected";?>>コメントが少ない順</option>
                    </select>
                    <input type="hidden" name="search" value=<?php if (isset($_POST["search"])) print htmlspecialchars($_POST["search"]);?>>
                    <input type="hidden" name="category" value=<?php if (isset($_POST["category"])) print $_POST["category"];?>>
                </form>
            </div>
            <div name="images" id="img">
                <?php
                    require_once "DB.php";
                    include 'OriginalException.php';
                    try {
                        if (isset($_POST["category"])) $category = htmlspecialchars($_POST["category"]);
                        if (isset($_POST["search"])) $search = $_POST["search"];
                        $dbconnect = new connect();
                        $searchsql = "";
                        if (!isset($category)) {
                            
                        } else if ($category == "title") {
                            //タイトル検索
                            $searchsql = "where $category like '%$search%' ";
                        } else if ($category == "tag") {
                            //タグ検索
                            $searchsql = "where image_id in (select image_id from tag_details  as a inner join tags as b on a.tag_id = b.tag_id where tag_name = '$search') ";

                            if (isset($_POST["sort"]) && $_POST["sort"] == "comde" || isset($_POST["sort"]) && $_POST["sort"] == "comas") {
                                $searchsql = "where a.image_id in (select image_id from tag_details  as a inner join tags as b on a.tag_id = b.tag_id where tag_name = '$search') ";
                            }
                        }

                        $sql = "select * from images ";

                        $sort = $searchsql." order by post_date desc;";

                        if (isset($_POST["sort"]) && $_POST["sort"] == "ascend") {
                            $sort = $searchsql." order by post_date;";
                        } else if (isset($_POST["sort"]) && $_POST["sort"] == "comde") {
                            $sql = "select a.image_id,a.image_src,a.title from images ";
                            $sort = "as a left outer join comments as b on a.image_id = b.image_id $searchsql group by a.image_id order by count(b.comment_id) desc;";
                        } else if (isset($_POST["sort"]) && $_POST["sort"] == "comas") {
                            $sql = "select a.image_id,a.image_src,a.title from images ";
                            $sort = "as a left outer join comments as b on a.image_id = b.image_id $searchsql group by a.image_id order by count(b.comment_id);";
                        }

                        $sql .= $sort;

                        $stmt = $dbconnect->sql_exe_list($sql);

                        $i = 0;
                        while ($result = $stmt->fetch()) {
                            $id = $result["image_id"];
                            $src = $result["image_src"];
                            $title = $result["title"];
                            $title = htmlspecialchars($title, ENT_QUOTES, "UTF-8");
                            echo <<<EOM
                            <form method="get" name="forms" action="detail.php">
                                <input type="hidden" name="image_id" value="$id">
                                <a id=images href="javascript:forms[$i].submit()">
                                    <img src=$src id="content"><text id="images">$title</text>
                                </a>
                            </form>
                            EOM;
                            $i++;
                        }

                        if ($i == 1) {
                            print <<<EOM
                            <script>
                                let element = document.getElementById("images");
                                let image = element.setAttribute("href", "javascript:forms.submit()");
                            </script>
                            EOM;
                        }
                        
                        if (isset($search) && $i == 0) {
                            $search = htmlspecialchars($search);
                            print <<<EOM
                            $search に一致する画像は見つかりませんでした
                            <script>
                            let element = document.getElementById("img");
                            element.style.display = "flex";
                            </script>
                            EOM;
                        }

                        $dbconnect->__destruct();
                    }catch (Exception $e){
                        throw new OriginalException($e);
                    }
                ?>
            </div>
        </main>
        <div id="fadeLayer"></div>
    </body>
</html>