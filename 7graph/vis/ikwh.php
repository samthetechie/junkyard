<html>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<!--

   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org

-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <script language="javascript" type="text/javascript" src="date.format.js"></script>
    <script language="javascript" type="text/javascript" src="../flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="../flot/jquery.flot.js"></script>

  <?php
  error_reporting(E_ALL);
  ini_set('display_errors','On');

  $tableid = $_GET["tableid"];                 //Get the table name so that we know what graph to draw
  $price = $_GET["price"];                 //Get the table name so that we know what graph to draw
  $path = dirname("http://".$_SERVER['HTTP_HOST'].str_replace('vis', '', $_SERVER['SCRIPT_NAME']))."/";
  ?>

</head>

<body>

   <div id="item" style="font-size: 40px; font-family: arial;  font-weight: bold; color: #333;">---</div>
   <div id="date" style="font-size: 20px; font-family: arial;  font-weight: bold; color: #333;"></div>
   <div id="placeholder" style="font-family: arial;"></div>     <!-- Graph placeholder -->
   <div id="stat" style="font-size: 20px; font-family: arial;  font-weight: normal; color: #333;"></div>

   <div id="out"></div>

   <script id="source" language="javascript" type="text/javascript">

   var table = "<?php echo $tableid; ?>";				//Fetch table name
   var price = "<?php echo $price; ?>";				//Fetch table name
   var path = "<?php echo $path; ?>";	
   var tkwh;
   var ndays;
   $(function () 
   {

     var width = $('body').width();
     var height = $('body').height();
     if (height<=100) height = 500;
     $('#placeholder').width(width);
     $('#placeholder').height(height-150);

     var graph_data = [];

     getkwh(table);

     //--------------------------------------------------------------------------------------
     // Fetch Data
     //--------------------------------------------------------------------------------------
     function getkwh(table)
     {
       $.ajax({                                      
         url: path+'api/getkwh.php', 
         data: "&table="+table,
         dataType: 'json',                             
         success: function(data) 
         {
           tkwh = 0;
           ndays=0;
           for (var z in data)                     //for all variables
           {
             tkwh += parseFloat(data[z][1]);
             ndays++;
           }   

 $("#stat").html("Total: "+(tkwh).toFixed(0)+" kWh : £"+(tkwh*price).toFixed(0) + " | Average: "+(tkwh/ndays).toFixed(1)+ " kWh : £"+((tkwh/ndays)*price).toFixed(2)+" | £"+((tkwh/ndays)*price*7).toFixed(0)+" a week, £"+((tkwh/ndays)*price*365).toFixed(0)+" a year");
$("#stat").append("<br/ ><br/ >Unit price: £"+price);
           graph_data = [];   
           graph_data = data;
           plotGraph();
         } 
       });
     }


     function plotGraph()
     {
        $.plot($("#placeholder"), [graph_data], 
        {
          bars: {
	    show: true,
	    align: "center",
            
	    barWidth: 3600*18*1000,
	    fill: true
          },
          grid: { show: true, hoverable: true, clickable: true },
          xaxis: { mode: "time"},
        });
     }

     $("#placeholder").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        var mdate = new Date(item.datapoint[0]);
        $("#item").html((item.datapoint[1]).toFixed(1)+" kWh/d | £"+(item.datapoint[1]*price).toFixed(2)+" | £"+(item.datapoint[1]*price*365).toFixed(0)+"/y <br/ >");
        $("#date").html(mdate.format("ddd, mmm dS, yyyy"));

       
     });
   });

   </script>
</body>
</html>

