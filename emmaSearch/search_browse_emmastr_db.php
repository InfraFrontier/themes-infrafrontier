<?php 

// Author: Chao-Kung Chen (ckchen@ebi.ac.uk)
// This script provides functionalities for searching / browsing genes and strains of EMMA

#require_once('ontology/scripts/jsonwrapper/jsonwrapper.php'); // our server is still < than PHP 5.2
require_once("class.EMMA_SQL.php");
require_once("mut_types.php");

// makes sure PHP doc handles utf-8
header('Content-Type:text/html; charset=UTF-8');

$emmaSql = new EMMA_SQL();

$sqla = <<<MS
  SELECT DISTINCT s.emma_id,       
  GROUP_CONCAT(distinct(g.symbol), '*__*') as symbol,
    ss.name as synonym, 
    s.id_str, 
    s.name,       
    s.str_status,    
    s.available_to_order,     
    s.code_internal,
    a.mgi_ref
  FROM       
    alleles a,       
    mutations m,       
    mutations_strains ms,   
MS;

$sqlb = <<<MS2
  strains s LEFT JOIN syn_strains ss ON ss.str_id_str=s.id_str,
  genes g LEFT JOIN syn_genes sg ON sg.gen_id_gene=g.id_gene      
  WHERE g.id_gene=a.gen_id_gene     
  AND a.id_allel=m.alls_id_allel     
  AND m.id=ms.mut_id     
  AND ms.str_id_str=s.id_str     
  AND s.str_access = 'P'     
  AND s.str_status IN ('TNA','ARRD','ARING','ARCHD')
MS2;

$sqlc = <<<MS3
  SELECT DISTINCT ao.id_allel, ao.omim_name, ao.omim_id, ao.mgi_internal_omim_id, s.emma_id,      
  GROUP_CONCAT(distinct(g.symbol), '*__*') as symbol,
    ss.name as synonym, 
    s.id_str, 
    s.name,       
    s.str_status,    
    s.available_to_order,     
    s.code_internal,
    a.mgi_ref
  FROM       
    alleles a,       
    mutations m,       
    mutations_strains ms,   
MS3;

$sql       = '';
$qrystr    = '';
$tblClass  = 'resultSet';

$rtools = $emmaSql->fetch_rtools();

$code_rtls_id = $rtools['id'];
$rtls_id_type = $rtools['type'];

if ($_GET['subType']){  
    $list = array();
    $typelist = $emmaSql->get_subtypes($_GET['subType']);
    foreach ( $typelist as $subtype ){       
        $list[] = $subtype . '_2';
    }
    echo json_encode($list); // returns a list of mutant types for this subType
}
else if ( $_GET['sublist'] ){
        
    $code = $mode = $_GET['sublist'];         
    $tables = '';

    if ( $code == 'TM' or $code == 'IN' or $code == 'ALL' ){ // see strain_menu.php

        #-----------------------------
        #  concat individual tables
        #-----------------------------

        $typelist = $emmaSql->get_subtypes($code);
        #echo var_dump($typelist);
        $tblId_record =  array();
        $internal_links = array();
                
        foreach ( $typelist as $subtype ){
            //echo "$subtype <br>";
            $mode = $subtype . '_2';
            $type_desc = $mutype[$subtype];
                           
            array_push($internal_links, "<a class='a_name' href=\"#${subtype}i\" title='$type_desc'> $type_desc </a>");
            if ( array_key_exists($subtype, $code_rtls_id) ) {
                #echo "rtools $subtype - $code<br>";
                $sql = $emmaSql->fetch_rtool_sql($code_rtls_id[$subtype], $sqla, $sqlb);
                #echo "$sql <br>";

                $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr, $mode);  
                $caption = "<div class='strainType'><a name=${subtype}i> $mutype[$subtype] </a><a class='top' href='#top'>Top</a></div>";
                $tblId_record[$mode] = $DATA['record'];                     
                #$table .= $caption . $DATA['record'] . $DATA['result'];
                $table .= $caption . $DATA['result'];
            }
            else {
                $sql = $emmaSql->fetch_mutype_sql($subtype, $sqla, $sqlb);
                $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr, $mode); 
                $caption = "<div class='strainType'><a name=${subtype}i> $mutype[$subtype] </a><a class='top' href='#top'>Top</a></div>";
                $tblId_record[$mode] = $DATA['record']; 
                //$table .= $caption . $DATA['record'] . $DATA['result'];  
                $table .= $caption . $DATA['result'];                       
            }            
        }
        unset($DATA['record']);
        $DATA['records'] = json_encode($tblId_record);        
    }
    else {
        #------------------
        #  do single table
        #------------------

        if ( array_key_exists($code, $code_rtls_id) ) {         
            $sql = $emmaSql->fetch_rtool_sql($code_rtls_id[$code], $sqla, $sqlb);
        }
        else { 
            $sql = $emmaSql->fetch_mutype_sql($code, $sqla, $sqlb); 
        }

        $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr, $mode);  

        # strain introduction info for DEL, LEX, EUCOMM ...
        $strain_info = $emmaSql->fetch_strain_intro($code);       
        $caption =  "<div class='strainType'> $mutype[$code] </div>";            
        $table .= $caption . $strain_intro . $DATA['result'];
    }

    if ( $internal_links ){
        //echo "<div id='alinks'>" . implode(' | ', $internal_links) . "</div>";    
        $table = "<div id='alinks'>" . implode(' | ', $internal_links) . "</div>" . $table;
    }

    $DATA['result'] = $table;
    to_browser($DATA);
    //echo $table;
    
}
else if ( isset($_GET['gene_dataTbl']) ){
    $params = json_decode($_GET['gene_dataTbl']);
    $letter = $params->{'letter'}; 
   
    $qrystr = $letter;
    $letter = "'$letter"."%'";
    $restriction = strlen($qrystr) == 1 ? " LIKE $letter" : " REGEXP '^[0-9]'";
    
    $sql = $sqla . $sqlb . " AND g.symbol $restriction";
 

    $dataType = 'gene';
    $DATA = $emmaSql->makeDataTable($sql, $_POST, $_GET, $tblClass, $qrystr, $dataType, $_GET);  
    //echo print_r($DATA);
    to_browser($DATA);
}
else if ( isset($_GET['letter']) ){
    // gene name
    $letter = $_GET['letter'];
    $mode = 'geneName' . $letter;
    $qrystr = $letter;
    $letter = "'$letter"."%'";
    $restriction = strlen($qrystr) == 1 ? " LIKE $letter" : " REGEXP '^[0-9]'";
    
    $sql = $sqla . $sqlb
    . " AND g.symbol $restriction";                 
  
    $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr, $mode);  
    to_browser($DATA);              
}
else if ( isset($_GET['omLetter']) ){
    // omim
    $letter = $_GET['omLetter'];    
    $mode = 'omim' . $letter;        

    $qrystr = $letter;
    $letter = "'$letter"."%'";
    $restriction = strlen($qrystr) == 1 ? " LIKE $letter" : " REGEXP '^[0-9]'";
    
    $sql = $sqlc . ' alleles_omims ao, ' . $sqlb
    . " AND a.id_allel = ao.id_allel AND ao.omim_name $restriction";  
    
    $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr, $mode);  
    to_browser($DATA);
}
else if ( isset($_GET['nodeId']) ){
    $node_id = $_GET['nodeId'];
    $term_infos = $emmaSql->fetch_term_infos_by_node_id($node_id); 
}          
else if ( isset($_POST['id_strs']) ){
    // MP ontology browse
    $id_strs = $_POST['id_strs'];   
    $sql = $sqla . $sqlb . " AND s.id_str in ($id_strs) ";

    $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr);  
    to_browser($DATA);
}
else if ( isset($_GET['query']) ){
    $qrystr = '%' . $_GET['query'] .'%';   
   	$restriction = "LIKE '$qrystr'";

    /*$qrystr1 = trim($_GET['query']);
    $qrystr = '%' . $qrystr1 .'%';   
    $restriction = "LIKE '$qrystr'";

    $id_str_restriction = null;

    if ( preg_match('/^\d+$/', $qrystr1) ){
        $id_str_restriction = $qrystr1;              
    }
    else if (preg_match('/^EM:(\d+)$/i', $qrystr1, $matches) ){
        $id_str_restriction = $matches[1];
    } 
    */
    $randomId = intval(rand());

    $sql = "SELECT DISTINCT ao.omim_name, ao.omim_id, ao.mgi_internal_omim_id, s.emma_id,      
  			GROUP_CONCAT(distinct(g.symbol), '*__*') as symbol,
    			ss.name as synonym, 
    			s.id_str, 
    			s.name,       
    			s.str_status,    
    			s.available_to_order,     
    			s.code_internal,
    			a.mgi_ref
  			FROM       
    			alleles a LEFT JOIN alleles_omims ao ON a.id_allel=ao.id_allel,  
    			mutations m,      
    			mutations_strains ms,    
   		 		strains s LEFT JOIN syn_strains ss ON ss.str_id_str=s.id_str,   		 		
  				genes g LEFT JOIN syn_genes sg ON sg.gen_id_gene=g.id_gene  				      
  			WHERE g.id_gene=a.gen_id_gene     
  			AND a.id_allel=m.alls_id_allel     
  			AND m.id=ms.mut_id     
  			AND ms.str_id_str=s.id_str     
  			AND s.str_access = 'P'     
  			AND s.str_status IN ('TNA','ARRD','ARING','ARCHD')"  	 
    	 . " AND (g.symbol      $restriction"
     	 . " OR g.name          $restriction"
         . " OR sg.symbol       $restriction"
     	 . " OR s.name          $restriction"
     	 . " OR s.code_internal $restriction"
     	 . " OR ss.name         $restriction"
     	 . " OR s.pheno_text    $restriction"
     	 . " OR s.pheno_text    $restriction"
     	 . " OR ao.omim_name    $restriction"
     	 . " OR ao.omim_id      $restriction"
     	 . " OR convert(s.emma_id using latin1) collate latin1_general_ci $restriction)";  

    $mode = 'search'. $randomId;     
    $DATA = $emmaSql->make_table($sql, $_POST, $_GET, $tblClass, $qrystr, $mode);    
    //to_browser($DATA); 
    if ( $DATA ){
        echo $DATA['record'] . $DATA['result'];  
    }
    else {        
        echo "<span class='useMsg'>INFO: your search keyword returns nothing in the database</span>";
    }    
}
else if( isset($_GET['id_str']) ){
    // fetch strain desc into popup desc window
    $id_str = $_GET['id_str'];  
    
    $DATA =$emmaSql->compose_strain_description($id_str, $mutype);
  
    echo $DATA['descActionRow'] . $DATA['desc'] . $DATA['descActionRow'];
    exit;
}
/*else if ( isset($_GET['mp_idstr']) and isset($_GET['mp_id_allele']) ){
    // fetch info for annotated MP terms of allele
    $id_str = $_GET['mp_idstr'];
    $id_allele = $_GET['mp_id_allele'];
    $win_id = $_GET['win_id'];
    $tab_id = $_GET['tab'];
    
    $mpids = $emmaSql->fetch_mpids_of_strain_alleles_by_id_str_allel($id_str, $id_allele);  
    $emmaSql->compose_mp_list_display($mpids, $win_id, $tab_id);    
    exit; //don't want the JS
}
else if ( isset($_GET['q']) ){
    // jQuery autocomplete default parameter: q=    
    $term_qry = $_GET['q'];  
    
    // convert query in digits (ie, no MP:00xx prefix) to full term id
    $pattern = "/^\d+$/";
    if ( preg_match($pattern, $term_qry) ){
            $term_qry = 'MP:' . str_pad($term_qry, 7, "0", STR_PAD_LEFT);           
    }
    
    # MP term autocomplete: term ID (eg, MP:00005375) will be returned as term name
    if ( $nameString = $emmaSql->fetch_mp_term_names($term_qry) ){
        echo $nameString;
    }
    else {
        echo "Sorry. No match is found";
    }       
    exit; // js takes this parameter for term search
}
else if (isset($_GET['term_qry']) ){    
    # takes parameter return from autocomplete
    $term_qry = $_GET['term_qry'];  

    $DATA = $emmaSql->fetch_table_by_term_name($term_qry, $sqla, $sqlb, $_POST, $_GET);
    to_browser($DATA);      
}
else if (isset($_GET['twoUps_id']) or isset($_GET['node_id']) ){
    $two_ups_node_id = $_GET['twoUps_id'];
    $node_id = $_GET['node_id'];  
    $info['terminfo'] = $emmaSql->fetch_term_infos_by_node_id($node_id);
    
    echo json_encode($info);
    exit; //don't want the JS
}
else if ( isset($_GET['idstrAllele']) ){
    $idstr = $_GET['idstrAllele'];      
    echo json_encode($emmaSql->fetch_allele_info_of_strain($idstr));
    exit;
}
*/
#---------------------------------------------------------------------
#                        f u n c t i o n s
#---------------------------------------------------------------------
function to_browser($DATA){   
    //echo $DATA['record'] . $DATA['result']; 
    echo json_encode($DATA);
}

?>

