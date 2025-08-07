<?php
if(isset($_POST['pass']) && ($_POST['pass'] == "pass"))
{
	if(!isset($_SESSION)) {
		session_start();
	}
	$_SESSION['auth']="ok";
	header("Location: index.php", true, 301);
	  exit();
}
else
{
?>
<!DOCTYPE html>
	<html>
	<head>
	<meta charset="UTF-8">
	  <meta name="robots" content="noindex">
	  <meta name="googlebot" content="noindex">
	  <meta charset="utf-8">
	  <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Access Check</title>
	</head>

	<body>
		<form method="POST" action="login.php">
			<span style="height:40px;font-size:20pt;">Password:</span><br/> <input type="password" name="pass" style="height:40px;font-size:20pt;width:220px;" text="" value=""></input>
			<input type="submit" name="submit" value="Go" style="height:45px;font-size:20pt;width:80px;"></input>
		</form>
	</body>
	</html>

    <?
}
?>