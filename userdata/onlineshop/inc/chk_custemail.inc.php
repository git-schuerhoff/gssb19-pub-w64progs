<?php
    chdir("../");
    require_once("inc/class.shopengine.php");
    $cus = new gs_shopengine();
    $dbh = $cus->db_connect();
    $sql = 'SELECT * FROM ' . $cus->dbtoken . 'customer WHERE cusEmail = "' . $_POST['email'] . '"';
    $erg = mysqli_query($dbh,$sql);
    if(mysqli_num_rows($erg) == 1){
        echo "user_exist";
    } else {
        echo "NULL";
    }
    mysqli_free_result($erg);
    mysqli_close($dbh);
?>