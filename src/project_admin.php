<?php
	session_start();
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	if($conn->connect_error) die(mysql_fatal_error("Oh no!"));
	$correctPass = 0;
	
	if($_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {
		no_info_error();
		destroy_session_and_data();
	}
	if(isset($_SESSION['username']) && isset($_SESSION['password'])) {
		$user = $_SESSION['username'];
		$pass = $_SESSION['password']; 
	}
	
	$query = "SELECT * FROM users WHERE username = '$user'";
	$res = $conn->query($query);
	if(!$res) die(mysql_fatal_error("Oh no!"));
	else if($res->num_rows) {
		$row = $res->fetch_array(MYSQLI_NUM);
		$salt = $row[4];
		$token = hash('ripemd128', $salt.$pass.$salt);
		if($token == $row[5]) {
			$correctPass = 1;
		}
	}
	
	if($correctPass) {


echo <<<_END
		<html><head><title>Admin Page</title></head><body>
		<form method='post' action='project_admin.php' enctype='multipart/form-data'>
		<tr><td>Name of Malware</td>
				<td><input type="text" maxlength="20" name="virusName"></td></tr>
			Select File: <input type='file' name='filename' size='10'>
			<input type='submit' value='Upload'>
		</form>
_END;
	echo "Only accepts .txt files for security sake <br>";
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
				//Read the first 20 bytes, store that in the database
				while(!feof($procFile)) {
					$val = fread($procFile, 20);
					$vName = get_post($conn, 'virusName');
					$vName = mysql_entities_fix_string($conn, $vName);
					$checker = validate_virus_name($vName);
					if($checker == "") {
						$sql = "INSERT INTO malwareinfo VALUES ('$vName', '$val')";
						$result = $conn->query($sql);
						if(!$result) die ("Error, try again");
					}
					else {
						echo "$checker";
					}
					break;
				}
				fclose($procFile);
	
			}
			else echo "'$name' is not an acceptable txt file";
		}
		echo "<br>";
		
echo '<a href = project_guest.php>Click here to check if a file is infected</a>';

	$conn->close();
	
	}
	
	else {
		no_info_error();
		$conn->close();
	}

	





	function mysql_fix_string($conn, $string) {
		if(get_magic_quotes_gpc()) $string = stripslashes($string);
		$string = strip_tags($string);		
		$string = trim($string);
		return $conn->real_escape_string($string);
	}
	function mysql_entities_fix_string($conn, $string) {
		return htmlentities(mysql_fix_string($conn, $string));
	}
	
	function no_info_error() {
		echo "Error, please try logging in again";
	}

	function destroy_session_and_data() {
		$_SESSION = array();
		setcookie(session_name(), '', time() - 2592000, '/');
		session_destroy();
	}
	function get_post($conn, $var) {
		return $conn->real_escape_string($_POST[$var]);
	}
	function mysql_fatal_error($msg) {
		$msg2 = mysl_error();
		echo <<<_END
		Unfortunatley, we were unable to complete the requested 
		task. The error message for this reason was :
			<p>$msg: $msg2</p>
		Feel free to go back and try again after fixing the error.
		If there are still problems, please message the site 
		administrator. Thank you.
_END;
	}
	function validate_virus_name($s) {
		$s = preg_replace('/\s+/', '', $s);
		if($s == "") return "No name was entered <br>";
		if (!preg_match('/[a-zA-Z0-9]/', $s)) {
				return "Name should be alphanumeric";
		}
		return "";	
	}





?>