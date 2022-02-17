hostname = "http://192.168.0.4"
presence = "active"
slot_status = "available"
checkIfLogged = "logged-out"
credit_status = 0
slotTimeSec = 0
currentTime = 0
creditTime = 0
totalTime = 0


function sendPOST(v,url,param) {//Send post request output on grid
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			eval(v + "='" + Base64.encode(xhttp.responseText) + "'")
			sendPOSTresult = "Success"
		}else if(this.readyState == 4 && this.status != 200){
			console.log("Cannot Connect: " + url)
			sendPOSTresult = "Failed"
		}
	}
	setTimeout(function(){
		xhttp.abort();
	},4000)
	try{
		xhttp.open("POST", url, true);
		xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhttp.send(param);
	}catch(e){
		console.log("Error: " + e.message + "\n" + url)
	}
}

function id(e){
	return document.getElementById(e)
}


function bodyActive(){
	document.body.removeAttribute("onmouseover")
	presence = "active"
}

function bodyInactive(){
	document.body.setAttribute("onmouseover","bodyActive()")
	presence = "inactive"
}

function refreshStatus(){
	mac = id("mac").innerHTML
	setInterval(function(){
		if(presence == "active"){
			checkStatus = "NULL"
			sendPOST("checkStatus",hostname + "/hotspot.php","mode=status&mac="+mac)
			checkStatusWait = setInterval(function(){
				if(checkStatus != "NULL"){
					clearInterval(checkStatusWait)
					checkStatus = Base64.decode(checkStatus).split(",")
					slot_status = checkStatus[0]
					slotTimeSec = checkStatus[1]
					credit_status = checkStatus[2]
					currentTime = checkStatus[3]
					creditTime = checkStatus[4]
					totalTime = checkStatus[5]
					checkIfLogged = checkStatus[6]
					
					slotStatus(slot_status,slotTimeSec,credit_status,currentTime,creditTime,totalTime,checkIfLogged)
				}
				
			},500)
		}
	},1500)
}

function slotStatus(slot,slotTimeSec,slotCredit,currentTime,creditTime,totalTime,logged){		
	if(slot == "available"){
		
		if(id("statusError").innerHTML == "Empty Balance"){
			id("statusError").innerHTML =  ""
			alert("Empty Balance")
		}
		id("connectDevice").removeAttribute("hidden")
		if(logged == "logged-in"){
			id("connectDevice").setAttribute("hidden","")
		}else{
			id("connectDevice").removeAttribute("hidden")
		}
		id("screen").setAttribute("hidden","")
		id("wifiName").removeAttribute("hidden")
		id("insertcoin").removeAttribute("disabled")
		id("insertcoin").setAttribute("onclick","useSlot()")
		id("insertName").innerHTML = "Insert Coin"
		id("slotTime").innerHTML =  slotTimeSec > 1000 ? "" : slotTimeSec
		id("credit").innerHTML = "(" + slotCredit + ")"
	}else if(slot == "inuse"){
		id("connectDevice").removeAttribute("hidden")
		if(logged == "logged-in"){
			id("connectDevice").setAttribute("hidden","")
		}else{
			id("connectDevice").removeAttribute("hidden")
		}
		id("screen").setAttribute("hidden","")
		id("wifiName").removeAttribute("hidden")
		id("insertcoin").setAttribute("disabled","")
		id("insertName").innerHTML = "Slot In Use"
		id("slotTime").innerHTML = slotTimeSec > 1000 ? "" : slotTimeSec
		id("credit").innerHTML = "(" + slotCredit + ")"
	}else if(slot == "private"){
		id("connectDevice").setAttribute("hidden","")
		id("wifiName").setAttribute("hidden","")
		id("screen").removeAttribute("hidden")
		id("insertcoin").removeAttribute("disabled")
		id("insertcoin").setAttribute("onclick","useCredit()")
		id("insertName").innerHTML = "Use Credit"
		id("currentTime").innerHTML = currentTime
		id("creditTime").innerHTML = creditTime
		id("totalTime").innerHTML = totalTime
		id("slotTime").innerHTML = slotTimeSec > 1000 ? "" : slotTimeSec
		id("credit").innerHTML = "(" + slotCredit + ")"
	}
}

function connect(){
	id("ConnectDevice").submit()
}

function useCredit(){
	mac = id("mac").innerHTML
	sendPOST("use",hostname + "/hotspot.php","mode=useCredit&mac="+mac)
}

function useSlot(){
	mac = id("mac").innerHTML
	sendPOST("use",hostname + "/hotspot.php","mode=useSlot&mac="+mac)
}