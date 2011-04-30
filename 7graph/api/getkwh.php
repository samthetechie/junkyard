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

  $feedid = $_GET["table"];
  $feedname = "feed_".trim($feedid)."";

  require_once ('../includes/db.php');
  if (!db_connect()) echo "Database setup error!";


  $data = array();   
  //-------------------------------------------------------------------
  //Find number of days
  //-------------------------------------------------------------------
  $result = db_query("select * from $feedname ORDER BY time");
  while($array = mysql_fetch_array($result))                 //for all variables
  {
    $time = strtotime($array['time'])*1000;
    $kwhd = $array['data'];    
    $data[] = array($time , $kwhd);
  }

  echo json_encode($data);                             //encode the array as a JSON and send it on to the graphing script

 // mysql_close($con);                                   //close the mysql database
?>
