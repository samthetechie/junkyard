<?php

/*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
*/

  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  echo "ok";
  //-----------------------------------------------------------------------------------------
  // 1) Get json string
  //-----------------------------------------------------------------------------------------
  $json = $_GET["json"];
  //if ($json)
  //{
  //$json = '{"power":"100.0","kwh":"2.3","col":"22.3"}';

  $json = str_replace(chr(92), "", $json);

  $json = str_replace('"', '', $json);		//Remove quote characters "
  $json = str_replace('{', '', $json);		//Remove JSON start characters
  $json = str_replace('}', '', $json);		//Remove JSON end characters

  $datapairs = explode(",", $json);		//Seperate JSON string into individual data pairs. 
  //-----------------------------------------------------------------------------------------

  require_once ('../includes/db.php');
  if (!db_connect()) echo "Database setup error!";

  $user = 1;

  $inputs = array();
  //for all inputs
  foreach ($datapairs as $datapair)            
  {
    $datapair = explode(":", $datapair);
    $key = $datapair[0];                       
    $inputValue = $datapair[1]; 

    // find id of input
    $id = inputExist($user,$key);

    if ($id!=0) {
      $inputs[] = array($id , $inputValue);
      input_ULV($id , $inputValue); //Update Last Value
    }
    else {
      //print "new input<br/>";
      addInput($user,$key);
    }
  }
   
  foreach ($inputs as $input)            
  {
    //print "processing input<br/>";

    $id = $input[0];
    $inputValue = $input[1];
    //----------------------------------------------------------------------------
    // Carry out processing
    //----------------------------------------------------------------------------
    $inputProcessList = getInputProcessList($user,$id);	// Get input process list
    foreach ($inputProcessList as $inputProcess)    		// For all input processes
    {
      $inputProcess = explode(":", $inputProcess);    	// Divide into process id and arg
      $processid = $inputProcess[0];				// Process id
      if (isset($inputProcess[1])) $argA = $inputProcess[1];				// Can be value or feed id

      if ($processid == 1) {feedInsert($argA,$inputValue);}
      if ($processid == 2) $inputValue *= $argA;
      if ($processid == 3) $inputValue += $argA;
      if ($processid == 4) {powerTokwh($argA,$inputValue);}
      if ($processid == 5) {powerTokwhd($argA,$inputValue);}
      if ($processid == 6) {$inputValue = multInput($argA,$inputValue);}	// Multiply with another input
      if ($processid == 7) {pumpontime($argA,$inputValue);}
      if ($processid == 8) {kwhincTokWhd($argA,$inputValue);}
      //if ($processid == 3) $inputValue += getLast(feedname);
      //if ($processid == 4) feedUpdate(feedname,$inputValue);
      //if ($processid == 5) { if @time: feedInsert(feedname,$inputValue); }
    }
    //-----------------------------------------------------------------------------
  }
//}
  //---------------------------------------------------------------------------
  // Function variables exist
  //---------------------------------------------------------------------------
  function inputExist($userid,$key)
  {
    $result = db_query("SELECT * FROM input WHERE tag='$key' AND userid='$userid'");
    $array = mysql_fetch_array($result);
    if ($array) return $array['id']; else return 0;
  }

  //---------------------------------------------------------------------------
  // Function add input
  //---------------------------------------------------------------------------
  function addInput($user,$key)
  {
    db_query("INSERT INTO input (userid,tag) VALUES ('$user','$key')");
  }

  function input_ULV($id, $lastvalue)
  {
    $time = date("Y-n-j H:i:s", time());    
    db_query("UPDATE input SET time='$time', lastvalue = '$lastvalue' WHERE id = '$id'");
  }

  //---------------------------------------------------------------------------
  // Function get input list
  //---------------------------------------------------------------------------
  function getInputProcessList($user,$id)
  {
    $result = db_query("SELECT processList FROM input WHERE id='$id' AND userid='$user'");
    $array = mysql_fetch_array($result);
    $array = explode(",", $array['processList']);
    return $array;
  }

  //---------------------------------------------------------------------------
  // Function feed insert
  //---------------------------------------------------------------------------
  function feedInsert($feedid,$value)
  {                             
    $feedname = "feed_".trim($feedid)."";
    $time = date("Y-n-j H:i:s", time());                        
    db_query("INSERT INTO $feedname (`time`,`data`) VALUES ('$time','$value')");

    db_query("UPDATE feeds SET value = '$value', time = '$time' WHERE id='$feedid'");
    //print $time." : ".$value." : ".$feedname;
  }

  function multInput($argA,$inputValue)
  {
    $result = db_query("SELECT lastvalue FROM input WHERE id = '$argA'");
    $row = mysql_fetch_array($result);
   
    $inputValue = $inputValue * $row['lastvalue'];

    return $inputValue;
  }

  function powerTokWh($feedid,$value)
  {
    $feedname = "feed_".trim($feedid)."";
    $time_now = time();
    $new_kwh = 0;

    // Get last value
    $result = db_query("SELECT * FROM $feedname ORDER BY time DESC LIMIT 1");
    $last_row = mysql_fetch_array($result);
    if ($last_row)
    {
      $last_time = strtotime($last_row['time']);
      $last_kwh = $last_row['data'];
      
      // kWh calculation
      $time_elapsed = ($time_now - $last_time);
      $kwh_inc = ($time_elapsed * $value) / 3600000;
      $new_kwh = $last_kwh + $kwh_inc;
    }

    // Insert new feed
    $time = date("Y-n-j H:i:s", $time_now);  
    db_query("INSERT INTO $feedname (`time`,`data`) VALUES ('$time','$new_kwh')");
  }

  function powerTokWhd($feedid,$value)
  {
    $feedname = "feed_".trim($feedid)."";
    $new_kwh = 0;

    $time = date('y/m/d', mktime(0, 0, 0, date("m") , date("d") , date("Y")));

    // Get last value
    $result = db_query("SELECT * FROM $feedname WHERE time = '$time'");
    $last_row = mysql_fetch_array($result);

    if (!$last_row)
    {
      $result = db_query("INSERT INTO $feedname (time,data) VALUES ('$time','0.0')");

    $updatetime = date("Y-n-j H:i:s", time());
    db_query("UPDATE feeds SET value = '0.0', time = '$updatetime' WHERE id='$feedid'");
    }
    else
    {
      $result = db_query("SELECT * FROM feeds WHERE id = '$feedid'");
      $last_row = mysql_fetch_array($result);

      $last_kwh = $last_row['value'];
      $time_now = time();
      $last_time = strtotime($last_row['time']);
      // kWh calculation
      $time_elapsed = ($time_now - $last_time);
      $kwh_inc = ($time_elapsed * $value) / 3600000;
      $new_kwh = $last_kwh + $kwh_inc;
    }

    // update kwhd feed
    db_query("UPDATE $feedname SET data = '$new_kwh' WHERE time = '$time'");

    $updatetime = date("Y-n-j H:i:s", time());
    db_query("UPDATE feeds SET value = '$new_kwh', time = '$updatetime' WHERE id='$feedid'");
  }

  function kwhincTokWhd($feedid,$kwh_inc)
  {
    $feedname = "feed_".trim($feedid)."";
    $new_kwh = 0;

    $time = date('y/m/d', mktime(0, 0, 0, date("m") , date("d") , date("Y")));

    // Get last value
    $result = db_query("SELECT * FROM $feedname WHERE time = '$time'");
    $last_row = mysql_fetch_array($result);

    if (!$last_row)
    {
      $result = db_query("INSERT INTO $feedname (time,data) VALUES ('$time','0.0')");

    $updatetime = date("Y-n-j H:i:s", time());
    db_query("UPDATE feeds SET value = '0.0', time = '$updatetime' WHERE id='$feedid'");
    }
    else
    {
      $new_kwh = $last_row['data'] + ($kwh_inc/1000);
    }

    // update kwhd feed
    db_query("UPDATE $feedname SET data = '$new_kwh' WHERE time = '$time'");

    $updatetime = date("Y-n-j H:i:s", time());
    db_query("UPDATE feeds SET value = '$new_kwh', time = '$updatetime' WHERE id='$feedid'");
  }

  function pumpontime($feedid,$value){

    $feedname = "feed_".trim($feedid)."";
    $new_kwh = 0;

    $time = date('y/m/d', mktime(0, 0, 0, date("m") , date("d") , date("Y")));

    // Get last value
    $result = db_query("SELECT * FROM $feedname WHERE time = '$time'");
    $last_row = mysql_fetch_array($result);

    if (!$last_row)
    {
      $result = db_query("INSERT INTO $feedname (time,data) VALUES ('$time','0.0')");

      $updatetime = date("Y-n-j H:i:s", time());
      db_query("UPDATE feeds SET value = '0.0', time = '$updatetime' WHERE id='$feedid'");
    }
    else
    {
      $result = db_query("SELECT * FROM feeds WHERE id = '$feedid'");
      $last_row = mysql_fetch_array($result);

      $last_kwh = $last_row['value'];
      $time_now = time();
      $last_time = strtotime($last_row['time']);
      // kWh calculation
      $time_elapsed = ($time_now - $last_time);
      if ($value==1) {$new_kwh = $last_kwh + $time_elapsed;} else {$new_kwh = $last_kwh;}
    }

    // update kwhd feed
    db_query("UPDATE $feedname SET data = '$new_kwh' WHERE time = '$time'");

    $updatetime = date("Y-n-j H:i:s", time());
    db_query("UPDATE feeds SET value = '$new_kwh', time = '$updatetime' WHERE id='$feedid'");

  }


?>
