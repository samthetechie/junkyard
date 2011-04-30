<?php
  // Start session
  session_start();

  $flow = 1;			// Flow control variable

  //----------------------------------------------------------------
  // 1) Get path argument
  //----------------------------------------------------------------
  $path=""; if (isset($_GET['q'])) $path = $_GET['q'];

  //----------------------------------------------------------------
  // 2) Try to connect to database
  //----------------------------------------------------------------
  require_once ('db.php');
  if (!db_connect()) { $flow = 0; $path = "database"; }
  
  //----------------------------------------------------------------
  // 3) Check setup variable
  //----------------------------------------------------------------
  if ($flow){ 
    $row = db_query_row("SELECT * FROM is_setup");
    if (!$row['yes']) { $flow = 0; $path = "setup";}
  }

  //----------------------------------------------------------------
  // 4) Check session
  //----------------------------------------------------------------
  if ($flow) {if ($_SESSION['valid']){};}

  //----------------------------------------------------------------
  // 5) execute controllers
  //----------------------------------------------------------------
  if ($flow){ 
    $variables['content'] = "Database is good, setup is good, this is what you typed in the address bar: ".$path;
  }

  if ($path=="database") $variables['content'] = "Database configuration is incorrect";
  if ($path=="setup") $variables['content'] = "System has not yet been setup.. ";

  //----------------------------------------------------------------
  // 6) Content wrapper
  //----------------------------------------------------------------
  $variables['path'] = $properties['path'];
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
