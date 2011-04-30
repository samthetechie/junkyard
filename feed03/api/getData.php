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
  //--------------------------------------------------------------------------------------
  // Graph Data reader!
  //--------------------------------------------------------------------------------------
  $feedid = $_GET["table"];
  $feedname = "feed_".trim($feedid)."";


  $start = $_GET["start"]/1000.0;
  $end = $_GET["end"]/1000.0;
  $resolution = $_GET["resolution"];
  //--------------------------------------------------------------------------------------
  // 1) Load up database
  //--------------------------------------------------------------------------------------
  require_once ('../includes/db.php');
  if (!db_connect()) echo "Database setup error!";

  //--------------------------------------------------------------------------------------
  // 2) Get the data and send it via a JSON string back to the graphing program
  //--------------------------------------------------------------------------------------
  $start = date("Y-n-j H:i:s", $start);		//Time format conversion
  $end = date("Y-n-j H:i:s", $end);  		//Time format conversion

  //This mysql query selects data from the table at specified resolution
  if ($resolution>1){
  $result = db_query(
  "SELECT * FROM 
   (SELECT @row := @row +1 AS rownum, time,data FROM ( SELECT @row :=0) r, $feedname) 
   ranked WHERE (rownum % $resolution = 1) AND (time>'$start' AND time<'$end') order by time Desc");
  }
  else
  {
   //When resolution is 1 the above query doesnt work so we use this one:
   $result = db_query("select * from $feedname WHERE time>'$start' AND time<'$end' order by time Desc"); 
  }

  $data = array();                                     //create an array for them
  while($row = mysql_fetch_array($result))             // for all the new lines
  {

    $dataValue = $row['data'] ;                        //get the datavalue
    $time = (strtotime($row['time']))*1000;            //and the time value - converted to unix time * 1000
    $data[] = array($time , $dataValue);               //add time and data to the array
  }
  echo json_encode($data);                             //encode the array as a JSON and send it on to the graphing script

 // mysql_close($con);                                   //close the mysql database
?>
