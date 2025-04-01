<?php
$serverName="localhost:3309";
$dbUsername="root";
$dbPassword="";
$dbName="nexaraso_risi_rasa";

$conn=mysqli_connect($serverName,$dbUsername,$dbPassword,$dbName);

if(!$conn){
    die("connection faild :".mysqli_connect_error());
}



