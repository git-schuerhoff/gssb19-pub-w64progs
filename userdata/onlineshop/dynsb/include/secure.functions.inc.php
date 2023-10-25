<?php
/*

    Encrypt-Library - secure.functions.inc.php
    Author: Raimund Kulikowski / GS Software Solutions GmbH
    
    (c) 2004-2005 GS Software Solutions GmbH
    
    this code is NOT open-source or freeware
    you are not allowed to use, copy or redistribute it in any form
 
*/

function getDecodeArray($name) {
    $dec = array(   'cusBank' => array(4007,1890,1032,838,644,2960,2102,2832,1974,454,2106,2172,2238,718,121,2696,513,3037,1777,1180,4016,2755,1495,1158,821,2071,2657,733,2243,2829,243,829,3925,3185,3108,3031,1368,2214,1474,3244,917,1101,2870,1467,64,247,2940,4047,2644,577,1021,1464,321,764,2131,325,1692,809,3099,1292,2919,450,3780,479),
                    'cusBLZ' => array(1355,993,3543,3583,1649,2612,403,2290,3514,3814,682,1906,2466,3027,3588,2302,322,654,987,651,984,324,657,153,426,486,759,756,2109,1999,160,1598,1236,5184,1648,3794,6485,3287,6111,4999),
                    'cusAccountNo' => array(470,3540,3178,2152,2973,361,1844,380,4011,1256,1414,2495,1066,2147,2565,1136,111,222,333,444,555,667,777,888,999,1111,2222,3333,4444,5555,6666,7777,8888,9999,1321,4654,7987,1651,4984,4324),
                    'cusAccountOwner' => array(1645,1400,935,26,1770,1525,3789,3284,3701,23,441,1782,1797,1811,1826,1178,1192,3717,2808,1237,1512,200,1801,2999,3860,702,977,2175,1124,1659,1531,1403),
                    'cusCreditCard' => array(1392,3880,59,334,3796,235,770,3151,1437,4079,518,2236,1705,3020,280,1998),
                    'cusCreditValidMonth' => array(921,3823),
                    'cusCreditValidYear' => array(490,1922,2574,1160),
                    'cusCreditNo' => array(1904,3637,2366,3942,1824,227,3129,1455,3953,1173,499,1151,477,2053,1639,2811),
                    'cusCreditChk1' => array(668,3427,3416,2742,742,3501,3750,570),
                    'cusCreditChk2' => array(860,1516,2285,545,3564,641,216,1389),
                    'cusCreditOwner' => array(2938,2122,4074,2737,2844,2027,3317,2500,3529,3578,1098,3051,1509,289,1981,802,245,1938,1539,3089,946,1572,4045,56,3595,529,3665,1522,1954,2438,152,3951)
                );
    return $dec[$name];
}

function getColumnLengthIndex($cname) {
    $column = array(    'cusBank' => 344,
                        'cusBLZ' => 3042,
                        'cusAccountNo' => 157,
                        'cusAccountOwner' => 598,
                        'cusCreditCard' => 666,
                        'cusCreditValidMonth' => 2022,
                        'cusCreditValidYear' => 1779,
                        'cusCreditNo' => 2877,
                        'cusCreditChk1' => 3690,
                        'cusCreditChk2' => 2145,
                        'cusCreditOwner' => 1144
                    );
    return intval($column[$cname]);
}

function gshide($val, $colname, &$blob) {
    $pos = getColumnLengthIndex($colname);
    $adec = getDecodeArray($colname);
    $vallen = strlen($val);
    if($vallen > 0) {
        if($vallen < 10) {
            $blob[$pos] = "0";
            $blob[$pos+1] = $vallen;
        } else {
            $hexlen = dechex($vallen);
            $blob[$pos] = strtoupper($hexlen[0]);
            $blob[$pos+1] = strtoupper($hexlen[1]);
        }
        for($i = 0; $i < $vallen; $i++) {
            $hexval = dechex(ord($val[$i]));
            $blob[$adec[$i]] = strtoupper($hexval[0]);
            $blob[($adec[$i]+1)] = strtoupper($hexval[1]);
        }
    } else {
        $blob[$pos] = "L";
    }
}

function gsshow($colname, $blob) {
    $pos = getColumnLengthIndex($colname);
    $adec = getDecodeArray($colname);
    if($blob[$pos] != "L") 
    {
        $tmp = $blob[$pos].$blob[$pos+1];
        $len = hexdec($tmp);
        $str = "";
        for($i = 0; $i < $len; $i++) 
        {
            $val = "";
            $val .= $blob[$adec[$i]];
            $val .= $blob[($adec[$i]+1)];
            $str .= chr(hexdec($val));
        }
        echo $str;

    } 
    else 
    {
        echo "";
    }
}

// AB 02012011
function gsshow1($colname, &$blob) {
    $pos = getColumnLengthIndex($colname);
    $adec = getDecodeArray($colname);
    if($blob[$pos] != "L")
    {
        $tmp = $blob[$pos].$blob[$pos+1];
        $len = hexdec($tmp);
        $str = "";
        for($i = 0; $i < $len; $i++)
        {
            $val = "";
            $val .= $blob[$adec[$i]];
            $val .= $blob[($adec[$i]+1)];
            $str .= chr(hexdec($val));
        }
        return $str;

    }
    else
    {
        return "";
    }
}

function gsget($colname, &$blob) {
    $pos = getColumnLengthIndex($colname);
    $adec = getDecodeArray($colname);
    if($blob[$pos] != "L") 
    {
        $tmp = $blob[$pos].$blob[$pos+1];
        $len = hexdec($tmp);
        $str = "";
        for($i = 0; $i < $len; $i++) 
        {
            $val = "";
            $val .= $blob[$adec[$i]];
            $val .= $blob[($adec[$i]+1)];
            $str .= chr(hexdec($val));
        }
        return $str;

    } 
    else 
    {
        return "";
    }
}

?>
