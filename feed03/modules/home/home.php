<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  class home {

    //-------------------------------------------------------------------
    // 1) Direct according to path argument 1
    //-------------------------------------------------------------------
    function menu() {
        return $this->page();
    }

    //-------------------------------------------------------------------
    // This function actually creates the page...
    //-------------------------------------------------------------------    
    function page()
    {
      $variables['title'] = "Home";

 
      if ($_SESSION['valid'])  
      { 
        $out = "<p>Welcome user</p>";
      }
      else
      {
        $out = "<p>Welcome guest</p>";
      }

      // content will be rendered in the content area in the theme
      $variables['content'] = $out;
      return $variables;
    }

}

?>
