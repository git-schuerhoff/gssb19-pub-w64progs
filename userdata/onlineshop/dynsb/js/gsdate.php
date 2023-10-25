function checkDateFilter() {
    var iError = 0;
    var maxMonths = 36;
    var addsm = "", addem = "";
    
    if(document.frmGSsalevalue.startMonth.value.length < 2) addsm = "0";
    if(document.frmGSsalevalue.endMonth.value.length < 2) addem = "0";
    startval = document.frmGSsalevalue.startYear.value + '' + addsm + document.frmGSsalevalue.startMonth.value;
    endval = document.frmGSsalevalue.endYear.value + '' + addem + document.frmGSsalevalue.endMonth.value;
    if(parseInt(startval, 10) > parseInt(endval, 10)) iError = 1;
    
    if(iError == 0) {
        diffYear = (parseInt(document.frmGSsalevalue.endYear.value, 10) - parseInt(document.frmGSsalevalue.startYear.value, 10));
        diffMonth = (parseInt(document.frmGSsalevalue.startMonth.value, 10) - parseInt(document.frmGSsalevalue.endMonth.value, 10));
        if(diffYear < 0) diffYear = diffYear * -1;
        if(diffMonth < 0) diffMonth = diffMonth * -1;
        if(((diffYear * 12) + diffMonth + 1) > maxMonths) iError = 2;
    }
      
    return iError;
}

function updateStartDate() {
    var addsm = "";
    aSD = new Array();
    aSD = document.frmGSsalevalue.statStartDate.value.split(".");
    if(document.frmGSsalevalue.startMonth.value.length < 2) addsm = "0";
    aSD[1] = document.frmGSsalevalue.startMonth.value;
    aSD[2] = document.frmGSsalevalue.startYear.value;
    document.frmGSsalevalue.statStartDate.value = aSD[0] + '.' + addsm + aSD[1] + '.' + aSD[2];
}

function updateEndDate() {
    var addem = "";
    aSD = new Array();
    aSD = document.frmGSsalevalue.statEndDate.value.split(".");
    if(document.frmGSsalevalue.endMonth.value.length < 2) addem = "0";
    aSD[1] = document.frmGSsalevalue.endMonth.value;
    aSD[2] = document.frmGSsalevalue.endYear.value;
    document.frmGSsalevalue.statEndDate.value = aSD[0] + '.' + addem + aSD[1] + '.' + aSD[2];
}
