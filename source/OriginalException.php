<?php
class OriginalException extends Exception{

    function __construct(Exception $e) {
        
        switch (get_class($e)){
            case 'PDOException':
                $msg = "SQLエラー";
                switch ($e->getCode()){
                    case '1045':
                        $msg = 'DB接続エラー（ユーザー名エラー）';
                        break;
                    case '2002':
                    case '2003':
                        $msg = 'DB接続エラー（サーバー停止）';
                        break;
                    case '2005':
                        $msg = 'DB接続エラー（ホストエラー）';
                        break;
                    case "23000":
                        echo <<<EOM
                        <script>
                        if (!alert("データが紐付けされているため削除できません")) {
                            history.back();
                        }
                        </script>
                        EOM;
                        exit();
                        break;
                }
                exit($msg.' : メンテナンスへ連絡してください<br>');
                break;
            case 'Exception':
                exit('エラー : メンテナンスへ連絡してください<br>');
                break;
            default:
                echo '予定外のエラーが発生しました。';
        }
    }
}
?>