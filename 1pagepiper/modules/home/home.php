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
      $variables['title'] = "Page piper / module handler example";
      $out = "

      <p>When you enter: emoncms/home into the address bar in emoncms the .htaccess file redirects the request to emoncms/index.php?q=home</p>

<p>Once in this format the index.php can get the path argument using GET['q']</p>

<p>The path argument is split up according to forward slashes: user/login -> arg1: user arg2: login</p> 

<p>arg1 is then used to load the module called arg1</p>

<p>arg2 is then used by the module to direct any module specific pages for example: login or register</p>

      ";
 
      // content will be rendered in the content area in the theme
      $variables['content'] = $out;
      return $variables;
    }

}

?>
