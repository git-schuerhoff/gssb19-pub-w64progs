<?php
function sendHTTP($reqBody, $host, $port, $wsPath) {
 $res = "";
 
 flush();
 
 try {
  //Paymorrow Server URL
 
  if (!$host) {
   $host = "test.paymorrow.net";
  }
  if (!$port) {
   $port = 443;
  }
  if (!$wsPath) {
   $wsPath = "/perth/services/PaymorrowService.Paymorrow";   
  }
/*  
 //-->only for debuging
    $h = fopen('send.log', 'a+');
    fputs($h, date('Y-m-d H:j:s') . "\r\n");
 fputs($h, $reqBody);
 fputs($h, "\r\n------------------------------------------------------------------------------------------------------------------------------------------------------------\r\n\r\n");
 fclose($h);
 //<--
 */
  // HTTP Protocol settings for sending request
  $req = "POST ".$wsPath." HTTP/1.1\r\n"
   ."Host: $host\r\n"
   ."Content-Type: text/xml\r\n"
   ."Content-Length: ".strlen($reqBody)."\r\n"
   ."Connection: close\r\n\r\n"
   .$reqBody;
 
  //echo "<pre>";
  //var_dump($req);
  //echo "</pre>";
 
  #echo "Connecting: ".$host.":".$port.", path=".$wsPath."...";
  flush();
 
  $myHeader = "*** " . date(DATE_ATOM, time()) . " ******************************************************\n";
 
  #log_output("paymorrow_client_http_socket_log.txt", $myHeader.$req);
  
  if (!($fp = fsockopen("ssl://".$host, $port, $errNo, $errStr))) {
   echo "Cannot open:".$errStr;
   flush();
   return false;
  }
 
  // data placed on Stream
  fwrite($fp, $req, strlen($req));
 
  //Read data from Stream
  while($data = fread($fp, 32768)) {
   $res.=$data;
  }
 
  #log_output("paymorrow_client_http_socket_log.txt", "\n".$myHeader."Response:\n".$res."\n");
 
  fclose($fp) ;
  //echo "Succeeded.<hr>";
  flush();
 
 } catch (Exception $e) {
  //echo $e->getMessage();
 }
 
 flush();
 
  return $res;

}
 
?>