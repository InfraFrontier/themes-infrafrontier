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


//require('ontology/scripts/jsonwrapper/jsonwrapper.php'); // our server is still < than PHP 5.2
require_once("tools/dompdf-0.5.1/dompdf_config.inc.php");
require_once("class.EMMA_SQL.php");
require_once("mut_types.php");

$idstr    = sprintf("%05d", $_POST['idstr']);
$datetime = urldecode($_POST['datetime']);

$emmaSql = new EMMA_SQL();

$DATA = $emmaSql->compose_strain_description($idstr, $mutype, 'pdf'); 
$strain_desc = $DATA['desc'];

$qcTables;
if ( $DATA['qcTables'] != '' ){
	$qcTables    = "<div id='qcData'>QC/Screening Data</div>" . $DATA['qcTables'];
}	

$names       = $emmaSql->fetch_names_by_id_str($idstr);
$header      = "<div id='hdesc'><img src='images/logo-emma.jpg' />"			
             .  "<span id='id'>EM:$idstr General Strain Description</span><br>";

$name_tbl    = "<table id='names' border=0>";        
foreach ( $names as $name=>$val ){	
	$name_tbl .= "<tr><td>$name:</td><td class='names'>$val</td></tr>";
} 
$name_tbl .= '</table>';

$header   .= $name_tbl;
$header   .= "</div><div id='date'>Data as of $datetime (localtime)</div>";

$html =<<<HTML
	<html>
    	<head>
    	   <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    		<style type='text/css'>
			@page { margin: 0.5in 0.2in 0.5in 0.2in;} 
    			html {font-family: arial; font-size: 12px;}
    			body {maring: 30px;}
    			img {display: inline;}    			
    			div#hdesc {margin: 30px 50px 0 50px;}
    			#hdesc span#id {line-height: 35px; 
    						 	padding-left: 10px;    						     						 
    						 	font-size: 14px; 
    						 	font-weight: bold;    						 
    			}    						 
    			span.names {font-size: 11px;}			 
    			div#date {margin: 10px 50px 0 50px; font-size: 10px;}		 
    			table {width: 100%; 
    				   border-collapse: collapse; 
    				   margin: 10px 50px 30px 50px;
    			}   
    			table#names {margin: 0;} 
    			table#names td.names {width: 70%;}		
    			td {border: 1px solid gray; padding: 3px;}
    			.desc_field {width: 20%; 
    						 font-weight: bold; 
    						 padding: 3px; 
    						 text-align: left; 
    						 vertical-align: middle;
    			}
    			.infosec {background-color: #CDC8B1; text-align: center;}
    			sup {line-height: 15px;}
    			a {color: #104E8B;}
				div.qc_block {width: 100%; 
							  background-color: #CDC8B1; 
							  text-align: center;
							  padding: 5px; 	
							  font-weight: bold;
							  margin: 0 50px;	
				}
				div#qcData {font-weight: bold; text-align: center; margin-bottom: 10px;}
				div.qcCap {margin-left: 50px; font-weight: bold;}
    		</style>   		  		
    	</head>    	
    	<body>$header $strain_desc $qcTables</body>
    </html>
HTML;


$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream("EMMA:$idstr".'_strain_desc.pdf', array("Attachment" => 0)); // opens up in browser window/tab
//$dompdf->stream("EMMA$idstr".'_strain_desc.pdf');

?>
