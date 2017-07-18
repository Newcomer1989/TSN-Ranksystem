<?php
$link = mysql_connect('localhost', 'root', 'pass') or die("Nu se poate efectua conexiunea MYSQL.");
mysql_select_db('dbname', $link);

$dbh = new PDO('mysql:host=localhost;dbname=dbname;charset=utf8', 'root', 'pass');

$conn=mysqli_connect("localhost","root","pass","dbname");

if(!$conn)
{
die("Connection failed: " . mysqli_connect_error());
}


?>