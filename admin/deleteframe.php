<?php

include('../AuthClass.php');
include('../TableClass.php');
$id = authenticateUser();
		//echo $_POST['date'];
		//echo $_POST['timei'];
		//echo $_POST['timef'];
		
		//before deleting entry, get license key
		$date = $_POST['date'] . ' ' . $_POST['timei'];
		$query = "SELECT license AS id FROM `clock` WHERE `date` = '"
			. $date . "' "
			. "AND id='". getID() . "'";
		$link = initDb();
		selectDb($link);
		//echo $query;
		$license = queryDb2($link, $query);

		//delete clock entry
		$query = "DELETE FROM `clock` WHERE `date` = '"
			. $date . "' "
			. "AND id='". getID() . "' AND license='{$license}'";
		//echo $query;
		queryDb($link, $query);
		
		//get next clock out
		//$date = $_POST['date'] . ' ' . $_POST['timef'];
		$query = "SELECT date AS id FROM `clock` WHERE `date` > STR_TO_DATE('{$date}', '%Y-%m-%d %H:%i:%s') AND id='".getID()."' AND license='{$license}' ORDER BY date ASC LIMIT 1";
		
		$nextclock = queryDb2($link, $query);
		$query = "DELETE FROM `clock` WHERE `date` = '"
			. $nextclock . "' "
			. "AND id='". getID() . "'";
		queryDb($link, $query);
?>

