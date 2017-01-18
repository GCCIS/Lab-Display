<?php

	//Gather lab information from the URL
	/*
	* URL must follow these guidelines
	* /display.php?room=070-XXXX&name=Mac+Lab+1
	* In order to achieve a space in the name you must use + symbol
	*/

	//start the session so we can send the error to the error page
	session_start();
	
	
	if(isset($_GET['room'])){
		$roomNumber = $_GET['room'];
		//sanitize the value passed in
		$roomNumber = trim($roomNumber);
		$roomNumber = stripslashes($roomNumber);
		$roomNumber = strip_tags($roomNumber);
		$roomNumber = htmlspecialchars($roomNumber);
		//the room number does not contain the correct correct length for a room number
		if(strlen($roomNumber)!== 8){
			//send to an error page
			$_SESSION['roomNumberError'] = $roomNumber;
			$_SESSION['roomNumberErrorDescription'] = 'Room Number must be 8 characters (Example: 070-2670)';
			header('Location: error_landing_page.php');
		}
		//the room number does not valid digits
		else if(preg_match("/[A-Za-z]/", $roomNumber)){
			//send to an error page
			$_SESSION['roomNumberError'] = $roomNumber;
			$_SESSION['roomNumberErrorDescription'] = 'Room Number may not contain letters';
			header('Location: error_landing_page.php');
		}
		//the room number contains illegal symbols
		else if(preg_match('/[\[\]\'^£$%&*()}{@#~?!><>,|=_+¬]/', $roomNumber)){
			//send to an error page
			$_SESSION['roomNumberError'] = $roomNumber;
			$_SESSION['roomNumberErrorDescription'] = 'Room number may not contain illegal symbols (Examples: [\'^£$%&*()}{@#~?!><>,|=_+¬])';
			header('Location: error_landing_page.php');
		}
		else if(strpos($roomNumber, '-') !== 3){
			//send to an error page
			$_SESSION['roomNumberError'] = $roomNumber;
			$_SESSION['roomNumberErrorDescription'] = 'Room number must contain a dash (-) at the 4th character';
			header('Location: error_landing_page.php');
		}
		
	}
	if(isset($_GET['name'])){
		$roomName = $_GET['name'];
		//sanitize the value passed in
		$roomName = trim($roomName);
		$roomName = stripslashes($roomName);
		$roomName = strip_tags($roomName);
		$roomName = htmlspecialchars($roomName);
		//room name is not long enough
		if(strlen($roomName) < 4){
			//send to an error page
			$_SESSION['roomNameError'] = $roomName;
			$_SESSION['roomNameErrorDescription'] = 'Room Name must be 4 letters or longer';
			header('Location: error_landing_page.php');
		}
		//room name is too long
		else if(strlen($roomName) > 14){
			//send to an error page
			$_SESSION['roomNameError'] = $roomName;
			$_SESSION['roomNameErrorDescription'] = 'Room Name must be 14 characters or less';
			header('Location: error_landing_page.php');
		}
		//the room name contains illegal symbols
		else if(preg_match('/[\[\]\'^£$%&*()}{@#~?!><>,|=_+¬]/', $roomName)){
			//send to an error page
			$_SESSION['roomNameError'] = $roomName;
			$_SESSION['roomNameErrorDescription'] = 'Room name may not contain illegal symbols (Examples: [\'^£$%&*()}{@#~?!><>,|=_+¬])';
			header('Location: error_landing_page.php');
		}
	}

?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="./css/display.css" />
        <title>Lab-Display</title>
        <script src="https://code.jquery.com/jquery-latest.js"></script>
        <script src="./scripts/clock.js"></script>
		
		<!-- FullCalendar and Scheduler files -->
		<link href='cal/assets/fullcalendar.css' rel='stylesheet' />
		<script src='cal/lib/jquery.min.js'></script>
		<script src='cal/lib/moment.min.js'></script>
		<script src='cal/assets/fullcalendar.js'></script>
		<script type='text/javascript'>
			
			/*
			* Refresh at a certain time each day so the calendar can get new events
			*
			*/
			function refreshAt(hours, minutes, seconds) {
				var now = new Date();
				var then = new Date();

				if(now.getHours() > hours ||
				   (now.getHours() == hours && now.getMinutes() > minutes) ||
					now.getHours() == hours && now.getMinutes() == minutes && now.getSeconds() >= seconds) {
					then.setDate(now.getDate() + 1);
				}
				then.setHours(hours);
				then.setMinutes(minutes);
				then.setSeconds(seconds);

				var timeout = (then.getTime() - now.getTime());
				setTimeout(function() { window.location.reload(true); }, timeout);
			}
			
			
			
			function requestJSON(){
				//insert the room number from the URL to request JSON
				var requestUrl = 'http://api.rit.edu/v1/rooms/'+'<?php echo $roomNumber; ?>'+'/meetings?RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da';
				var request = new XMLHttpRequest();
				request.open('GET', requestUrl);
				request.responseType = 'json';
				request.send();
				return request;
			}//end of requestJSON()
			
			function createCalendar(){
				//Display the calendar and define defaults
				$('#calendar').fullCalendar({
					defaultView: 'agendaDay',
					header:{
						left: false,
						center: false,
						right: false
					},
					minTime: '08:00:00',
					maxTime: '22:00:00',
					contentHeight: 'auto',
					allDaySlot: false,
					defaultDate: '2017-02-01',
					eventBackgroundColor: '#FF3800',
					eventBorderColor: '#4C555C',
					slotDuration: '00:60:00'
				
				});
			}//end of createCalendar()
			
			function createEventObjects(dataObj, todayDate){
				
				//Date and time of all events
				for(var i=0, len = dataObj.data.length; i < len; i++){
					//The Date that the event will begin
					var eventStartDate = dataObj.data[i].date;
					
					//check if the event is on the currentDate -- only render events that will show to save time
					if (todayDate == eventStartDate){
						//Create a proper string of when the events are beginning and ending
						var eventStart = dataObj.data[i].date + ' ' + dataObj.data[i].start;
						var eventEnd = dataObj.data[i].date + ' ' + dataObj.data[i].end;
						
						//creation of the events object in the format fullcalendar requires
						var eventObj = '{"title": "'+ dataObj.data[i].meeting + '","start":"' + eventStart + '", "end": "' + eventEnd + '"}';
						var finalEventObj = JSON.parse(eventObj);
						//Add events to the fullcalendar 
						$('#calendar').fullCalendar( 'renderEvent', finalEventObj);	
						
						
						//working on determining the lab status by adding start and end times to an array
						var eventStartTime = dataObj.data[i].start;
						var eventEndTime = dataObj.data[i].end;						
					}		
				}//end of for
			}//end of createEventObjects
			
			$(document).ready(function() 
			{
				//call function to request JSON
				var request = requestJSON();
								
				request.onload = function() {
					var data = request.response;
					//Contains all the data from the request URL in 
					var dataObj = JSON.parse(JSON.stringify(data));
					
					
					//Today's Date and Time
					//var todayDate = moment().format('YYYY-MM-DD');
					var todayDate = '2017-02-01';
					/* NOT THE CORRECT MINUTES */
					var todayTime = moment().format('HH:MM:SS');
					
					
					//call function to create calendar with no events					
					createCalendar();
					//call function to render events and add them to the calendar
					createEventObjects(dataObj, todayDate);
					
				}//end of request.onload()					
			});//end of .ready()

			
			
			/*
			* Call refresh at 3AM each day
			* This will update the complete page
			*/
			refreshAt(3,0,0);
		</script>
    </head>
  
  <body>
        <div class="container">
            <div id="top" class="top-container">
                <div id="top" class="top-title">
					<!-- Room name gathered from the URL is placed on the page -->
                    <span class="top-title-text"><?php echo $roomName; ?></span>
                </div>
                <div id="top" class="top-status open">
                    <span class="top-status-text">Closed</span>
                </div>
            </div>
				
				<!--Calendar Here-->
				<div id='calendar'></div>
				<div id='labStaffContainer'>
					<div id='labStaff'>

						<?php
							include('scripts/labStaff.php');
							
						?>
						<table id="labAssistantTable">
							<tr>
								<th>Lab Assistants</th>
							</tr>
							<?php
									echo createLabStaff($labAssistantResults, 'LA');
									
							?>
						</table>
						<table id="teachingAssistantTable">
							<tr>
								<th>Teaching Assistants</th>
							</tr>
							<?php
								echo createLabStaff($teachingAssistantResults, 'TA');
							?>
						</table>
					</div>
				</div>
				<div class="bottom-clock">
                    <p id="clock"></p>
                    <p id="fulldate"></p>
                </div>
        </div>
    </body>
</html>

