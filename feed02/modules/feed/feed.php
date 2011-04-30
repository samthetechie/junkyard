<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
include_once "modules/feed/feed.inc.php";

class feed {

    protected $categories = array();
    protected $properties = array();

    //----------------------------------------------------------------
    // Implement categories URL menu - page piper
    //----------------------------------------------------------------
    function menu() {

       if (isset($GLOBALS['args'][1]))
       {
          switch ($GLOBALS['args'][1]){
                case "view" :
		  $userid = $_SESSION['userid'];
                  $variables['title']    = "My feeds:";
                  $variables['content']  = $this->input_list($userid);
                  $variables['content'] .= $this->input_config($userid);
                  $variables['content'] .= $this->feed_list($userid);   
                break;

                case "graph" :
		  $variables = $this->graph( $_POST["feedid"] , $_POST["feedname"] , $_POST["sel"] );
                break;
  
                default :
                break;
	    }
        }
        return $variables;
    }
 
    //----------------------------------------------------------------------------------------------------
    // Input List
    //----------------------------------------------------------------------------------------------------
    function input_list($userid)
    {
      $inputs = get_user_inputs($userid);

      $out = "<div class='lightbox' style='margin-bottom:20px;'>";
      $out .= '<h3>1) Inputs</h3>';

      if ($inputs)
      {
        $out .= "<table class='catlist'><tr><th>Name</th><th>Last Value</th><th>Action</th></tr>";
        $i = 0;
        foreach ($inputs as $input)
        {
          $i++;
          $out .= "<tr class='d" . ($i & 1) . "' >";
          $out .= "<td>".$input[0]."</td><td>".$input[1]."</td><td>";
          $out .= '<form name="inputSave" action="view" method="post">';
	  $out .= '<input type="hidden" name="form" value="1">';
	  $out .= '<input type="hidden" name="input" value="'.$input[0].'">';
          $out .= '<input type="submit" value=">" class="buttonLook"/></form></td></tr>';
        }
        $out .="</table>";
      } 
      else
      {
        $out .= "<p>You have no inputs, to get started connect up your monitoring hardware by sending data to the following address in the following form, try this example to see what happens:</p>";
        $testjson = $GLOBALS['systempath']."api/api.php?json={testA:123.4,testB:100}";
        $out .= "<p><a href='".$testjson."'>".$testjson."</a></p>";
      }
      $out .= "</div>";
      return $out;
    }

    //----------------------------------------------------------------------------------------------------
    // Feed List
    //----------------------------------------------------------------------------------------------------
    function feed_list($userid)
    {
      //------------------------------------------------------
      // Feeds
      //------------------------------------------------------
      $feeds = get_user_feeds($userid);
      $out = "<div class='lightbox' style='margin-bottom:20px;'>";
      $out .= '<h3>3) Feeds</h3>';

      if ($feeds)
      {
        $out .= "<table class='catlist'><tr><th>id</th><th>Name</th><th>updated</th><th>Value</th><th>Visualise</th></tr>";
        $i = 0;

        foreach ($feeds as $feed)
        {
          $timenow = time();
          $time = strtotime($feed[2]);
          $updated = ($timenow - $time)."s ago";
          if (($timenow - $time)>3600) $updated = "inactive";

          $i++;
          $out .= "<tr class='d" . ($i & 1) . "' >";
          $out .= "<td>".$feed[0]."</td><td>".$feed[1]."</td><td>".$updated."</td><td>".$feed[3]."</td><td>";
          $out .= '<form name="inputSave" action="graph" method="post">';
	  $out .= '<input type="hidden" name="feedid" value="'.$feed[0].'">';
	  $out .= '<input type="hidden" name="feedname" value="'.$feed[1].'">';
          $out .= '
          <select name="sel">
          <option value="1">LOD</option>
          <option value="2">kwhd</option>
          </select>';
          $out .= '<input type="submit" value="view" class="buttonLook"/></form></td></tr>';
        }
        $out .="</table>";
      }
      else
      {
        $out .= "<p>You have no feeds</p>";
      }
      $out .= "</div>";
      return $out;
    }

    //----------------------------------------------------------------------------------------------------
    // Input configuration
    //----------------------------------------------------------------------------------------------------
    function input_config($userid)
    {
      $out = "<div class='lightbox' style='margin-bottom:20px;'>";
      $out .= '<h3>2) Input Configuration:</h3>';

      if (isset($_POST["form"])) {$form = $_POST["form"];} else {$form = 0;}

      if ($form == 1 || $form == 2)
      {

        $input = $_POST["input"];
        $out .= "<h3>".$input."</h3>";

        $inputProcessList = getInputProcessList($userid,$input);

        // Form 2 is process addition ------------------------------------------
        if ($form == 2)
        {
          $processType = $_POST["sel"];					// get process type
          $arg = $_POST["arg"];		

          $out .= "<h2>here: ".$processType." arg:".$arg."</h2>";
		
          // get feed name or scaler, offset etc	
          if ($processType==1 || $processType==4 || $processType==5 || $processType==7 || $processType==8)	// if Type = log
          {
            $name = $arg;
            $id = getFeedId($userid,$name);
            if ($id==0) {						// Checks if feed of feed name arg exists
              $id = createFeed($userid,$name);				// if not create feed and get its id
            }
            $arg = $id;
          }

          if ($processType==6) $arg = getInputId($userid,$arg);

          $inputProcessList[] = array($processType,$arg);		// Add the new process list entry
          saveInputProcessList($userid,$input,$inputProcessList);	// Save the new process list
        }
        //----------------------------------------------------------------------
        $out .= $this->view_processList($input,$inputProcessList);
      }
      else
      {
        $out .= "<p>Select an input to configure and map through to a feed</p>";
      }
      $out .= "</div>";
      return $out;
    }

    //----------------------------------------------------------------------------------------------------
    // Input configuration: process list
    //----------------------------------------------------------------------------------------------------
    function view_processList($input,$inputProcessList)
    {
        $out = "<table class='catlist'><tr><th>Order</th><th>Process</th><th>Arg</th><th>Actions:</th></tr>";
        $i = 0;
     
        if ($inputProcessList)
        {
          foreach ($inputProcessList as $inputProcess)    		// For all input processes
          {
            $processid = $inputProcess[0];				// Process id
            $argA = $inputProcess[1];

            if ($processid==1) {$processDescription = "Log to feed: ";  $argA = getFeedName($argA);}
            if ($processid==2) $processDescription = "x ";
            if ($processid==3) $processDescription = "+ ";
            if ($processid==4) {$processDescription = "Power to kWh: ";  $argA = getFeedName($argA);}
            if ($processid==5) {$processDescription = "to kWhd: ";  $argA = getFeedName($argA);}
            if ($processid==6) {$processDescription = "x input: ";  $argA = getInputName($argA);}
            if ($processid==7) {$processDescription = "count on time/day: ";  $argA = getInputName($argA);}
            if ($processid==8) {$processDescription = "kwhinc to kWhd: ";  $argA = getFeedName($argA);}
            $i++;
            $out .= "<tr class='d" . ($i & 1) . "' >";
            $out .= "<td>".$i."</td><td>".$processDescription."</td><td>".$argA."</td>";
            $out .= "<td><button type='button'>Edit</button><button type='button'>Del</button></td></tr>";
          }
        }
        $out .= '<tr><td>New</td><td>
        <form action="view" method="post">
        <input type="hidden" name="form" value="2">
        <input type="hidden" name="input" value="'.$input.'">
        <select class="processSelect" name="sel">
        <option value="1">log</option>
        <option value="2">x</option>
        <option value="3">+</option>
        <option value="4">Power to kWh</option>
        <option value="5">Power to kWh/d</option>
        <option value="6">x input</option>
        <option value="7">count on time</option>
        <option value="8">kwhinc2kwhd</option>
        </select></td>
        <td><input type="text" name="arg" class="processBox" style="width:100px;" /></td>
        <td><input type="submit" value="add" /></form</td>
        </tr></table>';
        return $out;
    }

    function graph($feedid,$feedname,$graph_type)
    {
      // If graph type = LOD line graph
      if ($graph_type == 1)
      {
        $out = "<div class='lightbox' style='margin-bottom:20px;'>";
        $out .= '<iframe id="testG" style="width:100%; height:500px;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
        src="'.$GLOBALS['systempath'].'vis/igraph.php?tableid='.$feedid.'&price=0.12"></iframe>';
        $out .= "</div>";

        $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
        $out .= "<h3>Embed this graph</h3>";
        $out .= htmlspecialchars('<iframe id="testG" style="width:100%; height:500px;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
        src="'.$GLOBALS['systempath'].'vis/igraph.php?tableid='.$feedid.'&price=0.12"></iframe>');
        $out .= "</div>";
      }

      if ($graph_type == 2)
      {
        $out = "<div class='lightbox' style='margin-bottom:20px;'>";
        $out .= '<iframe id="testG" style="width:100%; height:500px;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
        src="'.$GLOBALS['systempath'].'vis/ikwh.php?tableid='.$feedid.'&price=0.14"></iframe>';
        $out .= "</div>";

        $out .= "<div class='lightbox' style='margin-bottom:20px;'>";
        $out .= "<h3>Embed this graph</h3>";
        $out .= htmlspecialchars('<iframe id="testG" style="width:100%; height:500px;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
        src="'.$GLOBALS['systempath'].'vis/ikwh.php?tableid='.$feedid.'&price=0.14"></iframe>');
        $out .= "</div>";
        
      }

      // ? $out .= "</div>";
      $variables['title']    = "Visualise feed: ".$feedname;
      $variables['content']  = $out;
      return $variables;
    }
}
?>
