<?php
include("functions.php");
$username="ttp708";
$password="\$cfJThXxtRwmGy4q";

$data="username=$username&password=$password";

// Connect to database to be able to log error information

$dblink=db_connect("database");


// echo $data;

$ch=curl_init('https://cs4743.professorvaladez.com/api/create_session');
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencoded',
	'content-length ' . strlen($data)));

$time_start = microtime(true);
$result = curl_exec($ch);
$time_end = microtime(true);
$execution_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);

if ($cinfo[0]=="Status: OK" && $cinfo[1]=="MSG: Session Created") 
{
	$sid=$cinfo[2];
	$data="sid=$sid&uid=$username";
	echo "\r\nSession Created Successfully!\r\n";
	echo "SID: $sid\r\n";
	echo "Create Session Execution Time: $execution_time\r\n";
	
	// Close Session now that it was sucessfully created
	
	$ch=curl_init('https://cs4743.professorvaladez.com/api/close_session');
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array(
		'content-type: application/x-www-form-urlencoded',
		'content-length: ' . strlen($data))
	);
	$time_start = microtime(true);
	$result = curl_exec($ch);
	$time_end = microtime(true);
	$execution_time = ($time_end - $time_start)/60;
	curl_close($ch);
	$cinfo=json_decode($result,true);
	
	if ($cinfo[0]=="Status: OK")
	{
		echo "Session Successfully closed!\r\n";
		echo "SID: $sid\r\n";
		echo "Close Session execution time: $execution_time\r\n";
	}
	
	else 
	{
		// Replace this with writing to the error_log database
			echo $cinfo[0];
			echo "\r\n";
			echo $cinfo[1];
			echo "\r\n";
			echo $cinfo[2];
			echo "\r\n";
	}
	
}

else 
{
	// Replace this with writing to the error_log database
		echo $cinfo[0];
		echo "\r\n";
		echo $cinfo[1];
		echo "\r\n";
		echo $cinfo[2];
		echo "\r\n";
}

?>