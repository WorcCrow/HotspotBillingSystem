<?php
$mip = "192.168.0.124";
$muser = "admin";
$mpass = "********";

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "wifi-vendo";
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Connect to Server

	try {
		require('./routeros_api.class.php');
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$API = new RouterosAPI();
	}
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//################################

	function executeQuery($sql){
		global $conn;
		$conn->query($sql);
	}
	
	function getItem($sql){
		global $conn;
		$result = $conn->query($sql);
		$x = "";
		foreach($result as $row){
				$x = $row[0];
		}
		
		if($x == ""){
			return "NULL";
		}else{
			return $x;
		}
	}
	
	function getPricing(){
		global $conn;
		$price = "";
		$minute = "";
		$sql = "SELECT price, min FROM pricing ORDER BY price DESC";
		$result = $conn->query($sql);
		foreach($result as $row){
			$price =  $price . $row[0] . ",";
			$minute =  $minute . $row[1] . ",";
		}
		return [$price . "1",$minute . "1"];
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//################################	Executing Function
	
	if(isset($_POST['mode'])){
		switch($_POST['mode']){

			case "coins":
			coins();
			break;
			
			case "useSlot":
			useSlot();
			break;
			
			case "useCredit":
			useCredit();
			break;
			
			case "status":
			status();
			break;
	
		}
	}
	
	function status(){
		$slotTime = expireSlot();
		$mac = $_POST['mac'];
		$creditTime = creditTOminute(checkCredit());
		$currentTime = checkCurrentTime($mac);
		$totalTime = $creditTime + mikrotikTOminute($currentTime);
		$totalTime = minuteTOtime($totalTime);
		$creditTime = minuteTOtime($creditTime);
		$totalTime = "$totalTime[0] d : $totalTime[1] h : $totalTime[2] m";
		$creditTime = "$creditTime[0] d : $creditTime[1] h : $creditTime[2] m";
		print(checkSlot($mac) . "," . $slotTime . "," . checkCredit() . "," . $currentTime . "," . $creditTime . "," . $totalTime);
	}

	function coins(){//Done
		global $conn;
		$time = time() + 60;
		$coin_value = $_POST['amount'];
		$current_mac = getItem("SELECT value FROM temp WHERE name='mac'");
		$check_credit = getItem("SELECT COUNT(id) FROM temp WHERE name='credit'");
		if($current_mac != "available" && $current_mac != "NULL"){
			executeQuery("UPDATE temp SET time='$time' WHERE name='mac'");
		}
		if($check_credit == 0){
			executeQuery("INSERT INTO temp(name, value) VALUES ('credit','$coin_value')");
		}else{
			executeQuery("UPDATE temp SET value=value + $coin_value WHERE name='credit'");
		}
	}
	
	function expireSlot(){
		$time = time();
		$expiry = getItem("SELECT time FROM temp WHERE name='mac'");
		if($expiry != "NULL"){
			if($time >= $expiry){
				$time*=$time;
				useCredit();
				executeQuery("UPDATE temp SET value='available', time='$time' WHERE name='mac'");
			}else{
				return $expiry - $time;
			}
		}else{
			return 9672;
		}
	}
	
	function useSlot(){//Done
		global $conn;
		$time = time() + 120;
		$mac = $_POST['mac'];
		$check_mac = getItem("SELECT COUNT(id) FROM temp WHERE name='mac'");
		$current_mac = getItem("SELECT value FROM temp WHERE name='mac'");
		if($check_mac == 0){
			executeQuery("INSERT INTO temp(name, value, time) VALUES ('mac','$mac','$time')");
		}else if($check_mac == 1){
			if($current_mac == "available" || $current_mac == "NULL"){
				executeQuery("UPDATE temp SET value='$mac', time='$time' WHERE name='mac'");
			}
		}
	}
	
	function useCredit(){
		global $conn;
		$mac = $_POST['mac'];
		$current_mac = getItem("SELECT value FROM temp WHERE name='mac'");
		$current_credit = getItem("SELECT value FROM temp WHERE name='credit'");
		$price = creditTOminute($current_credit);
		if($current_mac != "available" && $current_mac != "NULL"){	
			executeQuery("UPDATE temp SET value='available' WHERE name='mac'");
			executeQuery("UPDATE temp SET time='9672' WHERE name='mac'");
			executeQuery("UPDATE temp SET value='0' WHERE name='credit'");
			createUser($current_mac,$price);
			clearInactiveUser();
		}
	}
	
	function clearInactiveUser(){//Done
		global $mip, $muser, $mpass, $API;
		if ($API->connect($mip, $muser, $mpass)) {
			$ARRAY = $API->comm("/ip/hotspot/user/print", array(
				"detail"=> ""
			));
			$inactive_id = "";
			foreach($ARRAY as $arr){
				if(isset($arr['limit-uptime'])){
					if($arr['limit-uptime'] == $arr['uptime']){
						$ARRAY = $API->comm("/ip/hotspot/user/remove", array(
						".id"=> $arr['.id']
						));
					}
				}
			}
		}
	}
	
	function createUser($mac,$time){//Done
		global $mip, $muser, $mpass, $API;
		if ($API->connect($mip, $muser, $mpass)) {
			$ARRAY = $API->comm("/ip/hotspot/user/print", array(
				"detail"=> "",
				"where"=> "",
				"?name"=> $mac,
			));
			if(((bool) $ARRAY) == true){
				if(isset($ARRAY[0]['limit-uptime'])){
					$current_time = mikrotikTOminute($ARRAY[0]['limit-uptime']);
				}else{
					$current_time = 0;
				}
				$time += $current_time;
				$API->comm("/ip/hotspot/user/set", array(
					".id"=> $ARRAY[0]['.id'],
					"limit-uptime"=> $time*60,
				));
			}else{
				$API->comm("/ip/hotspot/user/add", array(
					"limit-uptime"=> $time*60,
					"name" => $mac,
					"password"=> $mac,
					"profile" => "Normal",
					"server"=> "WorcHotspot"
				));
			}
		}
	}
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//################################	Details and Computations Function	
	
	function checkSlot($mac){
		global $conn;
		$check_mac = getItem("SELECT COUNT(id) FROM temp WHERE name='mac'");
		$current_mac = getItem("SELECT value FROM temp WHERE name='mac'");
		if($check_mac == 1){
			if($current_mac == "available" || $current_mac == "NULL" ){
				return "available";
			}else if($current_mac == $mac){
				return "private";
			}else{
				return "inuse";
			}
		}else{
			return "available";
		}
	}
	
	function checkCredit(){
		$check_credit = getItem("SELECT value FROM temp WHERE name='credit'");
		if($check_credit == "NULL"){
			return 0;
		}else{
			return $check_credit;
		}
	}
	
	function checkCurrentTime($mac){
		$currentTime = getDetailCurrentUser($mac)[0];
		if($currentTime != "NULL"){
			return $currentTime['limit-uptime'];
		}else{
			return 0;
		}
	}
	
	function getActiveUser(){//Done
		global $mip, $muser, $mpass, $API;
		if ($API->connect($mip, $muser, $mpass)) {
			$API->write('/ip/hotspot/active/print');
			$ARRAY = $API->comm("/ip/hotspot/active/print", array(
				"count-only"=> "",
			));
			return $ARRAY[0];
		}
	}
	
	function getDetailCurrentUser($mac){//Done
		global $mip, $muser, $mpass, $API;
		if ($API->connect($mip, $muser, $mpass)) {
			$ARRAY = $API->comm("/ip/hotspot/user/print", array(
				"detail"=> "",
				"where"=> "",
				"?name"=> $mac,
			));
			if($ARRAY == []){
				return (["NULL"]);
			}else{
				return $ARRAY;
			}
		}
	}
	
	function timeTOminute($d,$h,$m){//Done
		$d *= 1440;
		$h *= 60;
		$m +=$d+$h;
		return $m;
	}
	
	function minuteTOtime($m){//Done
		$d = 0;
		$h = 0;
		if($m >= 1440){
			$d = $m / 1440;
			$m%=1440;
		}
		if($m >= 60){
			$h = $m / 60;
			$m%=60;
		}
		
		return [floor($d),floor($h),floor($m)];
	}
	
	function creditTOminute($credit){
		$price = getPricing();
		$pricing = explode(",",$price[0]);
		$minute = explode(",",$price[1]);
		$tempCredit = "";
		$totalCredit = 0;
		if($credit != 0){
			for($x=0;$x<count($pricing);$x++){
				if($pricing[$x] <= $credit){
					$tempCredit = floor($credit / $pricing[$x]) . "|" . $x . "," . $tempCredit;
					$credit = $credit%$pricing[$x];
				}
			}
			$tempCredit = explode(",",$tempCredit);
			for($x=0;$x<count($tempCredit);$x++){
					if($tempCredit[$x] != ""){
						$arr = explode("|",$tempCredit[$x]);
						$totalCredit += $arr[0] * $minute[$arr[1]];
					}
					
			}
			return $totalCredit;
		}else{
			return 0;
		}
	}
	
	function mikrotikTOminute($stringTime){
		$temp = explode("d",$stringTime);
		if(count($temp) == 2){
			$d = $temp[0];
			$temp = explode("h",$temp[1]);
		}else{
			$d = 0;
			$temp = explode("h",$temp[0]);
		}
		
		if(count($temp) == 2){
			$h = $temp[0];
			$temp = explode("m",$temp[1]);
		}else{
			$h = 0;
			$temp = explode("m",$temp[0]);
		}
		
		if(count($temp) == 2){
			$m = $temp[0];
			
		}else{
			$m = 0;
		}
		return timeTOminute($d,$h,$m);
	}
	
	$API->disconnect();
?>
