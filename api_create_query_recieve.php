<?php
include("functions.php");
$username="ttp708";
$password="\$cfJThXxtRwmGy4q";

$data="username=$username&password=$password";

// Connect to database to be be able to log errors/files recieved

$dblink=db_connect("database");

// Check if previous session exists.

$ch=curl_init('https://cs4743.professorvaladez.com/api/clear_session');
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
echo "\r\nChecking if previous session exists\r\n";
if ($cinfo[0]=="Status: OK" && $cinfo[1]=="MSG: Previous Session Found") // Check if previous session found
{
	echo "Session Succesfully cleared\r\n";
}
else { // Communicate no previous session found
	echo "There was no previous session to be cleared\r\n";
}


// echo $data;

// Once we cleared the potential previous session we can continue with creating a new one

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

if ($cinfo[0]=="Status: OK" && $cinfo[1]=="MSG: Session Created") // Session created successfully
{
	$sid=$cinfo[2];
	$data="sid=$sid&uid=$username";
	echo "\r\nSession Created Successfully!\r\n";
	echo "SID: $sid\r\n";
	echo "Create Session Execution Time: $execution_time\r\n";
	
	// Query files now that session was created
	
	$ch=curl_init('https://cs4743.professorvaladez.com/api/query_files');
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,array(
		'content-type: application/x-www-form-urlencoded',
		'content-length: ' . strlen($data))
	);
	$time_start = microtime(true);
	echo "About to run next!";
	$result = curl_exec($ch);
	$time_end = microtime(true);
	echo "Ran next!";
	$execution_time = ($time_end - $time_start)/60;
	curl_close($ch);
	$cinfo=json_decode($result,true);
	
	if ($cinfo[0]=="Status: OK")
	{
		if ($cinfo[1]=="Action: None") // No new files 
		{
			echo "\r\n No new Files to import found \r\n";
			echo "SID: $sid\r\n";
			echo "Username: $username\r\n";
			echo "Query Files Execution Time: $execution_time\r\n";
			
		}
		
		else // Else for if action is something other than none (There are new files)
		{
			$tmp=explode(":",$cinfo[1]);
			$files=explode(",",$tmp[1]);
			echo "Number of new files to import found: ".count($files)."\r\n";
			echo "Files:\r\n";
			foreach($files as $key=>$value) // Cycle through files to import
			{
				$tmp=explode("/",$value);
				$file=$tmp[4];
				echo "File: $file\r\n";
				$data="sid=$sid&uid=$username&fid=$file";
				$ch=curl_init('https://cs4743.professorvaladez.com/api/request_file');
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HTTPHEADER,array(
					'content-type: application/x-www-form-urlencoded',
					'content-length: ' . strlen($data))
				);
				
				$time_start=microtime(true);
				$result=curl_exec($ch);
				$time_end=microtime(true);
				$execution_time=($time_end - $time_start)/60;
				$content=$result;
				 
				// Storing files imported on local file system (backup in case storing on database ever fails)
				
				$fp=fopen("/var/www/html/recieve/$file","wb");
				fwrite($fp,$content);
				fclose($fp);
				echo "\r\n$file written to file system\r\n";
				
				// Initializing and filling local variables to be stored in the database as well
				
				$temp=explode("-",$file); // [fileid] , [file type], [date,time,file_extension]
				$date_time_ext_temp=explode(".",$temp[2]); // [date,time],[extension]
				$date_time_temp=explode("_",$date_time_ext_temp[0]); // [date], [hour], [minute] ,[sec]
				
				$year=substr($date_time_temp[0],0,4);
				$month=substr($date_time_temp[0],4,-2);
				$day=substr($date_time_temp[0],6,8);
				
				echo $year .  '.' . $month .'.'. $day;
				$date_received= $year .'-'. $month . '-' . $day;
				
				$file_id=$temp[0];
				$user_id=$tmp[3];
				$file_type=$temp[1];
				$file_extension=$date_time_ext_temp[1];
				// Playing with date_time_temp to properly display time
				
				
				$time_received= $date_time_temp[1] . ':' . $date_time_temp[2] . ':' . $date_time_temp[3];
				
				$file_security="none";
				$is_duplicate="0"; // create if statement to check if filename already exists in database
				$last_accessed=$date_received;
				$accessed_by=$user_id;
				$file_altered="0";
				$file_name=$file;
				$file_path="/var/www/html/recieve/";
				$state="active";
				
				$contents_clean=addslashes($content);
				
				$file_content=$contents_clean;
				
				// Now that we have all the appropriate variables , insert them into SQL statement
				
				echo "Im here!";
				$sql="Insert into `receive` (`account_number`,`user_id`,`file_type`,`date_received`,`time_recieved`,`file_security`,`is_duplicate`,`last_accessed`,`accessed_by`,`file_altered`,`file_name`,`file_path`,`file_content`,`state`,`file_extension`) values ('$file_id','$user_id','$file_type','$date_received','$time_received','$file_security','$is_duplicate','$last_accessed','$accessed_by','$file_altered','$file_name','$file_path','$file_content','$state','$file_extension')";
				
				$dblink->query($sql) or
					die("Something went wrong with $sql<br>".$dblink->error);
			}
			echo "Request Files Execution Time:$execution_time\r\n";
		}
		
		// Guarentee session is closed 
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
		
		if ($cinfo[0]=="Status: OK") // Session is cleared 
		{
			echo "Session Successfully closed!\r\n";
			echo "SID: $sid\r\n";
			echo "Close Session execution time: $execution_time\r\n";
		}
		
		else // Session was not cleared, report it to command line
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
	else // Else if Query files failed
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

else // Else if create session failed
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
