<?php
/*
使い方
    引数に判別した文字列を入れる
    文字列の先頭か末尾に半角スペースか全角スペースが含まれていない場合
    true を返す

    含まれている場合
    false を返す
*/
class content_chech {
    public function check_space($content) {
        $bool = "";
        $result = preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $content);
        if ($content === $result) {
            $bool = true;
        } else {
            $bool = false;
        }
        return $bool;
    }
}
?>