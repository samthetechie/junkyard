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
