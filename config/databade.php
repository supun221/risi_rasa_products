<?php
$serverName="localhost";
$dbUsername="root";
$dbPassword="";
$dbName="nexaraso_risi_rasa";

$conn=mysqli_connect($serverName,$dbUsername,$dbPassword,$dbName);

if(!$conn){
    die("connection faild :".mysqli_connect_error());
}
//t


