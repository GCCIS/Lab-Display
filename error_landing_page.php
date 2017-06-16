<?php

	//start a session so we can retrieve the variables to 
	//give user feedback about why there was an error
	session_start();
	
	if(isset($_SESSION['roomNumberError'])){
		echo 'There was an error with the room number provided<br>';
		echo 'Error: '.$_SESSION['roomNumberErrorDescription'];
		echo '<br>The room number you entered was: '.$_SESSION['roomNumberError'];
	}
	session_destroy();

?>
<!DOCTYPE html>
	<html>
	<head>
	<title>Error in Input</title>
	</head>

	<body>
		<p>There was an error in your input -- Please make sure to specify room in your URL (Example: www.someurl.com/display.php?room=070-2620)</p>
	</body>

	</html>