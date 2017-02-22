<?php
$link = mysql_connect('localhost', 'root', 'forum2gb') or die("Nu se poate efectua conexiunea MYSQL.");
mysql_select_db('ts3_ranksystem', $link);

$dbh = new PDO('mysql:host=localhost;dbname=ts3_ranksystem;charset=utf8', 'root', 'forum2gb');

$conn=mysqli_connect("localhost","root","forum2gb","ts3_ranksystem");

if(!$conn)
{
die("Connection failed: " . mysqli_connect_error());
}


?>