<link href="assets/css/bootstrap.css" rel="stylesheet" />
<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet" />
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.12.4.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap-fileupload.js"></script>

<?php
echo '<div id="page-inner">';
if (isset($_REQUEST['msg']) && ($_REQUEST['msg']=="success"))
{
	echo '<div class="alert alert-success alert-dismissable">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
	echo 'Document successfully uploaded!</div>';
}
echo '<h1 class="page-head-line">Upload a New File to DocStorage</h1>';
echo '<div class="panel-body">';
echo '<form method="post" enctype="multipart/form-data" action="">';
echo '<input type="hidden" name="user_id" value="user@test.mail">'; // User Id
echo '<input type="hidden" name="" value="">';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000000">';
echo '<div class="form-group">';
echo '<label class="control-label col-lg-4">File Upload</label>';
echo '<div class="">';
echo '<div class="fileupload fileupload-new" data-provides="fileupload">';
echo '<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>';
echo '<div class="row">';//buttons
echo '<div class="col-md-2"><span class="btn btn-file btn-primary"><span class="fileupload-new">Select File</span><span class="fileupload-exists">Change</span>';
echo '<input name="userfile" type="file"></span></div>';
echo '<div class="col-md-2"><a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a></div>';
echo '</div>';//end buttons
echo '</div>';//end fileupload fileupload-new
echo '</div>';//end ""
echo '</div>';//end form-group
echo '<hr>';
echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
echo '</form>';
echo '</div>';//end panel-body
echo '</div>';//end page-inner
if (isset($_POST['submit']))
{
   	$hostname="localhost";
    $username="webuser";
    $password="password";
    $db="database";
    $dblink=new mysqli($hostname,$username,$password,$db);
    if (mysqli_connect_errno())
    {
        die("Error connecting to database: ".mysqli_connect_error());   
    }
	
	// Data variables to insert into database table 'upload' 
	
	$file_name=str_replace(" ", "_", $_FILES['userfile']['name']);
	$user_id=$_POST['user_id'];
	$file_type="pdf";
	$date_created=date("Y-m-d");
	$date_created_name=date("Y-m-d_H:i:s_");
	$time_created=date("H:i:s");
	$file_security="none";
	$is_duplicate="0";
	$last_accessed=date("Y-m-d");
	$accessed_by=$_POST['user_id'];
	$state="active";
	$file_altered="0";
	$file_name=$file_name;
	$file_path="";
	
	// Data variables to be used for everything related to uploading file to database
	
//	$fileName=$_FILES['userfile']['name'];
	$tmpName=$_FILES['userfile']['tmp_name'];
	$fileSize=$_FILES['userfile']['size'];
	$fileType=$_FILES['userfile']['type'];
    $path="/var/www/html/uploads/";
	
	echo $tmpName;
	
	// File pointer to read content of file being uploaded
	
	$fp=fopen($tmpName, 'r');
	$content=fread($fp, filesize($tmpName));
	fclose($fp);
	
	$contents_clean=addslashes($content);
	
	// Upload data to database table
	
	$sql="Insert into `upload` (`user_id`,`file_type`,`date_created`,`time_created`,`file_security`, `is_duplicate`, `last_accessed`, `accessed_by`, `file_altered`, `file_name`, `file_path`, `file_content`, `state`) values ('$user_id', '$file_type', '$date_created', '$time_created', '$file_security', '$is_duplicate', '$last_accessed', '$accessed_by', '$file_altered', '$file_name', '$file_path', '$contents_clean', '$state')";
	$dblink->query($sql) or
		die("Something went wrong with $sql<br>".$dblink->error);
//	$fp=fopen($path.$file_name,"wb") or
//		die("Could not open $path$fileName for writing");
//	fwrite($fp,$content);
//	fclose($fp);
	
//	header("Location: http://192.168.56.102/upload.php?msg=success");
	redirect("upload.php?msg=success");
}
?>