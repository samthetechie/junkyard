<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  class setup {

    //-------------------------------------------------------------------
    // 1) Direct according to path argument 1
    //-------------------------------------------------------------------
    function menu() {
        switch ($GLOBALS['args'][1]){
            case "database":
                return $this->database();
                break;
            case "tables":
                return $this->tables();
                break;
            default:

        }
    }

    //-------------------------------------------------------------------
    // This function actually creates the page...
    //-------------------------------------------------------------------    
    function tables()
    {
      $variables['title'] = "System setup";
      $out = "<p>This is the system setup page</p>";

      // Create setup table
      $row = db_query("SELECT * FROM is_setup");
      if ($row)
      {
        $out .= "<p>is_setup table exists</p>"; 
      }
      else 
      { 
        $out .= "<p>is_setup table does not exists</p>";
        $result = db_query(
        "CREATE TABLE is_setup
        (yes int(1))");

        $result = db_query("INSERT INTO is_setup (yes) VALUES (1)");
        $out .= "<p>is_setup table created</p>"; 
      }

      // Create users table
      $row = db_query("SELECT * FROM users");
      if ($row)
      {
        $out .= "<p>users table exists</p>"; 
      }
      else 
      { 
        $out .= "<p>users table does not exists</p>";
        $result = db_query(
        "CREATE TABLE users
        (
        id int NOT NULL AUTO_INCREMENT, 
        PRIMARY KEY(id),
        username varchar(30),
        password varchar(64),
        salt varchar(3)
      )"); 
        $out .= "<p>users table created</p>"; 
      }




      // content will be rendered in the content area in the theme
      $variables['content'] = $out;
      return $variables;
    }

    //-------------------------------------------------------------------
    // This function actually creates the page...
    //-------------------------------------------------------------------    
    function database()
    {
      $variables['title'] = "Database setup";
      $out = "<p>There seems to be a problem with the database config?</p>";
 
      // content will be rendered in the content area in the theme
      $variables['content'] = $out;
      return $variables;
    }


}

?>
