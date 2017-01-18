<?php
	define('SCHEDULE_NAME_LAB_ASSISTANT', 'Lab Assistant');
	define('SCHEDULE_NAME_TEACHING_ASSISTANT', 'Teaching Assistant');
	include("modules/simple_html_dom.php");
	$html = file_get_html("http://work.cias.rit.edu/ist/widgets/unifiedschedule");

	/*
	 * <table><tr> in the widgetContent
	 * The result should be a <td> which contains tables
	 * Each table is a schedule
	 */
	$widgetCells = $html->find("table#widgetContent tr td");
	
	foreach ($widgetCells as $widgetCell) {	
		
		/* Retrieve Lab Assistant Schedule */
		/* The title should be in the <th> */
		$title = $widgetCell->find('th', 0)->plaintext;
		/* Test to see if this is the lab assistant table */
		if (stripos($title, SCHEDULE_NAME_LAB_ASSISTANT, 0) !== false) {
			
			/*
			 * A span that contains the lab assistant's name should look like this
			 * <di  class='list-group-item' style='background-color: #B39EB5'>
			 * <strong>
			 * <span id='replaceme' />Kyle Bansavage
			 * </strong>
			 */
			$labAssistants = $widgetCell->find('div.list-group-item');
			
			foreach ($labAssistants as $labAssistant) {
				$labAssistantName = $labAssistant->plaintext;
				
				if(stripos($labAssistantName, 'am -') OR stripos($labAssistantName, 'pm -')){
					//do nothing - don't display the time
				}
				else if(stripos($labAssistantName, 'Covered by:')){
					$labAssistantName = trim($labAssistantName);
					$coveredPieces = explode(" ", $labAssistantName);
					$coveredPieces = array_filter($coveredPieces, 'trim');
					$coveredPieces = array_values($coveredPieces);
					
					//the key that 'by:' is found in
					$byKey = array_search('by:', $coveredPieces);
					for($i= 0; $i <= $byKey; $i++){
						array_shift($coveredPieces);
					}
					$labAssistantNameClean = implode(" ", $coveredPieces);
					
					$labAssistantResults[] = $labAssistantNameClean;
					
				}
				else{
					if($labAssistantName == 'NOT COVERED'){
						array_pop($labAssistantResults);
					}
					else{
						$labAssistantResults[] = $labAssistantName;
					}
				}
			}	
		}
			
		/* Retrieve Teaching Assistant Schedule */
		/* The title should be in the <th> */	
		$title = $widgetCell->find('th', 0)->plaintext;
		/* Test to see if this is the teaching assistant table */
		if (stripos($title, SCHEDULE_NAME_TEACHING_ASSISTANT, 0) !== false) {
			/* TA Names */
			$TAs = $widgetCell->find('div.list-group-item');
			foreach ($TAs as $TA) {
				$TAName = $TA->plaintext;
				if(stripos($TAName, 'am -') OR stripos($TAName, 'pm -')){
					//do nothing - don't display the time
				}
				else if(stripos($TAName, 'Covered by:')){
					$TAName = trim($TAName);
					$coveredPieces = explode(" ", $TAName);
					$coveredPieces = array_filter($coveredPieces, 'trim');
					$coveredPieces = array_values($coveredPieces);
					
					//the key that 'by:' is found in
					$byKey = array_search('by:', $coveredPieces);
					for($i= 0; $i <= $byKey; $i++){
						array_shift($coveredPieces);
					}
					$TANameClean = implode(" ", $coveredPieces);
					
					$teachingAssistantResults[] = $TANameClean;
					
				}
				else{
					if(TAName == 'NOT COVERED'){
						array_pop($teachingAssistantResults);
					}
					else{
						$teachingAssistantResults[] = $TAName;
					}
				}
			}
		}
	}
	
	function createLabStaff($labStaffResults, $staffType){
		$labStaffTableStr = '';
		if(!empty($labStaffResults)){
			foreach($labStaffResults as $v){
				$labStaffTableStr .= '<tr><td>'.$v.'</td></tr>';
			}
		}
		else{
			if($staffType === 'LA'){
				$labStaffTableStr .= '<tr><td>No Labbie Available</td></tr>';
			}
			else{
				$labStaffTableStr .= '<tr><td>No TA Available</td></tr>';
			}
		}
		return $labStaffTableStr;
	}//end of function createLabStaff
	
	
	echo '<table id="labAssistantTable">
		<tr>
			<th>Lab Assistants</th>
		</tr>';
	echo createLabStaff($labAssistantResults, 'LA');
	echo '</table>
		<table id="teachingAssistantTable">
		<tr>
			<th>Teaching Assistants</th>
		</tr>';
	echo createLabStaff($teachingAssistantResults, 'TA');
	
	echo '</table>';
?>