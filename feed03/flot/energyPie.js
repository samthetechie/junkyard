/*
    All Emoncms code is released under the GNU General Public License v3.
    See COPYRIGHT.txt and LICENSE.txt.
    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
*/

(function($){

  energy_pie = function(data2) {
  
    var total = 0;
    var data = [];
    var series = data2.length;
    for( var i = 0; i<series; i++) 
    {    
      var kwhd = Number(data2[i][1]);
      data[i] = { label: data2[i][0], data: kwhd };
      total += kwhd;
    }

    // INTERACTIVE
    $.plot($("#energypie"), data, 
    {
      series: 
      {
        pie: 
        { 
	  show: true,
          label: 
          {
	    show: true,
 	    formatter: function(label, series)
            {

              if (series.data[0][1]>1.0){
              // Pie chart label format
	      return '<div style="font-size:10pt;text-align:center;padding:2px;color:rgb(100,100,100);">'+label+'<br/ >'+Math.round(series.data[0][1])+' kWh/d</div>';}
              return "";
	    }			
	  }
	}
      },
      grid: {
	hoverable: true,
	clickable: true
      },
      legend: {
        show: false
      }
    });

    $("#energypie").bind("plothover", pieHover);
    $("#energypie").bind("plotclick", pieClick);

    function pieHover(event, pos, obj) 
    {
      if (!obj) return;
      
      // To catch data from pie hover event:
      $("#slice").html("<b>"+obj.series.label+"</b><br/>"+obj.series.data[0][1]+" kWh/d");

   var kwhd = obj.series.data[0][1];
  var kwhy = kwhd * 365;

  var capital = ((kwhd/2.2)*4090);
  var payyear = (kwhy * 0.413);
  var payback = capital / payyear;
  var profit = (payyear * 25);


  var out = "PV required: "+(kwhd/2.2).toFixed(1)+"kWp @ £"+(capital).toFixed(0)+" (dulas) | Payback: "+ (payback).toFixed(1)+" years | " + (profit/capital).toFixed(1)+"x | "+(kwhd*0.10).toFixed(1) + "% land";

  $('#solarpv').html(out);

  capital = 1300000/(7200/kwhd);
  payyear = (kwhy * 0.082);
  payback = capital / (kwhy * 0.082)
  profit = (payyear * 20);

  out = "1/"+(7200/kwhd).toFixed(0)+" of a 1MW wind turbine | £"+(capital).toFixed(0) + " | Payback: "+ (payback).toFixed(1)+" years | " + (profit/capital).toFixed(1)+"x | "+(kwhd*0.52).toFixed(1) + "% land";

  /*
  var out = "1/"+(7200/kwhd).toFixed(0)+" of a 1MW wind turbine | "+(kwhy).toFixed(0)+" kWh/y<br/>"+
  "Capital: £"+(capital).toFixed(0)+"<br/>"+
  "Feed in tariff rate is 9.4p/kWh for 20 years | Maintanence: 1.2p/kWh<br/>" +
  "Payback/year: £"+(payyear).toFixed(0) +"<br/>"+
  "Payback: "+ (payback).toFixed(1)+" years<br/>" +
  "Lifetime: 20-25years | Profit: £"+(profit).toFixed(0) + " | "+(profit/capital).toFixed(1)+"x";
*/
    $('#mwwind').html(out);

    }

    function pieClick(event, pos, obj) 
    {
      if (!obj) return;
      if (obj.series.label=='Electricity') window.location = 'electric';
      if (obj.series.label=='Car') window.location = 'car';
      if (obj.series.label=='Wood Fire') window.location = 'woodfire';
      if (obj.series.label=='Solar PV') window.location = 'solarpv';
      if (obj.series.label=='Heating') window.location = 'heating';
      if (obj.series.label=='Solar Hot Water') window.location = 'solarhotwater';
      if (obj.series.label=='Flying') window.location = 'flying';
    }
  }
})(jQuery);
