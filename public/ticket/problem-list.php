<?php

global $conn;
set_time_limit(0);
session_start(); /* Starts the session */
date_default_timezone_set("Asia/Dhaka");
//include 'pack-function-stag.php'; // for API functions
include 'bq-conn.php'; //for bigquery connection
include 'db-conn.php'; //for Database connection

$complain_id = $_POST['complain_id'];

if (isset($complain_id) && $complain_id != 'Others') {
    $sql = "SELECT * FROM ts_payment_complain_list where id={$complain_id} ORDER BY id asc";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $complain_listArr = $result->fetch_assoc();
        //echo "<pre>";
        //print_r($complain_listArr);
        $complain_details = $complain_listArr['complain_details'];
        echo $complain_details;
    } else {
        echo "";
    }
} else {
    echo "";
}
