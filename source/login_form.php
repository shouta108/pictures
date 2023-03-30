<?php session_start()?>
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>タイトル</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="login_form.css">
    </head>
    <body>
      <div id="login_form">
      <form name="login_form" method="POST">
        <div class="login_form_top">
          <h1>LOGIN</h1>
          <p>UserName、Passwordをご入力の上、「LOGIN」ボタンをクリックしてください。</p>
        </div>
        <div class="login_form_btm">
          <input type="text" name="user_name" placeholder="UserName">
          <input type="password" name="password" placeholder="Password">
          <input type="submit" name="button" value="LOGIN">
          </form>
          <form method="post" action="sign_up.php" >
          <input type="submit" name="submit" value="新規登録">
          </form>   
        </div>
        </div> 
    </body>
</html>

<?php

if(isset($_POST["button"])){
  if(isset($_POST["user_name"]))$user_name = $_POST["user_name"];
  if(isset($_POST["password"]))$PASSWORD = $_POST["password"];
  if($user_name == ""||$PASSWORD ==""){

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
    include 'OriginalException.php';
    try {
      $dbconnect = new connect();
      $stmt = $dbconnect->sql_exe_list("select * from users;");
      $login = false;
      while ($result = $stmt->fetch()) {
        $db_name = $result["user_name"];
        $db_password = $result["user_password"];
        if ($user_name == $db_name && $PASSWORD == $db_password) {
          $user_id = $result["user_id"];
          $user_name = $result["user_name"];
          $login = true;
        }
      }

      $dbconnect->__destruct();
    }catch (Exception $e){
      throw new OriginalException($e);
    }
    

    if ($login) {
      $_SESSION["user_id"] = $user_id;
      $_SESSION["user_name"] = $user_name;
      header('Location:./top.php');
      exit();
    } else {
      echo <<<EOM
      <script type="text/javascript">
  
      if(!alert("ユーザー名またはパスワードが間違っています")){
        history.back();
      }
      </script>
      EOM;
      exit();
    }
  }
}
?>