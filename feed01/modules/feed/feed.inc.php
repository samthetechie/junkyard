<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
    function get_all_feeds()
    {
        $result = db_query("SELECT * FROM feeds");

        $feeds = array();
        while ($row = mysql_fetch_array($result)) {
            $feeds[] = $row['name'];
        }
        return $feeds;
    }

    function get_user_feeds($userid)
    {
        $result = db_query("SELECT * FROM feed_relation WHERE userid = '$userid'");
        $feeds = array();
        if ($result)
        {
          while ($row = mysql_fetch_array($result)) {

            $feedid = $row['feedid'];
            // 2) get feed name of id
            $feed_result = db_query("SELECT * FROM feeds WHERE id = '$feedid'");
            $feed_row = mysql_fetch_array($feed_result);
            $feeds[] = array($feed_row['id'],$feed_row['name'],$feed_row['time'],$feed_row['value']);
          }
        }
        return $feeds;
    }

    function get_user_inputs($userid)
    {
        $result = db_query("SELECT * FROM input WHERE userid = '$userid'");
        $feeds = array();
        if ($result)
        {
          while ($row = mysql_fetch_array($result)) {
            $feeds[] = array($row['tag'],$row['lastvalue']);
          }
        }
        return $feeds;
    }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets input process list
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function getInputProcessList($user,$key)
  {
    $result = db_query("SELECT processList FROM input WHERE tag='$key' AND userid='$user'");
    $array = mysql_fetch_array($result);
    $list = array();
    if ($array['processList']){		
      $array = explode(",", $array['processList']);
      foreach ($array as $row)    			// For all input processes
      {
        $row = explode(":", $row);    			// Divide into process id and arg
        $list[]=array($row[0],$row[1]);			// Populate list array
      }
    }
    return $list;
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Save input process list
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function saveInputProcessList($userid,$input,$inputProcessList) {
    if ($inputProcessList) {
      $output = "";
      foreach ($inputProcessList as $inputProcess) $output .= "".$inputProcess[0].":".$inputProcess[1].",";
      $output[strlen($output)-1]='';
      $result = db_query("UPDATE input SET processList = '$output' WHERE userid = '$userid' AND tag='$input'");
    }
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Creates a feed entry and relates the feed to the user
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function createFeed($userid,$key)
  {
    $result = db_query("INSERT INTO feeds (name) VALUES ('$key')");				// Create the feed entry
    $result = db_query("SELECT id FROM feeds WHERE name='$key'");				// Select the same feed to find the auto assigned id
    if ($result) {
      $array = mysql_fetch_array($result);
      $feedid = $array['id'];											// Feed id
      db_query("INSERT INTO feed_relation (userid,feedid) VALUES ('$userid','$feedid')");	// Create a user->feed relation

      // create feed table
      $feedname = "feed_".$feedid;
      $result = db_query(
      "CREATE TABLE $feedname
      (
        time DATETIME,
        data float
      )");

      return $feedid;												// Return created feed id
    } else return 0;
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets a feeds ID from it's name and user ID
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function getFeedId($user,$key)
  {
    $result = db_query("SELECT * FROM feed_relation WHERE userid='$user'");
    while ($row = mysql_fetch_array($result))
    {
      $feedid = $row['feedid'];
      $result = db_query("SELECT name FROM feeds WHERE id='$feedid'");
      $row_name = mysql_fetch_array($result);
      if ($key == $row_name['name']) return $feedid;
    }
    return 0;
  }

  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  // Gets a feeds name from its ID
  //----------------------------------------------------------------------------------------------------------------------------------------------------------------
  function getFeedName($feedid)
  {
    $result = db_query("SELECT name FROM feeds WHERE id='$feedid'");
    if ($result) { $array = mysql_fetch_array($result); return $array['name']; } 
    else return 0;
  }

  function getInputName($id)
  {
    $result = db_query("SELECT tag FROM input WHERE id='$id'");
    if ($result) { $array = mysql_fetch_array($result); return $array['tag']; } 
    else return 0;
  }

  function getInputId($user,$tag)
  {
    $result = db_query("SELECT id FROM input WHERE tag='$tag' AND userid='$user'");
    if ($result) { $array = mysql_fetch_array($result); return $array['id']; } 
    else return 0;
  }

?>
