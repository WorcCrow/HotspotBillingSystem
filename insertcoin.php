<html>
	<head>
		<title>LOGIN - NH-NET WIFI RENTAL</title>
		<link rel="icon" href="media/icon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta http-equiv="expires" content="-1" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;"/>
		<script src="js/md5.js"></script>
		<script src="js/Base64.js"></script>
		<script src="js/function.js"></script>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>

	<body onload="refreshStatus()" onmouseover="bodyActive()" onmouseleave="bodyInactive()" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0">
	<?php
		if(isset($_POST['mac'])){
			$mac = $_POST['mac'];
			$macpass = $_POST['macpass'];
			$error = $_POST['error'];
			$redirect = $_POST['redirect'];
			$mikrotikLogin = $_POST['mikrotikLogin'];
			echo "<span id='mac' hidden>$mac</span>";
			echo "<span id='error' hidden>$error</span>";
			echo "<form id='ConnectDevice' action='$mikrotikLogin' method='POST'>";
			echo "	<input type='hidden' name='username' value='$mac' />";
			echo "	<input type='hidden' name='password' value='$macpass' />";
			echo "	<input type='hidden' name='dst' value='$redirect' />";
			echo "</form>";
		}else{
			header("location: http://portal.nh.net");
		}
	?>		
		<div align="center">
			<img id="logo" src="media/wifi.gif" width="100px">
			<table id="frame">
				<tr><td align="center"><br /></td></tr>
				<tr>
					<td align="center" id="wifiName">
						<div style="font-size:40">NH-NET</div>
						<div style="font-size:25">WIFI PORTAL</div>
					</td>
				</tr>
				<tr>
					<td align="center" style="font-size:15">
						<div id="screen" hidden>
						<span style="font-size:25;font-weight:bold">TIME</span><br/><br/>
						<span id="currentTime" style="font-size:20;font-weight:bold"></span><br/>
						<span>CURRENT</span></span><hr/>
						<span id="creditTime" style="font-size:20;font-weight:bold"></span><br/>
						<span>CREDIT</span><hr/>
						<span id="totalTime" style="font-size:20;font-weight:bold"></span><br/>
						<span>TOTAL</span>
						</div>
					</td>
				</tr>
				<tr>
					<td align="center">
						<button class="portalButton" onclick="connect()">Connect</button>
					</td>
				</tr>
				<tr>
					<td align="center">
						<span id="slotTime" style="font-size:20;font-weight:bold"></span><br/>
						<button class="portalButton" id="insertcoin" onclick="useSlot()">
							<span id="insertName">Insert Coin</span>
							<span id="credit"></span>
						</button>
					</td>
				</tr>
				<tr><td align="center"><br /></td></tr>
			</table>
		</div>
	</body>
</html><html>
	<head>
		<title>LOGIN - NH-NET WIFI RENTAL</title>
		<link rel="icon" href="media/icon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="pragma" content="no-cache" />
		<meta http-equiv="expires" content="-1" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;"/>
		<script src="js/md5.js"></script>
		<script src="js/Base64.js"></script>
		<script src="js/function.js"></script>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>

	<body onload="refreshStatus()" onmouseover="bodyActive()" onmouseleave="bodyInactive()" bottommargin="0" topmargin="0" leftmargin="0" rightmargin="0">
	<?php
		if(isset($_POST['mac'])){
			$mac = $_POST['mac'];
			$macpass = $_POST['macpass'];
			$error = $_POST['error'];
			$redirect = $_POST['redirect'];
			$mikrotikLogin = $_POST['mikrotikLogin'];
			echo "<span id='mac' hidden>$mac</span>";
			echo "<span id='error' hidden>$error</span>";
			echo "<form id='ConnectDevice' action='$mikrotikLogin' method='POST'>";
			echo "	<input type='hidden' name='username' value='$mac' />";
			echo "	<input type='hidden' name='password' value='$macpass' />";
			echo "	<input type='hidden' name='dst' value='$redirect' />";
			echo "</form>";
		}else{
			header("location: http://portal.nh.net");
		}
	?>		
		<div align="center">
			<img id="logo" src="media/wifi.gif" width="100px">
			<table id="frame">
				<tr><td align="center"><br /></td></tr>
				<tr>
					<td align="center" id="wifiName">
						<div style="font-size:40">NH-NET</div>
						<div style="font-size:25">WIFI PORTAL</div>
					</td>
				</tr>
				<tr>
					<td align="center" style="font-size:15">
						<div id="screen" hidden>
						<span style="font-size:25;font-weight:bold">TIME</span><br/><br/>
						<span id="currentTime" style="font-size:20;font-weight:bold"></span><br/>
						<span>CURRENT</span></span><hr/>
						<span id="creditTime" style="font-size:20;font-weight:bold"></span><br/>
						<span>CREDIT</span><hr/>
						<span id="totalTime" style="font-size:20;font-weight:bold"></span><br/>
						<span>TOTAL</span>
						</div>
					</td>
				</tr>
				<tr>
				</tr>
				<tr>
					<td align="center">
						<span id="slotTime" style="font-size:20;font-weight:bold"></span><br/>
						<button class="portalButton" id="insertcoin" onclick="useSlot()">
							<span id="insertName">Insert Coin</span>
							<span id="credit"></span>
						</button>
					</td>
				</tr>
				<tr><td align="center"><br /></td></tr>
			</table>
		</div>
	</body>
</html>