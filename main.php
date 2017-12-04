<?php
include_once('support.php');
require_once("dbLogin.php");
require_once("setupDB.php");
session_start();

$title = "TA Office Hours Waiting Room";
$body = <<<EOBODY
<style>
.jumbotron {
    background-color: #5bc0de;
    color: white;
    font-size: 2em;
    padding: 0.75em;
}
.block {
    padding: 0em 1.25em 0em 1.25em;
}
.nav {
    color: white;
    display: inline-block;
    text-align: right; 
    margin: .5em 1em 0em 0em;
    float: right;
    /*border: 1px solid black;*/
}
a:hover{
    text-decoration: none;
}

.form-group {
    width: 40%;
    margin: auto;
    margin-top: 1.5em;
    padding: 1.25em;
}
.subheader {
    margin: auto;
    display: table;
}


</style>
	<div class="jumbotron">
        <h3 style="display: inline-block; width: 60%;"><strong>$title</strong></h3>
        <a href="logout.php" class="nav"><h4>Logout</h4></a>
        <a href="classList.php" class="nav"><h4>Edit Classes</h4></a>
    </div>
	
EOBODY;

$db_connection = initDBConnection($host, $user, $dbpassword, $database);
date_default_timezone_set('America/New_York');

// TA VIEW
$query = sprintf("select * from tblregistered join tblcourses on tblregistered.courseid = tblcourses.courseid where uid='%s' and usertype='%s' order by tblcourses.coursename ASC", $_SESSION['uid'], "TA");
$result = $db_connection->query($query);

if (!$result) {
    die("Retrieval failed: ". $db_connection->error);
} else {
    $num_rows = $result->num_rows;
    if ($num_rows > 0) {
        $result->data_seek(0);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $body .= "<h3 class='subheader'><strong>TA</strong></h3>";

        // for each course that the user is a TA for...
        for ($row_index = 0; $row_index < $num_rows; $row_index++) {
            $result->data_seek($row_index);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $body .= <<<EOBODY

            <form action="{$_SERVER['PHP_SELF']}" method="post">
            <div class="form-group panel panel-default">
            <h4><strong>Course:</strong> {$row['coursename']}</h4>
            <h4><strong>TA:</strong></h4>

            <h4><strong>Queue:</strong></h4>
                <table class="table table-hover table-striped" style="margin-right: 1.2em;">
                    <tr>
                        <th style="width: 10%;">Priority</th>
                        <th style="width: 30%;">Check-in Time</th>
                        <th style="width: 30%;">Wait Time</th>
                        <th style="width: 30%;">Name</th>
                    </tr>	
</table><br/>
<input type="submit" name="startTaHours" class="btn btn-info" value="Start TA Hours" style="display: table; margin: 0 auto;"/>
</div>
</form>
EOBODY;

        }
    }
}

// STUDENT VIEW
$query = sprintf("select * from tblregistered join tblcourses on tblregistered.courseid = tblcourses.courseid where uid='%s' and usertype='%s' order by tblcourses.coursename ASC", $_SESSION['uid'], "Student");
$result = $db_connection->query($query);

if (!$result) {
    die("Retrieval failed: ". $db_connection->error);
} else {
    $num_rows = $result->num_rows;
    if ($num_rows > 0) {
        $result->data_seek(0);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $body .= "<br/><br/><h3 class='subheader'><strong>Student</strong></h3>";

        // for each course that the user is a student for...
        for ($row_index = 0; $row_index < $num_rows; $row_index++) {
            $result->data_seek($row_index);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $body .= <<<EOBODY

            <form action="{$_SERVER['PHP_SELF']}" method="post">
            <div class="form-group panel panel-default">
            <h4><strong>Course:</strong> {$row['coursename']}</h4>
            <h4><strong>TA:</strong></h4>

            <h4><strong>Queue:</strong></h4>
                <table class="table table-hover table-striped" style="margin-right: 1.2em;">
                    <tr>
                        <th style="width: 10%;">Priority</th>
                        <th style="width: 30%;">Check-in Time</th>
                        <th style="width: 30%;">Wait Time</th>
                        <th style="width: 30%;">Name</th>
                    </tr>
EOBODY;

            // show course queue
            $query2 = sprintf("select * from tblqueue join tblusers on tblqueue.uid = tblusers.uid where courseid='%s' order by tblqueue.queuecheckintime ASC", $row['coursename']);
            $result2 = $db_connection->query($query2);

            if (!$result2) {
                die("Retrieval failed: " . $db_connection->error);
            } else {
                $num_rows2 = $result2->num_rows;
                if ($num_rows2 > 0) {
                    $result2->data_seek(0);
                    $row2 = $result2->fetch_array(MYSQLI_ASSOC);

                    for ($row_index2 = 0; $row_index2 < $num_rows2; $row_index2++) {
                        $body .= <<<EOBODY
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
EOBODY;
                    }
                }
                $body .= <<<EOBODY
                 </table ><br/>
    
                 <input type="submit" name="addToQueue" class="btn btn-info" value="Add Name to Queue" style="display: table; margin: 0 auto;"/>
                 <input type="hidden" name="courseid" value="{$row['courseid']}"/>
            </div>
        </form>
EOBODY;
            }
        }
    }
}

if (isset($_POST["addToQueue"])) {
    $query = sprintf("insert into tblqueue (uid, courseid, priority, queuecheckintime) values ('%s', '%s', '%s', '%s')", $_SESSION['uid'], $_POST['courseid'], 1, date("Y-m-d h:i:s"));
    $result = $db_connection->query($query);
    //header("Location: main.php");
}
# Generating final page
echo generatePage($body, $title);
?>