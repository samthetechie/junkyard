<?php

  function db_connect()
  {
    $success = 1;

    require_once ('settings.php');
    $conn = mysql_connect($server, $username, $password) or $success = 0;
    $db = mysql_select_db($database) or $success = 0;

    return $success;
  }

  function db_query($query)
  {
    return $result = mysql_query($query);
  }

  function db_query_row($query)
  {
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
  }


?>
