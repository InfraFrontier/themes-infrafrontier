<?php


#===================
# LICENCE
#===================

/*

Copyright 2015 EMBL-EBI

Licensed under the Apache License, Version 2.0 (the "License"); 
you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is 
distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 

See the License for the specific language governing permissions and limitations under the License.

*/

 header('Content-Type:text/html; charset=UTF-8'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" dir="ltr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Search, browse strains or genes in the EMMA database</title>

    <link type='text/css' rel='stylesheet' href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/themes/base/jquery-ui.css'  media='all' />
  
    <link type='text/css' rel='stylesheet' href='css/bootstrap.min.css'  />
    <link type='text/css' rel='stylesheet' href='css/bootstrap-responsive.min.css'  />
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.dataTables.css" />
      
    <link type="text/css" rel="stylesheet" media="all" href="css/strain_gene_search_bar.css" />
    <link type="text/css" rel="stylesheet" media="all" href="css/strain_list.css" /> 

    <script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js'></script>
    <script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js'></script>  
    <script>window.jQuery || document.write('<script src="js/jquery-1.8.1.min.js"><\/script><script src="js/jquery-ui-1.8.18.min.js"><\/script><link type="text/css" rel="stylesheet" href="css/jquery-ui-1.8.18.css" />');</script>
 
    <script type='text/javascript' src='js/DataTables-1.9.4/media/js/jquery.dataTables.min.js'></script>
 
    <script type='text/javascript' src='js/jquery-rounded_corner/jquery.corner.mini.js'></script>     
    <script type='text/javascript' src='js/search_browse_strains_genes.js'></script>
   
</head>

<!--[if lte IE 7]>
  <style type="text/css">
    html .jquerycssmenu{height: 1%;} /*Hack for IE7 and below*/
  </style>  
<![endif]-->

<body>

<div id="strain_list_container"><a name='top'></a>

 <div id="search_bar">
    <?php include("strain_gene_search_bar.php"); ?>
 </div> <!-- end of search_bar --> 

 <div id="emma_wrapper">
  <div id="dataDiv">
    <?php include("strain_gene_tab_panels.php"); ?>
  </div>
 </div>

<?php
   
 $keyword = urldecode($_GET['keyword']);
 if ( $keyword ){
   // this allows url with keyword to add a tab
   // automatically
   echo "<div id='hiddenKW'>$keyword</div>";
 }
 else {
   // dummy text really, so that action to search and then add tab
   // will not take place by default   
   echo "<div id='hiddenKW'>__ajax__</div>";
 }
   
?>
</div>

 <script type='text/javascript'>	
     
    var url = "search_browse_emmastr_db.php";

 	var Loader = "images/loading_small.gif";

	// tab: browse by strains 
	function fetch_data_by_sublist(sublist){	

        var containerId = 'strain_data';
        var tableId = sublist;
	    $('#'+containerId).html("<div class='loader'><img src=\""+Loader+"\" /><span>loading ...</span></div>"); 	

        var aTableIds = [];
        if ( sublist == 'TM' || sublist == 'IN' || sublist == 'ALL' || sublist == 'Cre' ){    

            var url1 = fetch_url() + "?subType=" + sublist; 
           
            $.get(url1, function(data){
                aTableIds = JSON.parse(data);                              
                var url2 = fetch_url() + "?sublist=" + sublist;               
                loadDataTable(url2, containerId, aTableIds, 6);
            });   
        }
        else { 			
 		    var url = fetch_url() + "?sublist=" + sublist;
            aTableIds.push(sublist);
            loadDataTable(url, containerId, aTableIds, 6);	
        }
        

 		/*$.get(url, function(data){
 	     		$('#strain_data').html(data);
                $('table#sublist').dataTable();
 	     		highlight_hovered_tr($('#strain_data'));				
				stripe_table($('#strain_data'));	
 	     		initDescTabs($('#strain_data'));
 		});*/ 		
 	}
	// tab: browse by genes
	$('a.ltrLink').each(function(){
		$(this).click(function(){

            $('a.ltrLink').css({'color':'#104E8B', 'border':'none'});			
			$(this).css({'border':'1px solid gray', 'text-decoration':'none','padding':'0 2px'});

			var containerId = 'gene_data';            

			$('#' + containerId).html("<div class='loader'><img src=\""+Loader+"\" /><span>loading ...</span></div>");
            
			var letter = $(this).attr('id');
            var url = fetch_url() + "?letter=" + letter;             

            var tableId = 'geneName' + letter;
            var aTableIds = [];
            aTableIds.push(tableId);
            loadDataTable(url, containerId, aTableIds, 6);

            /*var mode = 'gene_dataTbl';
            var dTable = $('<table></table>').attr({'id': mode});	    		    	
	 	   	var thead = EMMA.config.gene.tableHeader;
	 	   	var tds = '';
	 	   	for (var i=0; i<EMMA.config.gene.tableCols; i++){		 	   
			   		tds += "<td></td>";
	 	   	}
	 	   	var tbody = $('<tbody><tr>' + tds + '</tr></tbody>');
	 	   	dTable.append(thead, tbody);            			
            $('#gene_data').html(dTable);		

            // fetch dataTable from server side
            var oInfos = {};
            oInfos.mode = mode;
            oInfos.letter = letter;
            //oInfos.ajaxSrc = fetch_url() + '?letter=' + letter;
            oInfos.ajaxSrc = fetch_url();           
            invokeDataTable(oInfos);
            */
		});
	});

 	// tab: browse_by_human_diseases
 	$('a.omLtrLink').each(function(){
		$(this).click(function(){
			
			$('a.omLtrLink').css({'color':'#104E8B', 'border':'none'});			
			$(this).css({'border':'1px solid gray', 'text-decoration':'none','padding':'0 2px'});

			var containerId = 'omim_emma';
            
			$('#'+containerId).html("<div class='loader'><img src=\""+Loader+"\" /><span>loading ...</span></div>");

			var letter = $(this).attr('id');
			var url = fetch_url() + "?omLetter=" + letter;
            var tableId = 'omim' + letter;

            var aTableIds = [];
            aTableIds.push(tableId);

            loadDataTable(url, containerId, aTableIds, 7);
		});
	});
 	
	// initialize the above tabs
    var $tabs = $('#strainTabs').tabs({
		selected: 0,
        cache   : true,
        spinner : 'searching ...',
        add: function(e, ui) {  
    			// 'add' is a listener for 
    			// eg, $('#strainTabs').tabs('add', url, srchTxt);                 
				// and appends a close button
                $(ui.tab).parents('li:first')
                	.append('<span class="killTab" title="Close Tab"> x </span>')
                    .find('span.killTab')
                    .click(function() {
                    	$tabs.tabs('remove', $('li', $tabs).index($(this).parents('li:first')[0]));
                    });
	               // select just added tab
     	       	$tabs.tabs('select', '#' + ui.panel.id);

     	       	// define autowidth for data container
     	       	$('#' + ui.panel.id).css({'padding': 0, 'width':'auto'});      	       	    	       	       	    	                     
		},
		load: function(event, ui) {							
            // do something with loaded table            
            var containerId = ui.panel.id;
            var oTable = $('div#'+ui.panel.id).find('table');
            var aTableIds = [];
            aTableIds.push(oTable.attr('id'));            
            var iCols = oTable.find('th').size();
            loadDataTable(null, containerId, aTableIds, iCols);
            
            
		}  	            
 	}); 

 </script>
</body>
</html>

