<link href="assets/css/bootstrap.css" rel="stylesheet" />
<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet" />
<!-- JQUERY SCRIPTS -->
<script src="assets/js/jquery-1.12.4.js"></script>
<!-- BOOTSTRAP SCRIPTS -->
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootstrap-fileupload.js"></script>
<?php
include("functions.php");
$dblink=db_connect("database");
echo '<div id="page-inner">';
echo '<h1 class="page-head-line">Search files on DB</h1>';
echo '<div class="panel-body">';

if (!isset($_POST['submit'])) {
	echo '<form action="" method="post">';
	echo '<label>Search String:</label>';
	echo '<input type="text" class="form-control" name="searchString">';
	echo '<select name="searchType">';
	echo '<option value="name">Name</option>';
	echo '<option value="uploadedBy">Uploaded By</option>';
	echo '<option value="uploadDate">Date</option>';
	echo '<option value="active">Active</option>';
	echo '<option value="unactive">Unactive</option>';
	echo '<option value="all">All</option>';
	echo '</select>';
	echo '<hr>';
	echo '<button type="submit" name="submit" value="submit">Search</button>';
	echo '</form>';
}

if (isset($_POST['submit']))

{
	
	$searchType=$_POST['searchType'];
	$searchString=addslashes($_POST['searchString']);
	
	// My SQL statements are only grabbing the necessary data from the database, in this case I had to also grab file_path and file_content to ens
	
	switch($searchType) {
		case "name":
			$sql= "Select `file_name`, `file_path`, `file_content`, `date_created`, `user_id`, `file_id`, `time_created` from `upload` where `file_name` like '%$searchString%'";
			break;
		case "uploadedBy":
			$sql= "Select `file_name`,`file_path`, `file_content`, `date_created`, `user_id`, `file_id`, `time_created` from `upload` where `user_id` like '%$searchString%'";
			break;
		case "uploadDate":
			$sql= "Select `file_name`,`file_path`, `file_content`, `date_created`, `user_id`, `file_id`, `time_created` from `upload` where `date_created` like '%$searchString%'";
			break;
		case "active":
			$sql= "Select `file_name`, `file_path`, `file_content`, `date_created`, `user_id`, `file_id`, `time_created` from `upload` where `state` like '%$searchString%'";
			break;
		case "unactive":
			$sql= "Select `file_name`, `file_path`, `file_content`, `date_created`, `user_id`, `file_id`, `time_created` from `upload` where `state` like '%$searchString%'";
			break;
		case "all":
			$sql= "Select `file_name`, `file_path`, `file_content`, `date_created`, `user_id`, `file_id`, `time_created` from `upload`";
			break;
		default:
			redirect("search.php?msg=searchTypeError");
			break;
	}
	$result=$dblink->query($sql) or
		die("Something went wrong with $sql<br>".$dblink->error);
	
	
	echo '<table class="table"';
	echo '<tr>';
		echo '<th>Owned By</th>';
		echo '<th>File Name</th>';
		echo '<th>Date Created</th>';
		echo '<th>Time Created</th>';
		echo '<th>Action</th>';
	echo '</tr>';
	while ($data=$result->fetch_array(MYSQLI_ASSOC))
	{
		if ($data['file_path']!=NULL) 
		{
			echo '<p>We satify this</p>';
			echo '<p>File: <a href="uploads/'.$data['file_name'].'">'.$data['file_name'].'</a></p>';
		}
		else {
			$content=$data['file_content'];
			$file_name=$data['file_name'];
			$fname=$file_name;
			if (!($fp=fopen("/var/www/html/uploads/$fname","w")))
				echo "<p>File could not be loaded at this time</p>";
			else
			{
				fwrite($fp,$content);
				fclose($fp);
				echo '<tr>';
					echo '<td>'.$data['user_id'].'</td>';
					echo '<td>'.$data['file_name'].'</td>';
					echo '<td>'.$data['date_created'].'</td>';
					echo '<td>'.$data['time_created'].'</td>';
				
					// I believe an if statement wrapping around this echo would be necessary to validate that the person trying to open //
					// the document would be appropriate to ensure they have the permissions for it.									 //
																																		 //
					echo '<td><a href="uploads/'.$fname.'"target="_blank" name="clicked_id" value="'.$data['file_id'].'">View</a></td>'; //
																																		 //
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				echo '</tr>';
				
			}
		
		}
		
		
	}
}
echo '</div>';//end panel-body
echo '</div>';//end page-inner
?>