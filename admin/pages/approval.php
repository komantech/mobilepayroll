<?php

include('../../AuthClass.php');
$user = authenticateUser();
include('../../TableClass.php');



$link = initDb();
selectDb($link);
//print_r($user);
//echo '<br/>'. getCompanyId($_POST['user_id'], $link) ;
if(isset($_POST['user_id']) &&
$user['company_id'] == getCompanyId($_POST['user_id'], $link)){

	if(isset($_POST['request']) && isset($_POST['action']) && isset($_POST['request']) ){
		$act = $_POST['action'];
		
		if($act=='d'){
			$query = "DELETE FROM `approvals` WHERE `user_id` = '{$_POST['user_id']}' 
				AND `request`='{$_POST['request']}'";
		} else if($act=='a'){
			$query = "UPDATE `approvals` SET `approved`='1' WHERE `request` = '{$_POST['request']}'
				AND `user_id`='{$_POST['user_id']}'";
		}else if($act=='un'){
			$query = "UPDATE `approvals` SET `approved`='0' WHERE `request` = '{$_POST['request']}'
				AND `user_id`='{$_POST['user_id']}'";
		} else if($act=='del'){
			$query = "DELETE FROM `contact_info` WHERE `id` = '{$_POST['user_id']}' 
				";
		} else if($act=='act'){
		} else if($act=='unact'){
		}

		queryDb($link, $query);
		
	} else{
		$temp = $_POST['date'];
		$formatted = dateForSQL($temp);
		
		$query='';
		if($user['type'] == 'admin'){
		$query = "INSERT INTO `approvals` (`company_id`, `wage`, `user_id`, `hours`, `rollover`, `reason`, `approved`, `date`)
					VALUES ('{$user['company_id']}','{$_POST['wage']}', '{$_POST['user_id']}', '{$_POST['hours']}',
					'{$_POST['rollover']}', '{$_POST['reason']}', '1', '{$formatted}')";
		} else{
		$query = "INSERT INTO `approvals` (`company_id`, `wage`, `user_id`, `hours`, `rollover`, `reason`, `approved`, `date`)
					VALUES ('{$user['company_id']}','{$_POST['wage']}', '{$_POST['user_id']}', '{$_POST['hours']}',
					'{$_POST['rollover']}', '{$_POST['reason']}', '0', '{$formatted}')";
		
		}
		queryDb($link, $query);

		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
}
		
?>
