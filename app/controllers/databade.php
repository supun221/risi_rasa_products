<?php
$serverName="localhost";
$dbUsername="root";
$dbPassword="";
$dbName="nexaraso_grinding_meals";

$conn=mysqli_connect($serverName,$dbUsername,$dbPassword,$dbName);

if(!$conn){
    die("connection faild :".mysqli_connect_error());
}