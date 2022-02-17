function id(id){
	return document.getElementById(id)
}

function define(){
	date = new Date()
	systemdate = date.toLocaleDateString()
	systemtime = date.toLocaleTimeString()
	id("sysdate").innerHTML = systemdate
	sendPOSTresult = "available"
	hostname = "http://timer/"
	todayReport()
}

function readfile(filename) {//Read from file
	try{
		var Object1 = new ActiveXObject('Scripting.FileSystemObject');
		var str0 = Object1.GetFile(filename);
		newfile = str0.OpenAsTextStream(1); 
		var msg= newfile.ReadAll();
		newfile.Close();
	}catch(e){
		alert("Error: " + e.message + "\n" + filename)
	}
	return msg;
}

function sendPOST(v,url,param) {//Send post request output on grid
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			eval(v + "='" + Base64.encode(xhttp.responseText) + "'")
			sendPOSTresult = "Success"
		}else if(this.readyState == 4 && this.status != 200){
			alert("Cannot Connect: " + url)
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
		alert("Error: " + e.message + "\n" + url)
	}
}

function numberOnly(e){
	if(e.keyCode != 13){
		if(e.keyCode < 48 || e.keyCode > 57){
			e.keyCode = 0
		}
	}else{
		generate()
	}
}

////////////////////////////////////////////////////////////////////////
/*
0-30m = 5pesos
1h = 10
1d = 140
*/

rate = {
	minute:4,
	hour:8,
	day:60
}

function clearScreen(){
	id("day").innerHTML = "00"
	id("hour").innerHTML = "00"
	id("minute").innerHTML = "00"
	id("totalprice").innerHTML = "0"
	id("code").innerHTML = "000000"
	id("screen").style.border = "double blue 2px"
	id("screen").style.backgroundColor = "lightblue"
	id("time").removeAttribute("disabled")
	id("unit").removeAttribute("disabled")
	id("generateButton").removeAttribute("disabled")
}

function toMin(t,m){
	//t = time, m = mode[3 = day,2 = hour,1 = minute]
	switch(m){
		case 1:
		result = t 
		break;
		
		case 2:
		result = t * 60
		break;
		
		case 3:
		result = t * 24 * 60
		break;
	}
	return result
}




function comPute(t){
	h = 0
	d = 0
	if(t > 1439){
		d =  Math.floor(t/1440)
		t = t%1440
	}
	if(t > 59){
		h = Math.floor(t/60)
		t = t%60
	}
	id("day").innerHTML = (String(d).length == 2) ? d : "0"+d
	id("hour").innerHTML = (String(h).length == 2) ? h : "0"+h
	id("minute").innerHTML = (String(t).length == 2) ? t : "0"+t
	p = 0
	m = t
	
	if(m != 0){
		temp = m
		m = rate.minute
		if(parseInt(temp) > 30){
			m = rate.hour
		}
	}
	if(h != 0){
		h = rate.hour * parseInt(h)
	}
	if(d != 0){
		d = rate.day * parseInt(d)
	}
	id("totalprice").innerHTML = parseInt(d) + parseInt(h) + parseInt(m)
}



function statusupdate(){
	t = id("time").value
	m = id("unit").value
	if(isNaN(t) == false && t != ""){
		t = parseInt(t)
		id("time").value = t
		
		switch(m){
			case "minutes":
			comPute(toMin(t,1))
			break;
			
			case "hours":
			comPute(toMin(t,2))
			break;
			
			case "days":
			comPute(toMin(t,3))
			break;
		}
	}
}

function inputFocus(){
	id("time").select()
	id("time").focus()
}

function generate(){
	t = id("time").value
	u = id("unit").value
	d = id("day").innerHTML
	h = id("hour").innerHTML
	m = id("minute").innerHTML
	if(isNaN(t) == false && t != ""){
		switch(u){
			case "minutes":
			t = toMin(t,1)
			break;
			
			case "hours":
			t = toMin(t,2)
			break;
			
			case "days":
			t = toMin(t,3)
			break;
		}
		random = Math.floor((Math.random() * 9999) + 1);
		conf = prompt("Confirm Code: " + random,"")
		if(conf == random){
			returnCode = "NULL"
			sendPOST("returnCode",hostname + "generate.php","mode=generate&time="+parseInt(t)+"&systime="+systemtime+"&sysdate="+systemdate+"&renttime="+d+"d:"+h+"h:"+m+"m")
			genWait = setInterval(function(){
				if(returnCode != "NULL"){
					clearInterval(genWait)
					if(sendPOSTresult == "Success"){
						id("code").innerHTML = Base64.decode(returnCode)
						id("time").setAttribute("disabled","true")
						id("unit").setAttribute("disabled","true")
						id("generateButton").setAttribute("disabled","true")
						id("screen").style.border = "double red 2px"
						id("screen").style.backgroundColor = "white"
						todayReport()
					}else{
						alert("Connection Failed: Try Again")
					}
				}
			},100)
		}
	}
}


function todayReport(){
	id("todayreport").style.backgroundColor = "lightblue"
	todayR = "NULL"
	sendPOST("todayR",hostname + "generate.php","mode=todayreport&sysdate="+systemdate)
	trWait = setInterval(function(){
		if(todayR != "NULL"){
			clearInterval(trWait)
			if(sendPOSTresult == "Success"){
				id("gridcontent").innerHTML = Base64.decode(todayR)
				todaySales()
			}else{
				alert("Connection Failed: Try Again")
			}
			
		}
	},100)
}

function todaySales(){
	todayS = "NULL"
	sendPOST("todayS",hostname + "generate.php","mode=todaysales&sysdate="+systemdate)
	tsWait = setInterval(function(){
		if(todayS != "NULL"){
			clearInterval(tsWait)
			if(sendPOSTresult == "Success"){
				id("todaysales").innerHTML = Base64.decode(todayS)
			}else{
				alert("Connection Failed: Try Again")
			}
		}
	},100)
}



 
 