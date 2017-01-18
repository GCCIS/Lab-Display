<?php

	//start a session so we can retrieve the variables to 
	//give user feedback about why there was an error
	session_start();
	
	if(isset($_SESSION['roomNumberError'])){
		echo 'There was an error with the room number provided<br>';
		echo 'Error: '.$_SESSION['roomNumberErrorDescription'];
		echo '<br>The room number you entered was: '.$_SESSION['roomNumberError'];
	}
	else if(isset($_SESSION['roomNameError'])){
		echo 'There was an error with the room name provided<br>';
		echo 'Error: '.$_SESSION['roomNameErrorDescription'];
		echo '<br>The room name you entered was: '.$_SESSION['roomNameError'];
	}
	session_destroy();

?>
<!DOCTYPE html>
	<html>
	<head>
	<title>Error in Input</title>
	</head>

	<body>
		<p>There was an error in your input -- Please make sure to specify room and name in your URL (Example: www.istlabs/display.php?room=070-2620&name=Net+Lab)</p>
	</body>

	</html>