<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>タグ</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="root.css">
    <link rel="stylesheet" href="tag.css">
    <script src="function.js"></script>
</head>
<body>
    <?php
    require_once "DB.php";
    include "OriginalException.php";
    try {
      $dbconnect = new connect();
      if(isset($_POST["submit"])){
        if(isset($_POST["tag_name"])){
            $tag_name = $_POST["tag_name"];
          if($tag_name == ""){            
            echo <<<EOM
            <script>
              if(!alert("タグ名を入力してください")){
                history.back();
              }
            </scrip>
            EOM;
            exit();
          }else{
            if (!preg_match('/( |　)/',$tag_name)) {
            $bool = true;
            $tags = $dbconnect->sql_exe_listArray("select * from tags;");
            foreach ($tags as $tag) {
              $name = $tag["tag_name"];
              if($tag_name == $name){
                $bool = false;
              }
            }
            if($bool){
                $sql = "INSERT INTO tags (tag_name) VALUES (:name)"; // テーブルに登録するINSERT INTO文を変数に格納　VALUESはプレースフォルダーで空の値を入れとく
                $stmt = $dbconnect->db->prepare($sql); //値が空のままSQL文をセット
                $params = array(':name' => $tag_name); // 挿入する値を配列に格納
                $stmt->execute($params); //挿入する値が入った変数をexecuteにセットしてSQLを実行
                if ($_POST["submit"] == "追加" && isset($_POST["addid"])) {
                  $addid = $_POST["addid"];
                  header("location:./detail.php?image_id=$addid&event=1");
                  exit();
                }
            }else{
                echo <<<EOM
                  <script>
                    alert("既に登録されています。")
                  </script>
                EOM;
                if ($_POST["submit"] == "追加" && isset($_POST["addid"])) {
                  $addid = $_POST["addid"];
                  echo <<<EOM
                  <script>
                    location.href="./detail.php?image_id=$addid&event=1";
                  </script>
                  EOM;
                  exit();
                } else {
                  echo <<<EOM
                  <script>
                    history.back();
                  </script>
                  EOM;
                  exit();
                }
            }
            } else {
              echo <<<EOM
                <script>
                  alert("スペースは使えません")
                </script>
              EOM;
              if ($_POST["submit"] == "追加" && isset($_POST["addid"])) {
                $addid = $_POST["addid"];
                echo <<<EOM
                <script>
                  location.href="./detail.php?image_id=$addid&event=1";
                </script>
                EOM;
                exit();
              } else {
                echo <<<EOM
                <script>
                  history.back();
                </script>
                EOM;
                exit();
              }
            }
          }
        }
      }

      if (isset($_POST["tag_name"])) {
        $name = $_POST["tag_name"];
        if (isset($_POST["delete-$name"])) {
          $id = $_POST["delete-$name"];
          $stmt = $dbconnect->db->prepare("delete from tags where tag_id = $id;");
          $stmt -> execute();
        }
      }

      $tags = $dbconnect->sql_exe_listArray("select * from tags;");
      
      $dbconnect->__destruct();
    } catch (Exception $e) {
      throw new OriginalException($e);
    }
    ?>
    <header>
      <input type="button" id="goTop" onclick="return gotop()"value="戻る">
    </header>
    <main>
      <form method = "POST" action="" class="in">
        <label>  
        タグ名:<input type="text" id="tag_name" name="tag_name" size="30" maxlength="30" placeholder="最大30文字" autocomplete="off">
        </label>
        <input type="submit" name="submit" value="登録" onclick="return not_enter_check('tag_name')">
      </form>
      <br>
      <div id="tag">※使われていないタグをクリックすると削除できます</div>
      <form action="" method="POST" class="out">
      <?php
        foreach ($tags as $tag) {
          $name = $tag["tag_name"];
          $name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
          $id = $tag["tag_id"];
          echo <<<EOM
            <input type="hidden" name="delete-$name" value=$id>
            <input type="submit" name="tag_name" value="$name">
          EOM;
        }
      ?>
      </form>
    </main>
</body>
</html>