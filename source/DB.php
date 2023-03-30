<?php
class connect{
    public $db;

    // コンストラクタ（DB接続）
    function __construct() {
        $this->db = new PDO('mysql:host=localhost;dbname=pictures;charset=utf8', 'root', 'admin');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    // SQL実行（複数件）
    public function sql_exe_list($sql){
        return $this->db->query($sql);
    }
    // SQL実行（１件）
    public function sql_exe_oneLine($sql){
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    // SQL実行（１件１項目）
    public function sql_exe_oneColumn($sql){
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn();
    }
    // SQLを配列に格納(複数件)
    public function sql_exe_listArray($sql) {
        $stmt = $this->db->query($sql);
        $array = array();
        while($result = $stmt-> fetch()) {
            array_push($array, $result);
        }
        return $array;
    }
    // デストラクタ（DB切断）
    function __destruct() {
        $this->db = null;
    }
}


?>