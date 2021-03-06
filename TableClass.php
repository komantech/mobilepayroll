<?php
/*
function getCompanyId($user_id, $link=0){
	$query = "SELECT contact_info.company_id FROM contact_info WHERE contact_info.id='" . $user_id . "'";
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$company_id = $row['company_id'];
	return($company_id);
}
*/
function isClockInOrOut($id, $license){
	$query = "SELECT look_ahead AS look FROM clock WHERE id='"
	. $id .
	"' AND license='"
	. $license .
	"' " .
	"ORDER BY date DESC LIMIT 1";
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);

	//this holds when there are no entries in the system
	//as a result, no entries in $row
	if($row==FALSE){
		return 0;
	}
	
	$look = $row['look'];
	if($look==1){
		return 0;
	} else{
		return 1;
	}
}
/*
function getHours(){
	$company_id = getCompanyId($_COOKIE['id']);
	$query = "SELECT clock.date FROM clock WHERE clock.id='" . $_COOKIE['id'] . "' AND clock.company_id = '" . $company_id . "'";
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	// make 'live' the current db
	$time = array();
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$time[] = $row['date'];
	}

	for($i=0; $i <count($time); ++$i){
		echo $time[$i];
	}
	return $time;
}
*/
/*
The lookAhead function is required. It makes sure that the first entry has a look_ahead of 0. 0 refers to a clockin, 1 refers to a clockout.

*/
function getLookAhead($date, $id='0'){
	$query = "SELECT look_ahead AS look FROM clock WHERE id='"
	. $id .
	"' AND date BETWEEN '"
	. $date .
	"' AND DATE_ADD('"
	. $date .
	"', INTERVAL 1 DAY) ORDER BY date ASC LIMIT 1";
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$look = $row['look'];
	return($look);
}

function isLookAheadZero($date){

	$query = "SELECT look_ahead AS look FROM clock WHERE id='"
	. $_COOKIE['id'] .
	"' AND date BETWEEN '"
	. $date .
	"' AND DATE_ADD('"
	. $date .
	"', INTERVAL 1 DAY) ORDER BY date ASC LIMIT 1";
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$look = $row['look'];
	/*
	if(!$look){
		echo 'ZERO';
	} else{
		echo 'ONE';
	}
	*/
	return($look);
}



function getFirstPunchForNextDay($date, $id){
	$query = "SELECT TIME(date) AS date, look_ahead AS look FROM clock WHERE id='"
	. $id .
	"' AND date BETWEEN DATE_ADD('"
	. $date .
	"', INTERVAL 1 DAY) AND DATE_ADD('"
	. $date .
	"', INTERVAL 2 DAY) ORDER BY date ASC LIMIT 1";


	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	//$temp = $row['date'];
	if(isset($row['date']) == FALSE){
		return 0;
	}
	return $row;
}



function getPunchOut($date){
	$query = "SELECT TIME(date) AS date FROM clock WHERE id='"
	. $_COOKIE['id'] .
	"' AND date BETWEEN DATE_ADD('"
	. $date .
	"', INTERVAL 1 DAY) AND DATE_ADD('"
	. $date .
	"', INTERVAL 2 DAY) ORDER BY date ASC LIMIT 1";


	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$temp = $row['date'];
	return($temp);
}


function getMax($temp){
	$max = 0;
	for($i=0; $i<count($temp); ++$i){
		if(count($temp[$i]) > $max){ $max=count($temp[$i]);}
	}

	return($max);
}



function printRowIn($link, $date, $section, $id){
$weeks=1;
echo '<table width="100%" border="2" cellpadding="0" cellspacing="0">';
printRowWeek($date, $weeks, $section);
echo $date;
$temp = split('-',$date);
$month = $temp[0];
$day = $temp[1];
$year = $temp[2];
$from_unix_time = mktime(0, 0, 0, $month, $day, $year);
$milli_day = 60*60*24;

$tomo = strtotime("today", $from_unix_time);
$formatted = date('D M d', $tomo);

	$temp = array();

	//get the amount of punches for employee
	for($i=0;$i<7*$weeks;++$i){
			
		$tomo = strtotime("today", $from_unix_time);
		$formatted = date('Y-m-d', $tomo);
		$temp[] = getPunchesForDay($formatted, $id);
		$from_unix_time += $milli_day;
	}


//print a rectangular screen
//since data input looks like this:
/*
Example: dashes are timestamps in a 2 dimentional array
---------
---
--------
---
-

---------
*/
	$max = getMax($temp);
for($j=0; $j<($max); $j=$j+2){
	echo '<tr>
	<td align="center">In</td>
	';
	//print time for row 'In'
	for($i=0;$i<7*$weeks;++$i){
		//check
		echo '<td id="'. $j .'_'. $section . "" . $i  .'" rowspan="2" class="columnColor0">&nbsp;';
			if(isset($temp[$i][$j])){
				echo '<a href="javascript:void(null);deleteframe(\''. $j .'_'. $section . "" . $i.'\')';
				echo ';">X</a>';
			} else{
				echo '&nbsp;';
			}
		echo '</td>';
		

		echo '<td id="'. $j .'_'. $section . "" . $i . '_1' . '" class="columnColor0">&nbsp;';
		if(isset($temp[$i][$j])){echo $temp[$i][$j];}
		echo '</td>';
	}
	echo '<td class="columnColor0">&nbsp;';
	echo '</td>';


	//print time for row 'Out'
	echo '</tr><tr><td align="center">Out</td>';
	for($i=0;$i<7*$weeks;++$i){
		echo '<td id="'. $j .'_'. $section . "". $i . '_2' . '" class="columnColor0">&nbsp;';
		if(isset($temp[$i][$j+1])){echo $temp[$i][$j+1];}
		echo '</td>';
	}
	echo '<td class="columnColor0">&nbsp;';
	echo '</td>';

	echo '</tr>';
	
}
	echo '
	<tr>
		<td align="left" class="normal"><strong>Hours Worked</strong></td>
	';
	$hours = getHours($temp);
	for($i=0;$i<7*$weeks;++$i){
			echo '<td colspan="2" class="columnColor1"><strong>';
			echo $hours[$i];
			echo '</strong></td>';
	}

	$seconds = getSeconds($temp);
	$totalhours=0;
	for($i=0;$i<count($seconds); ++$i){
		$totalhours += $seconds[$i];
	}
	echo '<td class="columnColor1"><strong>';
	echo convertSecondsToTime($totalhours);
	echo '</strong></td>';
	echo '</table>';

}
function getApprovalHoursAdminForId($id, $from, $to){
	$link = initDb();
	selectDb($link);
	$query = "SELECT hours, wage, rollover FROM approvals
		WHERE user_id='{$id}'
		AND DATE_FORMAT(`date`,'%m-%d-%Y')>='{$from}'
		AND DATE_FORMAT(`date`,'%m-%d-%Y')<'{$to}'
		AND approved='1'";
	//echo $query . '<br />';
	//echo $query;
	$result = queryDbAll($link, $query);

	return $result;
}

function getApprovalHoursForId($id, $from, $to){
	$link = initDb();
	selectDb($link);
	$query = "SELECT hours, wage, rollover FROM approvals
		WHERE user_id='{$id}'
		AND DATE_FORMAT(`date`,'%m-%d-%Y')>='{$from}'
		AND DATE_FORMAT(`date`,'%m-%d-%Y')<'{$to}'
		AND approved='1'";
	//echo $query;
	//echo $query;
	$result = queryDbAll($link, $query);

	$seconds = 0;
	//previous billing cycle pay
	$prev_pay = 0;
	$prev_sec = 0;
	$curr_pay = 0;
	$curr_sec = 0;
	
	for($i=0;$i<count($result);++$i){
		//if rollover, then hours are for this billing cycle
		if($result[$i][2] == 0){
			$curr_pay += $result[$i][0]*$result[$i][1];
			$curr_sec += $result[$i][0];
		} else{
			$prev_pay += $result[$i][0]*$result[$i][1];
			$prev_sec += $result[$i][0];
		}
	}
	$result = array('current'=> array($curr_sec, $curr_pay),
		'previous'=>array($prev_sec, $prev_pay));

	return $result;

}
function getHoursWageForDay($date, $id='0'){
	$tt=$id;
	$look = getLookAhead($date, $id);
	$query = "SELECT date, wage FROM clock WHERE id='" . $tt . "' AND date BETWEEN '"
	. $date .
	"' AND DATE_ADD('"
	. $date .
	"', INTERVAL 1 DAY) ORDER BY date ASC";
	//echo $query;
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	$punches = array();
	//get company_id to filter clock
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$ll = explode(' ', $row['date']);
		if(isset($row)){
			$punches[] = array($ll[1], $row['wage']);
		}
	}
	
	$nextday = getFirstPunchForNextDay($date, $id);
	
	//echo '<br/><br/>';
	if(isset($nextday['look']) == 1){
		if($look==0 && $nextday['look']==1){
			$punches[] = array($nextday['date'], 0);
		}
	
		if($look==1 && $nextday['look']==1){
			$punches[] = array($nextday['date'], 0);
		}
	}
	if($look==1){
		array_shift($punches);
	}
	if(count($punches) %2 == 1){
		array_pop($punches);
	}
/*
	echo '<br />---getFirstPunchForNextDay';
	echo print_r($nextday);
	echo '---<br />';
	echo '---getLookahead' . print_r($look) . '---<br /> <br />';
	echo '<br/>CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC';
	print_r($punches);
	echo '**********************************<br />';
*/
	return($punches);
}

function getHoursForId($id, $date, $weeks=1){
	$temp = split('-',$date);
	$month = $temp[0];
	$day = $temp[1];
	$year = $temp[2];
	$from_unix_time = mktime(0, 0, 0, $month, $day, $year);
	$milli_day = 60*60*24;

	$tomo = strtotime("today", $from_unix_time);
	$formatted = date('D M d', $tomo);

	$temp=0;
	$punches = array();
	//echo $date;

	//get the amount of punches for employee
	for($i=0;$i<7*$weeks;++$i){
			
		$tomo = strtotime("today", $from_unix_time);
		$formatted = date('Y-m-d', $tomo);
		//print_r(getPunchesForDay($formatted, $id));
		$t = getHoursWageForDay($formatted, $id);
	
		if(count($t)){
			$punches[]=$t;
		}
		//echo '================================================================<br /><br />';
		$from_unix_time += $milli_day;
	}

	//$temp = getSeconds2($punches);
	return $punches;
}

function calculatePay($time, $wage){
	$matches = 0;
	$pattern = '/([0-9]+)H([0-9]+)M/';
	preg_match($pattern, $time, $matches);
	//print_r($matches);
	
	$pay = $matches[1]*$wage;
	$pay += ($matches[2]/60)*$wage;

	$pay = '$'.$pay;
	return($pay);
}

function getID(){
	$user=authenticateUser();
	$uri = $_SERVER['REQUEST_URI'];
	
	if(isset($_GET['id']) && $user['type']=='admin'){
		return trim($_GET['id']);
	} else if(isset($_POST['id']) && $user['type']=='admin'){
		return trim($_POST['id']);
	} else{
		return $user['id'];
		//return trim($_COOKIE['id']);
	}

}

function selectContact($link, $selected){
	$db_selected = mysql_select_db('live', $link);
	if (!$db_selected) {
	    die ('Can\'t use live : ' . mysql_error());
	}

	$query = "SELECT " . $selected . " FROM contact_info WHERE contact_info.id=" . getID();
	$result=mysql_query($query);
        if(!$result) {
            die('Invalid query: ' . mysql_error());
        }
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$tossed_salad=$row[$selected];
	echo $tossed_salad;
	return $tossed_salad;
}

function getPunchesForDay($date, $id='0'){
	$tt=$id;
	//echo $tt;
	//in current day make sure look_ahead=0.
	//look_ahead determines if the user checked in or out.
	//0,1 corresponds to in,out respectively
	//$look = isLookAheadZero($date);
	$look = getLookAhead($date, $id);
	//echo '<br />----' . $look;
	$query = "SELECT date FROM clock WHERE id='" . $tt . "' AND date BETWEEN '"
	. $date .
	"' AND DATE_ADD('"
	. $date .
	"', INTERVAL 1 DAY) ORDER BY date ASC";
	//echo $query;
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	$punches = array();
	//get company_id to filter clock
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$ll = explode(' ', $row['date']);
		$punches[] = $ll[1];
	}
	
	$nextday = getFirstPunchForNextDay($date, $id);
	
	//echo '<br/><br/>';
	//echo '---' . print_r($nextday) . '---<br /> <br />';
	if(isset($nextday['look']) == 1){
		if($look==0 && $nextday['look']==1){
			//add last punchout
			$punches[] = $nextday['date'];
		}
	
		if($look==1 && $nextday['look']==1){
			$punches[] = $nextday['date'];
			//array_shift($punches);
		}
	}
	if($look==1){
		//add last punchout
		array_shift($punches);
	}
	if(count($punches) %2 == 1){
		array_pop($punches);
	}
	//if even do nothing, if odd then pop to make it even
	
	return($punches);
}
function convertSecondsToTime($sec){
	$init = $sec;
	$hours = floor($init / 3600);
	$minutes = floor(($init / 60) % 60);
	$seconds = $init % 60;

	return($hours.'H'.$minutes.'M');
	//return(gmdate("H:i:s", 685));
}

function getHours($punches){
	$seconds = getSeconds($punches);
	for($i=0; $i<count($punches);++$i){
		$seconds[$i] = convertSecondsToTime($seconds[$i]);
	}
	return $seconds;
}

function getSecondsFromHH($time){
	$secs = (substr($time, 0, 2) * 3600) + (substr($time, 3, 2) * 60);

	return $secs;
}

function getSeconds2($punches){
	$temp = array();
	for($i=0;$i<count($punches);$i=$i+2){
		if(strtotime($punches[$i+1][0]) < strtotime($punches[$i][0])){
			//how many seconds in 24 hours? 86 400
			$aa = 86400;
			$workingsecs = $aa-getSecondsFromHH($punches[$i][0]);
			$workingsecs += getSecondsFromHH($punches[$i+1][0]);
		} else{
			$workingsecs = (strtotime($punches[$i+1][0])
					- strtotime($punches[$i][0]));
		}
		//echo $workingsecs . '----------------------------<br />';
		if($workingsecs >0){
		//	echo $workingsecs . '-------->0------------------<br />';
			$temp[] = array($workingsecs/3600, $punches[$i][1]);
		}
		//echo '<br />' . $temp;

	}

}

function getSeconds($punches){
//print_r($punches);
	$seconds=array();
for($j=0;$j<count($punches); ++$j){
	//echo "<br /> -------------" . count($punches[$j]);
	$temp=0;
	$workingsecs=0;
	for($i=0;$i<count($punches[$j]); $i=$i+2){
		if(strtotime($punches[$j][$i+1]) < strtotime($punches[$j][$i])){
			//how many seconds in 24 hours? 86 400
			$aa = 86400;
			$workingsecs = $aa-getSecondsFromHH($punches[$j][$i]);
			$workingsecs += getSecondsFromHH($punches[$j][$i+1]);
		/*
			$workingsecs = strtotime($punches[$j][$i]));
			//echo '<br />' . $workingsecs;
			$workingsecs += strtotime($punches[$j][$i+1]);
			//echo '<br />' . $workingsecs;
		*/
		} else{
			$workingsecs = (strtotime($punches[$j][$i+1])
					- strtotime($punches[$j][$i]));
		}
		$temp += $workingsecs;
		//echo '<br />' . $temp;
	}
	
	$seconds[] = $temp;
}
	//echo "<br />TOTAL HOURS:" . print_r($punches) . "<br />";

	return $seconds;
}


function countPeopleAtLocation(){
	$query = "SELECT contact_info.company_id FROM contact_info WHERE contact_info.id='" . $user_id . "'";
	$result = mysql_query($query);
	if (!$result) {
	    die('Invalid query: ' . mysql_error());
	}
	//get company_id to filter clock
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$company_id = $row['company_id'];
	return($company_id);
}

function addDaysToDate($days, $date){
	$temp = split('-',$date);
	$month = $temp[0];
	$day = $temp[1];
	$year = $temp[2];
	$from_unix_time = mktime(0, 0, 0, $month, $day, $year);
	$milli_day = 60*60*24;

	$secondsperday = 60*60*24;
	$tomo = strtotime("today", $from_unix_time+($secondsperday*$days));
	$formatted = date('m-d-Y', $tomo);
	return $formatted;

}
function getHoursWage($temp){
	$result = array();
foreach($temp as $punches){
	for($i=0; $i<count($punches); $i += 2){
		if(strtotime($punches[$i+1][0]) < strtotime($punches[$i][0])){
			//how many seconds in 24 hours? 86 400
			$aa = 86400;
			$workingsecs = $aa-getSecondsFromHH($punches[$i][0]);
			$workingsecs += getSecondsFromHH($punches[$i+1][0]);
		} else{
			$workingsecs = (strtotime($punches[$i+1][0])
					- strtotime($punches[$i][0]));
		}
		//echo $workingsecs . '----------------------------<br />';
		if($workingsecs >0){
		//	echo $workingsecs . '-------->0------------------<br />';
			$result[] = array($workingsecs/3600, $punches[$i][1]);
		}
		//echo '<br />' . $temp;
	}
}
	return $result;
}
function getTotalHours($hourswage){
	$t_hours=0;
	foreach($hourswage as $value){
	        $t_hours += $value[0];
	}
	
	return $t_hours;
}

function hoursHeader($id, $from, $to, $week='First'){

echo '
	<div class="timeDetailData">
        <fieldset><legend> <strong>
        <font color="#000000" class="normal">
        Approvals from ' .
	$week . ' Week '
        . '</font>
        </strong>
        </legend>
';
		include_once("admin/AdminClass.php");
		$regular=getHoursWage(getHoursForId($id, $from, 1));
		$approved=getApprovalHoursAdminForId($id, $from, $to);
		//print_r($approved2);
		$t_pay = 0;
		$t_hours = 0;

		/*
		*Calculate Hours for Week
		*/
		$p=payWeek($regular, $approved);
		$t_pay += $p['pay'];
		$t_hours += $p['hours'];
		echo '<span class="normal"> 
			Total Hours:<strong>
			';
		$temp = convertSecondsToTime(60*60*$t_hours);
		echo $temp;
		echo '</strong><br /></span>';

		echo '<span class="normal"> 
			Total Pay:<strong>
			';
		$temp = number_format(round($t_pay,2),2);
		echo '$'.$temp;
		echo '</strong></span><br/><br/>';




echo '<span class="normal"> 
		Regular Hours:<strong>
';
	$temp = convertSecondsToTime(60*60*getTotalHours(getHoursWage(getHoursForId($id, $from, 1))));
	echo $temp;

echo '</strong><br />';

echo '<span="normal">Approved Hours:<strong>
';
	$temp = getApprovalHoursForId($id, $from, $to);
	echo convertSecondsToTime($temp['current'][0]*60*60);

echo '</strong>';

echo '<br />Roll-over Hours (Approved Hours): <strong>
';
	echo convertSecondsToTime($temp['previous'][0]*60*60);

echo '</strong></span>';

echo '
	</fieldset>
';

}

function dateForSQL($date){
	$temp = explode('-', $date);
	return ($temp[2].'-'.$temp[0].'-'.$temp[1]);
}

function userAddTimeForm($id, $from){
//echo 'FUCK  YOU';
echo '<br />';
echo '<form method="POST" action="approval.php">';
echo '<table width="55%" border="1" cellpadding="0" cellspacing="0">';

$i=0;
echo '
	<tr>
	<td width="15%" align="left">
	<div>
';
	
	echo 'Hours';
echo '
	</div>
	</td>
	
';

echo '
	<td colspan="2" width="15%" align="left">
	<div>
	Wages
	<input style="display:none;" type="text" name="user_id" value="'. $id .'" onclick="">&nbsp;
	</div>
	</td>
	<td width="25%" align="left">
	<div>
	<input type="checkbox" name="rollover" value="1" onclick="">&nbsp; <span class="normal"><strong>Roll-over Hours</strong></span>
	</div>
	</td>
	</tr>
	<tr>
	<td>
		<input type="text" name="hours" value="4.5" onclick="">&nbsp;
	</td>
	<td colspan="2">
		<input type="text" name="wage" value="7" onclick="">&nbsp;
	</td>

	<td>
		&nbsp;
	</td>
	</tr>
';

echo '
	<tr>

	<td>
	<div>
	Reason
	</div>
	</td>

	<td colspan="2">
	<input size="55%" type="text" name="reason" value="Please specify a reason (E.g. Forgot to punch in)" class="entertext"/>
	</td>


	<td>
	<div>
	<input type="submit" id="sub" value="Send for Approval" class="entertext"/>
	</div>
	</td>
	</tr>
';

echo "</table>
<input type='text' name='date' value='{$from}' style='display:none;'>
</form>";

}

//section is used to create id for the date that goes into the database when
//the user deletes a frame
function printRowWeek($date, $weeks, $section){
// set the default timezone to use. Available since PHP 5.1
date_default_timezone_set('UTC');
// Prints something like: Monday
// Prints something like: Monday 8th of August 2005 03:12:46 PM


echo'
	<tr>
	<td width="20%" align="center" class="normal"><strong>Punches /<br />
	Time Off</strong></td>
';
$temp = split('-',$date);
$month = $temp[0];
$day = $temp[1];
$year = $temp[2];
$from_unix_time = mktime(0, 0, 0, $month, $day, $year);
$milli_day = 60*60*24;

for($i=0; $i<7*$weeks;++$i){

//print hidden date
$tomo = strtotime("today", $from_unix_time);
$formatted = date('Y-m-d', $tomo);
echo '<div style="display:none;" id="'. $section . "" . $i . '">'. $formatted .'</div>';

$tomo = strtotime("today", $from_unix_time);
$formatted = date('D M d', $tomo);
$temp = split(' ', $formatted);
	//check
echo '
	<td colspan="2" width="10%" align="center" class="normal"><strong>
';
echo $temp[0] . "<br />" . $temp[1] . " " . $temp[2];
echo '</strong></td>';
$from_unix_time += $milli_day;
}
echo '
	<td width="20%" class="normal">
		<strong>Weekly Total</strong>
	    </td>
	</tr>
	
';


}
?>

