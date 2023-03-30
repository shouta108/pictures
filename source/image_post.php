<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>投稿</title>
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="root.css">
  <link rel="stylesheet" href="image_post.css">
  <script src="function.js"></script>
</head>
<body>
<?php
  require_once "DB.php";
  include "OriginalException.php";
  include "content_check.php";
  $dbconnect = new connect();
  try {
    if (!empty($_FILES)) {
      if($_FILES["upload_image"]["size"] !== 0 && $_FILES["upload_image"]["size"] <= 33554432) {
        if (preg_match("/image/", $_FILES["upload_image"]["type"])) {
          $fp = fopen($_FILES['upload_image']['tmp_name'], "rb");
          $img = fread($fp, filesize($_FILES['upload_image']['tmp_name']));
          fclose($fp);
      
          $enc_img = base64_encode($img);
      
          $imginfo = getimagesize('data:application/octet-stream;base64,' . $enc_img);

          $src = "data:".$imginfo['mime'] . ';base64,'.$enc_img;

          $_SESSION["image_src"] = $src;
        }
      }
    }

    $check = new content_chech();
      
    if (isset($_POST["title"]) && isset($_POST["description"])) {
      if ($_POST["title"] != "" && $_POST["description"] != "") {
        if($check->check_space($_POST["title"]) && $check->check_space($_POST["description"])){
          if (isset($_SESSION["image_src"])) {

            $user_id = $_SESSION["user_id"];
            $title = $_POST["title"];
            $description = $_POST["description"];
  
            $src = $_SESSION["image_src"];
  
            $image = $dbconnect->db->prepare("insert into images (user_id, title, image_src, description, post_date) values(:user_id, :title, :image_src, :description, now());");
  
            $flag = array(":user_id" =>$user_id, ":title" => $title, ":image_src" => $src ,":description" => $description);
  
            $result = $image->execute($flag);
  
            unset($_SESSION["image_src"]);
  
            header('Location:./top.php');
            exit();
          } else {
            print <<<EOM
            <script>
            alert("画像が選択されていません");
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
        
      } else {
        print <<<EOM
        <script>
        alert("タイトルまたは説明が入力されていません");
        </script>
        EOM;
      }
    }

    if (!isset($_SESSION["user_id"])) {
      print <<<EOM
      <script>
      if (!alert("ログインしないと投稿できません")) {
        location.replace("./login_form.php");
      }
      </script>
      EOM;
    }

    if (isset($_SESSION["image_src"]) && $_SESSION["image_src"] != "") {
      $src = $_SESSION["image_src"];
      if (isset($_COOKIE["image_post_colorId"])) {
        $colorId = $_COOKIE["image_post_colorId"];
      } else {
        $colorId = 2;
      }
      print <<<EOM
      <script>
        onload = function () {
          document.getElementById("preview").style.padding = "15px 15px 10px 15px";
          document.getElementById("preview").innerHTML = '<img id="img" src="$src">';
          document.getElementById("color").style.visibility = "visible";
          background_color_change($colorId,'image_post','preview');
          radio($colorId, 'num1', 'num2');
        }
      </script>
      EOM;
    }

    $dbconnect->__destruct();
  } catch (Exception $e) {
    throw new OriginalException($e);
  }
  ?>
<header>
  <input type="button" id="goTop" onclick="gotop()" value="戻る">
</header>
<main>
  <form action="" name="image_form" id="image" method="post" enctype="multipart/form-data">

    <div id="left">
      <label id="file">
        <input type="file" name="upload_image" onchange="preview(this)" accept="image/*">ファイルを選択
      </label>
      <div class="colorchange" id="color">
        <div class = "color" id="num1" onclick="background_color_change(1,'image_post','preview'); radio(1, 'num1', 'num2')"></div>
        <div class = "color" id="num2" onclick="background_color_change(2,'image_post','preview'); radio(2, 'num1', 'num2')"></div>
      </div>
      <div id="preview">ファイルが選択されていません</div>
    </div>

    <div id="right">
      <input type="button" id="submitbutton" value="投稿" onclick="submit_once(this,'image')"><br>

      <textarea name="title" id="title" maxlength="50" placeholder="タイトル"><?php if (isset($_POST["title"])) print $_POST["title"];?></textarea><br>

      <textarea name="description" id="description" maxlength="300" placeholder="説明"><?php if (isset($_POST["description"])) print $_POST["description"];?></textarea>
    </div>
  </form>
  <script>
    function image_submit() {
      document.image_form.submit();
    }
    
    function preview (input) {
      let reader = new FileReader();
      reader.onload = (function (e) {
        if (!input.files[0].type.match("image.*")) {
          if (!alert("画像ファイルのみ投稿できます")) {
            header("./image_post.php");
          }
        }
        if (input.files[0].size > 33554432) {
          if (!alert("32MB以下の画像を投稿できます")) {
            header("./image_post.php");
          }
        }
        document.getElementById("preview").style.padding = "15px 15px 10px 15px";
        document.getElementById("preview").innerHTML = '<img id="img" name="test" src="' + e.target.result + '">';
        let id = <?php if (isset($_COOKIE["image_post_colorId"]))  {
                            print $_COOKIE["image_post_colorId"];
                        } else {
                            print 1;
                        }
                  ?>;
        background_color_change(id,'image_post','preview');
        radio(id, 'num1', 'num2');
        document.getElementById("color").style.visibility = "visible";
      });
      reader.readAsDataURL(input.files[0]);
    }

    window.addEventListener("DOMContentLoaded", () => {
      // textareaタグを全て取得
      const textareaEls = document.querySelectorAll("textarea");

      textareaEls.forEach((textareaEl) => {
        // デフォルト値としてスタイル属性を付与
        textareaEl.setAttribute("style", `height: ${textareaEl.scrollHeight}px;`);
        // inputイベントが発生するたびに関数呼び出し
        textareaEl.addEventListener("input", setTextareaHeight);
      });

      // textareaの高さを計算して指定する関数
      function setTextareaHeight() {
        this.style.height = "auto";
        this.style.height = `${this.scrollHeight}px`;
      }
    });
  </script>
</main>  
</body>
</html>