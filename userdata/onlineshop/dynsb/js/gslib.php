

function submitForm(frm) {
    document.forms[frm].submit();
}

function checkAllData(frm) {
  var iCountHits = 0;
  var iCountAvailable = 0;
  var sFormName = frm;

  for(var x = 0; x < document.forms[sFormName].elements.length; x++){
      var y = document.forms[sFormName].elements[x];

      if(y.type == 'checkbox' && y.name != 'alldata' && y.name.lastIndexOf('active') == -1 && y.id.lastIndexOf('chk') != -1) {
 
          iCountAvailable++;
          if(document.forms[sFormName].elements[x].checked == true) {
              iCountHits++;
              //setClassName('d'+document.forms[sFormName].elements[x].value, getCheckedRowColor());
              obj = document.getElementById('d' + y.value);

              //obj.getElementsByTagName("tr");

							if (obj.className.lastIndexOf(" highlighted") == -1) {
              	obj.className += " highlighted";
              	
              	if (obj2 = document.getElementById('da' + y.value)) {
              		obj2.className += " highlighted";
              	}
              }

          } else {
              //setClassName('d'+document.forms[sFormName].elements[x].value, getNormalRowColor());
              obj = document.getElementById('d'+document.forms[sFormName].elements[x].value)
              //obj.getElementsByTagName("tr");

              if (obj.className.lastIndexOf(" highlighted") != -1) {
              	obj.className = obj.className.substring(0, (obj.className.lastIndexOf(" highlighted")));
              	
              	if (obj2 = document.getElementById('da' + y.value)) {
              		obj2.className = obj.className;
              	}
							}
          }
      }
  }

  if(iCountHits == iCountAvailable) {
      document.forms[sFormName].alldata.checked = true;
  } else {
      document.forms[sFormName].alldata.checked = false;
  }
}

function selectAllData(frm) {
    var sFormName = frm;
    for(var x = 0; x < document.forms[sFormName].elements.length; x++){
        var y = document.forms[sFormName].elements[x];
        
        //only buttons with id "chk..."
        if(y.name != 'alldata' && y.id.lastIndexOf('chk') != -1) 
        	y.checked = document.forms[sFormName].alldata.checked;
    }
    checkAllData(sFormName);
}


function isDataSelected(frm) {
    var sFormName = frm;
    var bSelected = new Boolean(false);
    for(var x=0; x < document.forms[sFormName].elements.length; x++)  {
        var y = document.forms[sFormName].elements[x];
        if(y.name != 'alldata')  {
            if(y.checked) return bSelected = true;
        }
    }
    return bSelected;
}

function resetSearch(frm, pre, submitFlg) {
    var sFormName = frm;
    var sPrefix = pre;
    var iPreLength = sPrefix.length;
    var bFlg = new Boolean(submitFlg);
    for(var x=0; x < document.forms[sFormName].elements.length; x++)  {
        var y = document.forms[sFormName].elements[x];
        var name = y.name;
        if(name.substr(0, iPreLength) == sPrefix)  {
            document.forms[sFormName].elements[x].value = "";
        }
    }
    if(bFlg == true) {
        document.forms[sFormName].submit();
    }
}

function getRef(obj) {
    if(typeof obj == "string") obj = document.getElementById(obj);
    return obj;
}

function setClassName(obj, className) {
    if(typeof obj == "string") obj = document.getElementById(obj);
    var allTDs = obj.getElementsByTagName("td");
    var lastTD = allTDs.length;

    for (var i in allTDs) {
        if(i != lastTD) getRef(allTDs[i]).className = className;
    }
}

function changeRowColorHilight(obj) {

    var id = obj.substr(1, obj.length);
    chkbox = document.getElementById('chk'+id);
    if(chkbox.checked == true) {
        setClassName(obj, getCheckedRowHilightColor());
    } else {
        setClassName(obj, getNormalRowHilightColor());
    }
}

function changeRowColorNormal(obj) {
    var id = obj.substr(1, obj.length);
    chkbox = document.getElementById('chk'+id);
    if(chkbox.checked == true) {
        setClassName(obj, getCheckedRowColor());
    } else {
        setClassName(obj, getNormalRowColor());
    }
}

function changeRowColorHilightWithoutChkbox(obj) {
    setClassName(obj, getNormalRowHilightColor());
}

function changeRowColorNormalWithoutChkbox(obj) {
    setClassName(obj, getNormalRowColor());
}

function getCheckedRowColor() {
    return 'highlight_false_checked';
}

function getCheckedRowHilightColor() {
    return 'hilight_true_checked';
}

function getNormalRowColor() {
    return 'highlight_false_normal';
}

function getNormalRowHilightColor() {
    return 'hilight_true_normal';
}



function getElem(id) {
    return (document.getElementById ? document.getElementById(id) : document.all[id]);
}

function markField(elem) {
    (elem.value == '') ? elem.style.background = '#FF9999' : elem.style.background = '#FFFFFF';
    if(elem.value == '') {
        return 1;
    } else {
        return 0;
    }
}

/**
* Create a PopUp-Window
*
*/
  function popUpWindow(link,name,width,height) {
	var vPopUpWindow = window.open(link, name, "width=" + width + ",height=" + height);
	}



