<?php

  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  // Start session
  session_start();

  error_reporting(E_ALL);
  ini_set('display_errors','Off');

  $systempath = dirname("http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'])."/";

  $flow = 1;			// Flow control variable

  //----------------------------------------------------------------
  // 1) Get the path argument forwarded by htaccess to index.php?q=
  //----------------------------------------------------------------
  $path=""; if (isset($_GET['q'])) $path = $_GET['q'];

  //----------------------------------------------------------------
  // 2) Try to connect to database
  //----------------------------------------------------------------
  require_once ('includes/db.php');
  if (!db_connect()) { $flow = 0; $path = "setup/database"; }
  
  //----------------------------------------------------------------
  // 3) Check setup variable
  //----------------------------------------------------------------
  if ($flow){ 
    $row = db_query_row("SELECT * FROM is_setup");
    if (!$row['yes']) { $flow = 0; $path = "setup/tables";}
  }

  //----------------------------------------------------------------
  // 4) User implementation
  //----------------------------------------------------------------
  include_once "modules/user/user.php";
  $module = new user();
  $module->handler();
  $userbox = $module->userbox();

  //----------------------------------------------------------------
  // 5) Load module acording too path argument
  //----------------------------------------------------------------
  $args = explode('/', $path);
  $modulename = $args[0];

  if ($path=="") $modulename = "home"; // redirect to home

  $modulefile = "modules/$modulename/$modulename.php";
  if (file_exists($modulefile)){
    include_once $modulefile;
    $module = new $modulename();
    $variables = $module->menu();
  }

  //----------------------------------------------------------------
  // 6) Content wrapper
  //----------------------------------------------------------------
  $variables['userbox'] = $userbox;
  $variables['path'] = $systempath;
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
