/*A TS 18.07.2014: Parameter iTmpl zu gsse_get_host
hinzugefügt. 
iTmpl = 0: Pfad zur ShopEngine,
iTmpl = 1: Pfad zum Template
*/
/*var g_host = gsse_get_host(window.location.href,0);*/
var decodeBase64 = function(s) {
	var e={},i,b=0,c,x,l=0,a,r='',w=String.fromCharCode,L=s.length;
	var A="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
	for(i=0;i<64;i++){e[A.charAt(i)]=i;}
	for(x=0;x<L;x++){
		c=e[s.charAt(x)];b=(b<<6)+c;l+=6;
		while(l>=8){((a=(b>>>(l-=8))&0xff)||(x<(L-2)))&&(r+=w(a));}
	}
	return r;
};
var g_host = 'inc/';
/*A TS 18.07.2014: Pfad zum Template*/
/*var g_host_tmpl = gsse_get_host(window.location.href,1);*/
var g_host_tmpl = 'template/';
var g_req = null;
var g_reqerg = "";
var g_iState = 0;
var g_show_zoom = false;
var g_s_hoehe = screen.height;
var g_s_breite = screen.width;
var g_w_hoehe = window.innerHeight;
var g_w_breite = window.innerWidth;

/*Preloaded Settings*/
var g_cur1 = get_setting('edCurrencySymbol1_Text');
var g_cur2 = get_setting('edCurrencySymbol_Text');
var g_errbox = decodeBase64(g_errbox_enc);
var g_msg_class = 'note-msg';
var g_msg_text = '';
/*var g_url = get_setting('edAbsoluteShopPath_Text');*/
var g_url = '';
var g_maxqty = get_setting('edMaxQuantity_Text');
var g_on_basketpage = 0;
/*A TS 25.11.2014 Variable for Itemmenu*/
var g_itemmenu = '';
/*E TS 25.11.2014*/
/*A TS 17.12.2014*/
var g_itemlink = decodeBase64(g_itemlink_enc);
var g_itemimagelink = decodeBase64(g_itemimagelink_enc);
var g_itemimage = decodeBase64(g_itemimage_enc);
var g_pcontent =decodeBase64(g_pcontent_enc);
var g_mbouter = decodeBase64(g_mbouter_enc);
var g_mbitem = decodeBase64(g_mbitem_enc);
/*E TS 17.12.2014*/

/*A TS 09.12.2014*/
var g_permalink=get_setting('cbUsePermalinks_Checked');
/*E TS 09.12.2014*/

/*Tell PHP Screendimensions, Windowdimensions, isMobile and isPhone*/
var is_mobile = (isMobile) ? '1' : '0';
var is_phone = (isPhone) ? '1' : '0';

/*A TS 13.07.2015: reale Breite (ohne Scrollbars mitgeben)*/
//$.get(g_host + 'set_desktop.inc.php', { s_width: g_s_breite, s_height: g_s_hoehe, w_width: g_w_breite, w_height: g_w_hoehe, is_mobile: is_mobile, is_phone : is_phone} );
set_desktop();

var g_subcats = '';
var g_dontShowToolbar = get_setting('cbDontShowToolbar_Checked');
/*TS 01.12.2015: Flag if Validationemail is already sent*/
var g_valMailIsSend = get_sessvar('valmailsend');
if(g_valMailIsSend == '') {
	g_valMailIsSend = 0;
}

/*Loggedin User-Data*/
var g_login = new Array();
g_login = JSON.parse(decodeBase64(g_strlogin));

var browser = navigator.appName;
if(browser == "Microsoft Internet Explorer")
{
	var g_ie = true;
}
else
{
	var g_ie = false;
}

/*Begin base functions*/

/*Parse URL*/
function gsse_get_host(cUrl,iTmpl)
{
	var nPos;
	var cHost;
	nPos = cUrl.lastIndexOf("/");
	if(iTmpl == 0)
	{
		/*Pfad zur ShopEngine*/
		cHost = cUrl.substring(0, nPos + 1) + 'inc/';
	}
	else
	{
		/*Pfad zum Template*/
		cHost = cUrl.substring(0, nPos + 1) + 'template/';
	}
	return cHost;
}

/*Generate XMLHttpRequest-Object*/
function gen_req()
{
	var req = null;
	try {
		req = new XMLHttpRequest();
	} catch (ms) {
		try {
			 req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (nonms) {
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (failed) {
				req = null;
			}
		}
	}

	if (req == null) {
		alert("Error creating request object!");
		return;
	}
	return req;
}

/*Generate 2-dimensional Array from responseText*/ 
function gen_array(cString)
{
	var aAusgabe = new Array();
	if(cString.indexOf("~") < 0)
	{
		aAusgabe = cString.split("|");
		return aAusgabe;
	}
	else
	{
		var aHelper = cString.split("~");
		var aHelper2
		for(var h = 0; h < aHelper.length; h++)
		{
			if(aHelper[h].length > 0)
			{
				aHelper2 = aHelper[h].split("|");
				aAusgabe.push(aHelper2);
			}
		}
		return aAusgabe;
	}
}

function get_code(event)
{
	var kcode;
	event = event || window.event;
	kcode = event.keyCode;
	/*alert("Key: " + kcode);*/
	return kcode;
}

/*End base functions*/

/*Begin GSSE-spezific functions*/ 

function sel_var_change(iIDX)
{
	self.location.href = 'index.php?page=detail&item=' + iIDX;
	return;
}

function chk_number(e)
{
	var kcode = get_code(e);
	if((kcode >= 48 && kcode <= 57) || (kcode >= 96 && kcode <= 105) || kcode == 8 || kcode == 46 || kcode == 37 || kcode == 39)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function chk_upd_basket(e,idx,isDecimal)
{
	var kcode = get_code(e);
	if (isDecimal == 1)
	{
		if((kcode >= 48 && kcode <= 57) || (kcode >= 96 && kcode <= 105) || kcode == 8 || kcode == 46 || kcode == 37 || kcode == 39)
		{
			return true;
		}
		else
		{
			if(kcode == 13)
			{
				upd_basket(idx);
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	else
	{
		if((kcode >= 48 && kcode <= 57) || (kcode >= 96 && kcode <= 105) || kcode == 8 || kcode == 46 || kcode == 37 || kcode == 39 || kcode == 110 || kcode == 190 || kcode == 188)
		{
			return true;
		}
		else
		{
			if(kcode == 13)
			{
				upd_basket(idx);
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}

function upd_basket(idx)
{
	var imenge = document.getElementById('icount_' + idx).value;
	var cmd;
	var res;
	var xhr;
	cmd = g_host + 'update_basket.inc.php?idx=' + idx + '&menge=' + imenge;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				aerg = gen_array(res);
				if(aerg[0][0] == 0) {
					self.location.replace('index.php?page=basket');
				} else {
					if(aerg[0][0] == -2) {
						document.getElementById('icount_' + idx).value = aerg[0][2];
						alert(aerg[0][1]);
					} else {
						alert(aerg[0][1]);
					}
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	
	return;
}

function chg_paymshipm()
{
	var cmd;
	var cmd2;
	var cmd3;
	var res;
	var res2;
	var res3;
	var xhr;
	var xhr2;
	var xhr3;
	var aerg;
	var aerg2;
	var aerg3;
	var newOpt;
	var payIDX = 0;
	var shipIDX = 0;
	var aDelivery = new Array();
	var iArea = document.getElementById('addressArea').options[document.getElementById('addressArea').options.selectedIndex].value;
	
	cmd = g_host + 'get_delivery_ids.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				aDelivery = JSON.parse(res);
				//2. Request
				cmd2 = g_host + 'get_shipmpaym.inc.php?area=' + iArea + '&mode=ship';
				xhr2 = gen_req();
				xhr2.open("GET", cmd2, true);
				xhr2.onload = function (e) {
					if (xhr2.readyState === 4) {
						if (xhr2.status === 200) {
							res2 = xhr2.responseText;
							aerg2 = gen_array(res2);
							if(aerg2[0][0] == 0) {
								document.getElementById('shipment').options.length = 0;
								for(o = 0; o < aerg2.length; o++) {
									newOpt = new Option(aerg2[o][2], aerg2[o][1], false, false);
									document.getElementById('shipment').options[o] = newOpt;
									if(aDelivery['ship_id'] == aerg2[o][1]) {
										document.getElementById('shipment').options[o].selected = true;
									} else {
										document.getElementById('shipment').options[o].selected = false;
									}
								}
								//3. Request
								cmd3 = g_host + 'get_shipmpaym.inc.php?area=' + iArea + '&mode=pay';
								xhr3 = gen_req();
								xhr3.open("GET", cmd3, true);
								xhr3.onload = function (e) {
									if (xhr3.readyState === 4) {
										if (xhr3.status === 200) {
											res3 = xhr3.responseText;
											aerg3 = gen_array(res3);
											if(aerg3[0][0] == 0) {
												document.getElementById('payment').options.length = 0;
												for(o = 0; o < aerg3.length; o++) {
													newOpt = new Option(aerg3[o][2], aerg3[o][1], false, false);
													document.getElementById('payment').options[o] = newOpt;
													if(aDelivery['paym_id'] == aerg3[o][1]) {
														document.getElementById('payment').options[o].selected = true;
													} else {
														document.getElementById('payment').options[o].selected = false;
													}
												}
												set_section();
											} else {
												alert("Fehler");
											}
										} else {
											console.error(xhr3.statusText);
										}
									}
								}
							};
							xhr3.onerror = function (e) {
								console.error(xhr3.statusText);
							};
							xhr3.send(null);
						} else {
							console.error(xhr2.statusText);
						}
					}
				};
				xhr2.onerror = function (e) {
					console.error(xhr2.statusText);
				};
				xhr2.send(null);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function chg_addressArea()
{
	var $=jQuery;
	var cmd;
	var res;
	var xhr;
	var aerg;
	var newOpt;
	var lCntSelected = false;
	var iArea = document.getElementById('addressArea').options[document.getElementById('addressArea').options.selectedIndex].value;
	
	cmd = g_host + 'get_countries.inc.php?area=' + iArea;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				aerg = gen_array(res);
				if(aerg[0][0] == 0) {
					document.getElementById(get_lngtext('LangTagFNFieldState')).options.length = 0;
					newOpt = new Option(get_lngtext('LangTagPleaseSelect'), '', false, false);
					document.getElementById(get_lngtext('LangTagFNFieldState')).options[0] = newOpt;
					for(o = 0; o < aerg.length; o++) {
						
						if(aerg[o][1] == get_cookie(get_lngtext('LangTagFNFieldState'))) {
							newOpt = new Option(aerg[o][2], aerg[o][1], true, true);
							lCntSelected = true;
						} else {
							newOpt = new Option(aerg[o][2], aerg[o][1], false, false);
						}
						document.getElementById(get_lngtext('LangTagFNFieldState')).options[document.getElementById(get_lngtext('LangTagFNFieldState')).options.length] = newOpt;
					}
					//TS: Wenn nichts gewählt wurde, dann ersten Vorwählen
					if(!lCntSelected) {
						if(document.getElementById(get_lngtext('LangTagFNFieldState')).options.length > 1) {
							document.getElementById(get_lngtext('LangTagFNFieldState')).options.selectedIndex = 1;
							//document.getElementById(get_lngtext('LangTagFNFieldState')).options[document.getElementById(get_lngtext('LangTagFNFieldState')).options.selectedIndex].selected = true;
							//TS 28.12.2016: Und Cookie setzen
							set_cookie(document.getElementById(get_lngtext('LangTagFNFieldState')));
						}
					}
				} else {
					alert("Fehler");
				}
				
				//scrollTop: 400
				$('body,html').animate({
					scrollTop: 0
				}, 800);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return true;
}

function display() {
dis = window.open("inc/popup.php?content=AGB","my","toolbar=0,scrollbars,resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function displayanchor() {
dis = window.open("inc/popup.php?content=revocation","my","toolbar=0,scrollbars,resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function display2() {
dis = window.open("inc/popup.php?content=privacy","my","toolbar=0,scrollbars,resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function displayimprint() {
dis = window.open("inc/popup.php?content=imprint","my","toolbar=0,scrollbars,resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function displaydelivery() {
dis = window.open("inc/popup.php?content=VInfo","my","toolbar=0,scrollbars, resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function displayTermsAndCond() {
dis = window.open("inc/popup.php?content=AGB","my","toolbar=0,scrollbars, resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function displayROR() {
dis = window.open("inc/popup.php?content=revocation","my","toolbar=0,scrollbars, resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}
function displaywithdrawalform() {
dis = window.open("index.php?page=modelwithdrawalform","my","toolbar=0,scrollbars, resizable=1,status=no,width=700, height=500, innerHeight=500,innerWidth=700,outerHeight=500,outerwidth=700");
dis.focus();
}

function set_comporpriv(oidx)
{
	if(oidx == 0)
	{
		document.getElementById('fd_company').className = 'gs-no-display';
		/*document.getElementById('fd_cusnr').className = 'gs-no-display';*/
		document.getElementById('fd_vatid').className = 'gs-no-display';
		document.getElementById('fd_birthday').className = 'fields';
		if(document.getElementById('fd_scompany')) { document.getElementById('fd_scompany').className = 'displaynone'; }
	}
	else
	{
		document.getElementById('fd_company').className = 'fields';
		/*document.getElementById('fd_cusnr').className = 'fields';*/
		document.getElementById('fd_vatid').className = 'fields';
		document.getElementById('fd_birthday').className = 'gs-no-display';
		if(document.getElementById('fd_scompany')) {document.getElementById('fd_scompany').className = ''; }
	}
	return;
}

function set_compopriv_ext(oidx)
{
	if(oidx == 0)
	{
		document.getElementById('fd_company').className = 'gs-no-display';
		document.getElementById('fd_vatid').className = 'gs-no-display';
		if(document.getElementById('fd_scompany')) { document.getElementById('fd_scompany').className = 'gs-no-display'; }
	}
	else
	{
		document.getElementById('fd_company').className = 'fields';
		document.getElementById('fd_vatid').className = 'fields';
		if(document.getElementById('fd_scompany')) { document.getElementById('fd_scompany').className = 'fields'; }
	}
	return;
}

function set_compopriv_style(oidx)
{
	if(oidx == 0)
	{
		document.getElementById('fd_company').style.display = 'none';
		document.getElementById('fd_vatid').style.display = 'none';
		if(document.getElementById('fd_scompany')) { document.getElementById('fd_scompany').style.display = 'none'; }
	}
	else
	{
		document.getElementById('fd_company').style.display = '';
		document.getElementById('fd_vatid').style.display = '';
		if(document.getElementById('fd_scompany')) { document.getElementById('fd_scompany').style.display = ''; }
	}
	return;
}

function changestep(oidx)
{
	switch(oidx)
	{
		case 1:
			document.getElementById('billingform').style.display = '';
			document.getElementById('checkoutform').style.display = 'none';
			document.getElementById('summaryform').style.display = 'none';
			break;
		case 2:
			
			document.getElementById('billingform').style.display = 'none';
			document.getElementById('checkoutform').style.display = '';
			document.getElementById('summaryform').style.display = 'none';
			break;
		case 3:
			document.getElementById('billingform').style.display = 'none';
			document.getElementById('checkoutform').style.display = 'none';
			document.getElementById('summaryform').style.display = '';
			break;
		default:
			return false;
			break;
	}
}

function get_lngtext(cTag)
{
	var res;
	var dec = '';
	if(g_alng[cTag]) {
		res = g_alng[cTag];
		if(res != '') {
			//dec = decodeBase64(res)
			//dec = decodeURI(res);
			//TS 03.08.2017: Für weitere Zeichen decodeURIComponent verwenden
			dec = decodeURIComponent(res);
		}
	}
	return dec;
}

function get_setting(cName)
{var res = '';
	if(g_aSettings[cName]) {
		res = decodeBase64(g_aSettings[cName]);
	}
	return res;
}

function do_umlauts(str) {
	str = str.replace('**ae**','ä');
	str = str.replace('**Ae**','Ä');
	str = str.replace('**oe**','ö');
	str = str.replace('**Oe**','Ö');
	str = str.replace('**ue**','ü');
	str = str.replace('**Ue**','Ü');
	str = str.replace('**ss**','ß');
	return str;
}

function CheckBirthdayFormat(birthday)
{
	var ok = true;
	var re = new RegExp('^([0-9]{2})\\.([0-9]{2})\\.([0-9]{4})$');

	if(!re.test(birthday))
	{
		ok = false;
	}
	else if (RegExp.$3 < 1900 || RegExp.$3 > 2100)
	{
		alert(RegExp.$3);
		ok = false;
	}
	else if (RegExp.$2 < 1 || RegExp.$2 > 12)
	{
		ok = false;
	}
	else if (RegExp.$1 < 1 || RegExp.$1 > 31)
	{
		ok = false;
	}
	else if (RegExp.$1 > 30 && RegExp.$2 != 1 && RegExp.$2 != 3 && RegExp.$2 != 5 && RegExp.$2 != 7 && RegExp.$2 != 8 && RegExp.$2 != 10 && RegExp.$2 != 12)
	{
		ok = false;
	}
	else if (RegExp.$1 > 29 && RegExp.$2 == 2)
	{
		ok = false;
	}
	else if (RegExp.$1 > 28 && RegExp.$2 == 2 && RegExp.$3 % 4 != 0)
	{
		ok = false;
	}
	return ok;
}

function formfriendly(str)
{
	var cres;
	cres = str.replace('Ä','Ae');
	cres = cres.replace('Ö','Oe');
	cres = cres.replace('Ü','Ue');
	cres = cres.replace('ä','ae');
	cres = cres.replace('ö','oe');
	cres = cres.replace('ü','ue');
	cres = cres.replace('ß','sz');
	cres = cres.replace(/[^0-9a-zA-Z]/gi, '');
	return cres;
}

function chkcustomerlogin(cEmail, cPass, iB2B)
{
	var lastpage;
	var cmd;
	var res;
	var xhr;
	cmd = g_host + 'get_customerlogin.inc.php?cemail=' + cEmail + '&cpass=' + cPass;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				if (res == '1') {
					if(iB2B === undefined) {
						self.location.href = "index.php?page=addressdata_login";
					} else {
						self.location.href = "index.php?page=main";
					}
					return true;
				} else {
					msg = get_lngtext('LangTagTextLoginError');
					//Login fehlgeschlagen.
					alert(msg);
					return false;
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	/*if (get_customerlogin(cEmail, cPass) == true)
	{
		if(iB2B === undefined) {
			lastpage = get_sessvar('lastpage');
			if(lastpage != "") {
				self.location.href = lastpage;
			} else {
				self.location.href = "index.php?page=addressdata_login";
			}
		} else {
			self.location.href = "index.php?page=main";
		}
		return true;
	}
	else
	{
		msg = get_lngtext('LangTagTextLoginError');
		//Login fehlgeschlagen.
		alert(msg);
		return false;
	}*/
	
}

function hide_div(cID,cStyle)
{
	document.getElementById(cID).innerHTML = "";
	document.getElementById(cID).className = cStyle;
	return;
}

function sendMail(from, to, subject, message, captcha, check)
{
	var sendmsg;
	var xhr;
	sendmsg = encodeURIComponent(message);
	//sendmsg = message;
	var cmd = g_host + "sendmail.inc.php";
	var cParam = "mail_from=" + from + 
					 "&mail_to=" + to +
					 "&subject=" + subject +
					 "&message=" + sendmsg + 
					 "&captcha=" + captcha +
					 "&check=" + check;
	xhr = gen_req();
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				g_reqerg = xhr.responseText;
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(cParam);
	
	return true;
}

function dispRating(r) 
{
	var e = document.getElementById('commratingpic');
	if (e) 
	{
		e.src = 'template/images/rating' + r + '.gif';
	}
}

function setcomment(id)
{
	var subject = document.getElementById('commentsubject' + id).value;
	var commenttext = document.getElementById('commenttext' + id).value;
	
	document.getElementById('commsubject').value = subject;
	document.getElementById('commbody').value = commenttext;
	document.getElementById('commrating').options.selectedIndex = document.getElementById('rating' + id).value;
	document.getElementById('commrating').options.selectedIndex ++ ;
	document.getElementById('editcomment').value = id;
	/*document.getElementById('commbutton').name = 'editcomment';*/
}

function ChangeAddress()
{

	if ($('#cusFirmname').val() == '' && $('#_LANGTAGFNFIELDCOMPANYORPRIVATE_').val()=='firma')
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFNFieldCompany') + '!';
		/*Bitte eingeben: Firma!*/
		alert(msg);
		return false;
	}


	if (document.orderform.cusTitle.selectedIndex == 0)
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFNFieldFormToAddress') + ' ' + get_lngtext('LangTagBuy2ContactInputLabel') + '!';
		/*Bitte eingeben: Anrede Ansprechpartner!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusFirstName.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFieldFirstName') + ' ' + get_lngtext('LangTagBuy2ContactInputLabel') + '!';
		/*Bitte eingeben: Vorname Ansprechpartner!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusLastName.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFieldLastName') + ' ' + get_lngtext('LangTagBuy2ContactInputLabel') + '!';
		/*Bitte eingeben: Nachname Ansprechpartner!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusStreet.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFieldShippingStreet') + '!';
		/*Bitte eingeben: Straße!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusCity.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFieldCity') + '!';
		/*Bitte eingeben: Ort!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusZipCode.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFieldZipCode') + '!';
		/*Bitte eingeben: PLZ!*/
		alert(msg);
		return false;
	}

	if((!(CheckEmail(document.orderform.cusEMail.value))) || (document.orderform.cusEMail.value == ""))
	{
		msg = get_lngtext('LangTagTextEmailAddressIncorrect') + '!';
		/*Fehler im Feld Email-Adresse!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusPhone.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFNFieldPhone') + '!';
		/*Bitte eingeben: Telefon!*/
		alert(msg);
		return false;
	}

	if(document.orderform.cusBirthdate.value == "")
	{
		msg = get_lngtext('LangTagTextPleaseEnter') + ' ' + get_lngtext('LangTagFNFieldGeburtsdatum') + '!';
		/*Bitte eingeben: Geburtsdatum!*/
		alert(msg);
		return false;
	}
	else if(!(CheckBirthdayFormat(document.orderform.cusBirthdate.value)))
	{
		msg = get_lngtext('LangTagTextPleaseEnterBirthdayFormat') + '!';
		/*Sie müssen Ihr Geburtsdatum im Format "DD.MM.YYYY" eingeben!*/
		alert(msg);
		return false;
	}
return true;
}

function CheckEmail(emailAddress)
{
	var ok = true;
	var regExpression = /.+@.+\../;
	if(emailAddress != "")
	{
		if(!regExpression.exec(emailAddress))
		{
			ok = false;
		}
	}
	return ok;
}

function startaction(action)
{
	if(action=="order") 
	{
		Check = true;
	}

	if (Check == true) 
	{
		document.order_bonus.action.value = action;
		document.order_bonus.submit();
	}
}

function itemrequest()
{
	var regExpression = /.+@.+\../;
	var msg1 = document.getElementById('msg1').value;
	var msg2 = document.getElementById('msg2').value;
	var msg3 = document.getElementById('msg3').value;
	var dear = document.getElementById('dear').value;
	var thankyou = document.getElementById('thankyou').value;
	var subject = document.getElementById('subject').value;
	var recipient = document.getElementById('recipient').value;
	var email = document.getElementById('email').value;
	var message = document.getElementById('message').value;
	var productlink = document.getElementById('productlink').value;
	var item = decodeURI(document.getElementById('item').value);
	item = item.replace(/\+/g, " ");
	if(email == '')
	{
		alert(msg1);
		return;
	}
	else
	{
		if(!regExpression.exec(email))
		{
			alert(msg1);
			return;
		}
	}
	
	var mess = dear + ',\n\n'+message+'\n'+
				  '\n' + item + '\n' + productlink + '\n\nE-Mail: '+email+'\n\n' +
				  thankyou;
	
	if(sendMail(email,recipient,subject,mess,'', 'login'))
	{
		alert(msg3);
	}
	else
	{
		alert(msg2);
	}
	
	return;
}

function checkbuy3form(specpaym)
{
	var cmd;
	var xhr;
	
	cmd = g_host + 'kill_coupon.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				//res = xhr.responseText;
				//Wieso dieses Timeout?
				//setTimeout('sendOrderForm()',1000);
				sendOrderForm();
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function checkbuy3() {
	var lDebug = false;
	var res;
	var cmd;
	var erg;
	var xhr;
	var xhr2;
	var timeout = 10;
	var aerg = new Array();
	var specpaym = document.getElementById('_LANGTAGFNFIELDPAYMENTINTERNALNAME_').value;
	var totheight = 1;
	
	if(document.getElementById('SepaEinverstandenCheck') && (specpaym == 'PaymentDirectDebit')){
		if(document.getElementById('SepaEinverstandenCheck').checked == true){
			result_validate = true;
		} else {
			result_validate = false;
			alert(get_lngtext('LangTagPleaseDirectDebit'));
			document.getElementById('SepaEinverstanden').style.backgroundColor = '#FF5555';
			top = jQuery('#SepaEinverstanden').position().top;
			jQuery(window).scrollTop( 1400 );
		}
	} else {
		result_validate = true;
	}
	
	if(result_validate == true) {
		if(document.getElementById('sb_busy_screen')) {
			if(document.getElementById("buy_wrapper")) {
				totheight = document.getElementById("buy_wrapper").scrollHeight;
			}
			document.getElementById('sb_busy_screen').className = 'sb_progress';
			document.getElementById('sb_busy_screen').style.height = totheight + 'px';
			document.getElementById('sb_busy_screen_inner').className = 'sb_progress_inner';
		}
		
		//PayPal-Plus bei passenden Zahlungsarten verwenden, wenn ausgewählt
		if(specpaym == 'PaymentPayPal' && get_setting('rbUsePPPlus_Checked') == 'True' && specpaym == 'PaymentPayPal' && get_setting('rbUsePPClassic_Checked') == 'False' && get_setting('cbUsePayPal_Checked') == 'True') {
			if(document.getElementById('pp_state').value == 'ok') {
				//PAYPAL.apps.PPP.doCheckout();
				//TS 11.11.2016: Zahlung ausführen
				cmd = g_host + 'pp-plus_executepayment.inc.php';
				lDebug = false;
				if(lDebug) {
					self.location.replace(cmd+'?debug=1');
				} else {
					//TS 23.02.2017: Als asynchroner Request
					xhr = gen_req();
					xhr.open("GET", cmd, true);
					//Send the proper header information along with the request
					xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
					xhr.onload = function (e) {
						if (xhr.readyState === 4) {
							if (xhr.status === 200) {
								//Request ist durch gelaufen
								aRes = JSON.parse(xhr.responseText);
								//Interpretiere Ergebnis
								if(aRes['errno'] == 0) {
									kill_coupon();
									//Wieso dieses timeout?
									//setTimeout('sendOrderForm()',timeout);
									sendOrderForm();
								} else {
									alert(aRes['errmsg']);
								}
							} else {
								console.error(xhr.statusText);
							}
						}
					};
					xhr.onerror = function (e) {
						console.error(xhr.statusText);
					};
					xhr.send(null);
				}
			}
		} else {
			if(specpaym == 'PaymentPayPal' && get_setting('cbUsePayPal_Checked') == 'True') {
				if(document.getElementById('pp_state').value == 'ok' && document.getElementById('pp_token').value != '') {
					cmd = g_host + 'pp_getexpresscheckoutdetails.inc.php?token=' + document.getElementById('pp_token').value;
					xhr = gen_req();
					xhr.open("GET", cmd, true);
					xhr.onload = function (e) {
						if (xhr.readyState === 4) {
							if (xhr.status === 200) {
								res = xhr.responseText;
								aerg = JSON.parse(res);
								if(aerg['error_code'] == 0) {
									kill_coupon();
									//Wieso dieses timeout?
									//setTimeout('sendOrderForm()',timeout);
									sendOrderForm();
								} else {
									alert(aerg['error_message']);
								}
							} else {
								console.error(xhr.statusText);
							}
						}
					};
					xhr.onerror = function (e) {
						console.error(xhr.statusText);
					};
					xhr.send(null);
					return;
				}
			} else if(specpaym == 'PaymentSaferpay') {
				if(document.getElementById('sp_state').value == 'ok') {
					if(document.getElementById('rememberme')) {
						if(!document.getElementById('rememberme').checked) {
							delete_cookies();
						}
					}
					setTimeout('sendOrderForm()',timeout);
				}
			} else if(specpaym == 'PaymentWorldPay') {
				document.worldpayform.submit();
			} else if(specpaym == 'PaymentWebMoney') {
					document.webmoneyform.submit();
			} else if(specpaym == 'PaymentGiropay') {
				var AMOUNT = document.getElementById('GP_AMOUNT').value;
				var ACCOUNTID = document.getElementById('GP_ACCOUNTID').value;
				var CURRENCY = document.getElementById('GP_CURRENCY').value;
				var DESCRIPTION = document.getElementById('GP_DESCRIPTION').value;
				var NOTIFY = document.getElementById('GP_NOTIFY').value;
				var CVC = document.getElementById('GP_CVC').value;
				var NAME = document.getElementById('GP_NAME').value;
				var AUTOCLOSE = document.getElementById('GP_AUTOCLOSE').value;
				var ORDERID = document.getElementById('GP_ORDERID').value;
				var TESTMODE = document.getElementById('GP_TESTMODE').value;
				if(TESTMODE == 1) {
					window.open("https://www.saferpay.com/hosting/Redirect.asp?PROVIDERSET=631&LANGID=DE&MENUCOLOR=CCCCCC&HEADCOLOR=F6D95E&PROFILE=ShopBuilder&AMOUNT=" +AMOUNT
						+"&ACCOUNTID=99867-94913159&CURRENCY="+CURRENCY+"&ORDERID="+ORDERID
						+"&DESCRIPTION="+DESCRIPTION+"&CCCVC=yes&CCNAME=yes&AUTOCLOSE=0", "giropay", "toolbar,status");
				} else {
					var url = "https://www.saferpay.com/hosting/CreatePayInit.asp?PROVIDERSET=631&LANGID=DE&MENUCOLOR=CCCCCC&HEADCOLOR=F6D95E"  
						+ "&AMOUNT=" + AMOUNT 
						+ "&ACCOUNTID=" + ACCOUNTID 
						+ "&CURRENCY=" + CURRENCY 
						+ "&ORDERID=" + ORDERID 
						+ "&DESCRIPTION=" + DESCRIPTION 
						+ "&NOTIFYADDRESS=" + NOTIFY 
						+ "&CCCVC=" + CVC 
						+ "&CCNAME=" + NAME 
						+ "&AUTOCLOSE=" + AUTOCLOSE;
						window.open("inc/gotosafer.php?url="+escape(url), "giropay", "toolbar,status");
				}
			} else {
				kill_coupon();
				//Wieso dieses timeout?
				//setTimeout('sendOrderForm()',timeout);
				sendOrderForm();
			}
		}
	}
	return;
}

function checkorder() {
	var lDebug = false;
	var res;
	var cmd;
	var erg;
	var xhr;
	var xhr2;
	var timeout = 10;
	var aerg = new Array();
	var specpaym = document.getElementById('paymentInternalName').value;
	var totheight = 1;
	
	if(document.getElementById('SepaEinverstandenCheck') && (specpaym == 'PaymentDirectDebit')){
		if(document.getElementById('SepaEinverstandenCheck').checked == true){
			result_validate = true;
		} else {
			result_validate = false;
			alert(get_lngtext('LangTagPleaseDirectDebit'));
			document.getElementById('SepaEinverstanden').style.backgroundColor = '#FF5555';
			top = jQuery('#SepaEinverstanden').position().top;
			jQuery(window).scrollTop( 1400 );
		}
	} else {
		result_validate = true;
	}
	
	if(result_validate == true) {
		if(document.getElementById('sb_busy_screen')) {
			if(document.getElementById("buy_wrapper")) {
				totheight = document.getElementById("buy_wrapper").scrollHeight;
			}
			document.getElementById('sb_busy_screen').className = 'sb_progress';
			document.getElementById('sb_busy_screen').style.height = totheight + 'px';
			document.getElementById('sb_busy_screen_inner').className = 'sb_progress_inner';
		}
		
		//PayPal-Plus bei passenden Zahlungsarten verwenden, wenn ausgewählt
		if(specpaym == 'PaymentPayPal' && get_setting('rbUsePPPlus_Checked') == 'True' && get_setting('rbUsePPClassic_Checked') == 'False' && get_setting('cbUsePayPal_Checked') == 'True') {
			if(document.getElementById('pp_state').value == 'ok') {
				//PAYPAL.apps.PPP.doCheckout();
				//TS 11.11.2016: Zahlung ausführen
				cmd = g_host + 'pp-plus_executepayment.inc.php';
				lDebug = false;
				if(lDebug) {
					self.location.replace(cmd+'?debug=1');
				} else {
					//TS 23.02.2017: Als asynchroner Request
					xhr = gen_req();
					xhr.open("GET", cmd, true);
					//Send the proper header information along with the request
					xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
					xhr.onload = function (e) {
						if (xhr.readyState === 4) {
							if (xhr.status === 200) {
								//Request ist durch gelaufen
								aRes = JSON.parse(xhr.responseText);
								//Interpretiere Ergebnis
								if(aRes['errno'] == 0) {
									kill_coupon();
									//Wieso dieses timeout?
									//setTimeout('sendOrderForm()',timeout);
									sendOrder();
								} else {
									alert(aRes['errmsg']);
								}
							} else {
								console.error(xhr.statusText);
							}
						}
					};
					xhr.onerror = function (e) {
						console.error(xhr.statusText);
					};
					xhr.send(null);
				}
			}
		} else {
			if(specpaym == 'PaymentPayPal' && get_setting('cbUsePayPal_Checked') == 'True') {
				if(document.getElementById('pp_state').value == 'ok' && document.getElementById('pp_token').value != '') {
					cmd = g_host + 'pp_getexpresscheckoutdetails.inc.php?token=' + document.getElementById('pp_token').value;
					xhr = gen_req();
					xhr.open("GET", cmd, true);
					xhr.onload = function (e) {
						if (xhr.readyState === 4) {
							if (xhr.status === 200) {
								res = xhr.responseText;
								aerg = JSON.parse(res);
								if(aerg['error_code'] == 0) {
									kill_coupon();
									//Wieso dieses timeout?
									//setTimeout('sendOrderForm()',timeout);
									sendOrder();
								} else {
									alert(aerg['error_message']);
								}
							} else {
								console.error(xhr.statusText);
							}
						}
					};
					xhr.onerror = function (e) {
						console.error(xhr.statusText);
					};
					xhr.send(null);
					return;
				}
			} else if(specpaym == 'PaymentSaferpay') {
				if(document.getElementById('sp_state').value == 'ok') {
					if(document.getElementById('rememberme')) {
						if(document.getElementById('rememberme').value == 'Y') {
							delete_cookies();
						}
					}
					setTimeout('sendOrderForm()',timeout);
				}
			} else if(specpaym == 'PaymentWorldPay') {
				document.worldpayform.submit();
			} else if(specpaym == 'PaymentWebMoney') {
					document.webmoneyform.submit();
			} else if(specpaym == 'PaymentGiropay') {
				var AMOUNT = document.getElementById('GP_AMOUNT').value;
				var ACCOUNTID = document.getElementById('GP_ACCOUNTID').value;
				var CURRENCY = document.getElementById('GP_CURRENCY').value;
				var DESCRIPTION = document.getElementById('GP_DESCRIPTION').value;
				var NOTIFY = document.getElementById('GP_NOTIFY').value;
				var CVC = document.getElementById('GP_CVC').value;
				var NAME = document.getElementById('GP_NAME').value;
				var AUTOCLOSE = document.getElementById('GP_AUTOCLOSE').value;
				var ORDERID = document.getElementById('GP_ORDERID').value;
				var TESTMODE = document.getElementById('GP_TESTMODE').value;
				if(TESTMODE == 1) {
					window.open("https://www.saferpay.com/hosting/Redirect.asp?PROVIDERSET=631&LANGID=DE&MENUCOLOR=CCCCCC&HEADCOLOR=F6D95E&PROFILE=ShopBuilder&AMOUNT=" +AMOUNT
						+"&ACCOUNTID=99867-94913159&CURRENCY="+CURRENCY+"&ORDERID="+ORDERID
						+"&DESCRIPTION="+DESCRIPTION+"&CCCVC=yes&CCNAME=yes&AUTOCLOSE=0", "giropay", "toolbar,status");
				} else {
					var url = "https://www.saferpay.com/hosting/CreatePayInit.asp?PROVIDERSET=631&LANGID=DE&MENUCOLOR=CCCCCC&HEADCOLOR=F6D95E"  
						+ "&AMOUNT=" + AMOUNT 
						+ "&ACCOUNTID=" + ACCOUNTID 
						+ "&CURRENCY=" + CURRENCY 
						+ "&ORDERID=" + ORDERID 
						+ "&DESCRIPTION=" + DESCRIPTION 
						+ "&NOTIFYADDRESS=" + NOTIFY 
						+ "&CCCVC=" + CVC 
						+ "&CCNAME=" + NAME 
						+ "&AUTOCLOSE=" + AUTOCLOSE;
						window.open("inc/gotosafer.php?url="+escape(url), "giropay", "toolbar,status");
				}
			} else {
				kill_coupon();
				//Wieso dieses timeout?
				//setTimeout('sendOrderForm()',timeout);
				sendOrder();
			}
		}
	}
	return;
}

function kill_coupon() {
	var cmd;
	var xhr;
	cmd = g_host + 'kill_coupon.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	
	/*A TS 28.08.2014: Ggf. Cookies löschen*/
	if(document.getElementById('rememberme')) {
		if(!document.getElementById('rememberme').checked) {
			delete_cookies();
		}
	}
	return;
}

function sendOrderForm()
{
	/*A TS 01.09.2015: Vars for additional Script*/
	var useInterfaceScript = false;
	var useGSBM = false;
	var interfaceScriptURL;
	var useJSONResult;
	var jsonObject;
	var overwriteOID;
	var oidKeyName;
	var oidValue;
	var overwriteCID;
	var cidKeyName;
	var cidValue;
	var postData;
	var elemCount = 0;
	var oldFormAction;
	var aJSON;
	var lDebug = false;
	
	useGSBM = ((get_setting('cbUseGSBM_Checked') == 'True') ? true : false);
	useInterfaceScript = ((get_setting('cbUseInterfaceScript_Checked') == 'True') ? true : false);
	
	//Post-Daten für Odoo  oder Schnittstellenskript sammeln
	if(useGSBM || useInterfaceScript) {
		elemCount = document.orderform.elements.length;
		for(var i = 0; i < elemCount - 1; i++) {
			if(i == 0) {
				postData = document.orderform.elements[i].name + "=" + document.orderform.elements[i].value;
			} else {
				postData += "&" + document.orderform.elements[i].name + "=" + document.orderform.elements[i].value;
			}
		}
	}
	
	if(useGSBM) {
		//Post-Daten an Odoo-Schnittstelle senden
		sendtoodoo(postData, useInterfaceScript);
	} else if(useInterfaceScript == 'True') {
		//Post-Daten NUR an Schnittstellenskript senden
		sendtoifc(postData);
	} else {
		//Post-Daten weder an Odoo, noch an Schnittstellenskript senden
		if(!lDebug) {
			document.orderform.submit();
		}
	}
}

function sendOrder(){
	var xhr;
	var cmd;
	var aJSON;
	
	if(document.getElementById('sepamandat')){
		params = 'sepamandat=' + document.getElementById('SepaEinverstandenCheck').value;
	} else {
		params = '';
	}
	cmd = g_host + 'processorder.inc.php';
	xhr = gen_req();
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.onload = function(e){
		if(xhr.readyState === 4){
			if(xhr.status === 200){
				aJSON = JSON.parse(xhr.responseText);
				window.location.replace(aJSON);
				if(aJSON['errno'] == 0){
					
				} else {
					if(typeof aJSON['error'] !== "undefined"){
						alert(aJSON['error']);
					}
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(params);
	//window.open(cmd+'?'+params);
}
function sendtoodoo(postData, useInterfaceScript) {
	var gsbmInterfaceURL;
	var lDebug = false;
	var xhr;
	var oidValue;
	var cidValue;
	var aJSON;
	
	gsbmInterfaceURL = g_host + 'send_gsbm_order.php';
	if(!lDebug) {
		//Erzeuge Request
		xhr = gen_req();
		xhr.open("POST", gsbmInterfaceURL, true);
		//Send the proper header information along with the request
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
		xhr.onload = function (e) {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					//Request ist durch gelaufen
					aJSON = JSON.parse(xhr.responseText);
					//Interpretiere Ergebnis
					if(aJSON['errno'] == 0) {
						oidValue = aJSON['result']['oid'];
						document.getElementById('_LANGTAGFNFIELDPID_').value = oidValue;
						cidValue = aJSON['result']['cid'];
						document.getElementById('_LANGTAGFNFIELDCUSTOMERNR_').value = cidValue;
					} else {
						alert(aJSON['error']);
					}
					if(useInterfaceScript) {
						//Zusätzliches Schnittstellenskript soll auch aufgerufen werden
						sendtoifc(postData);
					} else {
						//Zusätliches Schnittstellenskript soll nicht aufgerufen werden, dann submit
						document.orderform.submit();
						//Busy-Screen abschalten
						if(document.getElementById('sb_busy_screen')) {
							document.getElementById('sb_busy_screen').className = 'gs-no-display';
							document.getElementById('sb_busy_screen_inner').className = 'gs-no-display';
						}
					}
				} else {
					console.error(xhr.statusText);
				}
			}
		};
		xhr.onerror = function (e) {
			console.error(xhr.statusText);
		};
		xhr.send(postData);
	} else {
		//window.open(gsbmInterfaceURL + '?' + postData);
		document.orderform.action = gsbmInterfaceURL;
		document.orderform.target = "_blank";
		document.orderform.submit();
		if(useInterfaceScript) {
			sendtoifc(postData);
		}
	}
	return;
}

function sendtoifc(postData) {
	var interfaceScriptURL;
	var useJSONResult;
	var jsonObject;
	var overwriteOID;
	var oidKeyName;
	var overwriteCID;
	var cidKeyName;
	var lDebug = false;
	var xhr;
	var aJSON;
	
	interfaceScriptURL = get_setting('edURLofInterfaceScript_Text');
	useJSONResult = get_setting('cbUseJSONResultForCIDOID_Checked');
	jsonObject = get_setting('edJSONObjectName_Text');
	
	if(interfaceScriptURL != '') {
		if(useJSONResult == 'True' && jsonObject != '') {
			overwriteOID = get_setting('cbReturnOrderID_Checked');
			oidKeyName = get_setting('edOrderIDKeyName_Text');
			overwriteCID = get_setting('cbReturnCustomerID_Checked');
			cidKeyName = get_setting('edCustomerIDKeyName_Text');
			postData += "&GS_useJSONResult=1";
			postData += "&GS_jsonObject=" + jsonObject;
			
			if(overwriteOID == 'True' && oidKeyName != '') {
				postData += "&GS_overwriteOID=1";
				postData += "&GS_oidKeyName=" + oidKeyName;
			} else {
				postData += "&GS_overwriteOID=0";
				postData += "&GS_oidKeyName=0";
			}
			
			if(overwriteCID == 'True' && cidKeyName != '') {
				postData += "&GS_overwriteCID=1";
				postData += "&GS_cidKeyName=" + cidKeyName;
			} else {
				postData += "&GS_overwriteOID=0";
				postData += "&GS_cidKeyName=0";
			}
		
		} else {
			postData += "&GS_useJSONResult=0";
			postData += "&GS_jsonObject=0";
		}
		
		if(!lDebug) {
			//Erzeuge Request
			xhr = gen_req();
			xhr.open("POST", interfaceScriptURL, true);
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
			xhr.onload = function (e) {
				if (xhr.readyState === 4) {
					if (xhr.status === 200) {
						//Request ist durch gelaufen
						if(useJSONResult == 'True' && jsonObject != '') {
							if(xhr.responseText != '') {
								aJSON = JSON.parse(xhr.responseText);
								if(aJSON['errno'] == 0) {
									if(overwriteOID == 'True' && oidKeyName != '') {
										if(aJSON[jsonObject][oidKeyName]) {
											if(aJSON[jsonObject][oidKeyName] != '') {
												oidValue = aJSON[jsonObject][oidKeyName];
												document.getElementById('_LANGTAGFNFIELDPID_').value = oidValue;
											}
										}
									}
									if(overwriteCID == 'True' && cidKeyName != '') {
										if(aJSON[jsonObject][cidKeyName]) {
											if(aJSON[jsonObject][cidKeyName] != '') {
												cidValue = aJSON[jsonObject][cidKeyName];
												document.getElementById('_LANGTAGFNFIELDCUSTOMERNR_').value = cidValue;
											}
										}
									}
								} else {
									alert(aJSON['error']);
								}
							}
						}
						document.orderform.submit();
						//Busy-Screen abschalten
						if(document.getElementById('sb_busy_screen')) {
							document.getElementById('sb_busy_screen').className = 'gs-no-display';
							document.getElementById('sb_busy_screen_inner').className = 'gs-no-display';
						}
					} else {
						console.error(xhr.statusText);
					}
				}
			};
			xhr.onerror = function (e) {
				console.error(xhr.statusText);
			};
			xhr.send(postData);
		} else {
			//window.open(interfaceScriptURL + '?' + postData);
			document.orderform.action = interfaceScriptURL;
			document.orderform.target = "_blank";
			document.orderform.submit();
		}
		
	}
	return;
}

function chk_coupon()
{
	var res;
	var xhr;
	var code = document.getElementById('couponcode').value;
	if(code != '')
	{
		cmd = g_host + 'chk_coupon.inc.php?code=' + code;
		xhr = gen_req();
		xhr.open("GET", cmd, true);
		xhr.onload = function (e) {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					res = xhr.responseText;
					if(res == 0) {
						self.location.reload();
					} else {
						document.getElementById('coupon_err').className = 'err_box';
						document.getElementById('coupon_err').innerHTML = get_lngtext('LangTagWrongCouponCode');
					}
				} else {
					console.error(xhr.statusText);
				}
			}
		};
		xhr.onerror = function (e) {
			console.error(xhr.statusText);
		};
		xhr.send(null);
	}
	return;
}

function chg_lang(slc,cnt)
{
	var cmd;
	var res;
	var xhr;
	cmd = g_host + 'chg_shoplang.inc.php?slc=' + slc + '&cnt=' + cnt;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				//Wieso dieses Timeout?
				//setTimeout('self.location.replace("index.php?page=main")',1000);
				self.location.replace('index.php?page=main');
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function chk_minordervalue(ordervalue, minordervalue)
{
	if(minordervalue == '')
	{
		minordervalue = 0;
	}
	
	if(ordervalue < minordervalue)
	{
		msg = get_lngtext('LangTagTextMinOrderValue1js') +' '+ ordervalue +' '+ get_lngtext('LangTagTextMinOrderValue2js') +' '+ minordervalue +' '+ get_lngtext('LangTagTextMinOrderValue3');
		/*Sie haben Artikel fuer "ordervalue" in Ihrem Warenkorb.\nDer Mindestbestellwert von "minordervalue" ist noch nicht erreicht.*/
		alert(unescape(msg)); 
		return false;
	}
	else
	{
		self.location.replace('index.php?page=buy');
		return;
	}
}

function mark_comparison(obj)
{
	var itemId = obj.value;
	var add = 0;
	var xhr;
	var cmd;
	
	if(obj.checked) {
		add = 1;
	}
	cmd = g_host + 'add_remove_comparison.inc.php?itemid=' + itemId + '&add=' + add;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				//res = xhr.responseText;
				self.location.reload();
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function start_comparison()
{
	var cmd;
	var res;
	var xhr;
	cmd = g_host + 'get_comparison_count.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				if(res > 0) {
					self.location.href = 'index.php?page=compare_items';
				} else {
					alert(get_lngtext('LangTagTitleNoArticleForComparison'));
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function load_pgitems(idx,orderby,orderdir)
{
	var cmd;
	var res;
	var xhr;
	var field = '';
	g_aItems = new Array();
	g_subcats = '';
	if(idx > 0) {
		cmd = g_host + 'get_pg_items.inc.php?idx=' + idx + '&amp;orderby=' + orderby + '&amp;orderdir=' + orderdir;
	} else {
		if(idx == -1) {
			field = 'itemIsOnIndexPage';
		}
		if(idx == -2) {
			field = 'itemIsNewItem';
		}
		cmd = g_host + 'get_spec_items.inc.php?field='+field+'&val=Y';
	}
	/*window.open(cmd);*/
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				var subgr = '<!--GROUPS-->';
				var suberg = res.substring(0,13);
				if(subgr != suberg) {
					g_msg_class = 'note-msg';
					g_msg_text = get_lngtext('LangTagNoItemsInGroup');
					g_aItems = JSON.parse(res);
					g_numitems = g_aItems.length;
				} else {
					g_subcats = res;
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;

}

function load_pgitemsnew(idx,pgview)
{
	var cmd;
	var res;
	var subgr;
	var field = '';
	g_aItems = new Array();
	g_subcats = '';
	if(idx > 0) {
		cmd = g_host + 'get_pg_itemids.inc.php?idx=' + idx;
	} else {
		if(idx == -1) {
			field = 'itemIsOnIndexPage';
		}
		if(idx == -2) {
			field = 'itemIsNewItem';
		}
		cmd = g_host + 'get_spec_itemids.inc.php?field='+field+'&val=Y';
	}
	//window.open(cmd);
	
	if(pgview != '') {
		//Es wurde eine Ansicht im Template hinterlegt
		if(pgview == '0') {
			//0 heißt Grid
			g_view = 'grid';
		} else if(pgview == '1') {
			//1 heißt List
			g_view = 'list';
		} else {
			//Nichts passendes, durchsuche Standard-Werte
			if(g_defview == '0') {
				//0 heißt Grid
				g_view = 'grid';
			} else if(g_defview == '1') {
				//1 heißt List
				g_view = 'list';
			} else {
				//Ansonsten Grid nehmen
				g_view = 'grid';
			}
		}
	} else {
		//Im Template wurde keine Ansicht hinterlegt
		if(g_defview != '') {
			//Es wurde eine globale Ansicht eingetragen
			if(g_defview == '0') {
				//0 heißt Grid
				g_view = 'grid';
			} else if(g_defview == '1') {
				//1 heißt List
				g_view = 'list';
			} else {
				//Ansonsten Grid nehmen
				g_view = 'grid';
			}
		} else {
			g_view = 'grid';
		}
	}

	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				subgr = '<!--GROUPS-->';
				suberg = res.substring(0,13);
				if(subgr != suberg) {
					g_msg_class = 'note-msg';
					g_msg_text = get_lngtext('LangTagNoItemsInGroup');
					g_aItemIds = JSON.parse(res);
					g_numitems = g_aItemIds.length;
					show_pgroupnew(0);
				} else {
					g_subcats = res;
					g_view = 'grid';
					show_pgroupnew(0);
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	
	return;

}

function search_itemsnew(spattern)
{
	var cmd;
	var res;
	var xhr;
	//Im Template wurde keine Ansicht hinterlegt
	//List durchsetzen
	if(g_defview != '') {
		//Es wurde eine globale Ansicht eingetragen
		if(g_defview == '0') {
			//0 heißt Grid
			g_view = 'grid';
		} else if(g_defview == '1') {
			//1 heißt List
			g_view = 'list';
		} else {
			//Ansonsten Liste nehmen
			g_view = 'list';
		}
	} else {
		g_view = 'list';
	}
	g_aItems = new Array();
	g_msg_class = 'note-msg';
	g_msg_text = get_lngtext('LangTagNoSearchResults') + ': &quot;' + spattern + '&quot;';
	cmd = g_host + 'do_shopsearchnew.inc.php?search=' + spattern;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				g_aItemIds = JSON.parse(res);
				g_numitems = g_aItemIds.length;
				show_pgroupnew(0);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function show_pgroup(iStart)
{
	var line;
	var html = '';
	var i;
	var outer;
	var box;
	var cur_lblsale = '';
	var cur_lblnew;
	var cur_item;
	var all_items = '';
	var aImgs = new Array();
	var aPrices = new Array();
	var erg;
	var priceval = '';
	var oldprice = '';
	var oldpriceval = '';
	var priceclass = '';
	var saleperiod = '';
	var itemcompare;
	var iaction = 'N';
	var imax;
	var addtocart = '';
	var itemimg;
	var itemdetail;
	var numpages = 0;
	var pager = '';
	var startitem = 0;
	var enditem = 0;
	var itemwishlist;
	var rating = '';
	var cur_lblbests = '';
	var imgsrc = '';
	var img2src = '';
	
	var date = new Date();
	var year = date.getFullYear();
	var month = '0' + (date.getMonth() + 1);
	month = month.slice(-2, (month.length - 2) + 3);
	var day = '0' + date.getDate();
	day = day.slice(-2, (day.length - 2) + 3);
	var cDate = year + month + day;
	
	var trialperiod = '&nbsp;';
	var aftertrial = '&nbsp;';
	var billingperiod = '&nbsp;';
	var aftertrialprice = '&nbsp;';
	var aftertrialperiod = '&nbsp;';
	var runtime = '&nbsp;';
	var runtimelng = '&nbsp;';
	var lPlural = false;
	var actbutwidth = 44;
	var actbutwidthall = actbutwidth;
	var f_oldpriceclass;
	
	if(isMobile || isPhone)
	{
		if(document.getElementById('gs_view-mode')) {
			document.getElementById('gs_view-mode').className = 'no-display';
		}
		g_view = 'list';
	}
	else
	{
		if(document.getElementById('gs_view-mode')) {
			document.getElementById('gs_view-mode').className = 'view-mode';
		}
	}
	
	//Zur Sicherheit
	if(g_count == 0) {
		g_count = g_numitems;
	}
	
	g_start = iStart;
	startitem = g_start * g_count;
	enditem = startitem + g_count;
	
	if(document.getElementById('gs_pager_top')) {
		if(g_numitems > g_count)
		{
			numpages = Math.ceil(g_numitems / g_count);
			pager = build_pager(numpages);
			document.getElementById('gs_pager_top').innerHTML = pager;
			setViewCount();
			if(document.getElementById('gs_pager_bottom')){
				document.getElementById('gs_pager_bottom').innerHTML = pager;
			}
		}
		else
		{
			if(g_dontShowToolbar == 'True') {
				document.getElementById('gs_pager_top').innerHTML = '';
				if(document.getElementById('gs_pager_bottom')){
					document.getElementById('gs_pager_bottom').innerHTML = '';
				}
			} else {
				numpages = Math.ceil(g_numitems / g_count);
				if(numpages > 1) {
					pager = build_pager(numpages);
					document.getElementById('gs_pager_top').innerHTML = pager;
					setViewCount();
					if(document.getElementById('gs_pager_bottom')){
						document.getElementById('gs_pager_bottom').innerHTML = pager;
					}
				}
			}
		}
	}
	
	if(g_view == 'list')
	{
		outer = g_outer_list;
		box = g_outer_listbox;
		f_oldpriceclass = 'old-price-l';
		/*if(isMobile || isPhone) {
			f_oldpriceclass = 'old-price';
		} else {
			f_oldpriceclass = 'old-price-l';
		}*/
	}
	else
	{
		if( typeof g_outer_grid == 'undefined'){
			var g_outer_grid='<ul class="products-grid">{GSSE_INCL_ITEMSBOXEDLINES}</ul><script type="text/javascript">decorateGeneric($$("ul.products-grid"), ["odd","even","first","last"])</script>';
		}
		if(typeof g_outer_gridbox == 'undefined'){
			var g_outer_gridbox='<li class="item item-products"><div class="product-item"><ul class="productlabels_icons">{GSSE_INCL_LABELNEW}{GSSE_INCL_LABELSALE}{GSSE_INCL_LABELBEST}</ul>{GSSE_INCL_ITEMIMG}<div class="product-shop"><h2 class="product-name">{GSSE_INCL_ITEMTITLE}</h2><p class="sku">{GSSE_INCL_ITEMNUMBER}</p><div class="gs-rating-box">{GSSE_INCL_RATINGIMG}</div><div class="price-box t-center">{GSSE_INCL_SALEPERIOD}{GSSE_INCL_OLDPRICENEW}<div class="clear"></div><span class="{GSSE_INCL_PRICECLASS}"><span class="price">{GSSE_INCL_ITEMPRICE}</span></span><p class="pinfo">{GSSE_INCL_ITEMPRICEINFO}</p></div></div><div class="gs-act-buttons" style="width: {GSSE_INCL_ACTBUTSWIDTH}px;"><ul class="gs-action-buttons-list">{GSSE_INCL_ADDTOCARTSMALL}{GSSE_INCL_WISHLIST}{GSSE_INCL_NOTEPAD}{GSSE_INCL_COMPAREITEM}</ul></div></div></li>';
		}
		outer = g_outer_grid;
		box = g_outer_gridbox;
		f_oldpriceclass = 'old-price';
	}
		
	if(g_aItems.length < g_count)
	{
		imax = g_aItems.length;
	}
	else
	{
		if(enditem < g_aItems.length)
		{
			imax = enditem;
		}
		else
		{
			imax = g_aItems.length;
		}
	}
	
	if(g_aItems.length > 0)
	{
		for(i = startitem; i < imax; i++)
		{
			cur_lblsale = '';
			priceval = '';
			oldprice = '';
			oldpriceval = '';
			priceclass = '';
			saleperiod = '';
			addtocart = '';
			g_exalblpricepermonth='';
			cur_item = box;
			aPrices = g_aItems[i]['aprices'];
			actbutwidthall = actbutwidth;
			/*
			g_itemimg_qs;
			g_itemimg;
			g_itemnamedetail;
			g_itemname;
			*/
			if(g_aItems[i]['itemHasDetail'] == 'Y')
			{
				detailurl = g_url + 'index.php?page=detail&amp;item=' + g_aItems[i]['itemItemId'] + '&amp;d=' + g_aItems[i]['itemItemPage'];
				/*A TS 09.12.2014: Benutze Permalink, wenn verfügbar*/
				if(g_sbedition == 13) {
					if(g_permalink == 'True') {
						if(g_aItems[i]['itemItemPage'] != '') {
							detailurl = g_aItems[i]['itemItemPage'];
						}
					}
				}
				
				itemimg = g_itemimg_qs;
				itemimg = itemimg.replace(/{GSSE_INCL_ITEMURL}/g, detailurl);
				itemdetail = g_itemnamedetail;
				itemdetail = itemdetail.replace(/{GSSE_INCL_ITEMURL}/g, detailurl);
				
			}
			else
			{
				itemimg = g_itemimg;
				itemdetail = g_itemname;
			}
			itemdetail = itemdetail.replace(/{GSSE_INCL_ITEMNAMEONLY}/g, g_aItems[i]['itemItemDescription']);
			itemimg = itemimg.replace(/{GSSE_INCL_ITEMNAMEONLY}/g, g_aItems[i]['itemItemDescription']);
			/*Bilder*/
			aImgs = g_aItems[i]['aimgs'];
			/*Bild online oder lokal?*/
			if(aImgs.length > 0) {
				if(aImgs[0]['ImageName'].indexOf('http') == -1 && aImgs[0]['ImageName'].indexOf('://') == -1) {
					if(aImgs[0]['MediumExists'] == 1) {
						imgsrc = 'images/medium/' + aImgs[0]['ImageName'];
					} else if(aImgs[0]['SmallExists'] == 1) {
						imgsrc = 'images/small/' + aImgs[0]['ImageName'];
					} else if(aImgs[0]['BigExists'] == 1) {
						imgsrc = 'images/big/' + aImgs[0]['ImageName'];
					} else {
						imgsrc = 'template/images/no_pic_mid.png';
					}
				} else {
					imgsrc = aImgs[0]['ImageName'];
				}
				if(aImgs.length > 1) {
					if(aImgs[1]['ImageName'].indexOf('http') == -1 && aImgs[1]['ImageName'].indexOf('://') == -1) {
						if(aImgs[0]['MediumExists'] == 1) {
							img2src = 'images/medium/' + aImgs[0]['ImageName'];
						} else if(aImgs[0]['SmallExists'] == 1) {
							img2src = 'images/small/' + aImgs[0]['ImageName'];
						} else if(aImgs[0]['BigExists'] == 1) {
							img2src = 'images/big/' + aImgs[0]['ImageName'];
						} else {
							img2src = '';
						}
					} else {
						img2src = aImgs[1]['ImageName'];
					}
				}
			} else {
				imgsrc = 'template/images/no_pic_mid.png';
			}
			
			
			itemimg = itemimg.replace(/{GSSE_INCL_ITEMIMG}/g, imgsrc);
			if(aImgs.length > 1 && img2src != '')
			{
				itemimg = itemimg.replace(/{GSSE_INCL_ITEMIMG2}/g, img2src);
			}
			else
			{
				itemimg = itemimg.replace(/{GSSE_INCL_ITEMIMG2}/g, imgsrc);
			}
			
			/*Aktionen*/
			iaction = 'N';
			if(g_aItems[i]['itemIsAction'] == 'Y')
			{
				iaction = g_aItems[i]['hasaction'];
			}
		
			/*Neuheit*/
			if(g_aItems[i]['itemIsNewItem'] == 'Y')
			{
				cur_lblnew = g_lbl_new;
				cur_lblnew = cur_lblnew.replace(/{GSSE_LANG_LangTagLabelNew}/g,g_lblnew);
			}
			else
			{
				cur_lblnew = '';
			}
		
			/*Aktionen*/
			if(g_aItems[i]['itemIsTextHasNoPrice'] == 'N')
			{
				if(iaction == 'Y')
				{
					cur_lblsale = g_lbl_sale;
					cur_lblsale = cur_lblsale.replace(/{GSSE_LANG_LangTagLabelSale}/g,g_lblsale);
					if(aPrices['actshowperiod'] == 'Y')
					{
						saleperiod = aPrices['actbegindate'] + " - " + aPrices['actenddate'];
					}
				}
				else
				{
					cur_lblsale = '';
					saleperiod = '';
				}
			}
			
			/*Bestseller*/
			cur_lblbests = '';
			if(g_usephp == 'True')
			{
				if(g_bestseller == 'True')
				{
					if(g_aItems[i]['bestseller'])
					{
						cur_lblbests = g_lbl_best;
					}
				}
			}
			cur_item = cur_item.replace(/{GSSE_INCL_LABELBEST}/g, cur_lblbests);
			
			/*cur_item = cur_item.replace(//g, g_aItems[i][]);*/
			cur_item = cur_item.replace(/{GSSE_INCL_LABELNEW}/g, cur_lblnew);
			cur_item = cur_item.replace(/{GSSE_INCL_LABELSALE}/g, cur_lblsale);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMNAME}/g, g_aItems[i]['itemItemDescription']);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMNUMBER}/g, g_aItems[i]['itemItemNumber']);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMIMG}/g, itemimg);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMTITLE}/g, itemdetail);
			
			/*Preise*/
			//if(g_aItems[i]['itemIsTextHasNoPrice'] == 'N')
			//{
				priceval = get_currency(aPrices['price'],0);
				oldpriceval = get_currency(aPrices['oldprice'],0);
				oldprice = g_oldprice;
				oldpriceclass = 'no-old-price';
				priceclass = 'price';
				if(aPrices['oldprice'] > 0 && iaction == 'N') {
					priceclass = 'special-price';
					oldprice = oldprice.replace(/{GSSE_INCL_ITEMOLDPRICENEW}/g,oldpriceval);
					oldpriceclass = f_oldpriceclass;
				} else {
					oldprice = oldprice.replace(/{GSSE_INCL_ITEMOLDPRICENEW}/g,'&nbsp;');
				}
			
				if(iaction == 'Y')
				{
					priceval = get_currency(aPrices['actprice'].replace(/,/g,'.'),0);
				}
				
				if(iaction == 'Y' && aPrices['actshownormal'] == 'Y')
				{
					if(aPrices['actnormprice'] != '' && aPrices['actnormprice'] != 0)
					{
						var actnormprice = aPrices['actnormprice'].replace(/,/g,'.');
						oldpriceval = get_currency(actnormprice,0);
					}
					else
					{
						oldpriceval = get_currency(aPrices['oldprice'],0);
					}
					priceclass = 'special-price';
					oldprice = g_oldprice;
					oldprice = oldprice.replace(/{GSSE_INCL_ITEMOLDPRICENEW}/g,oldpriceval);
					oldpriceclass = f_oldpriceclass;
				}
			
				if(iaction == 'N')
				{
					if(aPrices['abulk'])
					{
						if(aPrices['abulk'].length > 0)
						{
							priceval = get_lngtext('LangTagFromNew') + ' ' + get_currency(aPrices['abulk'][0][1],0);
						}
					}
				}
				
				/*Mietpreise*/
				runtimelng = '';
				runtime = '';
				aftertrialprice = '';
				aftertrialperiod = '';
				trialperiod = '';
				aftertrial = '';
				billingperiod = '';
				if(aPrices['isrental']) {
					if(aPrices['isrental'] == 'Y') {
						if(aPrices['istrial'] == 'Y') {
							if(aPrices['trialfrequency'] > 1) {
								lPlural = true;
							} else {
								lPlural = false;
							}
							
							aftertrialprice = priceval;
							if(aPrices['trialprice'] > 0) {
								trialperiod = aPrices['trialfrequency'] + " " + get_billingperiodfromid(aPrices['trialperiod'],false,lPlural,false) + " " + get_lngtext('LangTagForSomething');
								priceval = get_currency(aPrices['trialprice'].replace(/,/g,'.'),0);
								billingperiod = get_billingperiodfromid(aPrices['trialperiod'],true,false,true);
							} else {
								trialperiod = aPrices['trialfrequency'] + " " + get_billingperiodfromid(aPrices['trialperiod'],false,lPlural,false);
								priceval = get_lngtext('LangTagForFree');
								billingperiod = '&nbsp;';
							}
							
							aftertrial = get_lngtext('LangTagAfterSomething');
							aftertrialperiod = get_billingperiodfromid(aPrices['billingperiod'],true,false,true);
						} else {
							billingperiod = get_billingperiodfromid(aPrices['billingperiod'],true,false,true);
						}
						if(aPrices['rentalruntime'] > 0) {
							runtimelng = get_lngtext('LangTagRentalRunTime') + ": ";
							if(aPrices['rentalruntime'] > 1) {
								lPlural = true;
							} else {
								lPlural = false;
							}
							runtime = aPrices['rentalruntime'] + " " + get_billingperiodfromid(aPrices['billingperiod'],false,lPlural,false);
						}
					}
				}
			//}
			/*Mietpreise*/
			cur_item = cur_item.replace(/{GSSE_INCL_RUNTIMELNG}/g,runtimelng);
			cur_item = cur_item.replace(/{GSSE_INCL_RUNTIME}/g,runtime);
			cur_item = cur_item.replace(/{GSSE_INCL_AFTERTRIALPRICE}/g,aftertrialprice);
			cur_item = cur_item.replace(/{GSSE_INCL_AFTERTRIALPERIOD}/g,aftertrialperiod);
			cur_item = cur_item.replace(/{GSSE_INCL_TRIALPERIOD}/g,trialperiod);
			cur_item = cur_item.replace(/{GSSE_INCL_AFTERTRIAL}/g,aftertrial);
			cur_item = cur_item.replace(/{GSSE_INCL_BIILINGPERIOD}/g,billingperiod);
			/*Preise*/
			cur_item = cur_item.replace(/{GSSE_INCL_OLDPRICENEW}/g,oldprice);
			cur_item = cur_item.replace(/{GSSE_INCL_OLDPRICECLASS}/g,oldpriceclass);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMPRICEINFO}/g,get_setting('edPriceInformation_Text'));
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMPRICE}/g,priceval);
			cur_item = cur_item.replace(/{GSSE_INCL_PRICECLASS}/g,priceclass);
			/*Aktionszeitraum*/
			cur_item = cur_item.replace(/{GSSE_INCL_SALEPERIOD}/g,saleperiod);
			
			/*Begin Exalyser specific*/
			cur_item = cur_item.replace(/{GSSE_LANG_LangTagExaPricePerMonth}/g,g_exalblpricepermonth);
			/*End Exalyser specific*/
			
			/*Ratings*/
			rating = '';
			if(g_usephp == 'True')
			{
				if(g_userating == 'True')
				{
					if(g_login != null)
					{
						if(g_login['ok'] == 1)
						{
							rating = g_image;
							rating = rating.replace(/{GSSE_INCL_IMGCLASS}/g,'');
							rating = rating.replace(/{GSSE_INCL_IMGSRC}/g,'template/images/rating' + g_aItems[i]['rating_avg'] + '0.gif');
							rating = rating.replace(/{GSSE_INCL_IMGALT}/g,'Rating');
							rating = rating.replace(/{GSSE_INCL_IMGTITLE}/g,'Rating');
						}
					}
				}
			}
			cur_item = cur_item.replace(/{GSSE_INCL_RATINGIMG}/g,rating);
			
			
			/*Compare*/
			itemcompare = '';
			if(!isMobile) {
				if(g_compare == 'True')
				{
					actbutwidthall += actbutwidth;
					itemcompare = g_itemcompare;
					itemcompare = itemcompare.replace(/{GSSE_LANG_LangTagArticleCompare}/g,'');
					itemcompare = itemcompare.replace(/{GSSE_INCL_ICID}/g,g_aItems[i]['itemItemId']);
					itemcompare = itemcompare.replace(/{GSSE_INCL_ICNAME}/g,encodeURIComponent(g_aItems[i]['itemItemDescription']));
					itemcompare = itemcompare.replace(/{GSSE_INCL_ICPAGE}/g,encodeURIComponent(g_aItems[i]['itemItemPage']));
				}
			}
			cur_item = cur_item.replace(/{GSSE_INCL_COMPAREITEM}/g,itemcompare);
			
			/*Wishlist & Notepad*/
			itemwishlist = '';
			itemnotepad = '';
			if(g_usephp == 'True')
			{
				if(g_wishlist == 'True')
				{
					if(g_login != null)
					{
						if(g_login['ok'] == 1)
						{
							/*Wishlist*/
							if(g_aItems[i]['wl_act'] && g_aItems[i]['wl_cid'] && g_aItems[i]['wl_wlid'])
							{
								if(g_aItems[i]['wl_cid'] == g_login['cusIdNo'])
								{
									itemwishlist = g_rm_itemwishlist;
									itemwishlist = itemwishlist.replace(/{GSSE_INCL_WLID}/g,g_aItems[i]['wl_wlid']);
									itemwishlist = itemwishlist.replace(/{GSSE_LANG_LangTagDeleteFromWishlist}/g,g_rm_lblwishlist);
								}
								else
								{
									itemwishlist = g_itemwishlist;
									itemwishlist = itemwishlist.replace(/{GSSE_INCL_ITEMNO}/g,g_aItems[i]['itemItemNumber']);
									itemwishlist = itemwishlist.replace(/{GSSE_INCL_CUSID}/g,g_login['cusIdNo']);
									itemwishlist = itemwishlist.replace(/{GSSE_INCL_DATE}/g,cDate);
									itemwishlist = itemwishlist.replace(/{GSSE_LANG_LangTagMoveToWishList}/g,g_lblwishlist);
								}
							}
							else
							{
								itemwishlist = g_itemwishlist;
								itemwishlist = itemwishlist.replace(/{GSSE_INCL_ITEMNO}/g,g_aItems[i]['itemItemNumber']);
								itemwishlist = itemwishlist.replace(/{GSSE_INCL_CUSID}/g,g_login['cusIdNo']);
								itemwishlist = itemwishlist.replace(/{GSSE_INCL_DATE}/g,cDate);
								itemwishlist = itemwishlist.replace(/{GSSE_LANG_LangTagMoveToWishList}/g,g_lblwishlist);
							}
							actbutwidthall += actbutwidth;
							
							/*Notepad*/
							if(g_aItems[i]['np_act'] && g_aItems[i]['np_npid'])
							{
								itemnotepad = g_rm_itemnotepad;
								itemnotepad = itemnotepad.replace(/{GSSE_INCL_NPID}/g,g_aItems[i]['np_npid']);
								itemnotepad = itemnotepad.replace(/{GSSE_LANG_LangTagDeleteFromNotepad}/g,g_rm_lblnotepad);
								if(g_view != 'list')
								{
									/*in der Grid-Ansicht ist kein Platz für einen 4. Button,*/
									/*Wunschzettel-Button wird "mißbraucht"*/
									itemwishlist = itemnotepad;
								}
							}
							else
							{
								itemnotepad = g_itemnotepad;
								itemnotepad = itemnotepad.replace(/{GSSE_INCL_ITEMNO}/g,g_aItems[i]['itemItemNumber']);
								itemnotepad = itemnotepad.replace(/{GSSE_INCL_CUSID}/g,g_login['cusIdNo']);
								itemnotepad = itemnotepad.replace(/{GSSE_INCL_DATE}/g,cDate);
								itemnotepad = itemnotepad.replace(/{GSSE_LANG_LangTagNote}/g,g_lblnotepad);
							}
							actbutwidthall += actbutwidth;
						}
					}
				}
			}
			cur_item = cur_item.replace(/{GSSE_INCL_WISHLIST}/g,itemwishlist);
			cur_item = cur_item.replace(/{GSSE_INCL_NOTEPAD}/g,itemnotepad);
			
			cur_item = cur_item.replace(/{GSSE_INCL_ACTBUTSWIDTH}/g,actbutwidthall);
			
			/*Description*/
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMDETAILTEXT1}/g,g_aItems[i]['itemItemText']);
			
			/*Availibility*/
			//TS 11.10.2016: Nur Text anzeigen
			/*var availability = unescape(g_aItems[i]['avail']);*/
			var availability = unescape(g_aItems[i]['availtext']);
			cur_item = cur_item.replace(/{GSSE_INCL_AVAILBOX}/g,availability);
			
			/*Add-to-cart*/
			cur_item = cur_item.replace(/{GSSE_INCL_ATTRIBUTESNEW}/g,'');
			cur_item = cur_item.replace(/{GSSE_INCL_ADDTOCART}/g,'');
			if(g_aItems[i]['itemIsCatalogFlg'] == 'N' && g_aItems[i]['itemIsTextInput'] == 'N' && g_aItems[i]['itemAttribute1'] == '' && g_aItems[i]['itemAttribute2'] == '' && g_aItems[i]['itemAttribute3'] == '' && aPrices['isrental'] != 'Y')
			{
				addtocart = g_addtocartsmall;
				addtocart = addtocart.replace(/{GSSE_LANG_LangTagAddToBasket}/g,get_lngtext('LangTagAddToBasket'));
				addtocart = addtocart.replace(/{GSSE_INCL_ITEMID}/g,g_aItems[i]['itemItemId']);
			}
			else
			{
				if(g_aItems[i]['itemHasDetail'] == 'Y')
				{
					addtocart = g_gotodetail;
					addtocart = addtocart.replace(/{GSSE_LANG_LangTagViewDetails}/g,g_lblgotodetail);
					addtocart = addtocart.replace(/{GSSE_INCL_LINKURL}/g,detailurl);
				}
			}
			cur_item = cur_item.replace(/{GSSE_INCL_ADDTOCARTSMALL}/g,addtocart);
					
			all_items += cur_item;
		}
		if(g_view == 'list')
		{
			html = outer.replace(/{GSSE_INCL_ITEMSOVERVIEWLINES}/g, all_items);
		}
		else
		{
			html = outer.replace(/{GSSE_INCL_ITEMSBOXEDLINES}/g, all_items);
		}
		
		if(document.getElementById('items_count')) {
			document.getElementById('items_count').innerHTML = g_aItems.length;
			if(g_aItems.length>g_count){
				if(jQuery('#toolbar').hasClass('no-display')) {
					jQuery('#toolbar').removeClass('no-display');
				}
				if(jQuery('#pager').hasClass('no-display')) {
					jQuery('#pager').removeClass('no-display');
				}
			} else {
				if(g_dontShowToolbar == 'True') {
					jQuery('#toolbar').addClass('no-display');
					jQuery('#pager').addClass('no-display');
				} else {
					if(jQuery('#toolbar').hasClass('no-display')) {
						jQuery('#toolbar').removeClass('no-display');
					}
					if(jQuery('#pager').hasClass('no-display')) {
						jQuery('#pager').removeClass('no-display');
					}
				}
			}
		}
		
		document.getElementById('items_container').innerHTML = html;
		if(g_view == 'list')
		{
			decorateList('products-list', 'none-recursive');
		}
		else
		{
			decorateGeneric($$('ul.products-grid'), ['odd','even','first','last']);
		}
		responsive();
	}
	else
	{
		html = g_errbox;
		html = html.replace(/{GSSE_MSG_ERRORNEW}/g, g_msg_text);
		html = html.replace(/{GSSE_MSG_ERRORNEWCLASS}/g, g_msg_class);
		document.getElementById('items_count').innerHTML = '0';
		document.getElementById('items_container').innerHTML = g_subcats;
	}
	return;
}

function show_pgroupnew(iStart)
{
	var line;
	var i;
	var outer;
	var box;
	var cmd;
	var aItem;
	var axhr = [];
	var startitem;
	var enditem;
	var numpages;
	
	if(isMobile || isPhone)
	{
		if(document.getElementById('gs_view-mode')) {
			document.getElementById('gs_view-mode').className = 'no-display';
		}
		g_view = 'list';
	}
	else
	{
		if(document.getElementById('gs_view-mode')) {
			document.getElementById('gs_view-mode').className = 'view-mode';
		}
	}
	
	//Zur Sicherheit
	if(g_count == 0) {
		g_count = g_numitems;
	}
	
	g_start = iStart;
	startitem = g_start * g_count;
	enditem = startitem + g_count;
	
	if(document.getElementById('gs_pager_top')) {
		if(g_numitems > g_count)
		{
			numpages = Math.ceil(g_numitems / g_count);
			pager = build_pager(numpages);
			document.getElementById('gs_pager_top').innerHTML = pager;
			setViewCount();
			if(document.getElementById('gs_pager_bottom')){
				document.getElementById('gs_pager_bottom').innerHTML = pager;
			}
		}
		else
		{
			if(g_dontShowToolbar == 'True') {
				document.getElementById('gs_pager_top').innerHTML = '';
				if(document.getElementById('gs_pager_bottom')){
					document.getElementById('gs_pager_bottom').innerHTML = '';
				}
			} else {
				numpages = Math.ceil(g_numitems / g_count);
				if(numpages > 1) {
					pager = build_pager(numpages);
					document.getElementById('gs_pager_top').innerHTML = pager;
					setViewCount();
					if(document.getElementById('gs_pager_bottom')){
						document.getElementById('gs_pager_bottom').innerHTML = pager;
					}
				} else {
					if(document.getElementById('gs-limiter')) {
						document.getElementById('gs-limiter').className = 'no-display';
					} else {
						document.getElementById('gs-view-count').className = 'no-display';
					}
				}
			}
		}
	}
	
	if(g_view == 'list')
	{
		outer = g_outer_list;
		box = g_outer_listbox;
		f_oldpriceclass = 'old-price-l';
		/*if(isMobile || isPhone) {
			f_oldpriceclass = 'old-price';
		} else {
			f_oldpriceclass = 'old-price-l';
		}*/
	}
	else
	{
		if( typeof g_outer_grid == 'undefined'){
			var g_outer_grid='<ul class="products-grid">{GSSE_INCL_ITEMSBOXEDLINES}</ul><script type="text/javascript">decorateGeneric($$("ul.products-grid"), ["odd","even","first","last"])</script>';
		}
		if(typeof g_outer_gridbox == 'undefined'){
			var g_outer_gridbox='<li class="item item-products"><div class="product-item"><ul class="productlabels_icons">{GSSE_INCL_LABELNEW}{GSSE_INCL_LABELSALE}{GSSE_INCL_LABELBEST}</ul>{GSSE_INCL_ITEMIMG}<div class="product-shop"><h2 class="product-name">{GSSE_INCL_ITEMTITLE}</h2><p class="sku">{GSSE_INCL_ITEMNUMBER}</p><div class="gs-rating-box">{GSSE_INCL_RATINGIMG}</div><div class="price-box t-center">{GSSE_INCL_SALEPERIOD}{GSSE_INCL_OLDPRICENEW}<div class="clear"></div><span class="{GSSE_INCL_PRICECLASS}"><span class="price">{GSSE_INCL_ITEMPRICE}</span></span><p class="pinfo">{GSSE_INCL_ITEMPRICEINFO}</p></div></div><div class="gs-act-buttons" style="width: {GSSE_INCL_ACTBUTSWIDTH}px;"><ul class="gs-action-buttons-list">{GSSE_INCL_ADDTOCARTSMALL}{GSSE_INCL_WISHLIST}{GSSE_INCL_NOTEPAD}{GSSE_INCL_COMPAREITEM}</ul></div></div></li>';
		}
		outer = g_outer_grid;
		box = g_outer_gridbox;
		f_oldpriceclass = 'old-price';
	}
		
	if(g_aItemIds.length < g_count)
	{
		imax = g_aItemIds.length;
	}
	else
	{
		if(enditem < g_aItemIds.length)
		{
			imax = enditem;
		}
		else
		{
			imax = g_aItemIds.length;
		}
	}
	
	if(g_aItemIds.length > 0)
	{
		if(g_view == 'list') {
			all_items = '';
			for(i = startitem; i < imax; i++) {
				//placeholder = g_itemplaceholderlist;
				all_items += '<li id="itemplace'+g_aItemIds[i]['ID']+'" class="item"><img src="template/images/loading.gif" /></li>';
				//all_items += placeholder.replace(/{GSSE_INCL_ITEMID}/g, g_aItemIds[i]['ID']);
			}
			g_html = outer.replace(/{GSSE_INCL_ITEMSOVERVIEWLINES}/g, all_items);
		} else {
			all_items = '';
			for(i = startitem; i < imax; i++) {
				//placeholder = g_itemplaceholderbox;
				all_items += '<li id="itemplace'+g_aItemIds[i]['ID']+'" class="item item-products"><img class="productloader" src="template/images/loading.gif" /></li>';
				//all_items += placeholder.replace(/{GSSE_INCL_ITEMID}/g, g_aItemIds[i]['ID']);
			}
			g_html = outer.replace(/{GSSE_INCL_ITEMSBOXEDLINES}/g, all_items);
		}
		
		
		for(o = startitem; o < imax; o++) {
			(function(cntr,last) {
				cmd = g_host + 'get_pg_item.inc.php?idx=' + g_aItemIds[cntr]['ID'];
				axhr[cntr] = gen_req();
				axhr[cntr].open("GET", cmd, true);
				axhr[cntr].onload = function (e) {
					if (axhr[cntr].readyState === 4) {
						if (axhr[cntr].status === 200) {
							res = axhr[cntr].responseText;
							aItem = JSON.parse(res);
							show_item(cntr,outer,box,aItem);
							/*if(cntr == last) {
								if(g_view == 'list') {
									decorateList('products-list', 'none-recursive');
								} else {
									decorateGeneric($$('ul.products-grid'), ['odd','even','first','last']);
								}
								responsive();
							}*/
						} else {
							console.error(axhr[cntr].statusText);
						}
					}
				};
				axhr[cntr].onerror = function (e) {
					console.error(axhr[cntr].statusText);
				};
				axhr[cntr].send(null);
			})(o,imax -1);
		}
		document.getElementById('items_container').innerHTML = g_html;
		
		if(document.getElementById('items_count')) {
			document.getElementById('items_count').innerHTML = g_aItemIds.length;
			if(g_aItemIds.length>g_count){
				if(jQuery('#toolbar').hasClass('no-display')) {
					jQuery('#toolbar').removeClass('no-display');
				}
				if(jQuery('#pager').hasClass('no-display')) {
					jQuery('#pager').removeClass('no-display');
				}
			} else {
				if(g_dontShowToolbar == 'True') {
					jQuery('#toolbar').addClass('no-display');
					jQuery('#pager').addClass('no-display');
				} else {
					if(jQuery('#toolbar').hasClass('no-display')) {
						jQuery('#toolbar').removeClass('no-display');
					}
					if(jQuery('#pager').hasClass('no-display')) {
						jQuery('#pager').removeClass('no-display');
					}
				}
			}
		}
		
		//document.getElementById('items_container').innerHTML = html;
		
	}
	else
	{
		g_html = g_errbox;
		g_html = g_html.replace(/{GSSE_MSG_ERRORNEW}/g, g_msg_text);
		g_html = g_html.replace(/{GSSE_MSG_ERRORNEWCLASS}/g, g_msg_class);
		document.getElementById('items_count').innerHTML = '0';
		document.getElementById('items_container').innerHTML = g_subcats;
        jQuery('#toolbar').addClass('no-display');
		jQuery('#pager').addClass('no-display');
	}
	return;
}

function show_item(iIDX,outer,box,aItem) {
	var cur_lblsale = '';
	var cur_lblnew;
	var cur_item;
	var all_items = '';
	var aImgs = new Array();
	var aPrices = new Array();
	var erg;
	var priceval = '';
	var oldprice = '';
	var oldpriceval = '';
	var priceclass = '';
	var saleperiod = '';
	var itemcompare;
	var iaction = 'N';
	var imax;
	var addtocart = '';
	var addtocartsmall = '';
	var itemimg;
	var itemdetail;
	var numpages = 0;
	var pager = '';
	var startitem = 0;
	var enditem = 0;
	var itemwishlist;
	var rating = '';
	var cur_lblbests = '';
	var imgsrc = '';
	var img2src = '';
	var axhr = [];
	var cmd;
	
	var date = new Date();
	var year = date.getFullYear();
	var month = '0' + (date.getMonth() + 1);
	month = month.slice(-2, (month.length - 2) + 3);
	var day = '0' + date.getDate();
	day = day.slice(-2, (day.length - 2) + 3);
	var cDate = year + month + day;
	
	var trialperiod = '&nbsp;';
	var aftertrial = '&nbsp;';
	var billingperiod = '&nbsp;';
	var aftertrialprice = '&nbsp;';
	var aftertrialperiod = '&nbsp;';
	var runtime = '&nbsp;';
	var runtimelng = '&nbsp;';
	var lPlural = false;
	var actbutwidth = 44;
	var actbutwidthall = actbutwidth;
	var f_oldpriceclass;
	cur_lblsale = '';
	priceval = '';
	oldprice = '';
	oldpriceval = '';
	priceclass = '';
	saleperiod = '';
	addtocart = '';
	g_exalblpricepermonth='';
	cur_item = box;
	
	aPrices = aItem['aprices'];
	
	actbutwidthall = actbutwidth;
	
	if(g_view == 'list') {
		f_oldpriceclass = 'old-price-l';
	} else {
		f_oldpriceclass = 'old-price';
	}
	
	if(aItem['itemHasDetail'] == 'Y') {
		detailurl = g_url + 'index.php?page=detail&amp;item=' + aItem['itemItemId'] + '&amp;d=' + aItem['itemItemPage'];
		/*A TS 09.12.2014: Benutze Permalink, wenn verfügbar*/
		if(g_sbedition == 13) {
			if(g_permalink == 'True') {
				if(aItem['itemItemPage'] != '') {
					detailurl = aItem['itemItemPage'];
				}
			}
		}
		itemimg = g_itemimg_qs;
		itemimg = itemimg.replace(/{GSSE_INCL_ITEMURL}/g, detailurl);
		itemdetail = g_itemnamedetail;
		itemdetail = itemdetail.replace(/{GSSE_INCL_ITEMURL}/g, detailurl);
	} else {
		itemimg = g_itemimg;
		itemdetail = g_itemname;
	}
	if(g_view == 'list') {
		itemdetail = itemdetail.replace(/{GSSE_INCL_ITEMNAMEONLY}/g, aItem['itemItemDescription']+' '+aItem['itemVariantDescription']);
	} else {
		itemdetail = itemdetail.replace(/{GSSE_INCL_ITEMNAMEONLY}/g, aItem['itemItemDescription']);
	}
	itemimg = itemimg.replace(/{GSSE_INCL_ITEMNAMEONLY}/g, aItem['itemItemDescription']);
	/*Bilder*/
	aImgs = aItem['aimgs'];
	/*Bild online oder lokal?*/
	if(aImgs.length > 0) {
		if(aImgs[0]['ImageName'].indexOf('http') == -1 && aImgs[0]['ImageName'].indexOf('://') == -1) {
			if(aImgs[0]['MediumExists'] == 1) {
				imgsrc = 'images/medium/' + aImgs[0]['ImageName'];
			} else if(aImgs[0]['SmallExists'] == 1) {
				imgsrc = 'images/small/' + aImgs[0]['ImageName'];
			} else if(aImgs[0]['BigExists'] == 1) {
				imgsrc = 'images/big/' + aImgs[0]['ImageName'];
			} else {
				imgsrc = 'template/images/no_pic_mid.png';
			}
		} else {
			imgsrc = aImgs[0]['ImageName'];
		}
		img2src = '';
	} else {
		imgsrc = 'template/images/no_pic_mid.png';
	}
		
	itemimg = itemimg.replace(/{GSSE_INCL_ITEMIMG}/g, imgsrc);
	itemimg = itemimg.replace(/{GSSE_INCL_ITEMIMG2}/g, imgsrc);
	
	/*Aktionen*/
	iaction = 'N';
	if(aItem['itemIsAction'] == 'Y') {
		iaction = aItem['hasaction'];
	}

	/*Neuheit*/
	if(aItem['itemIsNewItem'] == 'Y') {
		cur_lblnew = g_lbl_new;
		cur_lblnew = cur_lblnew.replace(/{GSSE_LANG_LangTagLabelNew}/g,g_lblnew);
	} else {
		cur_lblnew = '';
	}

	/*Aktionen*/
	if(aItem['itemIsTextHasNoPrice'] == 'N') {
		if(iaction == 'Y') {
			cur_lblsale = g_lbl_sale;
			cur_lblsale = cur_lblsale.replace(/{GSSE_LANG_LangTagLabelSale}/g,g_lblsale);
			if(aPrices['actshowperiod'] == 'Y') {
				saleperiod = conv_date(aPrices['actbegindate'],'D') + " - " + conv_date(aPrices['actenddate'],'D');
			}
		} else {
			cur_lblsale = '';
			saleperiod = '';
		}
	}
	
	/*Bestseller*/
	cur_lblbests = '';
	if(g_usephp == 'True') {
		if(g_bestseller == 'True') {
			if(aItem['bestseller']) {
				cur_lblbests = g_lbl_best;
			}
		}
	}
	cur_item = cur_item.replace(/{GSSE_INCL_LABELBEST}/g, cur_lblbests);
	
	/*cur_item = cur_item.replace(//g, aItem[]);*/
	cur_item = cur_item.replace(/{GSSE_INCL_LABELNEW}/g, cur_lblnew);
	cur_item = cur_item.replace(/{GSSE_INCL_LABELSALE}/g, cur_lblsale);
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMNAME}/g, aItem['itemItemDescription']);
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMNUMBER}/g, aItem['itemItemNumber']);
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMIMG}/g, itemimg);
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMTITLE}/g, itemdetail);
	
	/*Preise*/
	//if(aItem['itemIsTextHasNoPrice'] == 'N')
	//{
	priceval = get_currency(aPrices['price'],0);
	oldpriceval = get_currency(aPrices['oldprice'],0);
	oldprice = g_oldprice;
	oldpriceclass = 'no-old-price';
	priceclass = 'price';
	if(aPrices['oldprice'] > 0 && iaction == 'N') {
		priceclass = 'special-price';
		oldprice = oldprice.replace(/{GSSE_INCL_ITEMOLDPRICENEW}/g,oldpriceval);
		oldpriceclass = f_oldpriceclass;
	} else {
		oldprice = oldprice.replace(/{GSSE_INCL_ITEMOLDPRICENEW}/g,'&nbsp;');
	}
	
	if(iaction == 'Y') {
		priceval = get_currency(aPrices['actprice'].replace(/,/g,'.'),0);
	}
	
	if(iaction == 'Y' && aPrices['actshownormal'] == 'Y') {
		if(aPrices['actnormprice'] != '' && aPrices['actnormprice'] != 0) {
			var actnormprice = aPrices['actnormprice'].replace(/,/g,'.');
			oldpriceval = get_currency(actnormprice,0);
		} else {
			oldpriceval = get_currency(aPrices['oldprice'],0);
		}
		priceclass = 'special-price';
		oldprice = g_oldprice;
		oldprice = oldprice.replace(/{GSSE_INCL_ITEMOLDPRICENEW}/g,oldpriceval);
		oldpriceclass = f_oldpriceclass;
	}
	
	if(iaction == 'N') {
		if(aPrices['abulk']) {
			if(aPrices['abulk'].length > 0) {
				priceval = get_lngtext('LangTagFromNew') + ' ' + get_currency(aPrices['abulk'][0][1],0);
			}
		}
	}
	
	/*Mietpreise*/
	runtimelng = '';
	runtime = '';
	aftertrialprice = '';
	aftertrialperiod = '';
	trialperiod = '';
	aftertrial = '';
	billingperiod = '';
	if(aPrices['isrental']) {
		if(aPrices['isrental'] == 'Y') {
			if(aPrices['istrial'] == 'Y') {
				if(aPrices['trialfrequency'] > 1) {
					lPlural = true;
				} else {
					lPlural = false;
				}
				aftertrialprice = priceval;
				if(aPrices['trialprice'] > 0) {
					trialperiod = aPrices['trialfrequency'] + " " + get_billingperiodfromid(aPrices['trialperiod'],false,lPlural,false) + " " + get_lngtext('LangTagForSomething');
					priceval = get_currency(aPrices['trialprice'].replace(/,/g,'.'),0);
					billingperiod = get_billingperiodfromid(aPrices['trialperiod'],true,false,true);
				} else {
					trialperiod = aPrices['trialfrequency'] + " " + get_billingperiodfromid(aPrices['trialperiod'],false,lPlural,false);
					priceval = get_lngtext('LangTagForFree');
					billingperiod = '&nbsp;';
				}

				aftertrial = get_lngtext('LangTagAfterSomething');
				aftertrialperiod = get_billingperiodfromid(aPrices['billingperiod'],true,false,true);
			} else {
				billingperiod = get_billingperiodfromid(aPrices['billingperiod'],true,false,true);
			}
			if(aPrices['rentalruntime'] > 0) {
				runtimelng = get_lngtext('LangTagRentalRunTime') + ": ";
				if(aPrices['rentalruntime'] > 1) {
					lPlural = true;
				} else {
					lPlural = false;
				}
				runtime = aPrices['rentalruntime'] + " " + get_billingperiodfromid(aPrices['billingperiod'],false,lPlural,false);
			}
		}
	}
	
	/*Mietpreise*/
	cur_item = cur_item.replace(/{GSSE_INCL_RUNTIMELNG}/g,runtimelng);
	cur_item = cur_item.replace(/{GSSE_INCL_RUNTIME}/g,runtime);
	cur_item = cur_item.replace(/{GSSE_INCL_AFTERTRIALPRICE}/g,aftertrialprice);
	cur_item = cur_item.replace(/{GSSE_INCL_AFTERTRIALPERIOD}/g,aftertrialperiod);
	cur_item = cur_item.replace(/{GSSE_INCL_TRIALPERIOD}/g,trialperiod);
	cur_item = cur_item.replace(/{GSSE_INCL_AFTERTRIAL}/g,aftertrial);
	cur_item = cur_item.replace(/{GSSE_INCL_BIILINGPERIOD}/g,billingperiod);
	/*Preise*/
	cur_item = cur_item.replace(/{GSSE_INCL_OLDPRICENEW}/g,oldprice);
	cur_item = cur_item.replace(/{GSSE_INCL_OLDPRICECLASS}/g,oldpriceclass);
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMPRICEINFO}/g,get_setting('edPriceInformation_Text'));
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMPRICE}/g,priceval);
	cur_item = cur_item.replace(/{GSSE_INCL_PRICECLASS}/g,priceclass);
	/*Aktionszeitraum*/
	cur_item = cur_item.replace(/{GSSE_INCL_SALEPERIOD}/g,saleperiod);
	
	/*Begin Exalyser specific*/
	cur_item = cur_item.replace(/{GSSE_LANG_LangTagExaPricePerMonth}/g,g_exalblpricepermonth);
	/*End Exalyser specific*/
	
	/*Ratings*/
	rating = '';
	if(g_usephp == 'True') {
		if(g_userating == 'True') {
			if(g_login != null) {
				if(g_login['ok'] == 1) {
					rating = g_image;
					rating = rating.replace(/{GSSE_INCL_IMGCLASS}/g,'');
					rating = rating.replace(/{GSSE_INCL_IMGSRC}/g,'template/images/rating' + aItem['rating_avg'] + '0.gif');
					rating = rating.replace(/{GSSE_INCL_IMGALT}/g,'Rating');
					rating = rating.replace(/{GSSE_INCL_IMGTITLE}/g,'Rating');
				}
			}
		}
	}
	cur_item = cur_item.replace(/{GSSE_INCL_RATINGIMG}/g,rating);
	
	
	/*Compare*/
	itemcompare = '';
	if(!isMobile) {
		if(g_compare == 'True') {
			actbutwidthall += actbutwidth;
			itemcompare = g_itemcompare;
			itemcompare = itemcompare.replace(/{GSSE_LANG_LangTagArticleCompare}/g,'');
			itemcompare = itemcompare.replace(/{GSSE_INCL_ICID}/g,aItem['itemItemId']);
			itemcompare = itemcompare.replace(/{GSSE_INCL_ICNAME}/g,encodeURIComponent(aItem['itemItemDescription']));
			itemcompare = itemcompare.replace(/{GSSE_INCL_ICPAGE}/g,encodeURIComponent(aItem['itemItemPage']));
		}
	}
	cur_item = cur_item.replace(/{GSSE_INCL_COMPAREITEM}/g,itemcompare);
	
	/*Wishlist & Notepad*/
	itemwishlist = '';
	itemnotepad = '';
	if(g_usephp == 'True') {
		if(g_wishlist == 'True') {
			if(g_login != null) {
				if(g_login['ok'] == 1) {
					/*Wishlist*/
					if(aItem['wl_act'] && aItem['wl_cid'] && aItem['wl_wlid']) {
						if(aItem['wl_cid'] == g_login['cusIdNo']) {
							itemwishlist = g_rm_itemwishlist;
							itemwishlist = itemwishlist.replace(/{GSSE_INCL_WLID}/g,aItem['wl_wlid']);
							itemwishlist = itemwishlist.replace(/{GSSE_LANG_LangTagDeleteFromWishlist}/g,g_rm_lblwishlist);
						} else {
							itemwishlist = g_itemwishlist;
							itemwishlist = itemwishlist.replace(/{GSSE_INCL_ITEMNO}/g,aItem['itemItemNumber']);
							itemwishlist = itemwishlist.replace(/{GSSE_INCL_CUSID}/g,g_login['cusIdNo']);
							itemwishlist = itemwishlist.replace(/{GSSE_INCL_DATE}/g,cDate);
							itemwishlist = itemwishlist.replace(/{GSSE_LANG_LangTagMoveToWishList}/g,g_lblwishlist);
						}
					} else {
						itemwishlist = g_itemwishlist;
						itemwishlist = itemwishlist.replace(/{GSSE_INCL_ITEMNO}/g,aItem['itemItemNumber']);
						itemwishlist = itemwishlist.replace(/{GSSE_INCL_CUSID}/g,g_login['cusIdNo']);
						itemwishlist = itemwishlist.replace(/{GSSE_INCL_DATE}/g,cDate);
						itemwishlist = itemwishlist.replace(/{GSSE_LANG_LangTagMoveToWishList}/g,g_lblwishlist);
					}
					actbutwidthall += actbutwidth;
					
					/*Notepad*/
					if(aItem['np_act'] && aItem['np_npid']) {
						itemnotepad = g_rm_itemnotepad;
						itemnotepad = itemnotepad.replace(/{GSSE_INCL_NPID}/g,aItem['np_npid']);
						itemnotepad = itemnotepad.replace(/{GSSE_LANG_LangTagDeleteFromNotepad}/g,g_rm_lblnotepad);
						if(g_view != 'list') {
							/*in der Grid-Ansicht ist kein Platz für einen 4. Button,*/
							/*Wunschzettel-Button wird "mißbraucht"*/
							itemwishlist = itemnotepad;
						}
					} else {
						itemnotepad = g_itemnotepad;
						itemnotepad = itemnotepad.replace(/{GSSE_INCL_ITEMNO}/g,aItem['itemItemNumber']);
						itemnotepad = itemnotepad.replace(/{GSSE_INCL_CUSID}/g,g_login['cusIdNo']);
						itemnotepad = itemnotepad.replace(/{GSSE_INCL_DATE}/g,cDate);
						itemnotepad = itemnotepad.replace(/{GSSE_LANG_LangTagNote}/g,g_lblnotepad);
					}
					actbutwidthall += actbutwidth;
				}
			}
		}
	}
	cur_item = cur_item.replace(/{GSSE_INCL_WISHLIST}/g,itemwishlist);
	cur_item = cur_item.replace(/{GSSE_INCL_NOTEPAD}/g,itemnotepad);
	
	/*Description*/
	cur_item = cur_item.replace(/{GSSE_INCL_ITEMDETAILTEXT1}/g,aItem['itemItemText']);
	
	/*Availibility*/
	//TS 11.10.2016: Nur Text anzeigen
	/*var availability = unescape(aItem['avail']);*/
	var availability = unescape(aItem['availtext']);
	cur_item = cur_item.replace(/{GSSE_INCL_AVAILBOX}/g,availability);
	
	/*Add-to-cart*/
	//TS 09.01.2017: Wenn ein Artikel Varianten hat (has_variants > 0), dann auch über die Detailseite
	//TS 09.01.2017: SB-Edition berücksichtigen
	//TS 03.03.2017: Berücksichtigen, dass es Kunden geben kann, die Artikel-Modifikatoren (Attribute, Texteingabe etc.)
	//definiert haben, aber keine Detailseiten wollen. In diesem Fall müssen die Modifikatoren und die Mengeneingabe auf der
	//Warengruppenseite erscheinen. Das geht selbstverständlich nur bei der Listen-Ansicht und nur im ProPlus
	//Es werden zunächst nur Attribute angezeigt
	var attributesHtml = '';
	
	if(g_sbedition == 13) {
		//ProPlus
		//Auf Modifikatoren prüfen
		var lHasModificators = false;
		var lHasAttributes = false;
		if(aItem['itemIsTextInput'] == 'Y' || aItem['itemAttribute1'] != '' ||
			aItem['itemAttribute2'] != '' || aItem['itemAttribute3'] != '' || aPrices['isrental'] == 'Y' ||
			aItem['has_variants'] == 1) {
			lHasModificators = true;
			//Verfeinern nach Modifikator
			//Attribute
			if(aItem['itemAttribute1'] != '' || aItem['itemAttribute2'] != '' || aItem['itemAttribute3'] != '') {
				lHasAttributes = true;
			}
		}
		//Verhalten nach Ansicht
		if(g_view == 'list') {
			//Listenansicht ist gewählt, umstände prüfen
			//Artikel ist ein Katalogartikel?
			if(aItem['itemIsCatalogFlg'] == 'N') {
				//Nein, dann weiter
				//Hat der Artikel Modifikatoren?
				if(lHasModificators) {
					//Ja
					//Hat der Artikel eine Detailseite?
					if(aItem['itemHasDetail'] == 'Y') {
						//Ja, Button anzeigen, der zur Detailseite führt
						addtocartsmall = g_gotodetail;
						addtocartsmall = addtocartsmall.replace(/{GSSE_LANG_LangTagViewDetails}/g,g_lblgotodetail);
						addtocartsmall = addtocartsmall.replace(/{GSSE_INCL_LINKURL}/g,detailurl);
					} else {
						//Nein, modifikatoren anzeigen
						//Attribute?
						//g_attributeOuter
						//g_select
						//g_option
						if(lHasAttributes) {
							var attributesSelect = '';
							var selmulti = '';
							var selsize = '1';
							var selstyle = '';
							var optselected = '';
							attributesHtml = g_attributeOuter;
							var allAttributes = '';
							//Über alle 3 Attribute
							for(var a = 1; a <= 3; a++) {
								(function(cntr,itemid,attributename) {
									attributesSelect = '';
									//Attribut ist vorhanden?
									if(attributename != '') {
										attributesSelect = g_select;
										attributesSelect = attributesSelect.replace(/{GSSE_SEL_STYLE}/g,selstyle);
										attributesSelect = attributesSelect.replace(/{GSSE_SEL_ID}/g,itemid+'-attr'+(cntr-1).toString());
										attributesSelect = attributesSelect.replace(/{GSSE_SEL_NAME}/g,itemid+'-attr'+(cntr-1).toString());
										attributesSelect = attributesSelect.replace(/{GSSE_SEL_MULTIPLE}/g,selmulti);
										attributesSelect = attributesSelect.replace(/{GSSE_SEL_OPTIONS}/g,'');
										//Optionen
										cmd = g_host + 'get_attributevalues.inc.php?attr=' + encodeURIComponent(attributename);
										axhr[cntr-1] = gen_req();
										axhr[cntr-1].open("GET", cmd, true);
										axhr[cntr-1].onload = function (e) {
											if (axhr[cntr-1].readyState === 4) {
												if (axhr[cntr-1].status === 200) {
													res = axhr[cntr-1].responseText;
													var aOpts = JSON.parse(res);
													var oSel = document.getElementById(itemid+'-attr'+(cntr-1).toString());
													if(oSel) {
														for(var o = 0; o < aOpts.length; o++) {
															var opt = new Option(aOpts[o], aOpts[o], false, false);
															oSel.options[oSel.length] = opt;
														}
													}
												} else {
													console.error(axhr[cntr-1].statusText);
												}
											}
										};
										axhr[cntr-1].onerror = function (e) {
											console.error(axhr[cntr-1].statusText);
										};
										axhr[cntr-1].send(null);
									}
								})(a,aItem['itemItemId'],aItem['itemAttribute'+a.toString()]);//Inline-Function
								allAttributes += attributesSelect;
							}//For
							attributesHtml = attributesHtml.replace(/{GSSE_ATTR_CHOOSE}/g,get_lngtext('LangTagTextPleaseChoose'));
							attributesHtml = attributesHtml.replace(/{GSSE_ATTR_SELECTS}/g,allAttributes);
						}
						
						//Button "In den Warenkorb" mit Mengeneingabe anzeigen
						addtocart = g_addtocart;
						//addtocart = addtocart.replace(/{}/g,);
						addtocart = addtocart.replace(/{GSSE_INCL_OBJ}/g,aItem['itemItemId']+'-qty');
						addtocart = addtocart.replace(/{GSSE_INCL_ERRBOX}/g,aItem['itemItemId']+'-errbox');
						addtocart = addtocart.replace(/{GSSE_INCL_ADDQUANTITY}/g,'gs-float-left');
						addtocart = addtocart.replace(/{GSSE_LANG_LangTagTextQuantity}/g,get_lngtext('LangTagTextQuantity'));
						//addtocart = addtocart.replace(/{}/g,get_lngtext(''));
						addtocart = addtocart.replace(/{GSSE_LANG_LangTagDecrease}/g,get_lngtext('LangTagDecrease'));
						addtocart = addtocart.replace(/{GSSE_LANG_LangTagTextQuantity}/g,get_lngtext('LangTagTextQuantity'));
						addtocart = addtocart.replace(/{GSSE_LANG_LangTagIncrease}/g,get_lngtext('LangTagIncrease'));
						addtocart = addtocart.replace(/{GSSE_INCL_PPEXPRESSCHECKOUT}/g,'');
						addtocart = addtocart.replace(/{GSSE_INCL_BILLINGFREQUENCYCLASS}/g,'no-display');
						addtocart = addtocart.replace(/{GSSE_LANG_LangTagAddToBasket}/g,get_lngtext('LangTagAddToBasket'));
						addtocart = addtocart.replace(/{GSSE_INCL_ITEMID}/g,aItem['itemItemId']);
						actbutwidthall += 136;
					}
				} else {
					//Nein, Artikel kann direkt in den Warenkorb gelegt werden
					addtocartsmall = g_addtocartsmall;
					addtocartsmall = addtocartsmall.replace(/{GSSE_LANG_LangTagAddToBasket}/g,get_lngtext('LangTagAddToBasket'));
					addtocartsmall = addtocartsmall.replace(/{GSSE_INCL_ITEMID}/g,aItem['itemItemId']);
				}
			} else {
				//Ja, kein Button anzeigen
			}
		} else {
			//Gitteransicht wie gehabt
			if(aItem['itemIsCatalogFlg'] == 'N' && lHasModificators === false) {
				addtocartsmall = g_addtocartsmall;
				addtocartsmall = addtocartsmall.replace(/{GSSE_LANG_LangTagAddToBasket}/g,get_lngtext('LangTagAddToBasket'));
				addtocartsmall = addtocartsmall.replace(/{GSSE_INCL_ITEMID}/g,aItem['itemItemId']);
			} else {
				if(aItem['itemHasDetail'] == 'Y') {
					addtocartsmall = g_gotodetail;
					addtocartsmall = addtocartsmall.replace(/{GSSE_LANG_LangTagViewDetails}/g,g_lblgotodetail);
					addtocartsmall = addtocartsmall.replace(/{GSSE_INCL_LINKURL}/g,detailurl);
				}
			}
		}
	} else {
		//Alle anderen
		if(aItem['itemIsCatalogFlg'] == 'N') {
			addtocartsmall = g_addtocartsmall;
			addtocartsmall = addtocartsmall.replace(/{GSSE_LANG_LangTagAddToBasket}/g,get_lngtext('LangTagAddToBasket'));
			addtocartsmall = addtocartsmall.replace(/{GSSE_INCL_ITEMID}/g,aItem['itemItemId']);
		}
	}
	cur_item = cur_item.replace(/{GSSE_INCL_ACTBUTSWIDTH}/g,actbutwidthall);
	cur_item = cur_item.replace(/{GSSE_INCL_ATTRIBUTESNEW}/g,attributesHtml);
	cur_item = cur_item.replace(/{GSSE_INCL_ADDTOCARTSMALL}/g,addtocartsmall);
	cur_item = cur_item.replace(/{GSSE_INCL_ADDTOCART}/g,addtocart);
	
	if(g_view == 'list') {
		cur_item = cur_item.replace(/<li class="item">/g,'');
	} else {
		cur_item = cur_item.replace(/<li class="item item-products">/g,'');
	}
	cur_item = cur_item.substring(0,cur_item.length-5);
	document.getElementById('itemplace'+aItem['itemItemId']).innerHTML = cur_item;
	
}

function gs_chg_view(view)
{
	
	g_view = view;
	show_pgroupnew(g_start);
	return;
}

function gs_chg_count(obj)
{
	g_count = obj.options[obj.options.selectedIndex].value;
	show_pgroupnew(0);
	return;
}

function gs_sort_name(a, b)
{
	return a['itemItemDescription'] > b['itemItemDescription'] ? 1 : a['itemItemDescription'] < b['itemItemDescription'] ? -1 : 0;
}

function gs_sort_price(a, b)
{
	return parseFloat(a['ItemPrice']) > parseFloat(b['ItemPrice']) ? 1 : parseFloat(a['ItemPrice']) < parseFloat(b['ItemPrice']) ? -1 : 0;
}

function gs_sort_pos(a, b)
{
	return parseInt(a['OrderID']) > parseInt(b['OrderID']) ? 1 : parseInt(a['OrderID']) < parseInt(b['OrderID']) ? -1 : 0;
}

function gs_sort_rating(a, b)
{
	return parseInt(a['rating_avg']) > parseInt(b['rating_avg']) ? 1 : parseInt(a['rating_avg']) < parseInt(b['rating_avg']) ? -1 : 0;
}

function gs_chg_order(obj)
{
	var attr = obj.options[obj.options.selectedIndex].value;
	switch(attr)
	{
		case "name":
			g_aItems = g_aItems.sort(gs_sort_name);
			break;
		case "price":
			g_aItems = g_aItems.sort(gs_sort_price);
			break;
		case "pos":
			g_aItems = g_aItems.sort(gs_sort_pos);
			break;
		case "rating":
			g_aItems = g_aItems.sort(gs_sort_rating);
			break;
		default:
			g_aItems = g_aItems.sort(gs_sort_pos);
			break;
	}
	if(g_orderdir == 'desc')
	{
		g_aItems.reverse();
	}
	show_pgroup(g_start);
	return;
}

function gs_chg_ordernew(obj)
{
	var attr = obj.options[obj.options.selectedIndex].value;
	switch(attr)
	{
		case "name":
			g_aItemIds = g_aItemIds.sort(gs_sort_name);
			break;
		case "price":
			g_aItemIds = g_aItemIds.sort(gs_sort_price);
			break;
		case "pos":
			g_aItemIds = g_aItemIds.sort(gs_sort_pos);
			break;
		case "rating":
			g_aItemIds = g_aItemIds.sort(gs_sort_rating);
			break;
		default:
			g_aItemIds = g_aItemIds.sort(gs_sort_pos);
			break;
	}
	if(g_orderdir == 'desc')
	{
		g_aItemIds.reverse();
	}
	show_pgroupnew(g_start);
	return;
}

function gs_chg_dirnew()
{
	var dir = document.getElementById('sortdir').value;
	var newdir;
	var newsortimg = new Image;
	if(dir == 'asc')
	{
		newdir = 'desc';
	}
	else
	{
		newdir = 'asc';
	}
	document.getElementById('sortdir').value = newdir;
	newsortimg.src = 'template/images/i_' + newdir + '_arrow.gif';
	document.getElementById('sortimg').src = newsortimg.src;
	g_orderdir = newdir;
	g_aItemIds.reverse();
	show_pgroupnew(g_start);
	return;
}

function gs_chg_dir()
{
	var dir = document.getElementById('sortdir').value;
	var newdir;
	var newsortimg = new Image;
	if(dir == 'asc')
	{
		newdir = 'desc';
	}
	else
	{
		newdir = 'asc';
	}
	document.getElementById('sortdir').value = newdir;
	newsortimg.src = 'template/images/i_' + newdir + '_arrow.gif';
	document.getElementById('sortimg').src = newsortimg.src;
	g_orderdir = newdir;
	g_aItems.reverse();
	show_pgroup(g_start);
	return;
}

function get_currency(fNumber,nSymbol)
{
	var cSymbol;
	var localNumber;
	if(nSymbol == 1)
	{
		cSymbol = g_cur1;
	}
	else
	{
		cSymbol = g_cur2;
	}
	localNumber = get_number_format(fNumber);
	return localNumber + ' ' + cSymbol;
}

function get_number_format(fNumber)
{
	var fNum = parseFloat(fNumber);
	var cNum;
	var dec = 2;
	var comma = ',';
	var thous = '.';
	
	
	if(g_cntID == 'gbr' || g_cntID == 'irl' || g_cntID == 'usa' ||
		g_cntID == 'aus' || g_cntID == 'can' || g_cntID == 'chn' ||
		g_cntID == 'mex' || g_cntID == 'jpn' || g_cntID == 'twn' ||
		g_cntID == 'hkg' || g_cntID == 'kor' || g_cntID == 'tha' ||
		g_cntID == 'ind' || g_cntID == 'sgp' || g_cntID == 'nzl' ||
		g_cntID == 'pak' || g_cntID == 'bgd' || g_cntID == 'phl' ||
		g_cntID == 'nic' || g_cntID == 'nga' || g_cntID == 'bwa' ||
		g_cntID == 'zwe'){
		/*Sonderfall Kanada*/
		if(g_cntID == 'can')
		{
			if(g_lngID == 'eng')
			{
				comma = '.';
				thous = ',';
			} 
		}
		else
		{
			comma = '.';
			thous = ',';
		}
	}
	fNum = fNum.toFixed(dec);
	cNum = fNum.toString();
	cNum = cNum.replace(/\./g, comma); 
	return cNum;
}

function mark_comparison2(win,idx,itemname,itempage)
{
	var res;
	var cmd;
	var xhr;
	var aerg = new Array();
	cmd = g_host + 'add_remove_comparison2.inc.php?itemid=' + idx + "&name=" + itemname + "&page=" + itempage;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				aerg = JSON.parse(res);
				update_compare_box(win,aerg);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function pre_update_compare_box(ajsonitems)
{
	var aItems = new Array();
	aItems = JSON.parse(ajsonitems);
	update_compare_box(window,aItems);
	return;
}

function update_compare_box(win,aitemstocompare)
{
	var $=jQuery;
	var aNames = new Array();
	var cNames;
	var noitems = g_pcontent;
	var outer = decodeBase64(g_cmp_outer);
	var item = decodeBase64(g_cmp_item);
	
	var cur_item = '';
	var all_items = '';
	var detailurl = '';
	if(document.getElementById('itemstocompare'))
	{
		noitems = noitems.replace(/{GSSE_INCL_PCLASS}/g,'empty');
		noitems = noitems.replace(/{GSSE_INCL_PCONTENT}/g,get_lngtext('LangTagItemCompareNoItems'));
		if(aitemstocompare.length == 0)
		{
			win.document.getElementById('itemstocompare').innerHTML = noitems;
			/*self.location.href = '#itemscomparebegin';*/
		}
		else
		{
			for(c = 0; c < aitemstocompare.length; c++)
			{
				/*{GSSE_SURL_}index.php?page=detail&amp;item={GSSE_INCL_ICITEMID}*/
				detailurl = g_url + 'index.php?page=detail&amp;item=' + aitemstocompare[c]['idx'];
				/*A TS 09.12.2014: Benutze Permalink, wenn verfügbar*/
				if(g_sbedition == 13) {
					if(g_permalink == 'True') {
						if(aitemstocompare[c]['page'] != '') {
							detailurl = aitemstocompare[c]['page'];
						}
					}
				}
				cur_item = item;
				cur_item = cur_item.replace(/{GSSE_LANG_LangTagRemoveFromCompare}/g,get_lngtext('LangTagRemoveFromCompare'));
				cur_item = cur_item.replace(/{GSSE_SURL_}/g,g_url);
				cur_item = cur_item.replace(/{GSSE_INCL_ICITEMID}/g,aitemstocompare[c]['idx']);
				cur_item = cur_item.replace(/{GSSE_INCL_ICITEMNAME}/g,aitemstocompare[c]['name']);
				cur_item = cur_item.replace(/{GSSE_INCL_DETAILURL}/g,detailurl);
				all_items += cur_item;
			}
			outer = outer.replace(/{GSSE_INCL_ITEMSTOCOMPARE}/g,all_items);
			outer = outer.replace(/{GSSE_LANG_LangTagCompare}/g,get_lngtext('LangTagCompare'));
			outer = outer.replace(/{GSSE_LANG_LangTagRemoveAllFromCompare}/g,get_lngtext('LangTagRemoveAllFromCompare'));
			outer = outer.replace(/{GSSE_LANG_LangTagClearAll}/g,get_lngtext('LangTagClearAll'));
			win.document.getElementById('itemstocompare').innerHTML = outer;
			/*self.location.href = '#itemscomparebegin';*/
			$('body,html').animate({
				scrollTop: 0
			}, 800);
		}
	}
	return;
}

function build_pager(numpages)
{
	var pager_outer = g_pagerouter;
	var cur_item;
	var all_items = '';
	var p;
	var disp = 0;
	var prev_item = g_pagerprev;
	var next_item = g_pagernext;
	var iprev = g_start - 1;
	var inext = g_start + 1;
	
	/*Previous-Button*/
	if(g_start > 0)
	{
		prev_item = prev_item.replace(/{GSSE_INCL_PREVNO}/g,iprev);
		prev_item = prev_item.replace(/{GSSE_LANG_LangTagPrevPageLinkName}/g,get_lngtext('LangTagPrevPageLinkName'));
		all_items = prev_item;
	}
	
	for(p = 0; p < numpages; p++)
	{
		disp = p + 1;
		
		if(p == g_start)
		{
			cur_item = g_pageritem;
		}
		else
		{
			cur_item = g_pageraitem;
		}
		cur_item = cur_item.replace(/{GSSE_INCL_PAGENO}/g,p);
		cur_item = cur_item.replace(/{GSSE_INCL_PAGENODISP}/g,disp);
		all_items += cur_item;
	}
	
	/*Next-Button*/
	if(g_start < (numpages - 1))
	{
		next_item = next_item.replace(/{GSSE_INCL_NEXTNO}/g,inext);
		next_item = next_item.replace(/{GSSE_LANG_LangTagNextPageLinkName}/g,get_lngtext('LangTagNextPageLinkName'));
		all_items += next_item;
	}
	pager_outer = pager_outer.replace(/{GSSE_INCL_PAGES}/g,all_items);
	return pager_outer;
}

function new_add_direct_to_basket(iItemId,iItemCount)
{
	var cText = '';
	var cAttr0 = '';
	var cAttr1 = '';
	var cAttr2 = '';
	var cmd;
	var param;
	var res;
	var xhr;
	var aerg;
	
	//TS 03.03.2017: Attribute checken, wenn verfügbar
	if(document.getElementById(iItemId+'-attr0')) {
		cAttr0 = document.getElementById(iItemId+'-attr0').options[document.getElementById(iItemId+'-attr0').options.selectedIndex].value;
	}
	if(document.getElementById(iItemId+'-attr1')) {
		cAttr1 = document.getElementById(iItemId+'-attr1').options[document.getElementById(iItemId+'-attr1').options.selectedIndex].value;
	}
	if(document.getElementById(iItemId+'-attr2')) {
		cAttr2 = document.getElementById(iItemId+'-attr2').options[document.getElementById(iItemId+'-attr2').options.selectedIndex].value;
	}
	
	//TS 17.08.2017: Übergabe per POST
	/*cmd = g_host + 'add_to_basket.inc.php?imenge=' + iItemCount + 
																'&ctext=' + cText + 
																'&cattr0=' + cAttr0 + 
																'&cattr1=' + cAttr1 +
																'&cattr2=' + cAttr2 +
																'&item=' + iItemId;*/
	cmd = g_host + 'add_to_basket.inc.php';
	param = 'imenge=' + iItemCount + 
				'&ctext=' + cText + 
				'&cattr0=' + cAttr0 + 
				'&cattr1=' + cAttr1 +
				'&cattr2=' + cAttr2 +
				'&item=' + iItemId;
	xhr = gen_req();
	//xhr.open("GET", cmd, true);
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				aerg = gen_array(res);
				if(aerg[0][0] == 0) {
					//document.getElementById('id_basket_icounts').innerHTML = aerg[0][2];
					//document.getElementById('id_basket_total').innerHTML = aerg[0][3];
					jQuery('.cartqty').html(aerg[0][2]);
					jQuery('.cartvalue').html(aerg[0][3]);
					if(aerg[0][2] != "0") {
						if(!jQuery('.div_link-cart_inner .top_cart span').hasClass('gs-cart-yellow')) {
							jQuery('.div_link-cart_inner .top_cart span').addClass('gs-cart-yellow');
						}
					} else {
						if(jQuery('.div_link-cart_inner .top_cart span').hasClass('gs-cart-yellow')) {
							jQuery('.div_link-cart_inner .top_cart span').removeClass('gs-cart-yellow');
						}
					}
					update_mini_basket();
				} else {
					alert("Fehler!\n" + res);
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(param);
	
	return;
}

function update_mini_basket()
{
	var cmd;
	var res;
	var xhr;
	
	cmd = g_host + 'get_basket_json.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				//alert(res);
				refresh_mini_basket(JSON.parse(res));
				jQuery('body,html').animate({
					scrollTop: 0
				}, 800);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	
	return;
}

function refresh_mini_basket(aBasket) {
	var mbempty;
	var mbouter;
	var mbitem;
	var i;
	var total = 0.0;
	var cnt = 0;
	var cur_item;
	var all_items = '';
	var last = '';
	var eoo = '';
	var itemlink;
	var itemimagelink;
	var itemimage;
	var itemname;
	var pcontent;
	var cur_image;
	var cur_name;
	var cur_p;
	var aAttr = new Array();
	var cAttr = '';
	var coupon;
	var imgfile;
	var item_price;
	var item_defprice;
	var inclvat;
	var netprice;
	var disprice;
	var vat_rate;
	var vat_factor;
	var discount;
	var discountsum;
	
	cnt = aBasket.length;
	if(cnt > 0) {
		if(get_setting('cbNetPrice_Checked') == 'True') {
			//Nettopreise
			inclvat = false;
		} else {
			inclvat = true;
		}
		itemlink = g_itemlink;
		itemimagelink = g_itemimagelink;
		itemimage = g_itemimage;
		pcontent = g_pcontent;
		mbouter = g_mbouter;
		mbitem = g_mbitem;
		
		mbouter = mbouter.replace(/{GSSE_LANG_LangTagTextGoodsValue}/g,get_lngtext('LangTagTextGoodsValue'));
		mbouter = mbouter.replace(/{GSSE_LANG_LangTagSubTotal}/g,get_lngtext('LangTagSubTotal'));
		mbouter = mbouter.replace(/{GSSE_LANG_LangTagOrder}/g,get_lngtext('LangTagOrder'));
		mbouter = mbouter.replace(/{GSSE_LANG_LangTagOrder}/g,get_lngtext('LangTagOrder'));
		mbouter = mbouter.replace(/{GSSE_LANG_LangTagTextViewBasket}/g,get_lngtext('LangTagTextViewBasket'));
		
		mbitem = mbitem.replace(/{GSSE_LANG_LangTagDelete}/g,get_lngtext('LangTagDelete'));
		for(i = 0; i < aBasket.length; i++) {
			coupon = 0;
			aAttr = new Array();
			cAttr = '';
			cur_item = mbitem;
			if(i == (aBasket.length - 1)) {
				last = ' last';
			} else {
				last = '';
			}
			if((i % 2) == 0) {
				eoo = ' even';
			} else {
				eoo = ' odd';
			}
			
			itemname = aBasket[i]['art_title'];
			if(aBasket[i]['art_vartitle'] != '') {
				itemname += ' '+aBasket[i]['art_vartitle'];
			}
			
			var spattern = get_lngtext('LangTagCoupon');
			var regex = new RegExp(spattern, "i");
			if (aBasket[i]['art_title'].search(regex) != -1) {
				coupon = 1;
			}
			
			/*cur_item = cur_item.replace(//g,get_lngtext(''));*/
			cur_item = cur_item.replace(/{GSSE_INCL_LAST}/g,last);
			cur_item = cur_item.replace(/{GSSE_INCL_EOO}/g,eoo);
			
			if(aBasket[i]['art_attr0'] != '') aAttr.push(aBasket[i]['art_attr0']);
			if(aBasket[i]['art_attr1'] != '') aAttr.push(aBasket[i]['art_attr1']);
			if(aBasket[i]['art_attr2'] != '') aAttr.push(aBasket[i]['art_attr2']);
			if(aAttr.length > 0) {
				cAttr = aAttr.join(', ');
			}
			vat_rate = parseFloat(aBasket[i]['art_vatrate']);
			vat_factor = (100 + vat_rate)/100;
			item_price = parseFloat(aBasket[i]['art_price']);
			/*A TS 07.03.2016: Ggf. Rabatt von NETTO!!!!!!-Preis abziehen*/
			//Originalpreis sichern
			item_defprice = item_price;
			discount = parseFloat(aBasket[i]['art_discount']);
			discountsum = 0;
			if(discount > 0) {
				if(inclvat) {
					//Shop hat Bruttopreise, d. h. erst UST herausrechnen
					netprice = Math.round((item_price / vat_factor) * 100) / 100;
					//Dann Rabatt rausrechnen
					//disprice = Math.round((netprice / ((100+discount)/100))*100) / 100;
					//$fNewPrice = $fPrice - round((($fPrice/100)*$fDiscount),$iDecimals);
					discountsum = Math.round(((netprice/100)*discount) * 100) / 100;
					disprice = netprice - discountsum;
					//Und MwSt wieder drauf
					//item_price = Math.round((disprice * vat_factor) * 100) / 100;
				} else {
					//Shop hat Nettopreise
					//Hier brauch nur der Rabatt rausgezogen werden
					//item_price = Math.round((item_price / ((100+discount)/100)) * 100) / 100;
				}
			}
			
			cur_item = cur_item.replace(/{GSSE_INCL_ATTRIBUTES}/g,cAttr);
			cur_item = cur_item.replace(/{GSSE_INCL_TEXT}/g,aBasket[i]['art_textfeld']);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMNUMBER}/g,aBasket[i]['art_num']);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMQTY}/g,aBasket[i]['art_count']);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMPRICE}/g,get_currency(item_price,0));
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMPRICEINFO}/g,get_setting('edPriceInformation_Text'));
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMIDX}/g,i);
			/*Begin exalyser specific*/
			cur_item = cur_item.replace(/{GSSE_LANG_LangTagExaPricePerMonthShort}/g,get_lngtext('LangTagExaPricePerMonthShort'));
			/*End exalyser specific*/
			
			
			if(aBasket[i]['art_img'] != '') {
				if(aBasket[i]['art_img'].indexOf('http') == -1 && aBasket[i]['art_img'].indexOf('://') == -1) {
					imgfile = g_url + 'images/medium/' + aBasket[i]['art_img'];
				} else {
					imgfile = aBasket[i]['art_img'];
				}
			} else {
				if(coupon == 0) {
					imgfile = g_url + 'template/images/no_pic_sma.png';
				} else {
					imgfile = g_url + 'template/images/coupon.png';
				}
			}
			
			if(aBasket[i]['art_hasdetail'] == 'W') {
				cur_image = itemimage;
				//cur_image = itemimagelink;
				//cur_image = cur_image.replace(/{GSSE_INCL_LINKCLASS}/g,'product-image');
				//cur_image = cur_image.replace(/{GSSE_INCL_LINKURL}/g,aBasket[i]['art_dpn']);
				//cur_image = cur_image.replace(/{GSSE_INCL_LINKTARGET}/g,'_self');
				cur_image = cur_image.replace(/{GSSE_INCL_IMGCLASS}/g,'');
				cur_image = cur_image.replace(/{GSSE_INCL_IMGSRC}/g,imgfile);
				cur_image = cur_image.replace(/{GSSE_INCL_IMGALT}/g,itemname);
				cur_image = cur_image.replace(/{GSSE_INCL_IMGTITLE}/g,itemname);
				
				cur_name = itemlink;
				cur_name = cur_name.replace(/{GSSE_INCL_LINKCLASS}/g,'');
				cur_name = cur_name.replace(/{GSSE_INCL_LINKURL}/g,aBasket[i]['art_dpn']);
				cur_name = cur_name.replace(/{GSSE_INCL_LINKTARGET}/g,'_self');
				cur_name = cur_name.replace(/{GSSE_INCL_LINKNAME}/g,itemname);
			} else {
				cur_name = itemname;
					
				cur_image = itemimage;
				cur_image = cur_image.replace(/{GSSE_INCL_IMGCLASS}/g,'');
				cur_image = cur_image.replace(/{GSSE_INCL_IMGSRC}/g,imgfile);
				cur_image = cur_image.replace(/{GSSE_INCL_IMGALT}/g,aBasket[i]['art_title']);
				cur_image = cur_image.replace(/{GSSE_INCL_IMGTITLE}/g,aBasket[i]['art_title']);
				cur_p = pcontent;
				cur_p = cur_p.replace(/{GSSE_INCL_PCLASS}/g,'product-image');
				cur_image = cur_p.replace(/{GSSE_INCL_PCONTENT}/g,cur_image);
			}
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMIMG}/g,cur_image);
			cur_item = cur_item.replace(/{GSSE_INCL_ITEMNAME}/g,cur_name);
			all_items += cur_item;
			total += item_price * aBasket[i]['art_count'];
		}
		mbouter = mbouter.replace(/{GSSE_INCL_MBITEMS}/g,all_items);
		mbouter = mbouter.replace(/{GSSE_INCL_BASKETURL}/g,g_url + 'index.php?page=basket');
		mbouter = mbouter.replace(/{GSSE_INCL_MBSUBTOTAL}/g,get_currency(total,0));
		minvalue = parseFloat(get_setting('edMinOrderValue_Text').replace(/\,/g,'.'));
		if(minvalue > total) {
			notreached = uml(get_lngtext('LangTagTextMinOrderNewValue1')) + ' ' + get_currency(total,0) + ' ' +
							 uml(get_lngtext('LangTagTextMinOrderNewValue2')) + ' ' + get_currency(minvalue,0) + ' ' +
							 uml(get_lngtext('LangTagTextMinOrderNewValue3'));
			mbouter = mbouter.replace(/{GSSE_INCL_SHOWCOBUTTON}/g,'no-display');
			mbouter = mbouter.replace(/{GSSE_MSG_ERRORNEWCLASS}/g,'notice-msg');
			mbouter = mbouter.replace(/{GSSE_MSG_ERRORNEW}/g,notreached);
		} else {
			mbouter = mbouter.replace(/{GSSE_INCL_SHOWCOBUTTON}/g,'button');
			mbouter = mbouter.replace(/{GSSE_MSG_ERRORNEWCLASS}/g,'no-display');
			mbouter = mbouter.replace(/{GSSE_MSG_ERRORNEW}/g,'');
		}
		mbouter = mbouter.replace(/{GSSE_INCL_BUYURL}/g,g_url + 'index.php?page=buy');
		document.getElementById('gs_minibasket_items').innerHTML = mbouter;
	} else {
		mbempty = decodeBase64(g_mbasket_empty);
		mbempty = mbempty.replace(/{GSSE_LANG_LangTagTextBasketEmpty}/g,get_lngtext('LangTagTextBasketEmpty'));
		document.getElementById('gs_minibasket_items').innerHTML = mbempty;
	}
	
	jQuery('.cartqty').html(cnt);
	jQuery('.cartvalue').html(get_currency(total,0));
	if(cnt > 0) {
		if(!jQuery('.div_link-cart_inner .top_cart span').hasClass('gs-cart-yellow')) {
			jQuery('.div_link-cart_inner .top_cart span').addClass('gs-cart-yellow');
		}
	} else {
		if(jQuery('.div_link-cart_inner .top_cart span').hasClass('gs-cart-yellow')) {
			jQuery('.div_link-cart_inner .top_cart span').removeClass('gs-cart-yellow');
		}
	}
	return;
}

function new_del_from_basket(idx)
{
	var cmd;
	var xhr;
	var res;
	if(idx == -1) {
		if(!confirm(get_lngtext('LangTagClearBasketWarn'))) {
			return;
		}
	}
	cmd = g_host + 'del_from_basket.inc.php?idx=' + idx;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				update_mini_basket();
				if(g_on_basketpage == 1) {
					self.location.reload();
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function new_add_to_basket(iItemIndex)
{
	var isDecimal;
	var cmd, cmd2;
	var res;
	var xhr, xhr2;
	var aPrices;
	
	cmd = g_host + 'get_isdecimal.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				isDecimal = xhr.responseText;
				cmd2 = g_host + 'get_prices.inc.php?itemid=' + iItemIndex;
				xhr2 = gen_req();
				xhr2.open("GET", cmd2, true);
				xhr2.onload = function (e) {
					if (xhr2.readyState === 4) {
						if (xhr2.status === 200) {
							res = xhr2.responseText;
							aPrices = JSON.parse(res);
							new_add_to_basket_execute(iItemIndex,isDecimal,aPrices);
						} else {
							console.error(xhr2.statusText);
						}
					}
				};
				xhr2.onerror = function (e) {
					console.error(xhr2.statusText);
				};
				xhr2.send(null);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	
	return;
}

function new_add_to_basket_execute(iItemIndex,isDecimal,aPrices) {
	var cMenge = 1;
	var fMenge;
	var fMax;
	var oErrBox;
	var cText = '';
	var cAttr0 = '';
	var cAttr1 = '';
	var cAttr2 = '';
	var idx = 0;
	var cmd, cmd2, cmd3;
	var param, param2, param3;
	var res, erg;
	var xhr, xhr2, xhr3;
	var billingfreq = 0;
	var billingfreqtext = '';
	
	if(document.getElementById('qty')) {
		cMenge = document.getElementById('qty').value.replace(/\,/g,'.');
	}
	
	if(document.getElementById(iItemIndex+'-qty')) {
		cMenge = document.getElementById(iItemIndex+'-qty').value.replace(/\,/g,'.');
	}
	
	if(document.getElementById('errbox')) {
		oErrBox = document.getElementById('errbox');
	}
	
	if(document.getElementById(iItemIndex+'-errbox')) {
		oErrBox = document.getElementById(iItemIndex+'-errbox');
	}
	
	if(cMenge == '' || cMenge < 1 || isNaN(cMenge)) {
		oErrBox.innerHTML = uml(get_lngtext('LangTagEnterNumber'));
		oErrBox.className = 'error-msg';
		return;
	}
	
	if(isDecimal == 1 && cMenge.indexOf('.') > -1) {
		oErrBox.innerHTML = uml(get_lngtext('LangTagIntQuantWarning'));
		oErrBox.className = 'error-msg';
		return;
	}
	
	fMenge = parseFloat(cMenge);
	fMax = parseFloat(g_maxqty);
	
	if(g_maxqty != '') {
		if(fMenge > fMax) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextMaxQuantity') + ' ' + g_maxqty);
			oErrBox.className = 'error-msg';
			return;
		}
	}
	
	if(document.getElementById('textfeld')) {
		cText = document.getElementById('textfeld').value;
	}
	
	if(document.getElementById('attr0')) {
		idx = document.getElementById('attr0').options.selectedIndex;
		cAttr0 = document.getElementById('attr0').options[idx].value;
		if((idx == 0) && (cAttr0.indexOf('?') != -1)) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextPleaseChoose') + ': ' + cAttr0);
			oErrBox.className = 'notice-msg';
			return;
		}
	}
	
	if(document.getElementById('attr1')) {
		idx = document.getElementById('attr1').options.selectedIndex;
		cAttr1 = document.getElementById('attr1').options[idx].value;
		if((idx == 0) && (cAttr1.indexOf('?') != -1)) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextPleaseChoose') + ': ' + cAttr1);
			oErrBox.className = 'notice-msg';
			return;
		}
	}
	
	if(document.getElementById('attr2')) {
		idx = document.getElementById('attr2').options.selectedIndex;
		cAttr2 = document.getElementById('attr2').options[idx].value;
		if((idx == 0) && (cAttr2.indexOf('?') != -1)) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextPleaseChoose') + ': ' + cAttr2);
			oErrBox.className = 'notice-msg';
			return;
		}
	}
	
	//TS 03.03.2017: Attribute aus Listenansicht checken, wenn verfügbar
	if(document.getElementById(iItemIndex+'-attr0')) {
		cAttr0 = document.getElementById(iItemIndex+'-attr0').options[document.getElementById(iItemIndex+'-attr0').options.selectedIndex].value;
		if((cAttr0.indexOf('?') != -1)) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextPleaseChoose') + ': ' + cAttr0);
			oErrBox.className = 'notice-msg';
			return;
		}
	}
	if(document.getElementById(iItemIndex+'-attr1')) {
		cAttr1 = document.getElementById(iItemIndex+'-attr1').options[document.getElementById(iItemIndex+'-attr1').options.selectedIndex].value;
		if((cAttr1.indexOf('?') != -1)) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextPleaseChoose') + ': ' + cAttr1);
			oErrBox.className = 'notice-msg';
			return;
		}
	}
	if(document.getElementById(iItemIndex+'-attr2')) {
		cAttr2 = document.getElementById(iItemIndex+'-attr2').options[document.getElementById(iItemIndex+'-attr2').options.selectedIndex].value;
		if((cAttr2.indexOf('?') != -1)) {
			oErrBox.innerHTML = uml(get_lngtext('LangTagTextPleaseChoose') + ': ' + cAttr2);
			oErrBox.className = 'notice-msg';
			return;
		}
	}
	
	if(document.getElementById('billingfrequency')) {
		if(document.getElementById('billingfrequency').options.length > 0) {
			billingfreq = document.getElementById('billingfrequency').options[document.getElementById('billingfrequency').options.selectedIndex].value;
			billingfreqtext = document.getElementById('billingfrequency').options[document.getElementById('billingfrequency').options.selectedIndex].text;
		}
	}
	
	if(aPrices['isrental']) {
		if(aPrices['isrental'] == 'Y') {
			if(aPrices['rentalruntime'] > 0) {
				lPlural = false;
				if(aPrices['rentalruntime'] > 1) { lPlural = true; }
				cAttr0 = get_lngtext('LangTagRentalRunTime') + ': ' + aPrices['rentalruntime'] + ' ' + get_billingperiodfromid(aPrices['billingperiod'],false,lPlural,false);
			}
			var fPeriod = parseFloat(billingfreq);
			var fPrice = parseFloat(aPrices['price']);
			var fInvPrice = fPeriod * fPrice;
			//TS 20.06.2017: Diese Info darf nicht in cText
			/*cText = cText + ' ' + get_lngtext('LangTagSubsequentInvoices') + ' ' + billingfreq + ' ' + get_billingperiodfromid(aPrices['billingperiod'],false,lPlural,false) + ' ' +
						get_currency(fInvPrice,2) + ' ' + get_lngtext('LangTagRuntimeEnd');*/
			cAttr1 = get_lngtext('LangTagSubsequentInvoices') + ' ' + billingfreq + ' ' + get_billingperiodfromid(aPrices['billingperiod'],false,lPlural,false) + ' ' +
						get_currency(fInvPrice,2) + ' ' + get_lngtext('LangTagRuntimeEnd');
		}
	}
	
	/*cmd = g_host + 'add_to_basket.inc.php?imenge=' + cMenge + 
																'&ctext=' + cText + 
																'&cattr0=' + cAttr0 + 
																'&cattr1=' + cAttr1 +
																'&cattr2=' + cAttr2 +
																'&item=' + iItemIndex +
																'&billingfreq=' + billingfreq +
																'&billingfreqtext=' + billingfreqtext +
																'&istrial=N';*/
	//TS 14.08.2017: Daten per POST übergeben
	cmd = g_host + 'add_to_basket.inc.php';
	param = 'imenge=' + cMenge + 
			'&ctext=' + cText + 
			'&cattr0=' + cAttr0 + 
			'&cattr1=' + cAttr1 +
			'&cattr2=' + cAttr2 +
			'&item=' + iItemIndex +
			'&billingfreq=' + billingfreq +
			'&billingfreqtext=' + billingfreqtext +
			'&istrial=N';
	//Hauptartikel hinzufügen
	xhr = gen_req();
	//TS 14.08.2017: Daten per POST übergeben
	//xhr.open("GET", cmd, true);
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				var aerg = gen_array(res);
				if(aerg[0][0] == 0) {
					var msgText = get_lngtext('LangTagItemToBask')
					oErrBox.innerHTML = msgText;
					oErrBox.className = 'success-msg';
					update_mini_basket();
					//Ggf. Trial-Periode und Einrichtungsartikel hinzufügen
					if(aPrices['isrental']) {
						if(aPrices['isrental'] == 'Y') {
							/*Trialperiode als separater Artikel*/
							if(aPrices['istrial'] == 'Y') {
								cMenge = fMenge - aPrices['trialfrequency'];
								if(aPrices['trialprice'] != 0) {
									//TS 14.08.2017: Daten per POST übergeben
									/*cmd2 = g_host + 'add_to_basket.inc.php?imenge=' + aPrices['trialfrequency'] + 
																				'&ctext=' +
																				'&cattr0=' + get_lngtext('LangTagTrialPeriod') + 
																				'&cattr1=' + cAttr1 +
																				'&cattr2=' + cAttr2 +
																				'&item=' + iItemIndex +
																				'&billingfreq=' + billingfreq +
																				'&billingfreqtext=' + billingfreqtext +
																				'&istrial=Y' +
																				'&isinitprice=0';*/
									cmd2 = g_host + 'add_to_basket.inc.php';
									param2 = 'imenge=' + aPrices['trialfrequency'] + 
											'&ctext=' +
											'&cattr0=' + get_lngtext('LangTagTrialPeriod') + 
											'&cattr1=' + cAttr1 +
											'&cattr2=' + cAttr2 +
											'&item=' + iItemIndex +
											'&billingfreq=' + billingfreq +
											'&billingfreqtext=' + billingfreqtext +
											'&istrial=Y' +
											'&isinitprice=0';
									xhr2 = gen_req();
									//TS 14.08.2017: Daten per POST übergeben
									//xhr2.open("GET", cmd2, true);
									xhr2.open("POST", cmd2, true);
									xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
									xhr2.onload = function (e) {
										if (xhr2.readyState === 4) {
											if (xhr2.status === 200) {
												update_mini_basket();
											} else {
												console.error(xhr2.statusText);
											}
										}
									};
									xhr2.onerror = function (e) {
										console.error(xhr2.statusText);
									};
									//TS 14.08.2017: Daten per POST übergeben
									//xhr2.send(null);
									xhr2.send(param2);
								}
							}
							/*Einrichtungsgebühr als separater Artikel*/
							if(aPrices['initialprice'] != 0) {
								//TS 14.08.2017: Daten per POST übergeben
								/*cmd3 = g_host + 'add_to_basket.inc.php?imenge=1' + 
																			'&ctext=' +
																			'&cattr0=' + get_lngtext('LangTagInitialPrice') + 
																			'&cattr1=' + cAttr1 +
																			'&cattr2=' + cAttr2 +
																			'&item=' + iItemIndex +
																			'&billingfreq=' + billingfreq +
																			'&billingfreqtext=' + billingfreqtext +
																			'&istrial=N' +
																			'&isinitprice=' + aPrices['initialprice'];*/
								cmd3 = g_host + 'add_to_basket.inc.php';
								param3 = 'imenge=1' + 
										'&ctext=' +
										'&cattr0=' + get_lngtext('LangTagInitialPrice') + 
										'&cattr1=' + cAttr1 +
										'&cattr2=' + cAttr2 +
										'&item=' + iItemIndex +
										'&billingfreq=' + billingfreq +
										'&billingfreqtext=' + billingfreqtext +
										'&istrial=N' +
										'&isinitprice=' + aPrices['initialprice'];
								xhr3 = gen_req();
								//TS 14.08.2017: Daten per POST übergeben
								//xhr3.open("GET", cmd3, true);
								xhr3.open("POST", cmd3, true);
								xhr3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
								xhr3.onload = function (e) {
									if (xhr3.readyState === 4) {
										if (xhr3.status === 200) {
											update_mini_basket();
										} else {
											console.error(xhr3.statusText);
										}
									}
								};
								xhr3.onerror = function (e) {
									console.error(xhr3.statusText);
								};
								//TS 14.08.2017: Daten per POST übergeben
								//xhr3.send(null);
								xhr3.send(param3);
							}
						}
					}
					
				} else {
					if(aerg[0][0] == -2) {
						oErrBox.innerHTML = get_lngtext('LangTagTextMaxQuantity') + ' ' + g_maxqty;
						oErrBox.className = 'error-msg';
					} else {
						oErrBox.innerHTML = get_lngtext('LangTagTextFailed') + ': ' + erg;
						oErrBox.className = 'error-msg';
					}
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	//TS 14.08.2017: Daten per POST übergeben
	//xhr.send(null);
	xhr.send(param);
	return;
}

function uml(str)
{
	var transformations = [
		["Ä", "&Auml;"],
		["ä", "&auml;"],
		["Ö", "&Ouml;"],
		["ö", "&ouml;"],
		["Ü", "&Uuml;"],
		["ü", "&uuml;"],
		["ß", "&szlig;"],
		["é", "&eacute;"]
	];

	for (i = 0; i < transformations.length; i++) {
		str = str.replace(
			new RegExp(transformations[i][0], "g"),
			transformations[i][1]
		);
	}
	return str;
}

function new_upd_basket(idx,imenge)
{
	var cmd;
	var res;
	var xhr;
	var aerg;
	cmd = g_host + 'update_basket.inc.php?idx=' + idx + '&menge=' + imenge;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				aerg = gen_array(res);
				if(aerg[0][0] == 0) {
					update_mini_basket();
					self.location.replace('index.php?page=basket');
					return true;
				} else {
					if(aerg[0][0] == -2) {
						document.getElementById('qty_' + idx).value = aerg[0][2];
						alert(aerg[0][1]);
					} else {
						alert(aerg[0][1]);
					} 
					return false;
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return false;
}

function extd_search_itemsnew(spattern)
{
	var cmd;
	var param;
	var res;
	g_msg_class = 'note-msg';
	g_msg_text = get_lngtext('LangTagNoSearchResults');
	g_aItems = new Array();
	cmd = g_host + 'do_extendedsearchnew.inc.php';
	param = 'search=' + spattern;
	xhr = gen_req();
	xhr.open("GET", cmd+'?'+param, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				g_aItemIds = JSON.parse(res);
				g_numitems = g_aItemIds.length;
				show_pgroupnew(0);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function notepad()
{
	var cmd;
	var res;
	var xhr;
	g_view = 'list';
	g_aItems = new Array();
	g_msg_class = 'note-msg';
	g_msg_text = get_lngtext('LangTagNoArticlesOnNotepad');
	cmd = g_host + 'do_notepad.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				g_aItems = JSON.parse(res);
				g_numitems = g_aItems.length;
				show_pgroup(0);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function wishlist(cid)
{
	var cmd;
	var param;
	var res;
	var xhr;
	g_view = 'list';
	g_aItems = new Array();
	g_msg_class = 'note-msg';
	g_msg_text = get_lngtext('LangTagNoArticlesInWishList');
	cmd = g_host + 'do_wishlist.inc.php?cid=' + cid;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				g_aItems = JSON.parse(res);
				g_numitems = g_aItems.length;
				show_pgroup(0);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function round(wert, dez) 
{
	wert = parseFloat(wert);
	if (!wert) return 0;

	dez = parseInt(dez);
	if (!dez) dez=0;

	var umrechnungsfaktor = Math.pow(10,dez);

	return Math.round(wert * umrechnungsfaktor) / umrechnungsfaktor;
}

function set_cookie(obj)
{
	var cName = obj.name;
	var cWert = obj.value;
	var cType = obj.type;
	var ablauf = new Date();
	var einjahr = ablauf.getTime() + (365 * 24 * 60 * 60 * 1000);
	ablauf.setTime(einjahr);
	document.cookie = cName + "=" + decodeURIComponent(cWert) + "; expires=" + ablauf;
	return;
}

function delete_cookies()
{
	var aCookie = new Array();
	var aLine;
	var cookielen = 0;
	var c;
	var cName = '';
	if(document.cookie)
	{
		aCookie = document.cookie.split(';');
		cookielen = aCookie.length;
		for(c = 0; c < cookielen; c++)
		{
			aLine  = new Array();
			aLine = aCookie[c].split("=");
			cName = myTrim(aLine[0]);
			if(cName != 'PHPSESSID')
			{
				document.cookie = cName + "=; expires=-1";
			}
		}
	}
	return;
}

function myTrim(x) {
	return x.replace(/^\s+|\s+$/gm,'');
}

function formatMoney(num) {
	var p = num.toFixed(2).split(".");
	return p[0].split("").reverse().reduce(function(acc, num, i, orig) {
		return  num + (i && !(i % 3) ? "." : "") + acc;
	}, "") + "," + p[1];
}

function freedownload(cFile) {
	var cmd;
	var erg;
	if(cFile)
	{
		cmd = g_host + 'freedownload.inc.php?cfile=' + cFile;
		self.location.href = cmd;
	}
	return;
}

function place_basket()
{
	if(isPhone)
	{
		var basketfunc = '{GSSE_FUNC_BASKETNEW|mobile}';
	}
	else
	{
		var basketfunc = '{GSSE_FUNC_BASKETNEW|default}';
	}
	document.getElementById('gs_basket').innerHTML = basketfunc;
	return;
}

function gs_test(e)
{
	alert(e.type);
	return;
}

function set_section()
{
	if(document.getElementById('pp_state').value != '' && document.getElementById('pp_token').value != '')
	{
		if(document.getElementById('pp_state').value == 'ok')
		{
			checkout.update_pinfo();
			if(g_valMailIsSend == 1) {
				checkout.gotoSection('emailvalidate', true);
					$('body,html').animate({
						scrollTop: 470
					}, 800);
			} else {
				checkout.gotoSection('summary', true);
				jQuery('body,html').animate({
					scrollTop: 470
				}, 800);
			}
		}
	}
	if(document.getElementById('sp_state').value != '')
	{
		if(document.getElementById('sp_state').value == 'ok')
		{
			checkout.update_pinfo();
			if(g_valMailIsSend == 1) {
				checkout.gotoSection('emailvalidate', true);
					$('body,html').animate({
						scrollTop: 470
					}, 800);
			} else {
				checkout.gotoSection('summary', true);
				jQuery('body,html').animate({
					scrollTop: 1370
				}, 800);
			}
		}
	}
	return;
}

function get_cookie(cpattern)
{
	var val = '';
	var aCookie = new Array();
	var aLine;
	var cookielen = 0;
	var c;
	var cName = '';
	if(document.cookie)
	{
		aCookie = document.cookie.split(';');
		cookielen = aCookie.length;
		for(c = 0; c < cookielen; c++)
		{
			aLine  = new Array();
			aLine = aCookie[c].split("=");
			cName = myTrim(aLine[0]);
			if(cName == cpattern)
			{
				val = myTrim(aLine[1]);
				break;
			}
		}
	}
	return val;
}

function itemcomments()
{
	var cmd;
	var res;
	var xhr;
	g_view = 'list';
	g_aItems = new Array();
	g_msg_class = 'note-msg';
	g_msg_text = get_lngtext('LangTagNoRatings');
	cmd = g_host + 'do_itemcomments.inc.php';
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				g_aItems = JSON.parse(res);
				g_numitems = g_aItems.length;
				show_pgroup(0);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function set_continue_button(payment_internalname)
{
	if(payment_internalname == 'PaymentPayPal')
	{
		document.getElementById('gs_order_cont_span').innerHTML = get_lngtext('LangTagOrderPayPal');
		document.getElementById('gs_order_cont_button').value = '';
		document.getElementById('gs_order_cont_button').title = get_lngtext('LangTagOrderPayPal');
		document.getElementById('gs_order_cont_button').className = 'pp_button btn-continue';
	}
	else if(payment_internalname == 'PaymentWorldPay')
	{
		document.getElementById('gs_order_cont_span').innerHTML = get_lngtext('LangTagOrderWorldPay');
		document.getElementById('gs_order_cont_button').value = get_lngtext('LangTagOrderWorldPay');
		document.getElementById('gs_order_cont_button').title = get_lngtext('LangTagOrderWorldPay');
		document.getElementById('gs_order_cont_button').className = 'wp_button btn-continue'; 
	}
	else if(payment_internalname == 'PaymentSofort')
	{
		document.getElementById('gs_order_cont_span').innerHTML = get_lngtext('LangTagOrderSofortPay');
		document.getElementById('gs_order_cont_button').value = get_lngtext('LangTagOrderSofortPay');
		document.getElementById('gs_order_cont_button').title = get_lngtext('LangTagOrderSofortPay');
		document.getElementById('gs_order_cont_button').className = 'sofort_button btn-continue';
	}
	else if(payment_internalname == 'PaymentPaymorrow')
	{
		document.getElementById('gs_order_cont_span').innerHTML = get_lngtext('LangTagOrderPaymorrowPay');
		document.getElementById('gs_order_cont_button').value = get_lngtext('LangTagOrderPaymorrowPay');
		document.getElementById('gs_order_cont_button').title = get_lngtext('LangTagOrderPaymorrowPay');
		document.getElementById('gs_order_cont_button').className = 'paymorrow_button btn-continue';
	}
	else
	{
		document.getElementById('gs_order_cont_span').innerHTML = get_lngtext('LangTagOrderContinue');
		document.getElementById('gs_order_cont_button').value = get_lngtext('LangTagOrderContinue');
		document.getElementById('gs_order_cont_button').title = get_lngtext('LangTagOrderContinue');
		document.getElementById('gs_order_cont_button').className = 'button btn-continue';
	}
}

function set_userdata()
{
	var xhr;
	var cmd;
	setcmd = g_host + 'set_userdata.inc.php';
	params = 'privorbusiness=' + document.getElementById(get_lngtext('LangTagFNFieldCompanyOrPrivate')).options[document.getElementById(get_lngtext('LangTagFNFieldCompanyOrPrivate')).options.selectedIndex].value + 
	'&company='  + document.getElementById(get_lngtext('LangTagFNFieldCompany')).value +
	'&cusnumber=' + document.getElementById(get_lngtext('LangTagFNFieldCustomerNR')).value + 
	'&firmvatid=' + document.getElementById(get_lngtext('LangTagFNFieldFirmVATId')).value + 
	'&mrormrs=' + document.getElementById(get_lngtext('LangTagFieldFormToAddress')).options[document.getElementById(get_lngtext('LangTagFieldFormToAddress')).options.selectedIndex].value +
	'&firstname=' + encodeURI(document.getElementById(get_lngtext('LangTagFieldFirstName')).value) + 
	'&lastname=' + encodeURI(document.getElementById(get_lngtext('LangTagFieldLastName')).value) +  
	'&address=' + encodeURI(document.getElementById(get_lngtext('LangTagFNFieldAddress')).value) +
	'&address2=' + encodeURI(document.getElementById(get_lngtext('LangTagFNFieldAddress2')).value) +  
	'&city=' + encodeURI(document.getElementById(get_lngtext('LangTagFieldCity')).value) + 
	'&zip=' + document.getElementById(get_lngtext('LangTagFieldZipCode')).value +
	'&state=' + encodeURI(document.getElementById(get_lngtext('LangTagFieldState')).options[document.getElementById(get_lngtext('LangTagFieldState')).options.selectedIndex].innerHTML) +
	'&email=' + encodeURI(document.getElementById('email').value) +
	'&phone=' + document.getElementById(get_lngtext('LangTagFNFieldPhone')).value +
	'&fax=' + document.getElementById(get_lngtext('LangTagFNFieldFax')).value +
	'&mobil=' + document.getElementById(get_lngtext('LangTagFNFieldMobil')).value +
	'&birth=' + document.getElementById(get_lngtext('LangTagFNFieldGeburtsdatum')).value +
	'&actionkey=' + encodeURI(document.getElementById(get_lngtext('LangTagFNFieldAktKey')).value) + 
	'&emailformat=' + document.getElementById(get_lngtext('LangTagFNFieldEmailFormat')).options[document.getElementById(get_lngtext('LangTagFNFieldEmailFormat')).options.selectedIndex].value; 
	
	if(document.getElementById(get_lngtext('LangTagFNTermsAndCondNewsletter')).checked == true)
	{
		params = params + '&accepttermsancond=' + encodeURI(document.getElementById('accepttermsancond').value);
	}
	
	if(document.getElementById(get_lngtext('LangTagFNTermsAndCondNewsletter')).checked == true)
	{
		params = params + '&wantnewsletter=' + encodeURI(document.getElementById(get_lngtext('LangTagFNTermsAndCondNewsletter')).value);
	}
	
	if(document.getElementById('acceptror').checked == true)
	{
		params = params + '&acceptror=' + encodeURI(document.getElementById('acceptror').value);
	}
	
	for(h = 1; h <= 5; h++)
	{
		if(get_setting('cb_activ' + h + '_Checked') == 'True')
		{
			fieldname = get_setting('ed_name' + h + '_Text');
			fieldformfriendly = formfriendly(fieldname);
			if(document.getElementById(fieldformfriendly))
			{
				params = params + '&' + fieldformfriendly + '=' + encodeURI(document.getElementById(fieldformfriendly).value);
			}
			
		}
	}
	
	xhr = gen_req();
	xhr.open("POST", setcmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(params);
	return;
}

function get_sessvar(cname)
{
	//Aus Cookie ermitteln. Dafür vorgesehene Variablen müssen dann zusätzlich in $_COOKIE gepseichert und gepflegt werden!
	var value = "; " + document.cookie;
	var parts = value.split("; " + cname + "=");
	if (parts.length == 2) return parts.pop().split(";").shift();
}

function set_sessvar(cname,xvalue)
{
	var xhr;
	var cmd;
	cmd = g_host + 'set_sessvar.inc.php?cname=' + cname + '&xvalue=' + xvalue;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
}

function chg_slc(idx) {
	var res;
	var xhr;
	var cmd;
	cmd = g_host + 'chg_slc.inc.php?idx=' + idx;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				if(res == 1) {
					self.location.replace('index.php?page=main');
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function showhide_old_accountinfo(iMode) {
	if(iMode == 1) {
		document.getElementById('old_account_form').className = 'form-list';
		document.getElementById('but_showoldaccountinfo').className = 'gs-no-display';
	} else {
		document.getElementById('old_account_form').className = 'gs-no-display';
		document.getElementById('but_showoldaccountinfo').className = 'input-box';
	}
	return;
}

function calc_iban(oAccountNoField,oBankCodeField) {
	var res;
	var xhr, xhr2;
	var cmd, cmd2;
	var cAccountNo = document.getElementById('old_account_number').value;
	var cBankCode = document.getElementById('old_bank_code').value;
	var bic = "";
	
	if(cAccountNo == "") {
		alert(get_lngtext('LangTagPlsEnterAccountNo'));
		document.getElementById('old_account_number').focus();
		return;
	}
	
	if(cBankCode == "") {
		alert(get_lngtext('LangTagPlsEnterBankcode'));
		document.getElementById('old_bank_code').focus();
		return;
	}
	
	cmd = g_host + 'calc_iban.inc.php?account=' + cAccountNo + '&bankcode=' + cBankCode;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				if(res) {
					document.getElementById(oAccountNoField).value = res;
					cmd2 = g_host + 'get_bic_by_bankcode.inc.php?bankcode=' + cBankCode + '&countryid=' + g_cntID;
					xhr2 = gen_req();
					xhr2.open("GET", cmd2, true);
					xhr2.onload = function (e) {
						if (xhr2.readyState === 4) {
							if (xhr2.status === 200) {
								bic = xhr2.responseText;
								if(bic != "") {
									document.getElementById(oBankCodeField).value = bic;
								}
							} else {
								console.error(xhr2.statusText);
							}
						}
					};
					xhr2.onerror = function (e) {
						console.error(xhr2.statusText);
					};
					xhr2.send(null);
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	
	return;
}

function getBicByIBAN(iban,oBIC) {
	var blz;
	var bic;
	var cmd, cmd2;
	var res;
	var xhr, xhr2;
	
	cmd = g_host + 'check_iban.inc.php?iban=' + iban;
	xhr = gen_req();
	xhr.open("GET", cmd, true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				if(res) {
					blz = iban.substr(4, 8);
					cmd2 = g_host + 'get_bic_by_bankcode.inc.php?bankcode=' + blz + '&countryid=' + g_cntID;
					xhr2 = gen_req();
					xhr2.open("GET", cmd2, true);
					xhr2.onload = function (e) {
						if (xhr2.readyState === 4) {
							if (xhr2.status === 200) {
								bic = xhr2.responseText;
								if(bic != "") {
									if(document.getElementById(oBIC).value == "") {
										document.getElementById(oBIC).value = bic;
									}
								}
							} else {
								console.error(xhr2.statusText);
							}
						}
					};
					xhr2.onerror = function (e) {
						console.error(xhr2.statusText);
					};
					xhr2.send(null);
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function resend_pwd() {
	var pwdEmail = document.getElementById('user_mail').value;
	var res;
	var xhr;
	var cmd;
	var aerg;
	if(pwdEmail != "") {
		cmd = g_host + 'resend_pwd.inc.php?user_mail=' + pwdEmail;
		xhr = gen_req();
		xhr.open("GET", cmd, true);
		xhr.onload = function (e) {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					res = xhr.responseText;
					switch(parseInt(res)) {
						case 1:
							jQuery('#fd_msg').html(get_lngtext('LangTagResentCustPasswd'));
							if(jQuery('#fd_msg').hasClass('no-display')) {
								jQuery('#fd_msg').removeClass('no-display');
							}
							if(jQuery('#fd_msg').hasClass('err_box')) {
								jQuery('#fd_msg').removeClass('err_box');
							}
							if(!jQuery('#fd_msg').hasClass('ok_box')) {
								jQuery('#fd_msg').addClass('ok_box');
							}
							break;
						case 2:
							jQuery('#fd_msg').html(get_lngtext('LangTagUnknownUser'));
							if(jQuery('#fd_msg').hasClass('no-display')) {
								jQuery('#fd_msg').removeClass('no-display');
							}
							if(jQuery('#fd_msg').hasClass('ok_box')) {
								jQuery('#fd_msg').removeClass('ok_box');
							}
							if(!jQuery('#fd_msg').hasClass('err_box')) {
								jQuery('#fd_msg').addClass('err_box');
							}
							break;
						case 3:
							jQuery('#fd_msg').html(get_lngtext('LangTagErrorMissingRootPathFile'));
							if(jQuery('#fd_msg').hasClass('no-display')) {
								jQuery('#fd_msg').removeClass('no-display');
							}
							if(jQuery('#fd_msg').hasClass('ok_box')) {
								jQuery('#fd_msg').removeClass('ok_box');
							}
							if(!jQuery('#fd_msg').hasClass('err_box')) {
								jQuery('#fd_msg').addClass('err_box');
							}
							break;
						default:
							jQuery('#fd_msg').html(get_lngtext('LangTagTextFailed'));
							if(jQuery('#fd_msg').hasClass('no-display')) {
								jQuery('#fd_msg').removeClass('no-display');
							}
							if(jQuery('#fd_msg').hasClass('ok_box')) {
								jQuery('#fd_msg').removeClass('ok_box');
							}
							if(!jQuery('#fd_msg').hasClass('err_box')) {
								jQuery('#fd_msg').addClass('err_box');
							}
							break;
					}
				} else {
					console.error(xhr.statusText);
				}
			}
		};
		xhr.onerror = function (e) {
			console.error(xhr.statusText);
		};
		xhr.send(null);
	}
	return;
}

function simulateClickByKey(e,iKey,cID) {
	var kcode;
	if(g_ie) {
		kcode = e.keyCode;
	} else {
		kcode = e.which;
	}
	
	if(kcode == iKey) {
		if(document.getElementById(cID)) {
			document.getElementById(cID).click();
		}
	}
	return;
}

function sendValMail(cMail,resend) {
	var from = get_setting('edShopEmail_Text');
	var to = cMail;
	var cShopName = get_setting('edShopName_Text');
	var fTitle = get_lngtext('LangTagFNFieldFormToAddress');
	var fFirstname = get_lngtext('LangTagFNFieldFirstName');
	var fName = get_lngtext('LangTagFNFieldLastName');
	var subject = cShopName + ' - ' + get_lngtext('LangTagValMailSubject');
	var cTitle = document.getElementById(fTitle).options[document.getElementById(fTitle).options.selectedIndex].value;
	var cFirstname = document.getElementById(fFirstname).value;
	var cName = document.getElementById(fName).value;
	var cDear = get_lngtext('LangTagDear');
	var cText1 = get_lngtext('LangTagValMailText1');
	var cText2 = get_lngtext('LangTagValMailText2');
	var cGreet = get_lngtext('LangTagGreetingsInquiry');
	var cShopAddress = get_setting('edShopSlogan_Text') + '\n' + 
							 get_setting('edShopCompany_Text') + '\n' + 
							 get_setting('edShopStreet_Text') + '\n' + 
							 get_setting('edShopZipCode_Text') + ' ' + get_setting('edShopCity_Text') + '\n' + 
							 get_setting('edShopCountry_Text') + '\n' + 
							 'Tel.: ' + get_setting('edShopTelephone_Text');
	var message;
	
	/*cGreet = '';*/
	
	message = cDear + ' ' + cTitle + ' ' + cFirstname + ' ' + cName + ',\n\n' +
				 cText1 + '\n' +
				 get_sessvar('valcode') + '\n' +
				 cText2 + '\n\n' +
				 cGreet + '\n' +
				 cShopName + '\n' +
				 cShopAddress;
	
	if(resend == 1) {
		g_valMailIsSend = 0;
	}
	
	if(g_valMailIsSend == 0) {
		sendMail(from, to, subject, message, '', '');
		set_sessvar('valmailsend',1);
		g_valMailIsSend = 1;
	}
}

function get_billingperiodfromid(iKey,lPer,lPlural,lAdj) {
	var s = '';
	var period = '';
	var lngtime;
	if(lPlural) { s = 's'; }
	if(!lAdj) {
		switch(iKey) {
			case "1":
				lngtime = "LangTagRentalDay"+s;
				break;
			case "2":
				lngtime = "LangTagRentalWeek"+s;
				break;
			case "3":
				lngtime = "LangTagRentalMonth"+s;
				break;
			case "4":
				lngtime = "LangTagRentalYear"+s;
				break;
			default:
				lngtime = "LangTagUnknownValue"+s;
				break;
		}
	} else {
		switch(iKey) {
			case "1":
				lngtime = "LangTagRentalDaily";
				break;
			case "2":
				lngtime = "LangTagRentalWeekly";
				break;
			case "3":
				lngtime = "LangTagRentalMonthly";
				break;
			case "4":
				lngtime = "LangTagRentalYearly";
				break;
			default:
				lngtime = "LangTagUnknownValue";
				break;
		}
	}
	period = get_lngtext(lngtime);
	if(!lAdj && !lPlural && lPer) {
		period = get_lngtext('LangTagPerSomething')+" "+period;
	}
	return period;
}

function setViewCount() {
	var i;
	var count;
	var opt;
	/*<option value="15">15 {GSSE_LANG_LangTagPerPage}</option>
						<option value="25">25 {GSSE_LANG_LangTagPerPage}</option>
						<option value="35">35 {GSSE_LANG_LangTagPerPage}</option>
	g_perPage*/
	if(document.getElementById('gs-view-count').length < 4){
		for(i = 1; i <= 4; i++) {
			count = i * g_count;
			opt = new Option(count + ' ' + g_perPage, count, false, false);
			document.getElementById('gs-view-count').options[document.getElementById('gs-view-count').length] = opt;
		}
	}
}

function changeQty(increase,obj) {
	var qty = parseInt($(obj).value);
	if ( !isNaN(qty) ) {
		qty = increase ? qty+1 : (qty>1 ? qty-1 : 1);
		$(obj).value = qty;
	}
	else{
		$(obj).value = 1;
	}
	if(document.getElementById('errbox')) {
		document.getElementById('errbox').className='no-display';
	}
}

function changeQtyBasket(id,increase,basket_idx) {
	var qty = parseInt($(id).value);
	var old_qty = qty;
	if ( !isNaN(qty) ) {
		qty = increase ? qty+1 : (qty>1 ? qty-1 : 1);
		if(qty != old_qty) {
			if(new_upd_basket(basket_idx,qty)) {
				$(id).value = qty;
			}
		}
	}
}

function changeQtyEnter(obj,basket_idx) {
	var qty = obj.value;
	if(new_upd_basket(basket_idx,qty)) {
		return true;
	} else {
		return false;
	}
}

function set_desktop() {
	var cmd;
	var xhr;
	var param;
	cmd = g_host + 'set_desktop.inc.php';
	param = 's_width='+g_s_breite+'&s_height='+g_s_hoehe+'&w_width='+g_w_breite+'&w_height='+g_w_hoehe+'&is_mobile='+is_mobile+'&is_phone='+is_phone;
	xhr = gen_req();
	xhr.open("GET", cmd+'?'+param, true);
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(null);
	return;
}

function conv_date( datum, dir ) {
	if( dir == "E") {
		ret_dat = datum.substr( 6, 4 ) + "-" + datum.substr( 3, 2 ) + "-" + datum.substr( 0, 2 );
	} else {
		ret_dat = datum.substr( 8, 2 ) + "." + datum.substr( 5, 2 ) + "." + datum.substr( 0, 4 );
	}
	return ret_dat;
}

function processCheckout(nextStep,currStep, mustvalidate='False', params=''){
	var $=jQuery;
	var cmd;
	var res;
	var xhr,xhr1,xhr2;
	var aerg;
	var Step;
	var htmlForm;
	var controls;
	var login;
	var userExist;
	
	if(document.getElementById('top_cart')){
		document.getElementById('top_cart').style.display = 'none';
	}
	switch(nextStep)
	{
		case 1:
			Step = "cardstepone";
			nStep(Step, params);
			break;
		case 2:
			login = true;
			if((mustvalidate == 'True') && (currStep == 1)){
				var valid = new Validation('stepone');
				var result = valid.validate();
				if(result == false){
					return;
				} 
				cmd = g_host + 'get_customerlogin.inc.php';
				xhr = gen_req();
				xhr.open("POST", cmd, true);
				xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
				xhr.onload = function (e) {
					if (xhr.readyState === 4) {
						if (xhr.status === 200) {
							res = xhr.responseText;
							if (res == '1') {
								showcustmenu();
								Step = "cardsteptwo";
								nStep(Step, '');
							} else {
								msg = get_lngtext('LangTagTextLoginError');
								//Login fehlgeschlagen.
								//self.location.replace('index.php?page=buy');//return false;
								alert(msg);
							}
						} else {
							console.error(xhr.statusText);
						}
					}
				};
				xhr.onerror = function (e) {
					console.error(xhr.statusText);
				};
				xhr.send('cemail=' + document.getElementById('cust_email').value + '&cpass=' + document.getElementById('cust_pass').value);
			} else {
				Step = "cardsteptwo";
				nStep(Step, '');
			}
			break;
		case 3:
			if(mustvalidate == 'True'){
				var valid = new Validation('steptwo');
				var result = valid.validate();
				if(result == false){
					return;
				}
				if(document.getElementById('cust_email_valid')){
					if(document.getElementById('cust_email').value != document.getElementById('cust_email_valid').value){
						alert(get_lngtext('LangTagEmailRepeatNotEqual'));
						return;
					}
					if(document.getElementById('cust_pass').value != document.getElementById('cust_pass_valid').value){
						alert(get_lngtext('LangTagPasswordsNotIdentical'));
						return;
					}
				}
			}
			if(currStep == 2){
				params = '&PrivatOderFirma=' + document.getElementById('PrivatOderFirma').options.selectedIndex;
				params = params + '&mrormrs=' + document.getElementById('mrormrs').options.selectedIndex;
                params = params + '&mrormrsText=' + document.getElementById('mrormrs').options[document.getElementById('mrormrs').options.selectedIndex].innerHTML;
				params = params + '&firstname=' + document.getElementById('firstname').value;
				params = params + '&lastname=' + document.getElementById('lastname').value;
				params = params + '&street=' + document.getElementById('street').value;
				params = params + '&street2=' + document.getElementById('street2').value;
				params = params + '&zip=' + document.getElementById('zip').value;
				params = params + '&city=' + document.getElementById('city').value;
				params = params + '&areaID=' + document.getElementById('areaID').options.selectedIndex;
                params = params + '&areaName=' + document.getElementById('areaID').options[document.getElementById('areaID').options.selectedIndex].innerHTML;
                params = params + '&stateISO=' + document.getElementById('areaID').options[document.getElementById('areaID').options.selectedIndex].value;
                params = params + '&cust_email=' + document.getElementById('cust_email').value;
                if(document.getElementById('EmailFormat')) {
					params = params + '&EmailFormat=' + document.getElementById('EmailFormat').options[document.getElementById('EmailFormat').options.selectedIndex].value;
				}
				if(document.getElementById('rememberme').checked){
                	params = params + '&rememberme=Y';
				}	
				if(document.getElementById('newsletterinput')){
					if(document.getElementById('newsletterinput').checked){
						params = params + '&newsletterinput=Y';
					}
				}
					
                if(document.getElementById('company')){
					params = params + '&company=' + document.getElementById('company').value;
					params = params + '&firmVATId=' + document.getElementById('firmVATId').value;
				}
				if(document.getElementById('cust_email_valid')){
					params = params + '&newcustomer=true';
					params = params + '&cust_pass=' + document.getElementById('cust_pass').value;
				}
				//Additionalfields
				for(h = 1; h <= 5; h++)
				{
					if(get_setting('cb_activ' + h + '_Checked') == 'True')
					{
						fieldname = get_setting('ed_name' + h + '_Text');
						fieldformfriendly = formfriendly(fieldname);
						if(document.getElementById(fieldformfriendly))
						{
							params = params + '&' + fieldformfriendly + '=' + encodeURI(document.getElementById(fieldformfriendly).value);
						}
						
					}
				}
				if(document.getElementById('cusPhone')){
					params = params + '&cusPhone=' + document.getElementById('cusPhone').value;
				}
				if(document.getElementById('cusFax')){
					params = params + '&cusFax=' + document.getElementById('cusFax').value;
				}
				if(document.getElementById('cusMobil')){
					params = params + '&cusMobil=' + document.getElementById('cusMobil').value;
				}
				if(document.getElementById('cusBirthday')){
					params = params + '&cusBirthday=' + document.getElementById('cusBirthday').value;
				}
				if(document.getElementById('cusAktKey')){
					params = params + '&cusAktKey=' + document.getElementById('cusAktKey').value;
				}
				if(document.getElementById('cusNextMessage')){
					params = params + '&cusNextMessage=' + document.getElementById('cusNextMessage').value 
				}
				if(document.getElementById('UseShippingAddress').checked){
					params = params + '&UseShippingAddress=Y';
					params = params + '&delivercompany=' + document.getElementById('delivercompany').value;
					params = params + '&delivermrormrs=' + document.getElementById('delivermrormrs').options.selectedIndex;
					params = params + '&deliverfirstname=' + document.getElementById('deliverfirstname').value;
					params = params + '&deliverlastname=' + document.getElementById('deliverlastname').value;
					params = params + '&deliverstreet=' + document.getElementById('deliverstreet').value;
					params = params + '&deliverstreet2=' + document.getElementById('deliverstreet2').value;
					params = params + '&deliverzip=' + document.getElementById('deliverzip').value;
					params = params + '&delivercity=' + document.getElementById('delivercity').value;
				}
			}
			Step = "cardstepthree";
			nStep(Step, params);
			break;
		case 4:
			if(currStep == 3){
				if(mustvalidate == 'True'){
					var valid = new Validation('stepthree');
					var result = valid.validate();
					if(result == false){
						return;
					}
				}	
				form = document.getElementById('paymentfields');
				controls = form.elements;
				for (var i=0, iLen=controls.length; i<iLen; i++) {
					if(controls[i].checked != ""){
						payment=controls[i].value;
					}	   
				}
				form = document.getElementById('shipmentfields');
				controls = form.elements;
				for (var i=0, iLen=controls.length; i<iLen; i++) {
					if(controls[i].checked != ""){
						shipment=controls[i].value;
					}	   
				}
				params = '&payment=' + payment;
				params = params + '&shipment=' + shipment;
				if(document.getElementById('financialinstitution')){
					params = params + '&financialinstitution=' + document.getElementById('financialinstitution').value;
					params = params + '&iban=' + document.getElementById('iban').value;
					params = params + '&bic=' + document.getElementById('bic').value;
					params = params + '&AccountHolderFirstName=' + document.getElementById('AccountHolderFirstName').value;
					params = params + '&AccountHolderLastName=' + document.getElementById('AccountHolderLastName').value;
					params = params + '&old_account_number=' + document.getElementById('old_account_number').value;
					params = params + '&old_bank_code=' + document.getElementById('old_bank_code').value;
				}
			}
			Step = "cardstepfour";
			break;	
		default:
			Step = "cardstepone";
			nStep(Step, params);
			break;
	}
	
	if(Step == "cardstepfour"){
		if((payment.indexOf('PaymentPayPal') != -1 && get_setting('rbUsePPClassic_Checked') == 'True')
			|| (payment.indexOf('PaymentPaymorrow') != -1 && get_setting('cbUsePaymorrow_Checked') == 'True')){
			if (document.getElementById('sb_busy_screen')) {
	            if (document.getElementById("buy_wrapper")) {
	                totheight = document.getElementById("buy_wrapper").scrollHeight;
	            }
	            document.getElementById('sb_busy_screen').className = 'sb_progress';
	            document.getElementById('sb_busy_screen').style.height = totheight + 'px';
	            document.getElementById('sb_busy_screen_inner').className = 'sb_progress_inner';
	        }
			cmd = g_host + 'get_nextstep.inc.php';
			xhr1 = gen_req();
			xhr1.open("POST", cmd, true);
			xhr1.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
			xhr1.onload = function (e) {
				if (xhr1.readyState === 4) {
					if (xhr1.status === 200) {
						res = jQuery.parseJSON(xhr1.responseText);
						if(res.indexOf('Location') != -1){
							url = res.replace('Location: ','');
							window.location.replace(url);
						} else {
							alert(res);
						}
					} else {
						console.error(xhr1.statusText);
					}
				}
			};
			xhr1.onerror = function (e) {
				console.error(xhr1.statusText);
			};
			xhr1.send('step=' + Step + params);
		} else {
			cmd = g_host + 'get_nextstep.inc.php';
			xhr2 = gen_req();
			xhr2.open("POST", cmd, true);
			xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
			//xhr2.setRequestHeader("Content-length", params.length);
			xhr2.withCredentials = true;
			xhr2.onload = function (e) {
				if (xhr2.readyState === 4) {
					if (xhr2.status === 200) {
						res = xhr2.responseText;
						htmlForm = jQuery.parseJSON(res);               
						document.getElementById('checkoutcard').innerHTML = htmlForm;
						if(Step == "cardstepfour"){
							if(document.getElementById('SepaEinverstandenCheck')){
								document.getElementById('SepaEinverstandenCheck').value = JSON.stringify(document.getElementById('SepaDirectDebit').innerHTML + '<br/><br/>' + get_lngtext('LangTagSepaMandatIssue'));
							}
							if(payment.indexOf('PaymentPayPal') != -1 && get_setting('rbUsePPPlus_Checked') == 'True'){
								showPPPform();
							}
		                	prepareForm(payment,shipment);
		                }
						/* Wenn die Werte existieren, dann das Form ausfühlen.*/
						fillMyForm(Step);
					} else {
						console.error(xhr2.statusText);
					}
				}
			};
			xhr2.onerror = function (e) {
				console.error(xhr2.statusText);
			};
			xhr2.send('step=' + Step + params);
		} 
	} else {
		/*cmd = g_host + 'get_nextstep.inc.php';
		xhr2 = gen_req();
		xhr2.open("POST", cmd, true);
		xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
		//xhr2.setRequestHeader("Content-length", params.length);
		xhr2.withCredentials = true;
		xhr2.onload = function (e) {
			if (xhr2.readyState === 4) {
				if (xhr2.status === 200) {
					res = xhr2.responseText;
					htmlForm = jQuery.parseJSON(res);               
					document.getElementById('checkoutcard').innerHTML = htmlForm;
					if(Step == "cardstepfour"){
						if(document.getElementById('SepaEinverstandenCheck')){
							document.getElementById('SepaEinverstandenCheck').value = JSON.stringify(document.getElementById('SepaDirectDebit').innerHTML + '<br/><br/>' + get_lngtext('LangTagSepaMandatIssue'));
						}
	                	prepareForm(payment,shipment);
	                }
					// Wenn die Werte existieren, dann das Form ausfühlen.
					fillMyForm(Step);
				} else {
					console.error(xhr2.statusText);
				}
			}
		};
		xhr2.onerror = function (e) {
			console.error(xhr2.statusText);
		};
		xhr2.send('step=' + Step + params);*/
	}
	return;
}

function nStep(Step, params){
	cmd = g_host + 'get_nextstep.inc.php';
	xhr2 = gen_req();
	xhr2.open("POST", cmd, true);
	xhr2.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	//xhr2.setRequestHeader("Content-length", params.length);
	xhr2.withCredentials = true;
	xhr2.onload = function (e) {
		if (xhr2.readyState === 4) {
			if (xhr2.status === 200) {
				res = xhr2.responseText;
				if (res == 'redirect'){
					window.location.replace('index.php?page=main');
				}
				htmlForm = jQuery.parseJSON(res);               
				document.getElementById('checkoutcard').innerHTML = htmlForm;
				if(Step == "cardstepfour"){
					if(document.getElementById('SepaEinverstandenCheck')){
						document.getElementById('SepaEinverstandenCheck').value = JSON.stringify(document.getElementById('SepaDirectDebit').innerHTML + '<br/><br/>' + get_lngtext('LangTagSepaMandatIssue'));
					}
					prepareForm(payment,shipment);
				}
				/* Wenn die Werte existieren, dann das Form ausfühlen.*/
				fillMyForm(Step);
			} else {
				console.error(xhr2.statusText);
			}
		}
	};
	xhr2.onerror = function (e) {
		console.error(xhr2.statusText);
	};
	xhr2.send('step=' + Step + params);
}

function fillMyForm(Step){
	var $=jQuery;
	var cmd;
	var res;
	var xhr, xhr1;
	var shipSet,paymSet;
		
	if(Step == 'cardstepone'){
		Step = 'cardsteptwo';
	}
	cmd = g_host + 'fillform.inc.php';
	params = 'step=' + Step;
	xhr = gen_req();
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.withCredentials = true;
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				res_array = jQuery.parseJSON(res);
				for (key in res_array) {				   
					if(document.getElementById(key)){					  
					   if (document.getElementById(key).type == "text" || document.getElementById(key).type == "email"){
						   document.getElementById(key).value = res_array[key];
					   }	
					   if (document.getElementById(key).type == "select-one"){
						   if(key == 'PrivatOderFirma' && res_array[key] == 1){
							   document.getElementById('fd_company').style.display = '';
							   document.getElementById('fd_vatid').style.display = '';
						   }
						   document.getElementById(key).options.selectedIndex = res_array[key];
					   }
					} else {
						if(key == 'customer'){
							for(k in res_array[key]){
								if(document.getElementById(k)){
									if(k == 'UseShippingAddress'){
										show_shippaddress();
									}
									if (document.getElementById(k).type == "text" || document.getElementById(k).type == "email"){
									   document.getElementById(k).value = res_array[key][k];
									}
									
									if (document.getElementById(k).type == "select-one"){
									   document.getElementById(k).options.selectedIndex = res_array[key][k];
									}
								}
							}
						}
						
						if(key == 'delivery'){
							shipSet = false;
							if(document.getElementById('shipmentfields')){
								iLen = document.getElementById('shipmentfields').elements.length;
								for (var i=0; i<iLen; i++) {
									if(document.getElementById('shipmentfields').elements[i].value == (res_array[key]['delivID'] + '|' + res_array[key]['delivName'])){
										document.getElementById('shipmentfields').elements[i].checked = "checked='checked'";
										radioToggle(shipmentfields,i);
										shipSet = true;
									} else {
										document.getElementById('shipmentfields').elements[i].checked = "";	
									}
								}
								if(!shipSet){
									document.getElementById('shipmentfields').elements[0].checked = "checked='checked'";
									radioToggle(shipmentfields,0);
								}
							}
						}
						if(key == 'payment'){
							paymSet = false;
							if(document.getElementById('paymentfields')){
								iLen = document.getElementById('paymentfields').elements.length;
								for (var i=0; i<iLen; i++) {
									if(document.getElementById('paymentfields').elements[i].value == (res_array[key]['paymInternalName'] + '|' + res_array[key]['paymName'] + '|' + res_array[key]['paymID'])){
										document.getElementById('paymentfields').elements[i].checked = "checked='checked'";
										radioToggle(paymentfields,i);
										paymSet = true;
									} else {
										document.getElementById('paymentfields').elements[i].checked = "";
									}
								}
								if(!paymSet){
									document.getElementById('paymentfields').elements[0].checked = "checked='checked'";
									radioToggle(paymentfields,0);
								}
							}
						}
					}
				}
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send(params);
	
	return;
}

function radioToggle(form,id){
	var controls = form.elements;
    var payment;
    var shipment;
    
	for (var i=0, iLen=controls.length; i<iLen; i++) {
		if(i != id){
			controls[i].checked = "";
		}		   
	}
	controls[id].checked="checked='checked'";
	
	if(form.id == "shipmentfields"){
		shipment = controls[id].value;
		cmd = g_host + 'setshipment.inc.php';
		params = 'shipment=' + shipment;
		xhr = gen_req();
    	xhr.open("POST", cmd, true);
    	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
    	xhr.withCredentials = true;
    	xhr.onload = function (e) {
    		if (xhr.readyState === 4) {
    			if (xhr.status === 200) {
    				res = xhr.responseText;
					basketpreview();
    			} else {
    				console.error(xhr.statusText);
    			}
    		}
    	};
    	xhr.onerror = function (e) {
    		console.error(xhr.statusText);
    	};
    	xhr.send(params);
	}
	
    if(controls[id].value.substr(0,7) == "Payment"){
        payment = controls[id].value;
        // Post to class.order.php
        cmd = g_host + 'setpayment.inc.php';
        params = 'paym=' + payment;
		payment = payment.substr(0,payment.indexOf('|'));
		if(payment == "PaymentCreditCard"){
			document.getElementById('creditform').style.display = '';
			document.getElementById('sepaform').style.display = 'none';
		}else if(payment == "PaymentDirectDebit"){
			document.getElementById('sepaform').style.display = '';
			document.getElementById('creditform').style.display = 'none';
		}else if(payment == "PaymentPayPal"){ 
			document.getElementById('creditform').style.display = 'none';
			document.getElementById('sepaform').style.display = 'none';
		}else{
			document.getElementById('sepaform').style.display = 'none';
			document.getElementById('creditform').style.display = 'none';
		}
    	xhr = gen_req();
    	xhr.open("POST", cmd, true);
    	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
    	xhr.withCredentials = true;
    	xhr.onload = function (e) {
    		if (xhr.readyState === 4) {
    			if (xhr.status === 200) {
    				res = xhr.responseText;
					basketpreview();
					
    			} else {
    				console.error(xhr.statusText);
    			}
    		}
    	};
    	xhr.onerror = function (e) {
    		console.error(xhr.statusText);
    	};
    	xhr.send(params);        
        
        
    }
}

function showcustmenu(){
	var cmd;
	var res;
	var xhr;
	var htmlForm;
	
	cmd = g_host + 'get_custmenu.inc.php';
	xhr = gen_req();
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				htmlForm = jQuery.parseJSON(res);
				document.getElementById('cust_menu').innerHTML = htmlForm;
				document.getElementById('gs-login-link').innerHTML = '<a href="index.php?page=customerlogout" title="' + get_lngtext('LangTagTextLogout') + '">' + get_lngtext('LangTagTextLogout') + '</a>';
                jQuery('.gs-login-link').unbind('click');
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send();
}

function chk_CustomerEmail(email){
	var cmd;
	var res;
	var xhr;
	var erg;
	
	cmd = g_host + 'chk_custemail.inc.php';
	xhr = gen_req();
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				if(res == 'user_exist'){
                    alert(get_lngtext('LangTagEmailAlreadyExist'));
                    document.getElementById('gs_order_cont_button').style.display = 'none';
                } else {
                    document.getElementById('gs_order_cont_button').style.display = '';
                }
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send('email=' + email);
}

function show_shippaddress(){
	if(jQuery('#useshipp').hasClass("checked")){
		document.getElementById('shippaddress').style.display = 'none';		
		document.getElementById('UseShippingAddress').checked = '';
	} else {
		document.getElementById('shippaddress').style.display = '';
		document.getElementById('UseShippingAddress').checked = 'checked';
	}
	jQuery('#useshipp').toggleClass("checked");
}

function checkToggle(spanID,inputID){
	if(jQuery('#'+spanID).hasClass("checked")){	
		document.getElementById(inputID).checked = '';
	} else {
		document.getElementById(inputID).checked = 'checked';
	}
	jQuery('#'+spanID).toggleClass("checked");
}

function prepareForm(payment,shipment){
    var paymText;
    var shipText;
    
  
}

function basketpreview(){
	var cmd;
	var res;
	var xhr;
	var erg;
	
	cmd = g_host + 'basketpreview.inc.php';
	xhr = gen_req();
	xhr.open("POST", cmd, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				res = xhr.responseText;
				document.getElementById('basket-preview').innerHTML = jQuery.parseJSON(res);
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {
		console.error(xhr.statusText);
	};
	xhr.send();
}

function showPPPform() {
	if(get_setting('rbUsePPClassic_Checked') == 'False'){
		document.getElementById('paypalplusform').style.display = '';
		if(get_setting('edPPPMode_Text') == 'live') {
			pppmode = 'live';
		} else {
			pppmode = 'sandbox';
		}
		cmd = g_host + 'ppplus_createpayment.inc.php';
		lDebug = false;
		if(lDebug) {
			self.location.replace(cmd+'?debug=1');
		} else {
			xhr1 = gen_req();
			xhr1.open("GET", cmd, true);
			xhr1.onload = function (e) {
				if (xhr1.readyState === 4) {
					if (xhr1.status === 200) {
						res = xhr1.responseText;
						aRes = JSON.parse(res);
						if(aRes['errno'] == 0) {
							lSuccess = true;
						} else {
							lSuccess = false;
							alert('PayPal-Plus-Meldung: ' + aRes['errmsg']);
						}
						if(lSuccess) {
							var ppp = PAYPAL.apps.PPP({
								"approvalUrl": aRes['result']['approvalurl'],
								"placeholder": "ppplus",
								"mode": pppmode,
								"country": "DE",
								"language": "de_DE"
							});
							document.getElementById('order').style.display = 'none';
						}
					} else {
						console.error(xhr1.statusText);
					}
				}
			};
			xhr1.onerror = function (e) {
				console.error(xhr1.statusText);
			};
			xhr1.send(null);
		}
	}
}

function changedisplay(elementid) {
  var x = document.getElementById(elementid);
  if (x.style.display === "none") {
	x.style.display = "block";
  } else {
	x.style.display = "none";
  }
} 