<?php

  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

//----------------------------------------------------------------
// 1) Get the path argument forwarded by htaccess to index.php?q=
//----------------------------------------------------------------
$path=""; if (isset($_GET['q'])) $path = $_GET['q'];
$args = explode('/', $path);
$modulename = $args[0];

if ($path=="") $variables['content']="Try typing in pagepiper/home above";

//----------------------------------------------------------------
// 2) Load module acording too path argument
//----------------------------------------------------------------
$modulefile = "modules/$modulename/$modulename.php";
if (file_exists($modulefile)){
  include_once $modulefile;
  $module = new $modulename();
  $variables = $module->menu();
}
//----------------------------------------------------------------
// 3) Content wrapper
//----------------------------------------------------------------
$variables['path'] = dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])."/";
//These variables will be passed to the theme to be wrapped...
print theme_render_template("theme/theme.php", $variables); //and pass it here!
//This function is a direct copy from drupal's theme.inc script

function theme_render_template($template_file, $variables) {
    extract($variables, EXTR_SKIP);  // Extract the variables to a local namespace
    ob_start();                      // Start output buffering
    include "$template_file";        // Include the template file
    $contents = ob_get_contents();   // Get the contents of the buffer
    ob_end_clean();                  // End buffering and discard
    return $contents;                // Return the contents
}

?>
