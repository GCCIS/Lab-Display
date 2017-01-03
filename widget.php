<?php
/**
 * Created by PhpStorm.
 * User: jdmics
 * Date: 12/2/2015
 * Time: 9:45 PM
 */

define('SCHEDULE_NAME_LAB_ASSISTANT', 'Lab Assistant');

include("modules/simple_html_dom.php");

$html = file_get_html("http://work.cias.rit.edu/ist/widgets/unifiedschedule");

/*
 * <table><tr> in the widgetContent
 * The result should be a <td> which contains tables
 * Each table is a schedule
 */
$widgetCells = $html->find("table#widgetContent tr td");


foreach ($widgetCells as $widgetCell) {
    /* The title should be in the <th> */
    $title = $widgetCell->find('th', 0)->plaintext;

    /* Test to see if this is the lab assistant table */
    if (stripos($title, SCHEDULE_NAME_LAB_ASSISTANT, 0) !== false) {
        $results[0] = "Lab Assistants";

        /*
         * A block like this should contain the time
         *
         * <div class="list-group-item list-group-item-info">
         * 04:00 pm - 07:00 pm
         * </div>
         */
        $time = $widgetCell->find('.list-group-item-info', 0)->plaintext;
        echo $time;

        /*
         * A span that contains the lab assistant's name should look like this
         * <div  class='list-group-item' style='background-color: #B39EB5'>
         * <strong>
         * <span id='replaceme' />Kyle Bansavage
         * </strong>
         * </div>
         */
        $labAssistants = $widgetCell->find('.list-group-item');
        foreach ($labAssistants as $labAssistant) {
            $labAssistantName = $labAssistant->plaintext;
            echo $labAssistantName;
        }
    }
}