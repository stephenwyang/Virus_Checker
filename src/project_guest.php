<?php
	//Basic implementation finished. Need to have a better error message and parsing, can be done tomorrow
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	//Make a better error statement later
	if($conn->connect_error) die(mysql_fatal_error("Oh no!"));
	
	session_start();
	destroy_session_and_data();
	
	echo <<<_END
		<html><head><title>Database Upload</title></head><body>
		<form method='post' action='project_guest.php' enctype='multipart/form-data'>
			Select Possible Infected File: <input type='file' name='filename' size='10'>
			<input type='submit' value='Upload'>
		</form>
_END;
	if ($_FILES)
		{
			$name = $_FILES['filename']['name'];
			$name = strtolower(preg_replace("/[^A-Za-z0-9.]/","", $name));
			switch($_FILES['filename']['type']) {
				case 'text/plain'	: $ext = 'txt'; break;
				default				: $ext = ''; break;
			}
			if($ext) {
				$n = "processed.$ext";
				move_uploaded_file($_FILES['filename']['tmp_name'], $n);
				echo "Uploaded file '$name' as '$n': <br>";
				$procFile = fopen($n, 'r') or die ("Failed to create file");
				//Convert bits into a string, then check it against the database
				$contents = fread($procFile, filesize($n));
				$isVirus = 0;
				$virusNames = "";
				$query = "SELECT * FROM malwareinfo";
				$result = $conn->query($query);
				if(!$result) die ("Error, try again");
				$rows = $result->num_rows;
				for($j = 0; $j < $rows; ++$j) {
					$result->data_seek($j);
					$row = $result->fetch_array(MYSQLI_NUM);
					$testStr = $row[1];
					if(preg_match('/'.$testStr.'/', $contents)) {
						$isVirus = $isVirus + 1;
						$virusNames = $virusNames . ' ' . $row[0];
					}
				}
				if($isVirus != 0) {
					echo "File is infected by $isVirus virus(es). Viruses include $virusNames";
				}
				$result->close();
				
				
				fclose($procFile);
	
			}
			else echo "'$name' is not an acceptable txt file";
		}
		else echo "No file uploaded";
		echo "<br>";
		$conn->close();
	function destroy_session_and_data() {
		$_SESSION = array();
		setcookie(session_name(), '', time() - 2592000, '/');
		session_destroy();
	}

?>