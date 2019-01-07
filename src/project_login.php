<?php
	session_start();
//Login page for the project
//Can either go to the admin page, or the non-admin page
	require_once 'login.php';
	$conn = new mysqli($hn, $un, $pw, $db);
	
	//Make sure to make the username and pass safe 
	if(isset($_POST['username'])) $_SESSION['username'] = mysql_entities_fix_string($conn, $_POST['username']);
	if(isset($_POST['password'])) $_SESSION['password'] = mysql_entities_fix_string($conn, $_POST['password']);
	$_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
	if(!isset($_SESSION['new'])) {
		session_regenerate_id();
		$_SESSION['new'] = 1;
	}
	
	
	//Look for an input, or if the user wants to enter as guest (Not an admin)
echo <<<_END
	<style>
	.signup {
		border:1px solid #999999; font: normal 14px helvetica; color: #444444;
	}
	</style>
	<script type = "text/javaScript">
	function validate(form) {
		fail = validateUsername(form.username.value)
		fail += validatePassword(form.password.value)
		
		if (fail == "") return true
		else { alert(fail); return false }
	}
	
	function validateUsername(field) {
		field = field.replace(/\s/g,'')
		if (field == "") return "No username entered.\n"
		else if(/[^a-zA-Z0-9_-]/.test(field)) return "Only alphanumeric and _ and - accepted.\n"
		return ""
	}
	function validatePassword(field) {
		field = field.replace(/\s/g,'')
		if(field == "") return "No password entered.\n"
		else if(field.length < 12 && (!/[a-z]/.test(field) || ! /[A-Z]/.test(field) ||!/[0-9]/.test(field))
			return "Passwords require one each of a-z, A-Z and 0-9.\n"
		return ""
	}
	
	</script>
	<body>
	<table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
		<th colspan="2" align="center">Login</th>
		<form method="post" action="project_login.php" onSubmit="return validate(this)">
			<tr><td>Username</td>
				<td><input type="text" maxlength="16" name="username"></td></tr>
			<tr><td>Password</td>
				<td><input type="text" maxlength="24" name="password"></td></tr>
			<tr><td colspan="2" align="center"><input type="submit"
				value="Login"></td></tr>
		</form>
	</table>
</body>

_END;

echo '<a href = project_guest.php>Click here to proceed as a guest</a>';
echo '<br>';
	
	if(isset($_SESSION['username']) && isset($_SESSION['password'])) {
		echo '<a href = project_admin.php>Click here to verify as admin</a>';
	}	
	
	$conn->close();
	
	function mysql_fix_string($conn, $string) {
		if(get_magic_quotes_gpc()) $string = stripslashes($string);
		$string = strip_tags($string);		
		$string = trim($string);
		return $conn->real_escape_string($string);
	}
	function mysql_entities_fix_string($conn, $string) {
		return htmlentities(mysql_fix_string($conn, $string));
	}


?>