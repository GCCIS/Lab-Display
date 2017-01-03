<?php
/**
 * User: Jimmy McNatt
 * Date: 11/18/2015
 * Time: 11:32 AM
 */
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="./css/display.css" />
        <title></title>
        <script src="https://code.jquery.com/jquery-latest.js"></script>
        <script src="./scripts/display.js"></script>
    </head>
    <body>
        <div class="container">
            <div id="top" class="top-container">
                <div id="top" class="top-title">
                    <span class="top-title-text">Projects Lab</span>
                </div>
                <div id="top" class="top-status open">
                    <span class="top-status-text">Closed</span>
                </div>
            </div>
            <div id="middle" class="middle-container">
                <div id="middle" class="middle-labassistants">

                </div>
                <div id="middle" class="middle-teachingassistants">

                </div>
            </div>
            <div id="bottom" class="bottom-container">
                <div id="bottom" class="bottom-clock">
                    <p id="clock"></p>
                    <p id="fulldate"></p>
                </div>
                <div id="bottom" class="bottom-staff">
                    <table>
                        <thead>
                            <th class="bottom-data">Lab Assistants (Cage)</th>
                            <th class="bottom-data">Tutors (Open Lab)</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="bottom-data">Bruce DeWald</td>
                                <td class="bottom-data">Jaymes Davis</td>
                            </tr>
                            <tr>
                                <td class="bottom-data">Scott Wetzel</td>
                                <td class="bottom-data">Davante Ray</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>

