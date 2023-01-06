<?php
echo "Hello world, I am php!\r\n";
echo "Hello world, I am php!\r\n";
$hostname="localhost";
$username="webuser";
$password="password";
$db="database";
$mysqli=new mysqli($hostname,$username,$password,$db);
if (mysqli_connect_errno()) 
{
	die("Error connecting to database: ".mysqli_connect_error());
}
$sql="SELECT * FROM `upload` WHERE 1";
$result=$mysqli->query($sql) or 
	die("Something went wrong with $sql".$mysqli->error);
while ($data=$result->fetch_array(MYSQLI_ASSOC))
{
	echo"<p>Entry $data[file_id]: $data[user_id] - $data[file_name]<p>";
}
?>