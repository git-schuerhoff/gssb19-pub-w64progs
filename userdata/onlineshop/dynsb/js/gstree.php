/*

    GS-Tree Navigation System - gstree.js
    Author: Raimund Kulikowski / GS Software Solutions GmbH

    (c) 2004-2005 GS Software Solutions GmbH

    this code is NOT open-source or freeware
    you are not allowed to copy or redistribute it for your own purposes

*/
<?php
//$path = substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"dynsb/"));


?>

function toggle(item) {
    obj = document.getElementById(item);
    visible = (obj.style.display != "none");
    key = document.getElementById("x" + item);
    if(visible) {
        obj.style.display = "none";
        key.innerHTML = "<img src='../image/folder.gif' >";
    } else {
        obj.style.display = "block";
        key.innerHTML = "<img src='../image/textfolder.gif' >";
    }
}

function expand() {
    divs = document.getElementsByTagName("DIV");
    for(i = 0; i < divs.length; i++) {
        divs[i].style.display = "block";
        key = document.getElementById("x" + divs[i].id);
        key.innerHTML = "<img src='../image/textfolder.gif' >";
    }
}

function collapse() {
    divs = document.getElementsByTagName("DIV");
    for(i = 0; i < divs.length; i++) {
        divs[i].style.display = "none";
        key = document.getElementById("x" + divs[i].id);
        key.innerHTML = "<img src='../image/folder.gif' >";
    }
}

function showall(am) {
	var actLevel = 0;
	var cstatus = 0;
	var nextLevel = 0;
	for(i = 0; i < am.length; i++) {
		newLevel = am[i][0];
		diff = 0;
		if(i != (am.length - 1)) nextLevel = am[i+1][0];
		if(newLevel > actLevel) cstatus = 0;
		if(newLevel == actLevel) cstatus = 1;
		if(newLevel < nextLevel) {addNode(am[i],diff);actLevel++;}
		if(newLevel == nextLevel) {addLeaf(am[i]);}
		if(newLevel > nextLevel) {
			diff = newLevel - nextLevel;
			if(am[i][1] == 0) {
                addNode(am[i],diff+cstatus);
            } else {
                addLeaf(am[i]);
                addClosingDivs(diff);
            }
			actLevel = actLevel - diff;
		}
	}
}

function addNode(an, diff) {
	document.write("<p class=\"node e"+ an[0] +"\"><a id=\"x"+an[2]+"\" href=\"javascript:toggle('"+an[2]+"');\" onMouseover=\"return hidestatus();\"><img src='../image/folder.gif'></a>  <a id=\"x"+an[2]+"2\" href=\"javascript:toggle('"+an[2]+"');\" onMouseover=\"return hidestatus();\">"+an[4]+"</a></p>\n");

	//document.write("<div id='"+an[2]+"' style=\"display: none; margin-left: 2em; border:1px solid #000;\">\n");
	document.write("<div id='"+an[2]+"' style=\"display: none;\">\n");
	addClosingDivs(diff);
}

function addLeaf(an) {
    document.write("<p class=\"leaf e"+ an[0] +"\"><img src='../image/right.gif'><a href='"+an[5]+"' target=\"contentFrame\" onMouseover=\"return hidestatus();\">"+an[4]+"</a></p>\n");
}

function addClosingDivs(amount) {
	for(x = 0; x < amount; x++) {
		document.write("</div>\n");
	}
}
