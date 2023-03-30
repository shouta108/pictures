<?php session_start(); ?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="detail.css">
    <link rel="stylesheet" href="root.css">
    <?php
        require_once "DB.php";
        include "OriginalException.php";
        include "content_check.php";
        try {
            if (isset($_GET["image_id"]) && $_GET["image_id"] != null) {
                $image_id = $_GET["image_id"];

                $dbconnect = new connect();
                $check = new content_chech();

                if (isset($_POST["comment"])){
                    if(isset($_POST["explanation"])&&$check->check_space($_POST["explanation"])){
                        if ($_POST["explanation"] != "") {
                            if (isset($_SESSION["user_id"])) {
                                $comment = $_POST["explanation"];
                                $sql = "INSERT INTO comments (image_id,user_id,comment,comment_date) VALUES (:image_id,:user_id,:comment,now())"; // テーブルに登録するINSERT INTO文を変数に格納　VALUESはプレースフォルダーで空の値を入れとく
                                $stmt = $dbconnect->db->prepare($sql); //値が空のままSQL文をセット
                                $user_id = $_SESSION["user_id"];
                                $params = array(':image_id'=>$image_id,':user_id'=>$user_id,':comment'=>$comment); // 挿入する値を配列に格納
                                $stmt->execute($params); //挿入する値が入った変数をexecuteにセットしてSQLを実行
                            }   
                        } else {
                            echo <<<EOM
                            <script type="text/javascript">
                            if(!alert("文字を入力してください")){
                            }
                            </script>
                            EOM;
                        }
                    }else{
                        echo <<<EOM
                        <script type="text/javascript">
                        if(!alert("文字を入力してください")){
                        }
                        </script>
                        EOM;
                    }
                }

                if (isset($_POST["comment_delete"])) {
                    if (isset($_POST["user_id"]) && $_POST["user_id"] == $_SESSION["user_id"]) {
                        if (isset($_POST["comment_id"])) $comment_id = $_POST["comment_id"];
                        $sql = "delete from comments where comment_id = :comment_id";
                        $stmt = $dbconnect->db->prepare($sql);
                        $params = array(":comment_id"=>$comment_id);
                        $stmt->execute($params);
                    }
                }

                if (isset($_POST["deltag"])) {
                    $tag = $_POST["deltag"];
                    $id = $_POST["del-$tag"];
                    $stmt = $dbconnect->db->prepare("delete from tag_details where image_id = $image_id AND tag_id = $id;");
                    $stmt -> execute();
                }

                if (isset($_POST["settag"])) {
                    $tag = $_POST["settag"];
                    $id = $_POST["set-$tag"];
                    $alltag = $dbconnect->sql_exe_listArray("select * from tag_details;");
                    $bool = true;
                    foreach ($alltag as $t) {
                        if ($t["image_id"] == $image_id && $t["tag_id"] == $id) {
                            $bool = false;
                        }
                    }
                    if ($bool) {
                        $stmt = $dbconnect->db->prepare("insert into tag_details (image_id, tag_id) values($image_id, $id);");
                        $stmt -> execute();
                    }
                }

                $result = $dbconnect->sql_exe_oneLine("select * from images where image_id = $image_id;");
                if ($result) {
                    $user_id = $result["user_id"];
                    $title = htmlspecialchars($result["title"]);
                    $image_src = $result["image_src"];
                    $description = htmlspecialchars($result["description"]);
                    $post_date = $result["post_date"];
                    $result = $dbconnect->sql_exe_oneLine("select * from users where user_id = $user_id;");
                    $user_name = htmlspecialchars($result["user_name"]);
                } else {
                    $title = "エラー";
                }

                $tags = $dbconnect->sql_exe_listArray("select * from tag_details as a inner join tags as b on a.tag_id = b.tag_id where image_id = $image_id order by b.tag_id;");

                $alltag = $dbconnect->sql_exe_listArray("select * from tags where tag_id not in (select tag_id from tag_details where image_id = $image_id);");
            
                $comments = $dbconnect->sql_exe_listArray("select * from comments where image_id = $image_id order by comment_date desc;");//コメントを配列に格納

            } else {
                header("location:top.php");
                exit();
            }

            if (isset($_POST["delete"])) {
                $stmt = $dbconnect->db->prepare("
                delete from tag_details where image_id = :image_id;
                delete from comments where image_id = :image_id;
                delete from images where image_id = :image_id;");
                $stmt-> bindParam(":image_id", $image_id, PDO::PARAM_INT);
                $del = $stmt->execute();
                header("Location:./top.php");
            }

            $dbconnect->__destruct();
        } catch (Exception $e) {
            throw new OriginalException($e);
        }
        ?>
        <title><?php print $title; ?></title>
        <script src="function.js"></script>
        <script>
            window.onload = function () {
                let id = <?php if (isset($_COOKIE["detail_colorId"]))  {
                    print $_COOKIE["detail_colorId"];
                } else {
                    print 1;
                }
                ?>;
                background_color_change(id, "detail","enlarge","imageback");
                radio(id, "num1", "num2");
                <?php
                if (isset($_GET["event"])) {
                    if ($_GET["event"] == 1) {
                        echo 'confirm_pop("tagpop");';
                    }
                }

                if (isset($_POST["settag"]) || isset($_POST["deltag"])) {
                    echo "confirm_pop('tagpop');";
                }
                ?>
            }

            function tag_search(tag_name) {
                var name = document.getElementById("tag_name");
                name.value = tag_name;
                document.tagsearch.submit();
            }

            function invalid_transition(id1 ,id2) {
                let element1 = document.getElementById(id1);
                let element2 = document.getElementById(id2);
                element1.style.transition = "0s";
                element2.style.transition = "0s";
            }
        </script>
</head>
    <body>
        <header>
            <?php
                if (isset($_SESSION["user_id"], $user_id)) {
                    if ($_SESSION["user_id"] == $user_id) {
                        print <<<EOM
                        <div id="deletebox">
                            <form action="" method="post" class="delete" name="deleteform">
                                <input type="hidden" name="image_id" value=$image_id>
                                <input type="hidden" name="delete" value="delete">
                                <input type="button" name="delete" id="delete" value="delete" onclick="return confirm_pop('deletepop')">
                            </form>
                            <div id="deletepop">
                                削除しますか？<br>
                                <button id="ok" onclick="okfunc(deleteform)">削除</button>
                                <button id="no" onclick="nofunc('deletepop')">キャンセル</button>
                            </div>
                        </div>
                        EOM;
                    }
                }
            ?>
            <input type="button" id="goTop" onclick="gotop()" value="戻る">
            <img class = "logo" src="TsuZukiV.png" oncontextmenu="return false;" onclick="gotop()">
        </header>
        <main>
            <?php
                if (!$result) {
                    echo <<<EOM
                        <div style="color :#ff0000; font-size: 20px;">
                        <diV>エラーが発生しました</diV>
                        <div>不正なリクエストです</div>
                        </div>
                    EOM;
                    exit();
                }
            ?>
            <div class = "image" id="imageback">
                <a class = "imga" href="#" onclick="confirm_pop('enlarge')">
                <img class = "img" src="<?php print $image_src; ?>">
                </a>
            </div>
            <div class = "enlarge" id="enlarge" onchange="test()">
                <div class = "enlargehead">
                    <div class = "color" id="num1" onclick="background_color_change(1,'detail','imageback','enlarge'); radio(1, 'num1', 'num2')"></div>
                    <div class = "color" id="num2" onclick="background_color_change(2,'detail','imageback','enlarge'); radio(2, 'num1', 'num2')"></div>
                    <div class = "batsu" onclick="nofunc('enlarge'); invalid_transition('num1' ,'num2')"></div>
                </div>
                <img class="img" id="enlarge" src="<?php print $image_src; ?>">
            </div>
            <div class = "contents">
                <div class = "contentbox" id="title">タイトル <div class="content" id="title"><?php print $title; ?></div></div>
                <div class = "contentbox" id="author">作者 <div class="content" id="author"><?php print $user_name; ?></div></div>
                <div class = "contentbox" id="overview">概要<div class="content" id='description'><?php print $description; ?></div></div>
                <div class = "contentbox" id="tag">タグ
                    <form class="content" name="tagsearch" method="post" action="top.php">
                        <?php
                        print "<input type='hidden' name='category' value='tag'>";
                        print "<input type='hidden' id='tag_name' name='search' value=''>";
                            foreach ($tags as $tag) {
                                $tag_name = htmlspecialchars($tag["tag_name"]);
                                echo <<<EOM
                                <a href="#" id="tags" onclick="tag_search('$tag_name');">$tag_name</a> 
                                EOM;
                            }
                        ?>
                    </form>
                </div>
                <input type="submit" id="tagEdit" onclick="return confirm_pop('tagpop')" value="編集">
                <div id="tagpop">
                    <form method="post" action="" name="tagform">
                        <div id="selected">
                        <?php 
                        $count = 0;
                            foreach ($tags as $tag) {
                                $name = htmlspecialchars($tag["tag_name"]);
                                $id = $tag["tag_id"];
                                echo <<<EOM
                                <input type="hidden" name="del-$name" value=$id>
                                <input type="submit" id="selectedtag-$count" name="deltag" value=$name>
                                EOM;
                                $count++;
                            }
                        if ($count == 0) {
                            print "設定されているタグはありません。";
                        }
                        ?>
                        </div>
                        <div id="alltags">
                            <?php 
                            $count = 0;
                            foreach ($alltag as $tag) {
                                $name = htmlspecialchars($tag["tag_name"]);
                                $id = $tag["tag_id"];
                                echo <<<EOM
                                <input type="hidden" name="set-$name" value=$id>
                                <input type="submit" id="alltag-$count" name="settag" value=$name>
                                EOM;
                                $count++;
                            }
                            if ($count == 0) {
                                print "登録できるタグがありません。";
                            }
                            ?>
                        </div>
                    </form>
                    <form method="post" action="tag.php" name="addtag">
                        <input type="hidden" name="addid" value=<?php print $image_id; ?>>
                        <label>タグ名：<input type="text" id="textbox" name="tag_name" autocomplete="off"></label>
                        <input type="submit" id="add" name="submit" value="追加" onclick="return not_enter_check('textbox')">
                    </form>
                    <button id="no" onclick="nofunc('tagpop')">キャンセル</button>
                </div>
            </div>
        </main>
        <div class="comment">
            <div class="comment_text">コメント</div>
            <?php
                if (isset($_SESSION["user_id"])) {
                    echo <<<EOM
                    <form method="post" action="detail.php?image_id=$image_id" name="com" id="com">
                    <input type="hidden" name="image_id" value="$image_id">
                    <input type="hidden" name="comment" value="コメント">
                    <textarea class="explanation" name="explanation" id="text" placeholder="300文字まで" maxlength="300"></textarea><br>
                    <input type="button" id="commentsubmit" class="btn_submit" value="送信" onclick="submit_once(this, 'com')">
                    </form>
                    EOM;
                }
            ?>
            <div id="comments">
            <?php
            $count = 0;
            $dbconnect = new connect();
                foreach ($comments as $comment) {
                    $count++;
                    $text = $comment["comment"];
                    $text = htmlspecialchars($text,ENT_QUOTES, "UTF-8");
                    $user_id = $comment["user_id"];
                    $comment_id = $comment["comment_id"];
                    $comment_date = $comment["comment_date"];
                    $result = $dbconnect->sql_exe_oneLine("select * from users where user_id = $user_id");
                    $user_name = $result["user_name"];
                    $user_name = htmlspecialchars($user_name, ENT_QUOTES, "UTF-8");
                    $delete = "";
                    if (isset($_SESSION["user_id"])) {
                        if ($user_id == $_SESSION["user_id"]) {
                            $delete = <<<EOM
                            <form method=post action="" id=comment_delete>
                            <input type=hidden name=user_id value=$user_id>
                            <input type=hidden name=comment_id value=$comment_id>
                            <input type=submit name=comment_delete value="削除">
                            </form>
                            EOM;
                        }
                    }
                    echo <<<EOM
                        <div id="comment">
                            <div id="comment_info">
                                <span>ユーザー名: $user_name</span>
                                <span>投稿日時: $comment_date</span>
                                $delete
                            </div>
                            <hr>
                            $text
                        </div>
                    EOM;
                }
                if ($count == 0) {
                    print "コメントはありません。";
                }
                $dbconnect->__destruct();
            ?>
            </div>
        </div>
        <div id="fadeLayer"></div>
    </body>
</html>
