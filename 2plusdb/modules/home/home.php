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
        switch ($GLOBALS['args'][1]){
            //case "whatever":
            //    break;
            default:
                return $this->page();
                echo "deault";
        }
    }

    //-------------------------------------------------------------------
    // This function actually creates the page...
    //-------------------------------------------------------------------    
    function page()
    {
      $variables['title'] = "Home";
      $out = "

      <p>Home page</p>

      ";
 
      // content will be rendered in the content area in the theme
      $variables['content'] = $out;
      return $variables;
    }

}

?>
