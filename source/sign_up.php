<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>新規登録</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="sign_up.css">
    <script>
      function login() {
        location.href = "./login_form.php";
      }
    </script>
    </head>
    <body>
        <form name="login_form" method = "POST" action="">
          <div class="login_form_top">
            <h1>新規登録</h1>
          </div>
          <br>
          <div class="login_form_btm">
            <input type="text" name="user_name" size="45" maxlength="20" placeholder="登録名を設定してください。(最大20文字)">
            <input type="password" name="password" size="45" maxlength="20" placeholder="パスワードを設定してください。(最大20文字)">
            <input type="submit" name="SHINKI" value="登録" size="300" >
            <input type="button" class="login" value="ログイン" onclick="login()">
          </div>
        </form>
    </body>
</html>

<?php
include "content_check.php";

$check = new content_chech();

if(isset($_POST["SHINKI"])){
  if(isset($_POST["user_name"])&&$check->check_space($_POST["user_name"])){
    $user_name = $_POST["user_name"];
  }else{
    echo <<<EOM
    <script type="text/javascript">
    if(!alert("文字を入力してください")){
      history.back();
    }
    </script>;
    EOM;
    exit();
  }
  if(isset($_POST["password"])&&$check->check_space($_POST["password"])){
    $PASS = $_POST["password"];
  }else{
    echo <<<EOM
    <script type="text/javascript">
    if(!alert("文字を入力してください")){
      history.back();
    }
    </script>;
    EOM;
    exit();
  }
  if($user_name == ""||$PASS ==""){
    echo <<<EOM
    <script type="text/javascript">

    if(!alert("ユーザー名またはパスワードを入力してください")){
      history.back();
    }
    </script>
    EOM;
    exit();
  
  }else {
    require_once "DB.php";
    include "OriginalException.php";
    try {
      $dbconnect = new connect();
      $bool = true;
      $stmt = $dbconnect ->sql_exe_list("select * from users;");
      while ($result = $stmt->fetch()){
        $name = $result["user_name"];
        $password = $result["user_password"];
        if($user_name == $name && $PASS == $password){
          $bool = false;
        }
      }
      if($bool){
        $sql = "INSERT INTO users (user_name,user_password) VALUES (:name,:password)"; // テーブルに登録するINSERT INTO文を変数に格納　VALUESはプレースフォルダーで空の値を入れとく
        $stmt = $dbconnect->db->prepare($sql); //値が空のままSQL文をセット
        $params = array(':name' => $user_name, ':password' => $PASS); // 挿入する値を配列に格納
        $stmt->execute($params); //挿入する値が入った変数をexecuteにセットしてSQLを実行
        print <<<EOM
        <script>
        if (!alert("登録成功！")) {
          location.href = "login_form.php";
        }
        </script>
        EOM;
      } else {

        echo <<<EOM
        <script type="text/javascript">
    
        if(!alert("既に登録されています。")){
          history.back();
        }
        </script>
        EOM;
        exit();
      }
      
      $dbconnect->__destruct();
    } catch (Exception $e) {
      throw new OriginalException($e);
    }
  } 
}
?>
