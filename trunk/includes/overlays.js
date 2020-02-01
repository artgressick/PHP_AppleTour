
	function revert() {
		document.getElementById('overlaypage').style.display = "none";
//		document.getElementById('normal').style.display = "block";
		
		document.getElementById('warning').style.display = "block";
		document.getElementById('notice').style.display = "none";
	}

	function warning(id,val1) {
		var height = (document.height > window.innerHeight ? document.height : window.innerHeight);
		document.getElementById('gray').style.height = height + "px";
		document.getElementById('message').style.top = window.pageYOffset+"px";
		
		document.getElementById('delName').innerHTML = val1;
		document.getElementById('idDel').value = id;
		document.getElementById('overlaypage').style.display = "block";
	}
	
	function delItem(address) {
		var id = document.getElementById('idDel').value;
	
		var XMLHttpRequestObject = false; 
	
		if (window.XMLHttpRequest) {
			XMLHttpRequestObject = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		if(XMLHttpRequestObject) {
			XMLHttpRequestObject.open("GET", address + id);
		
			XMLHttpRequestObject.onreadystatechange = function() { 
				if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) { 
					showNotice(id,XMLHttpRequestObject.responseText);
				} 
			} 
		
			XMLHttpRequestObject.send(null); 
		}
	}
	
	function SelectALL(name){
       theForm = document.getElementById('idForm'); // This is the id of the form
       var tmpName = name+"[]";
       for(i = 0; i < theForm.length; i++) {
           if(theForm[i].name == tmpName) {
    	       //alert(name+theForm[i].value);  // used for testing.  doesn't need to be here.
               if(document.getElementById(name+theForm[i].value)) {
                   document.getElementById(name+theForm[i].value).checked = true; // false will unselect, true will select
               }
           }
       }
   }

	function UnSelectALL(name){
       theForm = document.getElementById('idForm'); // This is the id of the form
       var tmpName = name+"[]";
       for(i = 0; i < theForm.length; i++) {
           if(theForm[i].name == tmpName) {
               //alert("id"+name+"Div"+theForm[i].value);  // used for testing.  doesn't need to be here.
               if(document.getElementById(name+theForm[i].value)) {
                   document.getElementById(name+theForm[i].value).checked = false; // false will unselect, true will select
               }
           }
       }
   }	
	
	function FieldClick(name, id){
		if(!document.getElementById(name+id).checked) {
        	document.getElementById(name+id).checked = true; // false will unselect, true will select
        } else {
        	document.getElementById(name+id).checked = false;			
		}
	}	

	function showNotice(id, type) {

		document.getElementById('tr' + id).style.display = "none";
		repaint();
		
		revert();
	}

	function quickdel(address, idUser, fatherTable) {
		var XMLHttpRequestObject = false; 
	
		if (window.XMLHttpRequest) {
			XMLHttpRequestObject = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		if(XMLHttpRequestObject) {
			XMLHttpRequestObject.open("GET", address);
		
			XMLHttpRequestObject.onreadystatechange = function() { 
				if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) { 
					document.getElementById(fatherTable + 'tr' + idUser).style.display = "none";
					repaintmini(fatherTable);
				} 
			} 
		
			XMLHttpRequestObject.send(null); 
		}
	} 

	function repaintmini(fatherTable) {
		var menuitems = document.getElementById(fatherTable).getElementsByTagName("tr");
		var j = 0;
		for (var i=0; i<menuitems.length; i++) {
			if(menuitems[i].style.display != "none") {
				((j%2) == 0 ? menuitems[i].style.background = "#fff" : menuitems[i].style.background = "#eee");
				j += 1;
			}		
		}
	}

	var http_request = false;
    function postInfo(url, parameters) {

    	http_request = false;
        if (window.XMLHttpRequest) { // Mozilla, Safari,...
        	http_request = new XMLHttpRequest();
         	if (http_request.overrideMimeType) {
         	// set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            	http_request.overrideMimeType('text/html');
         	}
      	} else if (window.ActiveXObject) { // IE
         	try {
            	http_request = new ActiveXObject("Msxml2.XMLHTTP");
         	} catch (e) {
            	try {
               		http_request = new ActiveXObject("Microsoft.XMLHTTP");
            	} catch (e) {}
         	}
      	}
      	if (!http_request) {
        	alert('Cannot create XMLHTTP instance');
         	return false;
      	}
      
		//http_request.onreadystatechange = alertContents;
		http_request.open('POST', url, true);
		http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request.setRequestHeader("Content-length", parameters.length);
		http_request.setRequestHeader("Connection", "close");
		http_request.send(parameters);
		
		http_request.onreadystatechange = function() { 
	   		if (http_request.readyState == 4 && http_request.status == 200) {
	   			//alert(http_request.responseText);
	   			//document.getElementById('showinfo').innerHTML = http_request.responseText;
	   			
	   			//self.scrollTo(0,0);
    	    	//document.getElementById('overlaypage').style.display = "block";
				//document.getElementById('notice').style.display = "block";
      		}
      	}
   	}
	
	function quickHideG(id) {
		if(document.getElementById('qhBody'+ id).style.display == 'none') {
			document.getElementById('qhBody'+ id).style.display = "";
		} else {
			document.getElementById('qhBody'+ id).style.display = "none";
		}
	}
	

