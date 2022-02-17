<?php
$mip = "192.168.0.124";
$muser = "admin";
$mpass = "9666777222";

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "wifi-hotspot";
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Connect to Server

	try {
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//################################
	
if(isset($_POST['mode'])){
	switch($_POST['mode']){
		case "generate":
		generate();
		break;
		
		case "todayreport":
		todayReport($_POST['sysdate']);
		break;
		
		case "todaysales":
		todaySales($_POST['sysdate']);
		break;
	}
}

function comPute($t){
	$h = 0;
	$d = 0;
	if($t > 1439){
		$d = floor($t/1440);
		$t = $t%1440;
	}
	if($t > 59){
		$h = floor($t/60);
		$t = $t%60;
	}
	$m = $t;
	
	if($m != 0){
		$temp = $m;
		$m = 4;
		if($temp > 30){
			$m = 8;
		}
	}
	if($h != 0){
		$h = 8 * $h;
	}
	if($d != 0){
		$d = 60 * $d;
	}
	return $d + $h + $m;
}

function generate(){
	if(isset($_POST['time'])){
		global $mip, $muser, $mpass;
		$t = $_POST['time'];
		$c = rand(100000,999999);
		require('./routeros_api.class.php');
		$API = new RouterosAPI();
		if ($API->connect($mip, $muser, $mpass)) {
			$ARRAY = $API->comm("/ip/hotspot/user/add", array(
				"limit-uptime"=> $t*60,
				"name" => $c,
				"password"=> $c,
				"profile" => "Normal",
				"server"=> "WorcHotspot"
			));
			$API->disconnect();
			$p = comPute($t);
			executeQuery("INSERT INTO sales(code, expiry, price, time, date) VALUES ('".$c."','".$_POST['renttime']."','".$p."','".$_POST['systime']."','".$_POST['sysdate']."')");
			print $c;
		}
	}
}

function todayReport($systemdate){
	global $conn;
		$sql = "SELECT code, expiry, time FROM sales WHERE date = '$systemdate' ORDER BY id DESC";
		$result = $conn->query($sql);
		
		echo '
			<table width="235">
				<tr>
					<th class="contentheader">
						CODE<hr/>
					</th>
					<th class="contentheader">
						RENTED TIME<hr/>
					</th>
				</tr>
		';	
		foreach($result as $row){
			print '
				<tr>
					<td class="contentdata">
						'.$row[0].'
					</td>
					<td class="contentdata">
						'.$row[1].'
					</td>
				</tr>
			';
		}
		echo "</table><script>alert(123)</script>";
}

function todaySales($systemdate){
	global $conn;
	$sql = "SELECT SUM(price) FROM sales WHERE date = '$systemdate'";
	$result = $conn->query($sql);
	foreach($result as $row){
		print $row[0];
	}
}
?>
