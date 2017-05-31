<?php

	//Gather lab information from the URL
	/*
	* URL must follow these guidelines
	* /display.php?room=070-XXXX
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
	else{
		header('Location: error_landing_page.php');
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
			
			var eventStartTime = [];
			var eventEndTime = [];
			
			
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
						eventStartTime.push(dataObj.data[i].start);
						eventEndTime.push(dataObj.data[i].end);						
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
					var todayDate = moment().format('YYYY-MM-DD');
					
					
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
		
			
			$(document).ready( function(){
				
				//call this to get information from the data file and display it on the page
				getLabInformation();
				
				
				$('#labStaff').load('widget.php');
				refreshLabStaff();
				
				//call function that will set a timeout to consistently refresh the lab status
				refreshLabStatus();
				
			});
			
			/*
			* Refresh the lab staff tables every minute
			* Place the labStaff into the labStaff id
			*/
			function refreshLabStaff(){
				setTimeout( function(){
					$('#labStaff').hide().load('widget.php').show();
					refreshLabStaff();
				}, 60000);
			}
			
			/*
			* Refresh the lab status
			*/
			function refreshLabStatus(){
				setTimeout( function(){
					//get the current time
					var clock = new Date();
					var h = clock.getHours();
					var m = clock.getMinutes();
					var currentTime = h + ":" + m + ":01";
					var currLabStatusText = "Unknown";
					var currentLabStatusColor = "Black";
					
				
					if(h < 8 && h < 22){
						//lab is not open becuase it is after hours
						currLabStatusText = "Closed";
						currentLabStatusColor = "#9e0b0f";
					}
					else if(eventStartTime.length > 0){
						
						//if this is changed to true then there is an event at the current time
						var parsedCurrentTimeIsBetween = false;
						
						//there are events scheduled today --  check if they are happening right now
						for(var i = 0; eventStartTime.length > i; i++){
							
							var parsedStartTime = moment(eventStartTime[i], "HH:mm:ss");
							var parsedEndTime = moment(eventEndTime[i], "HH:mm:ss");
							
							parsedCurrentTimeIsBetween += moment(currentTime, "HH:mm:ss").isBetween(parsedStartTime, parsedEndTime);
						}//end of for
						
						if(parsedCurrentTimeIsBetween == 1){
							currLabStatusText = "Class";
							currentLabStatusColor = "#9e0b0f";
						}
						else{
							//the default for this lab
							currLabStatusText = labDefaultStatus;
							if(currLabStatusText == 'open'){
							currentLabStatusColor = "#197b30";
							}
							else{
								currentLabStatusColor = "#9e0b0f";
							}
						}
						
					}
					else{
						//lab by default is open -- no events scheduled today
						//default for this lab
						currLabStatusText = labDefaultStatus;
						if(currLabStatusText == 'open'){
							currentLabStatusColor = "#197b30";
						}
						else{
							currentLabStatusColor = "#9e0b0f";
						}
					}
					
					$('.top-status').css("background-color", currentLabStatusColor);
					$('.top-status-text').hide().text(currLabStatusText).show();
					refreshLabStatus();
				}, 3000);
			}
			
			//variable holds the default for this lab - this is used in refreshing the lab status
			var labDefaultStatus = "";
			/*
			* Working on getting informaiton about the labs from a data file
			*/
			function getLabInformation(){
				$.get( "Lab_Information.csv", function(data) {
					//data stored by row
					var databyline = data.split("\n");
					
					//arrays for the data
					var arrRoomNumbers = [];
					var arrRoomNames = [];
					var arrLabDefault = [];
					var arrOpenTime = [];
					var arrCloseTime = [];
					
					//get all the data in arrays
					for(var i= 0, len = databyline.length; i < len-1; i++){
						var databyattr = databyline[i+1].split(",");
							arrRoomNumbers.push(databyattr[0]);
							arrRoomNames.push(databyattr[1]);
							arrLabDefault.push(databyattr[2]);
							arrOpenTime.push(databyattr[3]);
							arrCloseTime.push(databyattr[4]);
								
					}//end of for
					
					//for the mini schedule display 
					for(var h = 0, len = arrRoomNames.length; h < len; h++){	
						if(arrLabDefault[h] == "open"){
							$("#labSchedules").append(arrRoomNames[h] + " " + arrRoomNumbers[h] + "<br>");
						}//end of if
						
					}//end of for
					
					
					//put the roomName as the page title
					for(var j = 0, len = arrRoomNumbers.length; j < len; j++){	
						if(arrRoomNumbers[j] == "<?php echo $roomNumber; ?>"){
							$(".top-title-text").append(arrRoomNames[j]);
							labDefaultStatus = arrLabDefault[j];
							
						}
					}
					
				}, "text");
			}
			
		</script>
    </head>
  
  <body>
        <div class="container">
            <div id="top" class="top-container">
                <div id="top" class="top-title">
					<!-- Room name gathered from the URL is placed on the page -->
                    <span class="top-title-text"></span>
                </div>
                <div id="top" class="top-status">
                    <span class="top-status-text"></span>
                </div>
            </div>
				
				<!--Calendar Here-->
				<div id='calendar'></div>
				<div id='labStaffContainer'>
					<div id='labStaff'>
			
						
					</div>
				</div>
				<div id='labSchedules'>
					<p>Open Lab Schedules</p>
				</div>
        </div>
    </body>
</html>

