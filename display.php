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
		
		<!-- FullCalendar and Scheduler files -->
		<link href='cal/assets/fullcalendar.css' rel='stylesheet' />
		<script src='cal/lib/jquery.min.js'></script>
		<script src='cal/lib/moment.min.js'></script>
		<script src='cal/assets/fullcalendar.js'></script>
		
		
		<script type="text/javascript">
			//when an event starts and ends - used while creating the calendar
			var eventStartTime = [];
			var eventEndTime = [];
			//variable holds the default for this lab - this is used in refreshing the lab status
			var labDefaultStatus = "";
			//time this lab opens and closes - from the csv file
			var labOpenTime = "";
			var labCloseTime = "";
			
			/*
			* Refresh the entire page at a certain time each day 
			* so the calendar can get new events
			* The hours, minutes, seconds will be the exact time set to refresh each day
			* @param {number} hours - the hour the page will refresh at
			* @param {number} minutes - the minutes the page will refresh at
			* @param {number} seconds - the seconds the page will refresh at
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
			}//end of refreshAt
					
			/*
			* Request the JSON from the API based on the roomnumber and todays date
			* Once it is request return the request to be processed
			* @param {string} currentRoomNumber - the room number being shown on this page (from the URL)
			* @param {string} todayDate - the date today
			* @return {object} request - contains JSON objects based on currentRoomNumber and todays date
			*/
			function requestJSON(currentRoomNumber, todayDate){
				//insert the room number from the URL to request JSON
				var requestUrl = "http://api.rit.edu/v1/rooms/"+currentRoomNumber+"/meetings?date="+todayDate+"&RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da";
				var request = new XMLHttpRequest();
				request.open("GET", requestUrl);
				request.responseType = "json";
				request.send();
				return request;
			}//end of requestJSON()
		
			/*
			* Creates all the events for the calendar in a format that fullcalendar can understand
			* Then adds all events to the calendar
			* @param {object} dataObj - parsed JSON data from the request
			*/
			function createEventObjects(dataObj){
				//Date and time of all events
				for(var i=0, len = dataObj.data.length; i < len; i++){
					//The Date that the event will begin
					var eventStartDate = dataObj.data[i].date;
					

						//Create a proper string of when the events are beginning and ending
						var eventStart = dataObj.data[i].date + " " + dataObj.data[i].start;
						var eventEnd = dataObj.data[i].date + " " + dataObj.data[i].end;
						
						//creation of the events object in the format fullcalendar requires
						var eventObj = '{"title": "'+ dataObj.data[i].meeting + '","start":"' + eventStart + '", "end": "' + eventEnd + '"}';
						var finalEventObj = JSON.parse(eventObj);
						//Add events to the fullcalendar 
						$("#calendar").fullCalendar( "renderEvent", finalEventObj);	
						
						
						//working on determining the lab status by adding start and end times to an array
						eventStartTime.push(dataObj.data[i].start);
						eventEndTime.push(dataObj.data[i].end);						
							
				}//end of for
			}//end of createEventObjects
			
			/*
			* This ready function creates the calendar and calendar events
			* it also gets the information from the csv file, creates the mini schedule, 
			* calls roomNameAndStatus, and calls refreshLabStaff
			*/			
			$(document).ready(function(){
				
				//Today's Date and Time
				var todayDate = moment().format('YYYY-MM-DD');
					
				//call function to request JSON
				var currentRoomNumber = "<?php echo $roomNumber; ?>";
				var request = requestJSON(currentRoomNumber, todayDate);
								
				request.onload = function() {
					
					//arrays for the data
					var arrRoomNumbers = [];
					var arrRoomNames = [];
					var arrLabDefault = [];
					var arrOpenTime = [];
					var arrCloseTime = [];
					//call this to get information from the data file and display it on the page
					$.get( "Lab_Information.csv", function(data2) {
						//data stored by row
						var databyline = data2.split("\n");
						
						//get all the data in arrays
						for(var i= 0, len = databyline.length; i < len-1; i++){
							var databyattr = databyline[i+1].split(",");
							arrRoomNumbers.push(databyattr[0]);
							arrRoomNames.push(databyattr[1]);
							arrLabDefault.push(databyattr[2]);
							arrOpenTime.push(databyattr[3]);
							arrCloseTime.push(databyattr[4]);
								
							//For each lab that is defalt open - show the name and whether there are upcoming classes
							if(databyattr[2] == "open"){
								createMiniSchedule(databyattr[1], databyattr[0], databyattr[4], databyattr[3], todayDate);
							}//end of if
						}//end of for
						
						//this is the data from the api
						var data = request.response;
						//Contains all the data from the request URL in 
						var dataObj = JSON.parse(JSON.stringify(data));
						
						//put the roomName as the page title and call function to update lab status
						roomNameAndStatus(arrRoomNumbers, arrRoomNames, arrLabDefault, arrOpenTime, arrCloseTime);
						//call function to create calendar with no events					
						createCalendar();
						//call function to render events and add them to the calendar
						createEventObjects(dataObj);
					}, "text");	//end of $.get()
				}//end of request.onload()	
	
				$("#labStaff").load("widget.php");
				refreshLabStaff();
				
			});//end of .ready()
			
			/*
			* Display the room name and room status
			* @param {array} arrRoomNumbers - room numbers in string format
			* @param {array} arrRoomNames - room names in string format
			* @param {array} arrLabDefault - lab defaults in string format
			* @param {array} arrOpenTime - lab open time in string format
			* @param {array} arrCloseTime - lab close time in string format
			*/
			function roomNameAndStatus(arrRoomNumbers, arrRoomNames, arrLabDefault, arrOpenTime, arrCloseTime){
				//put the roomName as the page title
					for(var j = 0, len = arrRoomNumbers.length; j < len; j++){	
						if(arrRoomNumbers[j] == "<?php echo $roomNumber; ?>"){
							$(".top-title-text").append(arrRoomNames[j]);
							labDefaultStatus = arrLabDefault[j];
							labOpenTime = arrOpenTime[j];
							labCloseTime = arrCloseTime[j];
							
							//call function that will set a timeout to consistently refresh the lab status
							refreshLabStatus(arrOpenTime[j],arrCloseTime[j]);
						}
					}
			}//end of roomNameAndStatus
			
			/*
			* Create the mini schedule for each room that has a default of open to show if there are upcoming classes
			* @param {string} roomName - the room name
			* @param {string} roomNumber - the room number
			* @param {string} closeTime - the time the lab closes
			* @param {string} openTime - the time the lab opens
			* @param {string} todayDate - the date today
			*/
			function createMiniSchedule(roomName, roomNumber, closeTime, openTime, todayDate){
				//get all of the events that are happening in this room today
				$.getJSON('http://api.rit.edu/v1/rooms/'+roomNumber+'/meetings?date='+todayDate+'&RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da',
					function (json){
						//variables used to check to see if the lab is closed
						var clock = new Date();
						var h = addZero(clock.getHours());
						var m = addZero(clock.getMinutes());
						var currentTimeNoSec = h + ":" + m;
						
						//store the strings based on whether the id already exists on the page or not
						var str = "";
						
						//if the lab is closed then you don't need to check anything else - just print the open time
						if(currentTimeNoSec <= openTime || currentTimeNoSec >= closeTime){
							//the lab is closed so you should show the closed response
							str = "<table id="+roomNumber+" style='background-color: #9e0b0f;' width='100%'><tr><td><b>"+roomName + "</b></td> <td>Lab opens at "+convertTimeTo12(openTime)+"</td></tr></table>";
							idExistCheck(roomNumber, str);
						}
						else{
							//the lab is open so determine what should be displayed
							if(json.data.length == 0){
								//there are no classes for this room today
								str = "<table id="+roomNumber+" style='background-color: #197b30;' width='100%'><tr><td><b>"+roomName + "</b></td> <td>No Upcoming Classes - Closes at "+convertTimeTo12(closeTime)+"</td></tr></table>";								
								idExistCheck(roomNumber, str);
							}//end of if
							else if(json.data.length == 1){
								//there is 1 class scheduled for thie room today
								//the current time
								var currentTimeS = new Date();
								var currentHours = addZero(currentTimeS.getHours());
								var currentMinutes = addZero(currentTimeS.getMinutes());
								var currentTime =  currentHours+":"+currentMinutes;
								//class start time and end time
								var startTime = json.data[0].start.substring(0,5);
								var endTime = json.data[0].end.substring(0,5);
								
								if(currentTime < startTime){
									str = "<table id="+roomNumber+" style='background-color: #1458a9;'width='100%'><tr><td><b>"+roomName + "</b></td><td><b>"+ json.data[0].meeting + "</b> " + convertTimeTo12(startTime) + "-" + convertTimeTo12(endTime) +"</td></tr></table>";							
									idExistCheck(roomNumber, str);
								}//end of if
								else if(startTime <= currentTime && currentTime <= endTime){
									str = "<table id="+roomNumber+" style='background-color: #9e0b0f;'width='100%'><tr><td><b>"+roomName + "</b></td><td>Class in Session</td></tr></table>";								
									idExistCheck(roomNumber, str);
								}//end of else if
								else if(currentTime > endTime){
									//This class is past and is not important to the schedule
									str = "<table id="+roomNumber+" style='background-color: #197b30;' width='100%'><tr><td><b>"+roomName + "</b></td> <td>No Upcoming Classes - Closes at "+convertTimeTo12(closeTime)+"</td></tr></table>";									
									idExistCheck(roomNumber, str);
								}//end of else if
							}//end of else if
							else if(json.data.length > 1){
								//there are more then 1 class scheduled for this room today
								//importantClass keeps track of the i value for the class that should be displayed
								var importantClass = -1;
								//howFar is the difference between the start time of a class and the current time in seconds
								var howFar = -1;
								//howFar is the difference between the start time of a class and the current time in seconds - BUT of the previous class or the most recent important class
								var prevHowFar = -1;
								//go through each class that is scheduled on this day for this room
								for(var i= 0, len = json.data.length; i < len; i++){
									var currentTimeS = new Date();
									var currentHours = addZero(currentTimeS.getHours());
									var currentMinutes = addZero(currentTimeS.getMinutes());
									var currentTime =  currentHours+":"+currentMinutes;
									//the start time and end time of this class without the seconds
									var startTime = json.data[i].start.substring(0,5);
									var endTime = json.data[i].end.substring(0,5);
									
									if(startTime <= currentTime && currentTime <= endTime){
										//class is in session
										str = "<table id="+roomNumber+" style='background-color: #9e0b0f;'width='100%'><tr><td><b>"+roomName + "</b></td><td>Class in Session</td></tr></table>";									
										idExistCheck(roomNumber, str);
										
										//This will end the for loop because there is a class in session and we no longer care to know about the rest of the classes
										i = len;
									}
									else if(startTime > currentTime){
										//this class might be the upcoming class and we need to compare to the other classes to see which is up first
										//this is howFar away this class is from right now
										howFar = toSeconds(startTime) - toSeconds(currentTime);
										//if this is the first time through the prevHowFar will not yet have been set
										if(prevHowFar == -1){
											prevHowFar = howFar;
											//also set importantClass to this class
											importantClass = i;
										}
										//if the difference between this class and now is smaller then the previous class and now then set the new important class to this i
										else if(howFar < prevHowFar){
											importantClass = i;
											prevHowFar = howFar;
										}
										if(i+1 == len){
											//if this is the last time around the for loop then print out
											str = "<table id="+roomNumber+" style='background-color: #1458a9;'width='100%'><tr><td><b>"+roomName + "</b></td><td><b>"+ json.data[importantClass].meeting + "</b> " + convertTimeTo12(json.data[importantClass].start.substring(0,5)) + "-" + convertTimeTo12(json.data[importantClass].end.substring(0,5)) +"</td></tr></table>";									
											idExistCheck(roomNumber, str);
										}
									}//end of else if
									else{
										//this class time is already past
										if(i+1 == len){
											//if this is the last time around the for loop then print out
											if(importantClass > -1){
												//if the important class has been set then print the imfomation about that class
												str = "<table id="+roomNumber+" style='background-color: #1458a9;'width='100%'><tr><td><b>"+roomName + "</b></td><td><b>"+ json.data[importantClass].meeting + "</b> " + convertTimeTo12(json.data[importantClass].start.substring(0,5)) + "-" + convertTimeTo12(json.data[importantClass].end.substring(0,5)) +"</td></tr></table>";									
												idExistCheck(roomNumber, str);
											}
											else{
												//if the important class has not been set then there are no upcoming classes
												str = "<table id="+roomNumber+" style='background-color: #197b30;' width='100%'><tr><td><b>"+roomName + "</b></td> <td>No Upcoming Classes - Closes at "+convertTimeTo12(closeTime)+"</td></tr></table>";								
												idExistCheck(roomNumber, str);
											}//end of else
										}//end of if
									}//end of else
								}//end of for
							}//end of else if
						}//end of else
					}//end of success
				);//end of getJSON
			}//end of function createMiniSchedule
			
			/*
			* Check if the id exists on the page and based on whether it does treat the page update differently
			* @param {string} roomNumber - the room number that is the id
			* @param {string} str - the html string that should be written
			*/
			function idExistCheck(roomNumber, str){
				if(!document.getElementById(roomNumber)){
					//the id does not exist yet so append
					$("#labSchedules").append(str);
				}
				else{
					$("#"+roomNumber).replaceWith(str);
				}
			}//end of idExistCheck
			
			/*
			* Converts HH:MM to seconds for easier calculations in comparing time
			* @param {string} t - the 24 hour time that needs to be converted to seconds
			* @return {number} seconds - the seconds that are equivilent to the time that was given
			*/
			function toSeconds(t) {
				var bits = t.split(':');
				return bits[0]*3600 + bits[1]*60;
			}
			
			/* 
			* Adds a zero to the beginning of time if it is less than 10 
			* @param {number} i - the number that might need a 0 added to the beginning
			* @return {number} i - the number either as it started or with  zero on the beginning
			*/
			function addZero(i) {
				if (i < 10) {
					i = "0" + i;
				}
				return i;
			}
			
			/*
			* An empty calendar is created on the page in the calendar id 
			*/
			function createCalendar(){
				//Display the calendar and define defaults
				$("#calendar").fullCalendar({
					defaultView: "agendaDay",
					header:{
						left: false,
						center: false,
						right: false
					},
					minTime: "08:00:00",
					maxTime: "22:00:00",
					contentHeight: "auto",
					allDaySlot: false,
					eventBackgroundColor: "#FF3800",
					eventBorderColor: "#4C555C",
					slotDuration: "00:60:00"
				
				});
			}//end of createCalendar()
					
			/*
			* Refresh the lab staff tables every minute and place the labStaff into the labStaff id
			*/
			function refreshLabStaff(){
				//load widget.php to show who is currently working
				//all manipulation is done in widget.php
				$("#labStaff").hide().load("widget.php").show();
			}//end of refreshLabStaff
			
			/*
			* Refresh the lab status to say whether the lab is open, class or closed
			* Sets a timeout so the the lab status is refreshed every 3 seconds
			* @param {string} openTime - time the lab opens (retrieved from the lab Schedule csv file)
			* @param {string} closeTime - time the lab opens (retrieved from the lab Schedule csv file)
			*/
			function refreshLabStatus(openTime, closeTime){
				
					//get the current time
					var clock = new Date();
					var h = addZero(clock.getHours());
					var m = addZero(clock.getMinutes());
					var currentTime = h + ":" + m + ":01";
					
					var currentTimeNoSec = h + ":" + m;
					var currLabStatusText = "Unknown";
					var currentLabStatusColor = "Black";
					
					if(currentTimeNoSec <= openTime || currentTimeNoSec >= closeTime){
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
					}//end of else if
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
			}//end of refreshLabStatus
			
			/*
			* Call refresh at 3AM each day
			* This will update the entire page
			*/
			refreshAt(3,0,0);
			/*
			* Converts military time to 12hr time
			* @param {string} t - 24hr time in format HH:MM
			* @return {string} h+":"+m+ampm - 12hr time format HH:MM am/pm
			*/
			function convertTimeTo12(t){
				var h = t.substring(0,2);
				var m = t.substring(3,5);
				var ampm = ""
				if(h === "00" || h === "24"){
					//change hours to 12
					h = "12";
					ampm = " am"
				}
				else if(h > "12"){
					h = h-12;
					ampm = " pm";
				}
				else if(h === "12"){
					ampm = " pm";
				}
				else{
					ampm = " am";
				}
				return h+":"+m+ampm;
				
			}//end of convertTimeTo12
			
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
					<h3>Open Lab Schedules</h3>
				</div>
        </div>
    </body>
</html>

