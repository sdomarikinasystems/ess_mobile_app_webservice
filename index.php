<?php
$c = mysqli_connect("localhost","root","","depedmar_cdtrs");
date_default_timezone_set('Asia/Manila');
$tag = $_POST["tag"];

switch ($tag) {
	case 'addtaskfortoday':
		
		$account_id = $_POST["account_id"];

		$timex = date("H:i:s");
		$datex = $_POST["taskdate"];
		$firstmonday = date("Y-m-d",strtotime("monday this week",strtotime($datex )));
		$task_desc = htmlentities($_POST["description"]);

		$q = "INSERT INTO wfh_task SET status='0', timefrom='" . $timex . "',adv_timefrom='" . $timex . "',timeto='" . $timex . "',adv_timeto='" . $timex . "',origin_date='" . $firstmonday . "',daydate='" . $datex . "',dayname='" . date("l",strtotime($datex)) . "',time_logs='',is_submitted='0',task_desc='" . $task_desc . "',userid='" . $account_id  . "'";
		$res = mysqli_query($c,$q);
		if($res){
			echo "--added task---";
		}else{
			echo "--failed adding task---";
		}
		break;
	case 'sync_attendancelogsdata':
		$logs_data = $_POST["attendance_data"];
		$eid = $_POST["eid"];
		$date = $_POST["date"];
		$company = $_POST["company"];
		if($logs_data != "empty"){
		//DELETE ATTENDANCE LOGS FOR TODAY
		$q = "SELECT * FROM attendance_logs WHERE eid='"  . $eid. "' AND
													date='"  . $date . "' AND
													company='" . $company . "'";
		if(mysqli_num_rows(mysqli_query($c,$q)) < 2){

			$q = "DELETE FROM attendance_logs WHERE eid='"  . $eid. "' AND
													date='"  . $date . "' AND
													company='" . $company . "'";
		$res = mysqli_query($c,$q);


			$logs_fragments = explode("~", $logs_data );

			for($i = 0; $i < count($logs_fragments);$i++){
				if($logs_fragments[$i] != "" && $logs_fragments[$i] != "empty"){
					$single_log = explode(",", $logs_fragments[$i]);
				// access_type, date, timestamp
				$fulltime = date("Y-m-d H:i:s",strtotime($single_log[1] . " " . $single_log[2]));
				$q = "INSERT INTO attendance_logs SET eid='" . $eid  . "',
					access_type='" . $single_log[0] . "',
					access_image='',
					date='" . $single_log[1] . "',
					timestamp='" . $fulltime . "',
					ismanual='0',
					company='" . $company . "',
					local_id='0'";
				$res = mysqli_query($c,$q);
				if($res){
					echo "--log enter success--";
				}else{
					echo "--log enter failed--";
				}
				}
			}
		}
	}else{
		echo "empty_logs";
	}
		break;
	case 'sync_taskdata':
		$task_data = $_POST["task_data"];
		$eid = $_POST["eid"];
		$account_id =$_POST["account_id"];
		
		// VERIFY TASK TO SYNC
		$taskdataarray = explode("~", $task_data );
		for($i = 0 ; $i < count($taskdataarray);$i++){
			if($taskdataarray[$i] != ""){
				// CONVERT TO ARRAY
			$task_fragment = explode(";", $taskdataarray[$i]);
			// TRANSFER TO VARIABLES
			$id = $task_fragment[0];
			$origin_date = $task_fragment[1];
			$dayname = $task_fragment[2];
			$daydate = $task_fragment[3];
			$task_desc = $task_fragment[4];
			$timefrom = $task_fragment[5];
			$adv_timefrom = $task_fragment[6];
			$timeto = $task_fragment[7];
			$adv_timeto = $task_fragment[8];
			$status = $task_fragment[9];
			$is_submitted = $task_fragment[10];
			$time_logs = $task_fragment[11];
			//UPDATE
			$q = "UPDATE wfh_task SET 

			origin_date = '" . $origin_date . "',
			dayname = '" . $dayname . "',
			daydate = '" . $daydate . "',
			task_desc = '" . $task_desc . "',
			timefrom = '" . $timefrom . "',
			adv_timefrom = '" . $adv_timefrom . "',
			timeto = '" . $timeto . "',
			adv_timeto = '" . $adv_timeto . "',
			status = '" . $status . "',
			is_submitted = '" . $is_submitted . "',
			time_logs = '" . $time_logs . "'

			WHERE id='" . $id . "'";

			$res = mysqli_query($c,$q);

			if($res){
				echo "-good-";
			}else{
				echo "-bad-";
			}
			}
		}
	break;
	case "gettimelogsfortoday";
	$user_eid = $_POST["eid"];
	$date = $_POST["date"];
	$company = $_POST["company"];
	$q = "SELECT * FROM attendance_logs WHERE eid='" . $user_eid  . "' AND
											date='" . 	$date . "' AND
											company='" .$company  . "' ORDER BY timestamp ASC LIMIT 2";
	$toecho = "";
	$res = mysqli_query($c,$q);
	if(mysqli_num_rows($res) == 0){
		//no logs
		$toecho = "empty";
	}else{
		while($row = mysqli_fetch_array($res)){
			// access_type, date, timestamp
			$toecho .= $row["access_type"] . "," . $row["date"] . "," . date("g:i a",strtotime($row["timestamp"])) . "~";
		}
	}
	$toecho = rtrim($toecho,"~");

	echo $toecho;
	break;
	case 'logatime':
	$employee_id = $_POST["eid"];
	$time_del = $_POST["timedel"];
	$company = $_POST["company"];
	$type = $_POST["type"];

	$q = "INSERT INTO attendance_logs SET
	eid='" . $employee_id  . "',
	date='" . date("Y-m-d",strtotime($time_del)) . "', 
	timestamp='" . date("Y-m-d H:i:s",strtotime($time_del)) . "', 
	company='" . $company . "', 
	access_type='" . $type . "'";

	$res = mysqli_query($c,$q);

	if($res){
		echo "true";
	}else{
		echo "false";
	}
	break;
	case 'gettodaysworkweekplan':
	$account_id = $_POST["account_id"];
	$thedate = date("Y-m-d",strtotime($_POST["date"]));
	//GET WORKWEEK PLAN
	$q = "SELECT * FROM submitted_wfh WHERE weekdate != '1970-01-01' AND user_id='" . $account_id . "' AND status='2' ORDER BY weekdate DESC LIMIT 1";
	$res = mysqli_query($c,$q);
	$output = mysqli_fetch_all($res,MYSQLI_ASSOC);
	// echo json_encode(mysqli_fetch_all($res,MYSQLI_ASSOC));
	if(!$res){
		echo "false";
	}else{
		
		if(mysqli_num_rows($res) == 1){
			$dateStart = $output[0]["weekdate"];
			$dateEnd = $output[0]["weekdate_to"];
			$current_date = date("Y-m-d",strtotime($dateStart));
			$belongs = false;
			while(strtotime($current_date) <= strtotime($dateEnd))
			{
				if($current_date == $thedate){
					$belongs = true;
					break;
				}
				$current_date= date("Y-m-d",strtotime("+1 day",strtotime($current_date)));
			}

			if($belongs == true){
			//CHECK IF THE DAY BELONGS TO SUBMITTED WFH
				
				$q_a = "SELECT * FROM wfh_task WHERE daydate='" . $thedate . "' AND userid='" . $account_id . "'";
				$res_a = mysqli_query($c,$q_a);
				$output_a = mysqli_fetch_all($res_a,MYSQLI_ASSOC);
				if(count($output_a) == 0){
					echo "empty";
				}else{
					for($i = 0; $i < count($output_a);$i++){
					// id, origin_date, dayname, daydate, task_desc, timefrom,adv_timefrom,timeto,adv_timeto, status,is_submitted,time_logs 
					echo str_replace(";", "", $output_a[$i]["id"]) . ";" . 
					str_replace(";", "", $output_a[$i]["origin_date"]) . ";" . 
					str_replace(";", "", $output_a[$i]["dayname"]) . ";" . 
					str_replace(";", "", $output_a[$i]["daydate"]) . ";" . 
					str_replace(";", "", $output_a[$i]["task_desc"]) . ";" . 
					str_replace(";", "", $output_a[$i]["timefrom"]) . ";" . 
					str_replace(";", "", $output_a[$i]["adv_timefrom"]) . ";" . 
					str_replace(";", "", $output_a[$i]["timeto"]) . ";" . 
					str_replace(";", "", $output_a[$i]["adv_timeto"]) . ";" . 
					str_replace(";", "", $output_a[$i]["status"]) . ";" . 
					str_replace(";", "", $output_a[$i]["is_submitted"]) . ";" . 
					str_replace(";", "", $output_a[$i]["time_logs"]) . "~";
				}
				}
			}else{
				echo "invalid";
			}
		}else{
			echo "not_approved";
		}
	}
	break;
	case 'logintoess':
	$username = strtolower($_POST["username"]);
	$password = sdmenc($_POST["password"]);
	$q = "SELECT *,employees.id AS data_id FROM employees LEFT JOIN employees_ess_pass ON employees.id = employees_ess_pass.account_id
	WHERE 
	cont_email='" . $username . "' AND 
	password='" . $password . "'
	ORDER BY employees.id DESC
	LIMIT 1
	";

	$c = mysqli_query($c,$q);
	if(mysqli_num_rows($c) ==0){
		echo "false";
	}else{
		$result =mysqli_fetch_array($c);

		echo "data_id:" . $result["data_id"] .";" . 
			"fname:" . $result["fname"] .";" . 
			"mname:" . $result["mname"] .";" . 
			"lname:" . $result["lname"] .";" .
			"eid:" . $result["eid"] .";" .
			"company:" . $result["company"] ."";
	}
	
		break;
}

function sdmenc($data){
		
		$keycode = openssl_digest(utf8_encode("virmil"),"sha512",true);
		$string = substr($keycode, 10,24);
		$utfData = utf8_encode($data);
		$encryptData = openssl_encrypt($utfData, "DES-EDE3", $string, OPENSSL_RAW_DATA,'');
		$base64Data = base64_encode($encryptData);
		return $base64Data;
	}
	function sdmdec($data){
		
		$keycode = openssl_digest(utf8_encode("virmil"),"sha512",true);
		$string = substr($keycode, 10,24);
		$utfData = base64_decode($data);
		$decryptData = openssl_decrypt($utfData, "DES-EDE3", $string, OPENSSL_RAW_DATA,'');
		return $decryptData;
	}
?>