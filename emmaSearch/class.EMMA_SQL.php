<?php 
#require('ontology/scripts/jsonwrapper/jsonwrapper.php'); // our server is still < than PHP 5.2
require_once("class.database.php");
require_once("mut_types.php");
$db = new database();

#even when mysql server is set to use utf8, the client still needs to be set accordingly
$db->db_fetch("SET NAMES utf8");

# serverside dataTable stuff
$sLimit = ""; // paging
$sOrder = ""; // ordering
$sWhere = ""; // filtering

$drupalScriptPath = '/sites/infrafrontier.eu/themes/custom/infrafrontier/emmaSearch';

class EMMA_SQL {

	function fetch_rtools(){
		$code_rtls_id = array(
		      'Cre' => 1,
		      'FLP' => 3,
		      'TET' => 5,
		      'LEX' => 6,
		      'DEL' => 7,
		      'EUC' => 9
		      );

		$rtls_id_type = array_flip($code_rtls_id);
		$rtools['id'] = $code_rtls_id;
		$rtools['type'] = $rtls_id_type;
		return $rtools;	
	}	
	function fetch_allele_ids_by_strain_id($id_str){
		global $db;
		$sql = "SELECT a.id_allel
			    FROM strains s, mutations_strains ms, mutations m, alleles a
		    	WHERE s.id_str=ms.str_id_str 
		    	AND ms.mut_id=m.id 
		    	AND m.alls_id_allel=a.id_allel	
		    	AND s.id_str=$id_str";
		#echo "$sql<br>";		
		$rows = $db->db_fetch($sql);

		$ids = array();
		
		
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){
				$id = $row['id_allel'];				
				$ids[] = $id; 							
    		}    		
    		return $ids; 		   		
  		}	
	}
	function fetch_omim_by_strain_id($id_str) {	
		// MGI associated human diseases for models involving the same allele
  		$alIds = $this->fetch_allele_ids_by_strain_id($id_str);
  		if ( $alIds[0] ){  		
  			$omimRows = $this->fetch_omim_info_by_allele_id_list($alIds);
  			  			
  			if ( $omimRows[0] ){
  				$omimInfos = false;	
  				foreach ( $omimRows as $row ){  							
					$links = $this->fetch_omim_display($row);
					$spacer = "&nbsp;&nbsp;";			 
					$omimInfos[] = "<li class='omd'>{$links['omimName']} $spacer / $spacer {$links['omimID']}</li>";									
				}
				$data = join("", $omimInfos); 
				$field = "MGI associated human diseases for models involving the same allele";	 	
				return "<tr><td class='desc_field'>$field</td> <td>$data</td></tr>";			
  			}  			
  		}
  		else {
  			return false;
  		}
	}	
	function fetch_omim_info_by_allele_id_list($alIds){
		global $db;	  	
		$ids = join(",", $alIds);		
		$sql = "select distinct omim_id, omim_name, mgi_internal_omim_id from alleles_omims where id_allel in ($ids)";		
	  	$rows = $db->db_fetch($sql);
	  	
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error() . $sql );
	  	}
	  	else {	  		
	  		return $rows;
	  	}	 	
	}
	function compose_strain_description($id_str, $mutype, $pdf){
		
		$qcTables = false;
		$desc  = '';
	  	$avais = '';
	
		$material_info = '';
		$provider_info = '';
		$emma_info = '';
		$emma_info .= $this->fetch_omim_by_strain_id($id_str);  		
		$mta_info      = '';
		$archiving_ctr = '';
		$geno_protocol = '';		
				
		// emmaId, Genetic background, Breeding history, Homozygosity required, Phenotype description, Genetic description, Original producer, Additional owner 
		$sql1 = "SELECT s.emma_id,
                      GROUP_CONCAT(distinct bg.name) AS 'Genetic background', 
			          s.maintenance AS 'Breeding history', 
		              s.pheno_text, 
		              s.code_internal, 
		              s.charact_gen AS 'Genetic description', 
		              p.firstname, 
		              p.surname, 
		              s.ex_owner_description AS 'Additional owner', 
		              s.mta_file AS 'MTA', 
		              s.genotype_file AS 'Genotyping protocol', 
		              s.mutant_viable AS 'Homozygous viable?', 
		              s.mutant_fertile AS 'Homozygous fertile?', 
		              s.require_homozygous AS 'Homozygous matings required?', 
		              s.immunocompromised AS 'Immunocompromised?' ,
                              s.europhenome_data_exists,
                              s.sanger_phenotype_data_exists
		           FROM backgrounds bg, people p, strains s, mutations_strains ms, mutations m 
		           WHERE s.bg_id_bg=bg.id_bg 
		           AND s.per_id_per=p.id_per 
		           AND s.id_str=ms.str_id_str 
		           AND ms.mut_id=m.id 
		           AND s.id_str=$id_str 
		           GROUP BY bg.name";
		#echo $sql1;
		// references
		$sql2 = "SELECT b.title, b.author1, b.author2, b.year, b.journal, b.volume, b.pages, b.pubmed_id 
		  	       FROM strains s 
		  	       LEFT JOIN biblios_strains bs 
		  	       ON s.id_str=bs.str_id_str 
		  	       LEFT JOIN biblios b 
		  	       ON bs.bib_id_biblio=b.id_biblio 
		  	       WHERE b.id_biblio IS NOT NULL 
		  	       AND s.id_str=$id_str";
		
		// availabilities
		$sql3 = "SELECT DISTINCT ca.to_distr, ca.code, ca.description, s.str_status, s.available_to_order 
		  	       FROM strains s 
		  	       LEFT JOIN availabilities_strains abs 
		  	       ON s.id_str=abs.str_id_str 
		  	       LEFT JOIN cv_availabilities ca 
		  	       ON abs.avail_id=ca.id 
		  	       WHERE ca.id IS NOT NULL 
		  	       AND ca.to_distr=1 
		  	       AND s.id_str=$id_str";
		
		// archiving centre 
		$sql4 = "SELECT s.owner_xref as 'Other EMMA lines from the same provider', 
             s.mutation_xref as 'Other EMMA lines with the same mutation',  
             CONCAT(l.name, ', ', l.town, ', ', l.country) as 'Archiving centre',
             a.breeding as 'Breeding at archiving centre',             
             a.embryo_state as 'Stage of embryos',
             a.male_bg_id,
             a.female_bg_id,
             a.males, a.females,
             a.id,
             s.emma_id
           FROM strains s, laboratories l, archive a 
           WHERE a.id = s.archive_id
           AND a.lab_id_labo=l.id_labo
           AND id_str=$id_str";	
			
		// QC/Screening data  		
  		$sql5 = "SELECT DISTINCT s.code_internal, l.kermits_center 
  			 FROM strains s, rtools_strains rt, archive a, laboratories l 
  			 WHERE s.id_str=rt.str_id_str 
  			 AND rt.rtls_id=9 
  			 AND s.archive_id=a.id 
  			 AND a.lab_id_labo=l.id_labo 
  			 AND s.str_access='P'
  			 AND s.id_str=$id_str";  		
  		
  		#echo "$sql5<br>";		
		
		$sqls = array($sql1, $sql2, $sql3, $sql4, $sql5);		

        $bottom_icon_rows = array();
      	
		global $db;
				
		for ( $i=0; $i<count($sqls); $i++ ){	
			#echo $sqls[$i] . "<p>";
			$rows = $db->db_fetch($sqls[$i]);			
			if ($rows == 'ERROR'){			
				die("Can't execute query: ".mysql_error());
		    }
		    else {		    
		    	switch($i){			
		    	
		      	case 0:		      	
				// Genetic background, Breeding history, Homozygosity required, Phenotype description, Genetic description, Provider, Additional owner
				foreach ( $rows as $row ){
									
			  		foreach ( array('emma_id', 'Provider', 'Additional owner', 'Strain type', 'Genetic description', 'Genotyping protocol', 'Phenotype description', 'References', 'Strain background', 'Archiving', 'MTA', 'Breeding history', 'Homozygous viable?', 'Homozygous fertile?', 'Homozygous matings required?', 'Immunocompromised?') as $field ){

                        if ( $field == 'emma_id' ){
                            $material_info .= "<tr><td class='emma_id'>EMMA ID</td><td>${row[$field]}</td></tr>";                                       

                            $gci = $this->fetch_gci_by_strain_id($id_str);
                            $material_info .= "<tr><td>Gene symbol</td><td class='gSymLinks'>${gci['geneNameLinks']}</td></tr>";
                            $material_info .= "<tr><td>Common strain name(s)</td><td>${gci['commonName']}</td></tr>";
                            $material_info .= "<tr><td>(International) strain designation</td><td>${gci['intlStrainName']}</td></tr>"; 
                        }
                        else if ( $field == 'Provider' ){
					    	$name = $row['firstname'] . ' ' . $row['surname'];
					      	if ( $name ){
								$provider_info .= "<tr><td class='desc_field'>$field</td> <td> $name </td></tr>"; 
					      	}
					    }
					    else if ( $field == 'Additional owner' ){
					    	$other_owner = $row[$field];
					      	if ( $other_owner ){
								$provider_info .= "<tr><td class='desc_field'> $field </td> <td> $other_owner </td></tr>"; 
					      	}
					    }
                        else if ( $field == 'Strain type' ){					    	
							$provider_info .= $this->fetch_strain_type($id_str); 					     
					    }
			  			else if ( $field == 'Phenotype description' ){
			      			$pheno_text = $row['pheno_text'];
						    $sanger_phenotype_data_exists = $row['sanger_phenotype_data_exists'];
						    $europhenome_data_exists = $row['europhenome_data_exists'];
						    $epd_id = $row['code_internal'];

						    if ($sanger_phenotype_data_exists == 'yes'){
						        $pheno_text = "Phenotype data at the <a href='http://www.sanger.ac.uk/mouseportal/search?query=$epd_id' target='_blank'>Sanger mouse portal</a>";
						    }
						    if ($europhenome_data_exists == 'yes'){
						        if ($sanger_phenotype_data_exists == 'yes'){
						        $pheno_text = "$pheno_text. Phenotype data at <a href='http://www.europhenome.org/databrowser/lineSummary.jsp?epd=$epd_id' target='_blank'>Europhenome</a>";
						        }
						        else{
						            $pheno_text = "Phenotype data at <a href='http://www.europhenome.org/databrowser/lineSummary.jsp?epd=$epd_id' target='_blank'>Europhenome</a>";
						        }
						    }

			      			if ( $pheno_text ){
								if ( $new_text = $this->superscript_munging($pheno_text) ){
				  					$pheno_text = $new_text;
								}
								$provider_info .= "<tr><td class='desc_field'>$field</td> <td>$pheno_text";								
			      			}
					  
			      			if ( $pheno_text !== 'No phenotype data was provided' ){
								$del_lex_info = $this->compose_DELTA_LEX_info($row['code_internal']);	      	    
								$provider_info .= $del_lex_info[0] . "</td></tr>";
									    
								if ( $del_lex_info[1] !== '' ){
									$provider_info .= "<tr><td class='desc_field'>Note</td><td>" . $del_lex_info[1] . "</td></tr>";
								}
			      			}
			    		}
					    else if ( $field == 'References' ){
					    	$ref = $this->fetch_references($sql2);					    	
				            if ( $ref ){
				               	$provider_info .= "<tr><td class='desc_field'>$field</td> <td><ul>$ref</ul></td></tr>"; 
				            }
					    }					   
					    else if ( $field == 'MTA' ){
					    	$mta_file = $row[$field];
                                
					      	$mta = "Distribution of this strain is subject to a Material Transfer Agreement (MTA)."
					      	     . "<b> Both signing of the <a href='mtas/$mta_file' target='_blank'>MTA</a> "
					      	     . "  and submission of the online EMMA<br>Mutant Request Form are required</b> before "
					      	     . "frozen material or live mice can be shipped.";

                            $bottom_icon_rows['mta'] = false; // default
					      	if ( $mta_file ){

                                $bottom_icon_rows['mta'] = $mta_file;

								$mta_info .= "<tr><td class='desc_field'>$field</td> <td>$mta</td></tr>";
								// add disclaimer for EUCOMM lines
								if ( $disclaimer = $this->isEucomm($id_str) ){
						  			$mta_info .= "<tr><td class='desc_field'>Disclaimer</td> <td>$disclaimer</td></tr>";	
								}
					      	}            
					    	$material_info .= $mta_info;
					    }					    
					    else if ( $field == 'Genotyping protocol' ){
					    	if ( $filename = $row[$field] ){
								$gtfile = "<a href='http://www.emmanet.org/genotype_protocols/$filename' target='_blank'>$filename</a>";
                                $bottom_icon_rows['genotyping'] = $filename; 

								$geno_protocol = "<tr><td class='desc_field'>$field</td> <td>$gtfile</td></tr>"; 					        	
					   		}
					    }
					    else {                           
					    	if ( isset($row[$field]) && $row[$field] != '' ){
					    		$data = $row[$field];
								$data = $this->capitalize_first_letter_of_sentence($data);
								if ( $new_text = $this->superscript_munging($data) ){
						  			$data = $new_text;
								}			
								$provider_info .= "<tr><td class='desc_field'>$field</td> <td>$data</td></tr>"; 
					      	}
					    }					    
			  		}
				}
				
				break;
			
		      	case 2:
				// availabilities
				
				$avs;
				foreach ( $rows as $row ){
					
					#while ( $row = mysql_fetch_assoc($sbottom_icon_rowsth) ){
					$order_label = $this->fetch_order_label($row['str_status'], $row['available_to_order']);
                                       
					if ( $order_label == 'register interest' ) {
                        $bottom_icon_rows['availabilities'] = false;
					   	break;
					}
					if ( $row['to_distr'] == 1 ){
					  	$code     = $row['code'];
					    $price    = ($code == 'R' or $code == 'L') ? "&#128;2400." : "&#128;1100.";
						$shipping = $code == 'R' ? 'Delivered in 4-6 months (after paperwork in place).' : 'Delivered in 4 weeks (after paperwork in place).';
						$gdesc = isset($row['description']) ? $row['description'] : '';
						$avs     .= "<li>" . $gdesc . ". $shipping $price</li>";                                         
					}
				}
		
				if ( isset($avs) ){
                  $bottom_icon_rows['availabilities'] = true; 
                  $avs = "<ul> $avs </ul>" . "<br>Due to the dynamic nature of our processes strain availability may change at short notice. 
                                 The local repository manager will advise you in these   circumstances.";  
				  $material_info .= "<tr><td class='desc_field'> Availabilities </td><td>$avs</td></tr>";
				}			
				break;			
				
		      	case 3:
				// archiving info		    	
			  	foreach ( $rows as $row ){	
			  		
					foreach ( array('Archiving centre', 'Animals used for archiving', 'Breeding at archiving centre',
							 'Stage of embryos', 'Other EMMA lines from the same provider', 'Other EMMA lines with the same mutation') as $field ) {
	   		        
	 		   			if ($field == 'Animals used for archiving'){
	 		   			    
	   		 				if (is_null($row['male_bg_id']) 
	    						&& is_null($row['female_bg_id']) 
	    						&& is_null($row['males']) 
	    						&& is_null($row['females'])){
                                // not doing anything for now
							}
							else {                               
								$arch_bg = $this->concat_bg($row['id'], $row['male_bg_id'], $row['female_bg_id'], $row['males'], $row['females'] );
                                if ( $arch_bg != '' ){
								    $arch_bg = $this->capitalize_first_letter_of_sentence($arch_bg);
								    $emma_info .= "<tr><td class='desc_field'>$field</td> <td>$arch_bg</td></tr>";
                                }
								$mat_in_repos_desc = $this->capitalize_first_letter_of_sentence($this->fetch_material_in_repository_desc($row['males'], $row['females']));
                                if ( $mat_in_repos_desc != '' ){
								    $emma_info .= "<tr><td class='desc_field'>Material in repository</td> <td>$mat_in_repos_desc</td></tr>";						
                                }
							}
		 				}
		 				else if ( $field == "Stage of embryos" ){
						  if ($row[$field]){
						    $emma_info .= "<tr class='emma_show_hide emma'><td class='desc_field'>$field</td> <td>" . $row[$field] . "</td></tr>";
						  }
		 				}

						else if ( $field == "Archiving centre" ){
						  if ( $data = $row[$field] ){
						    $data = $this->capitalize_first_letter_of_sentence($data);
						    if ($data == 'MRC, Medical Research Council, Harwell, Didcot, United Kingdom'){
						      $emmaid = $row['emma_id'];
						      $mouse_book_link = "Potential extra data at <a href='http://www.mousebook.org/index.php?search=$emmaid'>MouseBook</a>.";
						      $emma_info .= "<tr><td class='desc_field'>$field</td> <td>$data. $mouse_book_link</td></tr>";
						    }
						    else{
						      $emma_info .= "<tr><td class='desc_field'>$field</td> <td>$data</td></tr>";
						    }
						  }
						}

		 				else {
						  if ( $data = $row[$field] ){
						    $data = $this->capitalize_first_letter_of_sentence($data);
						    $emma_info .= "<tr><td class='desc_field'>$field</td> <td>$data</td></tr>";
						  }
						}
	  				}
				}			  		
				break;
				
				case 4:
    			//QC/Screening data       
    			$kburl = "http://www.knockoutmouse.org/kb/entry/90/";
    			foreach ( $rows as $row ){      				
					$cname = $row['code_internal'];
					$center = $row['kermits_center'];
								
					$qcTables = $this->fetch_qc_tables($this->fetch_ws_xml($cname, $center));
					                  
					$viewData = "<span class='qc'>View data</span>";
					$qcTablesDiv = "<div id='qcData'>$qcTables</div>";
					$kbLink = "<a id='qcKB' href='$kburl' target='_blank'>Info</a>";
					
					$provider_info .= "<tr><td class='desc_field'>QC/Screening data</td> 
			                       	       <td><div>
			                       				$viewData
			                           			$kbLink
			                           		   </div>
									   		   $qcTablesDiv
								   		   </td></tr>";					
    			}				
    			break;				
		    	}
			}
		}
		$pricingAndDeliveryLink = "<a href='http://www.emmanet.org/strains.php' target='_blank'>";
		$material_info .= "<tr><td colspan=2>${pricingAndDeliveryLink} More detail on pricing and delivery times</a></td></tr>"; 
        $bottom_icon_rows['pricingAndDelivery'] = $pricingAndDeliveryLink . "Pricing and Delivery</a>";

        $desc .= "<h3>General information</h3><div><table class='desc'>$material_info</table></div>";		
		
		$emma_info .= $geno_protocol; 
        
        $healthReportFile = $this->fetch_health_report_file($id_str);
        $bottom_icon_rows['healthReportFile'] = $healthReportFile;
        $healthReportFileUrl = "http://www.emmanet.org/procedures/${healthReportFile}";
        $emma_info .= "<tr><td>Example health report</td><td><a href=$healthReportFileUrl target='_blank'>${healthReportFile}</a></td></tr>";
     
		if ($provider_info){			
		    $provider .= $provider_info;		   
		    $desc .= "<h3>Information from provider</h3><div><table class='desc'>$provider</table></div>";
		}
		if ($emma_info){		    
		    $emma .= $emma_info;		    		   
		    $desc .= "<h3>Information from EMMA</h3><div><table class='desc'>$emma</table></div>";
		}
        
        $desc = "<div class='descAccordion' id='descAccordion${id_str}'>$desc</div>";        	
		
		$DATA = false;	 
       
        // compose bottom icon rows in strain desc 
        $descActionRow = $this->compose_bottom_icon_rows($id_str, $bottom_icon_rows);

        $DATA['descActionRow'] = $descActionRow;      
        $DATA['desc'] = $desc;
		
        return $DATA; 
	}	
    function fetch_health_report_file($id_str){
        global $db;

        $sql = "select l.filename from sources_strains ss, laboratory_health_report l where ss.str_id_str=${id_str} and ss.lab_id_labo=l.id_labo";
        $rows = $db->db_fetch($sql);			
		if ($rows == 'ERROR'){			
			die("Can't execute query: ".mysql_error());
	    }
        else {                    
            return $rows[0]['filename'] == '' ? 'example-health-report_EMMA.pdf' : $rows[0]['filename'];            
        }
    }
	function fetch_ws_xml($cname, $center){
		// mouse and es cell QC	
		return 'http://www.i-dcc.org/biomart/martservice?query='.
		urlencode('<?xml version="1.0" encoding="UTF-8"?>
		<!DOCTYPE Query>
		<Query  virtualSchemaName = "default" formatter = "TSV" header = "0" uniqueRows = "0" count = "" datasetConfigVersion = "0.6" >	
         <Dataset name = "idcc_targ_rep" interface = "default" >
			<Attribute name = "production_qc_five_prime_screen" />
			<Attribute name = "production_qc_loxp_screen" />
			<Attribute name = "production_qc_three_prime_screen" />
			<Attribute name = "production_qc_loss_of_allele" />
			<Attribute name = "production_qc_vector_integrity" />
			<Attribute name = "distribution_qc_karyotype_high" />
			<Attribute name = "distribution_qc_karyotype_low" />
			<Attribute name = "distribution_qc_copy_number" />
			<Attribute name = "distribution_qc_five_prime_lr_pcr" />
			<Attribute name = "distribution_qc_five_prime_sr_pcr" />
			<Attribute name = "distribution_qc_three_prime_sr_pcr" />
			<Attribute name = "distribution_qc_thawing" />
			<Attribute name = "user_qc_southern_blot" />
			<Attribute name = "user_qc_map_test" />
			<Attribute name = "user_qc_karyotype" />
			<Attribute name = "user_qc_tv_backbone_assay" />
			<Attribute name = "user_qc_five_prime_lr_pcr" />
			<Attribute name = "user_qc_loss_of_wt_allele" />
			<Attribute name = "user_qc_neo_count_qpcr" />
			<Attribute name = "user_qc_lacz_sr_pcr" />
			<Attribute name = "user_qc_five_prime_cassette_integrity" />
			<Attribute name = "user_qc_neo_sr_pcr" />
			<Attribute name = "user_qc_mutant_specific_sr_pcr" />
			<Attribute name = "user_qc_loxp_confirmation" />
			<Attribute name = "user_qc_three_prime_lr_pcr" />
			<Attribute name = "user_qc_comment" />
		</Dataset> 
                <Dataset name = "imits" interface = "default" >
                         <Filter name = "escell_clone" value = "' . $cname . '"/>
                         <Filter name = "microinjection_status" value = "Genotype confirmed"/>
                         <Filter name = "distribution_centre" value = "' . $center . '"/>
			<Attribute name = "qc_southern_blot" />
			<Attribute name = "qc_tv_backbone_assay" />
			<Attribute name = "qc_five_prime_lr_pcr" />
			<Attribute name = "qc_loa_qpcr" />
			<Attribute name = "qc_homozygous_loa_sr_pcr" />
			<Attribute name = "qc_neo_count_qpcr" />
			<Attribute name = "qc_lacz_sr_pcr" />
			<Attribute name = "qc_five_prime_cassette_integrity" />
			<Attribute name = "qc_neo_sr_pcr" />
			<Attribute name = "qc_mutant_specific_sr_pcr" />
			<Attribute name = "qc_loxp_confirmation" />
			<Attribute name = "qc_three_prime_lr_pcr" />
		</Dataset>
	   </Query>');
	}
	function fetch_qc_tables($url){

		$Vals = split("\t", file_get_contents($url));
	
		$vals = false;
		foreach ( $Vals as $idx=>$val ) {
			if ( ! $val ){
				$val = '-';
			}
			$vals[$idx]=$val;
		}
	
		$tables = '';
	
		$tables .= "<div class='qc_block'>Mouse Production</div>";
		// kermits qc
		$tables .= $this->compose_qc_table(array("Southern Blot", "TV Backbone Assay", "5' LR-PCR", "Loss of WT Allele (LOA) qPCR",
									  "Homozygous Loss of WT Allele (LOA) SR-PCR", "Neo Count (qPCR)", 
									  "LacZ SR-PCR", "5' Cassette Integrity", "Neo SR-PCR",
									  "Mutant Specific SR-PCR", "LoxP Confirmation", "3' LR-PCR"), 
									array_slice($vals, 26, 12),
									''
									); 		

		$tables .= "<div class='qc_block'>ES Cell Clones With Conditional Potential</div>";
								
		// production center	
		$tables .= $this->compose_qc_table(array("5' Screen", "LoxP Screen", "3' Screen", 
						   			  "Loss of WT Allele (LOA)", "Vector Integrity"), 
									array_slice($vals, 0, 5),
									'Production Center'
									); 
	
		// distribution center		
		$tables .= $this->compose_qc_table(array("Karyotype High", "Karyotype Low", "Copy Number",  
						  			  "5' LR-PCR", "5' SR-PCR", "3' SR-PCR", "Cells Thawed Correctly"), 
									array_slice($vals, 5, 7),
									'Distribution Center'
									); 
	
		// user/mouse clinic	
		$tables .= $this->compose_qc_table(array("Southern Blot", "Map Test", "Karyotype", 
						  			  "TV Backbone Assay", "5' LR-PCR", "Loss of WT Allele (LOA)",
						   			  "Neo Count (qPCR)", "LacZ SR-PCR", "5' Cassette Integrity", 
						   			  "Neo SR-PCR", "Mutant Specific SR-PCR", "LoxP Confirmation",
						   			  "3' LR-PCR", "Comment"), 
									array_slice($vals, 12, 14),
									'User/Mouse Clinic'
									);	
	
		return $tables;		
	}
	function compose_qc_table($cols, $vals, $caption){
		$trs = '';	
		for ($i=0; $i<count($cols); $i=$i+3){		
			$tds = "<td class='qcCol'>{$cols[$i]}</td><td>{$vals[$i]}</td>
		  	        <td class='qcCol'>{$cols[$i+1]}</td><td>{$vals[$i+1]}</td>
		 	        <td class='qcCol'>{$cols[$i+2]}</td><td>{$vals[$i+2]}</td>";
			$trs .= "<tr>$tds</tr>";		
		}
        
		#return "<table class='qc'><caption>$caption</caption>$trs</table>";
		$caption = "<div class='qcCap'>$caption</div>"; # workaround as could not get caption to work in PDF
		return "$caption <table class='qc'>$trs</table>";
	}
	function fetch_strain_type( $id_str ){
		
		global $mutype;
		global $db;
		
	  	$type_sql = "SELECT DISTINCT m.main_type, m.sub_type 
	  				 FROM mutations_strains ms, mutations m 
	  				 WHERE ms.mut_id=m.id 
	  				 AND ms.str_id_str=$id_str";
			  	
	  	$rows = $db->db_fetch($type_sql);
		if ($rows == 'ERROR'){		
			die("Can't execute query: ".mysql_error());
		}
		else {
			$strain_types = array();
		 	foreach ( $rows as $row ){
		    #while ( $row = mysql_fetch_assoc($sth) ){
		
		    	$main_type = $row['main_type'];
		      	$sub_type  = $row['sub_type'];
		
		 	    $strain_type;
		     	if ($main_type && $sub_type) {
					$strain_type = $mutype[$main_type . $sub_type] 
			  		? $mutype[$main_type] . " : " . $mutype[$main_type . $sub_type] 
			  		: $mutype[$main_type]; 
		     	}
		      	else if ( $main_type && !$sub_type ){
					$strain_type = $mutype[$main_type];
		      	}
		    	array_push($strain_types, $strain_type);
		    }
		    
			return "<tr><td class='desc_field'>Strain type</td><td>" . implode("<br>", $strain_types) . "</td></tr>";
		}
	}	
	function superscript_munging($text){
	  
		$pattern = "/<a href/";
	  	if (preg_match($pattern, $text)) {
	    	return;
	  	}
	  	else {
	    	$substitue = array("<"=>"<sup>", ">"=>"</sup>");
	    	return strtr($text, $substitue);
	  	}
	}
	function concat_bg($archive_id, $male_bg_id, $female_bg_id, $males, $females){

	        if ($female_bg_id == '') {
		  $female_bg_id = 'NULL';
		}
		if ($male_bg_id == '') {
                  $male_bg_id = 'NULL';
                }

		$sqlm = "SELECT CONCAT('males were ', a.males, ' (', b.name, ')') as 'desc' ". 
	    	    "FROM  archive a, backgrounds b " . 
	        	"WHERE a.male_bg_id=b.id_bg AND a.male_bg_id=$male_bg_id";

	 	$sqlf = "SELECT CONCAT('females were ', a.females, ' (', b.name, ')') as 'desc' ". 
	    	    "FROM  archive a, backgrounds b " . 
	        	"WHERE a.female_bg_id=b.id_bg AND a.female_bg_id=$female_bg_id";
	
	 	global $db;
		$bgs = array();
	 	foreach ( array($sqlm, $sqlf) as $sql ){	 		
			$sql .= " AND a.id = $archive_id";
	
			$rows = $db->db_fetch($sql);
			if ($rows == 'ERROR'){	  			
	    		die("Can't execute query: ".$sql.":".mysql_error());
	  		}
	  		else {
	  			foreach ( $rows as $row ){
		   			array_push($bgs, $row['desc']);
		     	}
		   	}
	 	}	 			
		return implode(", ", $bgs);
	}	
	function capitalize_first_letter_of_sentence($str){
  		return substr_replace($str, strtoupper(substr($str, 0, 1)), 0, 1);
	}
	function fetch_order_label($str_status, $available_to_order) {
	  	$label = '';
	  	if ( $str_status == 'ARCHD' ){
			if ( $available_to_order == 'no' ) {
			//do nothing as there are instances where strains are archived but not available for order
			  $label = 'order';
			} else {
                	$label = 'order';
			}
	  	}
	  	else if ( $available_to_order == 'yes' ){
	    	$label = "order (only small colony available)";
	  	}
	  	else {
	    	$label = 'register interest';
	  	}	
	  	return $label;
	}
    function fetch_order_icon($label){
	    global $drupalScriptPath;
        $icon = false;
        if ( $label == 'order' ){
            $icon = "<img src='${drupalScriptPath}/images/green_dot_20.png' /><span class='orderTooltip'>Order mice</span>";
        }
        else if ( $label == 'register interest' ){
            $icon = "<img src='${drupalScriptPath}/images/red_dot_20.png' /><span class='orderTooltip'>Register interest</span>";
        }
        else if ( $label == 'order (only small colony available)' ){
            $icon = "<img src='${drupalScriptPath}/images/yellow_dot_20.png' /><span class='orderTooltip'>Order mice - only small colony available</span>";
        }
        return $icon;
    }
	function isEucomm($id_str) {
	  	$sql = "SELECT rtls_id FROM strains s, rtools_strains rs WHERE s.id_str=rs.str_id_str AND s.id_str=$id_str";	
		
	  	global $db;
	  	global $rtls_id_type;
	  	
	  	$rows = $db->db_fetch($sql);
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){
		    	#while ( $row = mysql_fetch_assoc($sth) ){  		
		      	if ( $rtls_id_type[$row['rtls_id']] == 'EUC' ) {
					return "Please note that for EUCOMM mice supplied to the scientific community by EMMA:<br>"
					  . "1) We can not guarantee a null mutation for Knock-out first alleles "
					  . "(tm1a alleles, see <a href=http://www.knockoutmouse.org/about/targeting-strategies>"
					  . "http://www.knockoutmouse.org/about/targeting-strategies</a>) "
					  . "as the critical exon has not been deleted.<br>"
					  . "2) That the structure of the targeted mutation in the ES cells obtained from EUCOMM to "
					  . "generate EUCOMM mice is not verified by EMMA. It is recommended that the recipient confirms "
					  . "the mutation structure.<br>3) No check for determining the copy number of the targeting "
					  . "construct in ES cells obtained from EUCOMM is done by EMMA.<br>4) The level of quality control "
					  . "before mice are released is to confirm the individual mouse genotype by short range PCR.";  				
		      	}
			}
		}
	}
	function fetch_references($sql){
		$ref = '';
	  	$pmid_seen = 0;
	  	global $db;
	  	
	  	$rows = $db->db_fetch($sql);
	  	
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){	  			
	      		$ref .= "<li>";
	      		foreach ( array('title', 'author1', 'author2', 'year', 'journal', 'volume', 'pages') as $field ){	      			
					$ref .= $row[$field] . "; ";
	      		}
	      		$pmid = $row['pubmed_id'];
	      		if ( $pmid ) {$pmid_seen++;}	      		 		
	      		$ref .= "<a href='http://www.ncbi.nlm.nih.gov/pubmed/${pmid}?dopt=Abstract' target='_blank'>$pmid</a></li>"; 
	    	}	    	
	    	if ($pmid_seen > 0){	    		
	      		return $ref;
	    	}
	  	}
	}
	function compose_DELTA_LEX_info($code_internal) {

		$del_lex = '';
	  	$funded = '';
	
	  	$tracking = "This line has been funded by the <a href='http://www.wellcome.ac.uk/'>Wellcome Trust</a>. "
	  			  . "To assist the Trust in tracking the outputs of the research to which it has contributed either " 
	  			  . "wholly or in part, the Trust's contributions must be acknowledged in all publications according "
	  			  . "to the Trust's <a href='http://www.wellcome.ac.uk/Managing-a-grant/End-of-a-grant/WTD037950.htm' "
	  			  . "target='_blank'>Guidance for Research Publication Acknowledgement Practice</a>. "
	  			  . "Customers must refer to the 'Wellcome Trust Knockout Mouse Resource'.";
	 
	  	if ( preg_match("/DELTAGEN/", $code_internal) ){
	    	$funded = $tracking;
	    	$del_lex = "<p><a href='http://www.emmanet.org/deltagen/$code_internal' target='_blank'>"
	    	         . "Deltagen phenotyping data</a>: the phenotypic data and presentation format were provided by "
	    	         . "Deltagen Inc. and are presented as received. EMMA has not verified the content or format of "
	    	         . "the material.";
	  	}
	  	else if ( preg_match("/LEXKO/", $code_internal) ){
	    	$funded = $tracking;
	    
	    	// substr up until first comma
	    	$pos = strpos($code_internal, ',');
	    	$short_lex = $code_internal;
	    	if ( $pos ){
	      		$short_lex = substr($code_internal, 0, $pos);
	    	}	 
	    	$del_lex = "<p><a href='http://www.emmanet.org/lexicon/combined_lexicon_data/${short_lex}-treeFrame.html' "
	    	         . "target='_blank'>Lexicon phenotyping data</a>: the phenotypic data and presentation format were "
	    	         . "provided by Lexicon Pharmaceuticals and are presented as received. EMMA has not verified the content "
	    	         . "or format of the material.";
	  	}
	
	  	return array($del_lex, $funded);
	}
	function fetch_names_by_id_str($id_str){
		// names heres are: gene_symbol(g.symbol), common_strain_name (syn_strains.synonym), international_strain_name (strains.name)
		// where when common name is missing, strains.code_internal will be used
		$sql = "SELECT DISTINCT GROUP_CONCAT(distinct(g.symbol), '*__*') as gene_symbol, 
					ss.name as common_strain_name, 
					s.code_internal, 
					s.name as international_strain_name
				FROM genes g, alleles a, mutations m, mutations_strains ms, strains s 
				LEFT JOIN syn_strains ss ON ss.str_id_str=s.id_str 
				WHERE g.id_gene=a.gen_id_gene 
				AND a.id_allel=m.alls_id_allel 
				AND m.id=ms.mut_id 
				AND ms.str_id_str=s.id_str 
				AND s.str_access = 'P'  
				AND s.str_status IN ('TNA','ARRD','ARING','ARCHD') 
				AND s.id_str=$id_str GROUP BY s.id_str";				   
		
		global $db;
	  	$names = array();
	  	$rows = $db->db_fetch($sql);
	  	
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){
	  			$symbols_string = $row['gene_symbol'];
	  			
	  			if ( preg_match("/__/", $symbols_string) ){	  				
	  				$symbols = explode('*__*', $symbols_string);	      			
	      			// mysql GROUP_CONCAT() adds one empty element to end of list, why? Remove it
      				array_pop($symbols);
	  			}
	  			
	  			$symbol_links = $this->attach_mgi_allele_url_to_gene_symbols($symbols, $id_str);
      			$names['Gene Symbol'] = implode(', ', $symbol_links); 
      			
	  			$names['Common Strain Name'] = $row['common_strain_name'] ? 
	  				$this->superscript_munging($row['common_strain_name']) : 
	  				$this->superscript_munging($row['code_internal']);
	  			
	  			$names['International Strain Name'] = $this->superscript_munging($row['international_strain_name']);	  			
	  		}
	  	}
	  	return $names;
	}
	function attach_mgi_allele_url_to_gene_symbols($symbols, $id_str){
		$symbol_links = array();
		foreach ( $symbols as $symbol ){

			// mysql GROUP_CONCAT() also adds a comma between elements
			// remove this
			$symbol = trim($symbol, ',');					
					
			$ref_alls =	$this->fetch_mgi_ref_id_alls_form_by_gene_symbol($symbol, $id_str);
			
			$mgi_allele_ID = $ref_alls['mgi_ref'];
			$alls_form     = $ref_alls['alls_form'];
			$flag = 0;
		
			$link = $symbol;
		
			# want to use allele mgi ref here
			if ( $mgi_allele_ID ){
				//$link = "<a href='http://www.informatics.jax.org/searches/accession_report.cgi?id=MGI:" . $mgi_allele_ID . "' target='_blank'>$symbol</a>";
				$link = "<a href='http://www.informatics.jax.org/javawi2/servlet/WIFetch?page=alleleDetail&id=MGI:" . $mgi_allele_ID . "' target='_blank'>$symbol</a>";				                 
			}
			else {
				foreach ( array('--','-','.','/','0','?','Unknown','unknown','Unknown at present','Not known','Not Known','None','None at present','NA','N/A','n.a.','__') as $to_exclude ){			
					if ( $alls_form == $to_exclude ){
				    	
				      	if ($symbol !== 'Unknown at present' and $symbol !== 'unknown'){
			            	$link = "<a href='http://www.informatics.jax.org/searchtool/Search.do?query=".$symbol."&submit=Quick+Search' target='blank'> $symbol </a>";
			          	}				      
				      				
				      	$flag = 1;
				      	break;
					} 
			  	}
		
			  	if ( $flag == 0 ){
			    	$link =  "<a href='http://www.informatics.jax.org/searchtool/Search.do?query=".$alls_form."&submit=Quick+Search' target='blank'>$symbol</a>";
			  	}
			}				
			array_push($symbol_links, $link);			
		}
		
		return $symbol_links;
	}	
	function fetch_mgi_ref_id_alls_form_by_gene_symbol($symbol, $id_str) {		
		$sql = "SELECT a.alls_form, a.mgi_ref		 
				FROM genes g, alleles a, mutations m, mutations_strains ms, strains s 
				WHERE s.id_str=ms.str_id_str 
				AND ms.mut_id=m.id 
				AND m.alls_id_allel=a.id_allel 
				AND g.id_gene=a.gen_id_gene 
				AND g.symbol = \"$symbol\" AND s.id_str= $id_str";
	
		global $db;
		$rows = $db->db_fetch($sql);
		
		$ref_alls = array();
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){
	  			$ref_alls['alls_form'] = $row['alls_form'];
	  			$ref_alls['mgi_ref']   = $row['mgi_ref'];	  			
	  		}
	  	}
	  	
	  	return $ref_alls;		
	}	
	function get_subtypes($code){
  
	 	 #echo $code;
  		$subtypes = array(
		    'TM'  => array('TMKO', 'TMKI', 'TMTC','TMTNC', 'TMPM', 'TMCM', 'TMOTH'),
		    'IN'  => array('INCH','INXray'),
		    'ALL' => array('TMKO', 'TMKI', 'TMTC','TMTNC', 'TMPM', 'TMCM', 'TMOTH', 'GT','TG','INCH', 'INXray', 'CH', 'SP', 'XX', 'Cre', 'TET', 'FLP', 'DEL', 'LEX', 'EUC')
		);
  
  		return $subtypes[$code];
	}
	function fetch_rtool_sql($rtool_id, $sqla, $sqlb){
  		return $sqla 
    	. "rtools_strains rs, " 
  	    . $sqlb 
	    . " AND s.id_str=rs.str_id_str "
	    . " AND rs.rtls_id = $rtool_id ";  
	} 
	function fetch_omim_display($row){
		if ( $row['omim_id'] ){
			$omimID = $row['omim_id'];
      		$omimIDlink = "<a href='http://www.omim.org/entry/$omimID' target='_blank'>$omimID</a>";
      		$mgi_internal_omim_id = $row['mgi_internal_omim_id']; 
      		$mgiOmim = "http://www.informatics.jax.org/javawi2/servlet/WIFetch?page=humanDisease&key=$mgi_internal_omim_id";
      		$omimName = "<a href='$mgiOmim' target='_blank'>{$row['omim_name']}</a>";
      		$links['omimName'] = $omimName;
      		$links['omimID'] = $omimIDlink;
      		return $links;
		}
		return false;
	}	
    function dataTableValsForSql($_GET){
         
	    /* 
	     * Paging
	     */
	    global $sLimit;
	    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ){
		    $sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			    mysql_real_escape_string( $_GET['iDisplayLength'] );
	    }
	
	
	    /*
	     * Ordering
	     */
        global $sOrder;
	    if ( isset( $_GET['iSortCol_0'] ) ){
		    $sOrder = "ORDER BY  ";
		    for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ){
			    if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ){
				    $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				     	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			    }
		    }
		
		    $sOrder = substr_replace( $sOrder, "", -2 );
		    if ( $sOrder == "ORDER BY" ){
			    $sOrder = "";
		    }
	    }

	    /* 
	     * Filtering
	     * NOTE this does not match the built-in DataTables filtering which does it
	     * word by word on any field. It's possible to do here, but concerned about efficiency
	     * on very large tables, and MySQL's regex functionality is very limited
	     */
	    global $sWhere;
	    if ( $_GET['sSearch'] != "" ){
		    $sWhere = "WHERE (";
		    for ( $i=0 ; $i<count($aColumns) ; $i++ ){
			    $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		    }
		    $sWhere = substr_replace( $sWhere, "", -3 );
		    $sWhere .= ')';
	    }
	
	    /* Individual column filtering */
	    for ( $i=0 ; $i<count($aColumns) ; $i++ ){
		    if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ){
			    if ( $sWhere == "" ){
				    $sWhere = "WHERE ";
			    }
			    else {
				    $sWhere .= " AND ";
			    }
			    $sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
		    }
	    }
    }
    function makeDataTable($sql, $post, $get, $tblClass, $qrystr, $dataType, $_GET){
		$mpid    = $post['mpid'];
  		$is_leaf = $post['leaf'];
  		$qry_term_name = $get['term_name'];
        global $sLimit, $sOrder, $sWhere;  

        



        $this->dataTableValsForSql($_GET);

  		$sql .= ' GROUP BY s.emma_id ';
        $sqlLimit = $sql . "  $where $sLimit";

  		global $db;
		$rowsTotal = $db->db_fetch($sql);
		$rowsLimit = $db->db_fetch($sqlLimit);

        $output = array(
		        "sEcho" => intval($_GET['sEcho']),
		        "iTotalRecords" => count($rowsTotal),
		        "iTotalDisplayRecords" => 10,
		        "aaData" => array(),              
                "sql" => $sqlLimit             
	        );

		$has_omim = false;
		# check rows include omim info (when available)
		foreach ( $rowsLimit as $row ){
			if ( $row['omim_id'] ) {
				$has_omim = true;
				break;		
			}
		}
		
		$ref_alls = array();
		if ($rowsLimit == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {  		
    		
			$row_count = 0;
    		$has_curr_term;           
            $has_MP = false;

    		foreach ( $rowsLimit as $row ){
      			#echo var_dump($row);
                $dRows = array();

      			$row_count++;
      			$id_str = $row['id_str'];
				#echo "is_str: $id_str --- MPID: $mpid<br>";
      			$has_MP = $mpid ? $this->strain_is_mapped_to_MP($id_str, $mpid) : 0;

      			if ( !$has_curr_term and $has_MP == 1 ){
					$has_curr_term = 1;
      			}

      			$table .= ($has_MP == 1 and $is_leaf == 'false') ?  "<tr class='mp' id='$id_str'>" : "<tr id='$id_str'>";
      			
				$emmaid = $row['emma_id'];
                $dRows[] = $row['emma_id'];

      			#$table .= "<td class='emmaID'>$emmaid</td>";
      			
      			if ( $has_omim ){  
      				$spacer = "&nbsp;&nbsp;";  	
      				$links = $this->fetch_omim_display($row);
      				if ( $links ){
						$omimName = $links['omimName'];
						$omimID = $links['omimID'];      				  				
						#$table .= "<td class='omim'>$omimName $spacer / $spacer $omimID</td>";
                       
                        $dRows[] = "$omimName $spacer / $spacer $omimID";
      				}
      				else {
      					#$table .= "<td class='omim'>NA $spacer / $spacer NA</td>";
                        $dRows[] = "NA $spacer / $spacer NA";
      				}      							
      			} 
   
      			if ($mpid){
					$node_id = $post['nodeId'];
					#echo "STR ID: $id_str - node: $node_id\n";
					$term_name = $this->fetch_term_name_by_node_strain_pair($node_id.'_'.$id_str);
					if ( !$term_name ){
		  				$term_name = $this->fetch_term_name_by_node_id($node_id);
					}
					#$table .= "<td>$term_name</td>";
                   
                    $dRows[] = $term_name;
      			}
	  			else if ( $qry_term_name ){
					#$table .= "<td>$qry_term_name</td>";
                  
                    $dRows[] = $qry_term_name;  
      			}
      
      			$symbol_links = array();
      
      			#echo "$id_str: GOT LIST " . $row['symbol'] . "<br>";
      			$symbols = explode('*__*', $row['symbol']);

      			// mysql GROUP_CONCAT() adds one empty element to end of list, why? -> remove it
     			array_pop($symbols);

      			foreach ( $symbols as $symbol ){
					#echo "SYMBOL: $symbol ---- <br>";
					// mysql GROUP_CONCAT() also adds a comma between elements, remove this
					$symbol = trim($symbol, ',');
	
					$mgi_allele_ID = $this->fetch_mgi_allele_id_by_gene_symbol($symbol, $id_str);					
					$alls_form     = $this->fetch_alls_form_by_gene_symbol($symbol, $id_str);
										
					$flag = 0;
	
					$link = $symbol;
					$superscripted_symbol = $this->superscript_munging($symbol);
					
					# want to use allele mgi ref here
					if ( $mgi_allele_ID ){
		  				//$link = "<a href='http://www.informatics.jax.org/searches/accession_report.cgi?id=MGI:" . $mgi_allele_ID . "' target='_blank'>$symbol</a>";
		  				$link = "<a href='http://www.informatics.jax.org/javawi2/servlet/WIFetch?page=alleleDetail&id=MGI:" . $mgi_allele_ID . "' target='_blank'>$superscripted_symbol</a>";
					}
					else {
						foreach ( array('--','-','.','/','0','?','Unknown','unknown','Unknown at present','Not known','Not Known','None','None at present','NA','N/A','n.a.','__') as $to_exclude ){
		
			    			if ( $alls_form == $to_exclude ){
			      				#echo "$id_str -- $symbol<br>";
			      				if ($symbol !== 'Unknown at present' and $symbol !== 'unknown'){
		                			$link = "<a href='http://www.informatics.jax.org/searchtool/Search.do?query=".$symbol."&submit=Quick+Search' target='blank'> $superscripted_symbol </a>";
		          				}
			      
			      				#echo "Symbol: $symbol vs $alls_form<br>";
		
			      				$flag = 1;
			      				break;
			    			} 
		  				}
	
		  				if ( $flag == 0 ){
		    				$link =  "<a href='http://www.informatics.jax.org/searchtool/Search.do?query=".$alls_form."&submit=Quick+Search' target='blank'>$superscripted_symbol</a>";
		  				}
					}
					array_push($symbol_links, $link);
      			}	

      			#$table .= "<td>" . implode(', ', $symbol_links)  . "</td>";
                $dRows[] = implode(', ', $symbol_links); 
                
      			$cname = $row['synonym'];
      
      			// need to deal with superscripts stored in MySQL utf-8
      			$strname = $row['name']; # original utf-8 string
     			if ( $new_text = $this->superscript_munging($strname) ){
					$intnl_strname = $new_text;
      			}
      			else {
					$intnl_strname = $strname;
      			}
      
      			$synonym = $cname ? $cname : $row['code_internal'];
      			$superscripted_synonym = $this->superscript_munging($synonym);
      			
      			if ( ! $cname ){ $cname = $synonym; }
	  			
      			
				#$table .= "<td><a class='sticky pdf' href='#' rel='#$id_str' title='Strain description - $intnl_strname'>$superscripted_synonym</a></td>";
      			#$table .= "<td>$intnl_strname</td>";
	            $dRows[] = "<a class='sticky pdf' href='#' rel='#$id_str' title='Strain description - $intnl_strname'>$superscripted_synonym</a>";
                $dRows[] = $intnl_strname;
               
      			$label = $this->fetch_order_label($row['str_status'], $row['available_to_order']);

	  			$project_id = $this->fetch_project_id_by_strain_id($id_str);
      
      			$lab_id = $this->fetch_lab_id_by_strain_id($id_str);

      			$registerTitle = $lab_id == 1961 
					? "This option offers a potential earlier opportunity " 
					 ."to obtain mice if available prior to archiving for sustainable distribution. "
					 ."Sanger MGP generates mutant mouse lines for in-house primary phenotypic studies " 
					 ."and is not a distribution centre. Availability and time to delivery cannot be guaranteed."
					: "This option offers a potential earlier opportunity "
					 ."to obtain mice if available prior to archiving for sustainable distribution. "
					 ."Availability and time to delivery cannot be guaranteed.";
	
 			   	// links to order strains
				$url= $label == 'register interest' 
	  				? "https://www.emmanet.org/apps/RegisterInterest/requestFormView.emma?id=$emmaid" . "&sname=" . urlencode($strname) . "&cname=$cname" . "&wr=1"
	 				: "https://www.emmanet.org/apps/RegisterInterest/requestFormView.emma?new=y". "&id=$emmaid" . "&sname=" . urlencode($strname) . "&cname=$cname"; 

	 			$url .= "&pid=$project_id";

     			if ( $label == 'register interest' ){
					#$table .= "<td><a class='requestForm' target='_blank' href='$url' title='$registerTitle'>$label</a></td>"; 
                
                    $dRows[] = "<a class='requestForm' target='_blank' href='$url' title='$registerTitle'>$label</a>";                    
      			}
      			else {
					#$table .= "<td><a class='requestForm' target='_blank' href='$url'>$label</a></td>"; 

                    $dRows[] = "<a class='requestForm' target='_blank' href='$url'>$label</a>";
      			}
      			#$table .= "</tr>";
               
                $output['aaData'][] = $dRows;
    		}     		
    		
    		if ( $row_count == 0 ){
      			// when search returns nothing
      			if ( isset($get['letter']) ){
					echo "<p id='errmsg'>Sorry. Found no strains with genes which symbols start with " . strtoupper($qrystr) . " or " . strtolower($qrystr) . " in the database.</p>";
					exit;
      			}
    		}
    		
    		
    		#$table_data = $row_count == 0 ? '' : $table . "</tbody></table><p>";
    
            #echo var_dump($output['aaData']);
            #echo $output['tableStructure'] = $table;
            #echo print_r($output['aaData']);
            return $output; 
            

    		#echo $table_data;
    		#$DATA['record'] = $entries;
    		#$DATA['result'] = $table_data;
    	
    		
    		#return $DATA;
  		}
	}
	function fetch_symbol_mgi_links_list($symbol_concat, $id_str){
        $symbols = explode('*__*', $symbol_concat);

      	// mysql GROUP_CONCAT() adds one empty element to end of list, why? -> remove it
     	array_pop($symbols);

		foreach ( $symbols as $symbol ){
			#echo "SYMBOL: $symbol ---- <br>";
			// mysql GROUP_CONCAT() also adds a comma between elements, remove this
			$symbol = trim($symbol, ',');

			$mgi_allele_ID = $this->fetch_mgi_allele_id_by_gene_symbol($symbol, $id_str);					
			$alls_form     = $this->fetch_alls_form_by_gene_symbol($symbol, $id_str);
								
			$flag = 0;

			$superscripted_symbol = $this->superscript_munging($symbol);
			
			# want to use allele mgi ref here
			if ( $mgi_allele_ID ){
                $href = "http://www.informatics.jax.org/javawi2/servlet/WIFetch?page=alleleDetail&id=MGI:" . $mgi_allele_ID;
                $hrefs[$symbol] = $href;
			}
			else {
				foreach ( array('--','-','.','/','0','?','Unknown','unknown','Unknown at present','Not known','Not Known','None','None at present','NA','N/A','n.a.','__') as $to_exclude ){

	    			if ( $alls_form == $to_exclude ){
	      				#echo "$id_str -- $symbol<br>";
	      				if ($symbol !== 'Unknown at present' and $symbol !== 'unknown'){
                            $href = "http://www.informatics.jax.org/searchtool/Search.do?query=${symbol}&submit=Quick+Search";
                            $hrefs[$symbol] = $href;                    			
          				}
	      
	      				$flag = 1;
	      				break;
	    			} 
  				}

  				if ( $flag == 0 ){
                    $href = "http://www.informatics.jax.org/searchtool/Search.do?query=${alls_form}";
                    $hrefs[$symbol] = $href;      		
  				}
			}			
		}	       
    
        return $hrefs; 
    }	 
    function fetch_gci_by_strain_id($id_str){
        // returns a concatenated symbol string
        $sql = "SELECT DISTINCT GROUP_CONCAT(distinct(g.symbol), '*__*') as symbol, ss.name as synonym, s.name, s.code_internal FROM alleles a, mutations m, mutations_strains ms, strains s LEFT JOIN syn_strains ss ON ss.str_id_str=s.id_str, genes g LEFT JOIN syn_genes sg ON sg.gen_id_gene=g.id_gene WHERE g.id_gene=a.gen_id_gene AND a.id_allel=m.alls_id_allel AND m.id=ms.mut_id AND ms.str_id_str=s.id_str AND s.str_access = 'P' AND s.str_status IN ('TNA','ARRD','ARING','ARCHD') and s.id_str=${id_str} GROUP BY s.emma_id ORDER BY s.emma_id";

        $gci = array(); //g: gene name, c: common name, i: international strain designation
        global $db;
		$rows = $db->db_fetch($sql);
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {          
                                    
            $linkList = $this->fetch_symbol_mgi_links_list($rows[0]['symbol'], $id_str);
            $links = array();                       
            foreach ( $linkList as $symbol=>$link ){
               $links[] = "<a href='${link}' target='_blank'>$symbol</a>";
            }
            $gci['geneNameLinks']  = join(", ", $links);
            $gci['commonName']     = $this->derive_common_strain_name($rows[0]);  
            $gci['intlStrainName'] = $this->derive_international_strain_name($rows[0]); 	  		  
        }
        return $gci;
    }
    function derive_common_strain_name($row){

        $cname = $row['synonym']; //syn_strain.name  		

		$synonym = $cname ? $cname : $row['code_internal'];  // strain.code_internal
		return $this->superscript_munging($synonym);
    } 
    function derive_international_strain_name($row) {

        // need to deal with superscripts stored in MySQL utf-8
        $intnl_strname = false;
		$strname = $row['name']; # original utf-8 string, strains.name
		if ( $new_text = $this->superscript_munging($strname) ){
			$intnl_strname = $new_text;
		}
		else {
			$intnl_strname = $strname;
		}

        return $intnl_strname;
    }
	function make_table($sql, $post, $get, $tblClass, $qrystr, $mode){

        global $drupalScriptPath;
		$mpid    = $post['mpid'];
  		$is_leaf = $post['leaf'];
  		$qry_term_name = $get['term_name'];
        $DATA = array();
  		$sql .= ' GROUP BY s.emma_id ORDER BY s.emma_id';
  		//echo $sql;  				
  		
  		global $db;
		$rows = $db->db_fetch($sql);
		
		$has_omim = false;
		# check rows include omim info (when available)
		foreach ( $rows as $row ){
			if ( $row['omim_id'] ) {
				$has_omim = true;
				break;		
			}
		}
    		
		$ref_alls = array();
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else if ( count($rows) == 0 ){                      
            return NULL;                    
        }
        else {  		
    		$mp_pheno = false;
    		if ($mpid or $qry_term_name){
    			#echo "GOT TERM ...... $qry_term_name<br>";
      			$mp_pheno = "<th>MP phenotype</th>";
    		}

    		# omim name/ID column will be added when needed
    		$omimTH = $has_omim ? "<th>OMIM name / ID</th>" : false;
    		
    		$table = <<<TBL
      			<table class="$tblClass tablesorter" id="$mode"> 
      			<thead>
       			<tr>        		     		
        		<th>EMMA ID</th>  
      		    $omimTH
        		$mp_pheno
      			<th>gene symbol</th>
      			<th>common strain name(s)</th>  
      			<th>(International) Strain Designation</th> 
              
        		<th>Status</th>
                <th></th>   
       		</tr>
      		</thead><tbody>
TBL;
            
            $colCount = ($omimTH && $mp_pheno) ? 8 : 6;               

			$row_count = 0;
    		$has_curr_term;

    		foreach ( $rows as $row ){
      			#echo var_dump($row);

      			$row_count++;
      			$id_str = $row['id_str'];
				#echo "is_str: $id_str --- MPID: $mpid<br>";
      			$has_MP = $mpid ? $this->strain_is_mapped_to_MP($id_str, $mpid) : 0;

      			if ( !$has_curr_term and $has_MP == 1 ){
					$has_curr_term = 1;
      			}

      			$table .= ($has_MP == 1 and $is_leaf == 'false') ?  "<tr class='mp' id='$id_str'>" : "<tr id='$id_str'>";
      			
				$emmaid = $row['emma_id'];                
      			$table .= "<td class='emmaID'>$emmaid</td>";
      			
                
                if ( $has_omim ){  
      				$spacer = "&nbsp;&nbsp;";  	
      				$links = $this->fetch_omim_display($row);
      				if ( $links ){
						$omimName = $links['omimName'];
						$omimID = $links['omimID'];      				  				
						$table .= "<td class='omim'>$omimName $spacer / $spacer $omimID</td>";
      				}
      				else {
      					$table .= "<td class='omim'>NA $spacer / $spacer NA</td>";
      				}      							
      			}     


      			if ($mpid){
					$node_id = $post['nodeId'];
					#echo "STR ID: $id_str - node: $node_id\n";
					$term_name = $this->fetch_term_name_by_node_strain_pair($node_id.'_'.$id_str);
					if ( !$term_name ){
		  				$term_name = $this->fetch_term_name_by_node_id($node_id);
					}
					$table .= "<td>* $term_name</td>";
      			}
	  			else if ( $qry_term_name ){
					$table .= "<td>*$qry_term_name</td>";
      			}
      
      			$symbol_links = array();
      
      			#echo "$id_str: GOT LIST " . $row['symbol'] . "<br>";
      			$symbols = explode('*__*', $row['symbol']);
                        
      			// mysql GROUP_CONCAT() adds one empty element to end of list, why? -> remove it
     			array_pop($symbols);
                $symbolList = array();
                foreach ( $symbols as $symbol ){					
					// mysql GROUP_CONCAT() also adds a comma between elements, remove this
					$symbolList[] = trim($symbol, ',');
                }
                $table .= "<td class='mgiLink'>" . join(", ", $symbolList) . "</td>";               

                $intnl_strname = $this->derive_international_strain_name($row); 

	  			$superscripted_common_strain_name = $this->derive_common_strain_name($row);      			
				//$table .= "<td><a class='sticky pdf' href='#' rel='#$id_str' title='Strain description - $intnl_strname'>$superscripted_common_strain_name</a></td>";
                $table .= "<td>$superscripted_common_strain_name</td>";                
      			$table .= "<td>$intnl_strname</td>";  // strains.name   
	
                /*if ( $has_omim ){  
      				$spacer = "&nbsp;&nbsp;";  	
      				$links = $this->fetch_omim_display($row);
      				if ( $links ){
						$omimName = $links['omimName'];
						$omimID = $links['omimID'];      				  				
						$table .= "<td class='omim'>$omimName $spacer / $spacer $omimID</td>";
      				}
      				else {
      					$table .= "<td class='omim'>NA $spacer / $spacer NA</td>";
      				}      							
      			} */

      			$label = $this->fetch_order_label($row['str_status'], $row['available_to_order']);

	  			$project_id = $this->fetch_project_id_by_strain_id($id_str);
      
      			$lab_id = $this->fetch_lab_id_by_strain_id($id_str);

      			$registerTitle = $lab_id == 1961 
					? "This option offers a potential earlier opportunity " 
					 ."to obtain mice if available prior to archiving for sustainable distribution. "
					 ."Sanger MGP generates mutant mouse lines for in-house primary phenotypic studies " 
					 ."and is not a distribution centre. Availability and time to delivery cannot be guaranteed."
					: "This option offers a potential earlier opportunity "
					 ."to obtain mice if available prior to archiving for sustainable distribution. "
					 ."Availability and time to delivery cannot be guaranteed.";
	
 			   	// links to order strains
				$url= $label == 'register interest' 
	  				? "https://www.emmanet.org/apps/RegisterInterest/requestFormView.emma?id=$emmaid" . "&sname=" . urlencode($strname) . "&cname=$cname" . "&wr=1"
	 				: "https://www.emmanet.org/apps/RegisterInterest/requestFormView.emma?new=y". "&id=$emmaid" . "&sname=" . urlencode($strname) . "&cname=$cname"; 

	 			$url .= "&pid=$project_id";
                //echo "icon: " . $this->fetch_order_icon($label) . "\n";
                $table .= "<td class='order' rel='$url'><span alt='$label'>" . $this->fetch_order_icon($label) . "</span></td>";
                     			

                // strain desc toggle    
                $table .= "<td class='toggle'><img src='${drupalScriptPath}/images/plus.png' id='$id_str' title='Click to view strain description'/></td>"; 
             

      			$table .= "</tr>";
               // $table .= "<tr class='hiddenRow'><td colspan=$colCount></td></tr>";
    		}     		
    		
    		if ( $row_count == 0 ){
      			// when search returns nothing
      			if ( isset($get['letter']) ){
					echo "<p id='errmsg'>Sorry. Found no strains with genes which symbols start with " . strtoupper($qrystr) . " or " . strtolower($qrystr) . " in the database.</p>";
					exit;
      			}
    		}

    		$record = "Found $row_count entries";    		
    		$entries = "<div id='bookmark'><span></span></div>". "<p id='records'>$record</p>";
    		$table_data = $row_count == 0 ? '' : $table . "</tbody></table><p>";
   
    		#echo $table_data;
    		$DATA['record'] = $entries;
    		$DATA['result'] = $table_data;
    	
            return $DATA;           		
  		}
	}
    function compose_bottom_icon_rows($id_str, $bottom_icon_rows){
        $nameVals = array(
                "infoSheet"          => array('label' => 'Info sheet', 'val' => true, 'link' => false),  // default, action via js
                "mgiLink"            => array('label' => 'MGI allele', 'val' => true, 'link' => false),  // default, action via js
                "genotyping"         => array('label' => 'Genotyping', 'val' => $bottom_icon_rows['genotyping'], 'link' => true, 'folder' => 'genotype_protocols'),
                "healthReportFile"   => array('label' => 'Health report', 'val' => $bottom_icon_rows['healthReportFile'], 'link' => true, 'folder' => 'procedures'),
                "availabilities"     => array('label' => 'Availabilities', 'val' => $bottom_icon_rows['availabilities'], 'link' => false), // opens general info tab in accordion
                "pricingAndDelivery" => array('label' => 'Pricing & delivery', 'val' => $bottom_icon_rows['pricingAndDelivery'], 'link' => true), 
                "mta"                => array('label' => 'MTA', 'val' => $bottom_icon_rows['mta'], 'link' => true, 'folder' => 'mtas'),
                "order"              => array('label' => 'Order', 'val' => true, 'link' => false) // default, actual value by js
                );

        $spans = '';    
        $class = false;
        foreach( $nameVals as $name=>$KV ){          
            $class = $KV['val'] ? '' : 'grayout';  
            if ( $KV['val'] && $KV['link'] ){
                if ( $KV['folder'] ) {
                    $url = "<a href='http://www.emmanet.org/${KV['folder']}/${KV['val']}' target='_blank'>${KV['label']}</a>";                    
                }
                else {                    
                    $url = $KV['val'];
                }
                $spans .= "<span class='$class descAction $name'>$url</span>"; 
            }
            else { 
                if ( $name == 'mgiLink' ){
                    // make a div (none-display) as container for multiple links, so that users can choose
                    // activate only if there is multiple links by JS
                    $spans .= "<span class='$class descAction $name'>${KV['label']} <div></div></span>"; 
                    
                }
                else {                    
                    $spans .= "<span class='$class descAction $name'>${KV['label']}</span>";                
                }    
            }    
        }
        return "<div class='descActionRow' id='$id_str'>$spans</div>";
    }
	function fetch_mutype_sql($code, $sqla, $sqlb) {
		$sqlc = (strlen($search_by) == 2) ? " AND m.main_type = '$code' " 
      		: " AND concat(m.main_type, m.sub_type) = '$code' "; 
  		return  $sqla . $sqlb . $sqlc;
	}
	function fetch_strain_intro($code){
		if ( preg_match('/DEL|LEX/', $code) ){
    		return "<p class='mutype_info'><br>Wellcome Trust Knockout Mouse Resource<br><br>The Wellcome Trust "
    	  		. "has negotiated and will fund the acquisition of a limited number of gene knockout mouse strains "
    	   		. "and associated phenotypic data from Deltagen Inc. and Lexicon Pharmaceuticals. This resource will "
    	   		. "be archived and distributed through EMMA. For background information please visit the "
    	   		. "<a href='http://www.wellcome.ac.uk/Education-resources/biomedical-resources/model-organisms/wtd025941.htm' "
    	   		. "target='_blank'>Knockout Mouse Resource page</a> on the Wellcome Trust site.<br>"
    	   		. "The 87 lines of the first two calls for proposals are currently being rederived and archived by EMMA "
    	   		. "and will be made available to the research community through EMMA. To assist the Wellcome Trust in "
    	   		. "tracking the outputs of the research to which it has contributed either wholly or in part, the Trust's "
    	   		. "contributions must be acknowledged in all publications according to the Trust's "
    	   		. "<a href=\"http://www.wellcome.ac.uk/Managing-a-grant/End-of-a-grant/WTD037950.htm\" "
    	   		. "target='_blank'>Guidance for Research Publication Acknowledgement Practice</a>. "
    	   		. "Customers must refer to the 'Wellcome Trust Knockout Mouse Resource'.</p><p>";
		}
  		else if ( $code == 'EUC' ){
    		return "<p class='mutype_info'><br>Distribution of EUCOMM mice<br><br>In contrast to EUCOMM vectors and ES cells "
           		. "which are distributed by the <a href='http://www.eummcr.org' target='_blank'>European Mouse Mutant Cell "
           		. "Repository</a>, mutant mice produced from the EUCOMM ES cell resource by the <a href='http://www.eucomm.org' "
           		. "target='_blank'>EUCOMM</a> and <a href='http://www.eumodic.org' target='_blank'>EUMODIC</a> projects are "
           		. "being distributed via the <a href='http://www.emmanet.org'>European Mouse Mutant Archive</a> (EMMA).<br>"
		  		. "EUCOMM is undertaking an extensive quality control effort for the ES cell resource and aims to produce 320 "
		  		. "mouse lines from EUCOMM ES cell lines. This activity will be complemented by the EUMODIC program which will "
		   		. "produce another 330 mouse mutant lines from the EUCOMM ES cell resource. EUMODIC will subsequently undertake "
		   		. "a primary phenotype assessment of up to 650 mouse mutant lines. Phenotype data will be made available to the "
		   		. "scientific community via the <a href='http://www.europhenome.org' target='_blank'>Europhenome database</a>."
		   		. "<br>While the cryopreservation process and the production of cohorts for phenotyping is ongoing mouse mutant "
		   		. "lines produced from the EUCOMM ES cell resource can readily be made available upon request. "
		   		. "All EUCOMM / EUMODIC mice for which germ line transmission has been confirmed along with their current "
		   		. "availability are listed below."; 
		}       
	}	
	function fetch_term_infos_by_node_id($node_id){
		// need to update schema for mp_synonyms so that distinct is not needed
		$sql = "SELECT distinct ti.*, s.*, ai.*
				FROM (mp_term_infos ti LEFT JOIN mp_node2term nt ON ti.term_id=nt.term_id) 
				LEFT JOIN mp_synonyms s ON s.node_id=nt.node_id
				LEFT JOIN mp_alt_ids ai on nt.term_id=ai.term_id
				WHERE nt.node_id=$node_id";
		#echo $sql;
		$alt_ids = array();
		$syn_name_type = array();
		$seen_syn = array();
		
		global $db;
		$rows = $db->db_fetch($sql);
		
		if ($rows == 'ERROR'){	  
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){		
   				$tinfo['def'] = "<div class='terminfo'>" . $row['definition'] . "</div>"; 
   				$is_obsolete = $row['is_obsolete'];
   				if ( $is_obsolete == 'yes'){	
   					$tinfo['is_obsolete'] = "<div class='terminfo'>$is_obsolete</div>"; 
   				}
   				if ($alt_id = $row['alt_id']){
   					array_push($alt_ids, $alt_id);	   			
   				}
   				if ($comment = $row['comment']){
   					$tinfo['comment'] =  "<div class='terminfo'>$comment</div>";   				
   				};
   				if ( $syn_type = $row['type'] and $syn_name = $row['syn_name'] ){   
			  		array_push($seen_syn, $syn_name);
			  		$check_unique = array_count_values($seen_syn);
			 
                	if ( $check_unique[$syn_name] == 1 ){
   			    		$syn_name_type[] = array('syn_name' => $syn_name, 'syn_type' =>  $syn_type);   			 
			  		}
				}   			
   			} 
   		
   			foreach ($alt_ids as $alt_id ){   			
   				$tinfo['alt_id'][] = $alt_id;
   			}
   			$tinfo['alt_id'] = array_unique($tinfo['alt_id']);
		
   			if ( count($syn_name_type) > 1 ){
		  		$tinfo['synonym'] = $syn_name_type; 		
   			}   		
   		
   			return $tinfo;
    	}  	
	}	
	function fetch_mp_term_names($term_qry) {
		// can do term queries by term name, term synonyn or term ID
		$sql = "SELECT DISTINCT name
	    	    FROM mp_term_infos 
	        	WHERE (name LIKE '%" . $term_qry . "%'
	        	OR term_id = '$term_qry')  
	        	UNION
	        	SELECT syn_name AS name 
	        	FROM mp_synonyms 
	        	WHERE syn_name 
	        	LIKE '%" . $term_qry . "%' 
	        	ORDER BY name";	        
	
		$nameStr = '';
		
		global $db;
		$rows = $db->db_fetch($sql);
		
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){		
      			$nameStr .= $row['name'] . "\n"; // new line is default jQuery autocomplete separator       
    		}
  		}
  		return $nameStr;
	}	
	function strain_is_mapped_to_MP($id_str, $mpid) {

  		$sql = "SELECT distinct m.str_id_str 
  				FROM mutations_strains ms, mutations m, alleles_mpids am 
  		  		WHERE m.str_id_str IS NOT NULL
                AND ms.mut_id=m.id 
  		  		AND m.alls_id_allel=am.id_allel 
  		  		AND ms.str_id_str=$id_str";
  		
  		if ($mpid){
  			$sql .= " AND am.mammalian_phenotype_id='$mpid'";
  		}
 		# echo "SQL: $sql<br>";
 		
		global $db;
		$rows = $db->db_fetch($sql);
		
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){  		    	
    			return $row['str_id_str'] ? 1 : 0;
  			}
		}
	}
	function fetch_mpids_of_strain_alleles_by_id_str_allel($id_str, $id_allel){
		// returns a list of mpids	
		$sql = "SELECT am.mammalian_phenotype_id as mpid, a.name, a.alls_form, a.mgi_ref, am.allelic_composition, am.genetic_background  
	    	    FROM strains s, mutations_strains ms, mutations m, alleles a, alleles_mpids am 
	   		    WHERE s.id_str=ms.str_id_str 
	        	AND ms.mut_id=m.id 
	        	AND m.alls_id_allel=a.id_allel 
	        	AND a.id_allel=am.id_allel 
	        	AND s.id_str=$id_str";
		if ( $id_allel ){
			$sql .= " AND am.id_allel=$id_allel";
		}
	
		$mpids = array();
		
		global $db;
		$rows = $db->db_fetch($sql);
		
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){		
    			$mpid = $row['mpid'];
    			//$mpids[$mpid]['allele_name'] = $row['name'];
    			$mpids[$mpid]['alls_form']  = $this->superscript_munging($row['alls_form']);
    			$mpids[$mpid]['mgi_ref'] = $row['mgi_ref']; 
    			$mpids[$mpid]['allele_composition'][] = $this->superscript_munging($row['allelic_composition']);   
    			$mpids[$mpid]['background'][] = $this->superscript_munging($row['genetic_background']); 
    			$mpids[$mpid]['mpid'] = $mpid;  		  		
    		}	
		}  

		return $mpids;	
	}
	function compose_mp_list_display($mpids, $win_id, $tab_id){	
		$onto_desc = array();
		global $db;
		foreach ($mpids as $mpid=>$allele_info){
			$mysql = "SELECT * from mp_quick_tree where term_id = '$mpid'";		
			$node_alleles = false;
  			$term_2ups = false;
  			$top_node_2_terms = false;
  		
			$rows = $db->db_fetch($mysql);
		
			if ($rows == 'ERROR'){	  		
	    		die("Can't execute query: ".mysql_error());
	  		}
	  		else {
	  			foreach ( $rows as $row ){					
  					$term_id = $row['term_id'];
  					$node_id = $row['node_id'];
	  				$top_node = $row['top_node'];
	  				$two_ups_node = $row['2ups_node']; 
	  				//echo "$term_id, $node_id, $top_node, $two_ups_node<br>";
	  				$term_name = $this->fetch_term_name_by_node_id($node_id);
	  				$top_node_name = $this->fetch_term_name_by_node_id($top_node);
	  			
	  				$node_alleles[$node_id] = $allele_info;
	  				#echo "GOT: " , print_r($node_alleles[$node_id]) ;
	  				$term_2ups[$term_name . " ($term_id)"][$node_id]= $two_ups_node;	  				
	  				$top_node_2_terms[$top_node_name][]= $term_name . " ($term_id)"; 		  													
	  			}
	  		
  				foreach ($top_node_2_terms as $top_node_name=>$name_termIds){
	  				foreach (array_unique($name_termIds) as $name_termId){
	  					foreach ( $term_2ups[$name_termId] as $node_id=>$two_ups_node_id){	  					
	  						//$onto_desc['nameId'] = $name_termId;
	  						//$onto_desc['node_id'] = $node_id;
	  						//$onto_desc['two_ups_node_id'] = $two_ups_node_id;
	  						//$onto_desc[$top_node_name][] = $name_termId;
	  						$onto_Desc[$top_node_name][$name_termId]['node_id'] =  $node_id;
	  						$onto_Desc[$top_node_name][$name_termId]['two_ups_node_id'] =  $two_ups_node_id;	  				
	  					
	  						foreach ( $node_alleles as $nodeId=>$a_info){	
	  					 						
	  							if ( $nodeId == $node_id ){
	  								//$onto_desc['allel_name'] = $a_info['allel_name'];
	  								//$onto_desc['alls_form']  = $this->superscript_munging($a_info['alls_form']);	  							
	  								//$onto_desc['mgi_ref']    = $a_info['mgi_ref'];	 
									//$onto_Desc[$top_node_name][$name_termId]['allele_name'] = $a_info['allele_name'];
	  								$onto_Desc[$top_node_name][$name_termId]['alls_form']          = $a_info['alls_form'];
	  								$onto_Desc[$top_node_name][$name_termId]['mgi_ref']            = $a_info['mgi_ref'];	
	  								$onto_Desc[$top_node_name][$name_termId]['allele_composition'] = $a_info['allele_composition'];   
    								$onto_Desc[$top_node_name][$name_termId]['background']         = $a_info['background'];  
    								$onto_Desc[$top_node_name][$name_termId]['mpid']               = $mpid;   							
	  							}  						  	
	  						}						  				
	  					}
	  					//$ontologyDesc[] = $onto_desc;	
	  				}	  			
  				}	
	  			/* this can be used for EXT JS grid
	  		 
	  			foreach ($top_node_2_terms as $top_node_name=>$name_termIds)	{
	  				foreach (array_unique($name_termIds) as $name_termId){
	  					$onto_desc['top'] = $top_node_name;					
	  				
	  					foreach ( $term_2ups[$name_termId] as $node_id=>$two_ups_node_id){	  					
	  						$onto_desc['nameId'] = $name_termId;
	  						$onto_desc['node_id'] = $node_id;
	  						$onto_desc['two_ups_node_id'] = $two_ups_node_id;
	  					
	  						foreach ( $node_alleles as $nodeId=>$a_info){	  						
	  							if ( $nodeId == $node_id){
	  								$onto_desc['allel_name'] = $a_info['allel_name'];
	  								$onto_desc['alls_form']  = $this->superscript_munging($a_info['alls_form']);	  							
	  								$onto_desc['mgi_ref']    = $a_info['mgi_ref'];	  							
	  							}
	  						}  						  	
	  					}
						$ontologyDesc[] = $onto_desc;	  				
	  				}
	  			}
	  			*/
  			}
		}
		$this->make_allele_term_list($onto_Desc, $win_id, $tab_id);
		// EXT JS stuff
		// echo '{"total": ' . count($mpids) . ', "results": ' . json_encode($ontologyDesc) . '}';	
	}
	function make_allele_term_list($ontologyDesc, $win_id, $tab_id){
		$tabid = $tab_id ? $tab_id : 0;
				
		$html .= "<div id='onto_container'><div id='onto_wrapper'>";
		$boxid = "mpterms$tabid" . '_' . $win_id;
		$html .= "<div id='$boxid' class='mpterms'>";	
	
		ksort($ontologyDesc);                
		$counter = 0;
		$term_count = 0;
		foreach ( $ontologyDesc as $top_term_name=>$name_Ids){
			$counter++;		
			$gid = 'group'.$counter."_$win_id";
		
			$html_ul = "<ul id='$win_id' class='listBox'>";
		
			ksort($name_Ids);
			//echo "Top: " . $top_term_name . "<br>";
			$subCount = 0;
			foreach ( $name_Ids as $name_Id=>$infos ){		
				$term_count++;
				$subCount++;				
			
				$info = array('node_id'     => $infos['node_id'],
						  'two_ups_node_id' => $infos['two_ups_node_id'],
				          'symbol'          => $infos['alls_form'],
				          'mgi_id'          => $infos['mgi_ref'],
						  //'allele_name'     => $infos['allele_name'],
 		   				  'background'      => $infos['background'],  
				          'composition'     => $infos['allele_composition'],
						  'mpid'            => $infos['mpid'],
						  'tab'		        => $tabid
						);								
			  
				$html_ul .= "<li><a href='#' data='" . json_encode($info) ."'>$name_Id</a></li>";		 			
			}
			$html_1 = "<div class='termGroupIconPlus' id='$gid'>$top_term_name ($subCount)</div>";		
			$html_ul .= "</ul>";
			$html_2 .= $html_1 . $html_ul;
		}
	
		$term_sum_id = 'term_sum' . $tabid;
		$sum = "<div id='$term_sum_id'>Annotated MP terms of allele ($term_count)</div>";
	
		$html_2 = $sum . $html_2 ."</div>";
		$html .= $html_2 . "<div>";	

		$term_info_id = 'onto_term_info'.$tabid."_$win_id";
		$term_tree_id = 'onto_term_tree' . $tabid."_$win_id";
		$tree_header_id = 'partialTree' . $tabid."_$win_id";
		$wrapper = 'infoTreeWrapper' . $tabid."_$win_id";
		$ti_header = 'term_info_header' . $tabid."_$win_id";

		$html .= "<div id='$wrapper'>
		           <div class='header_collapse' id='$ti_header'>Term info</div>
		           <div id='$term_info_id' class='onto_term_info'></div>
		          <!-- <div id='$tree_header_id' class='data_header partialTree'>Term hierarchy</div>
		           <div id='$term_tree_id' class='onto_term_tree'></div> -->
		          </div></div>";		
	               
		echo $html;
	}
	function fetch_term_name_by_node_id($node_id){
		$sql = "SELECT ti.name 
        	    FROM mp_node2term nt, mp_term_infos ti 
         		WHERE nt.term_id=ti.term_id 
         		AND nt.node_id=$node_id";
		
		global $db;
		$rows = $db->db_fetch($sql);
		
		if ($rows == 'ERROR'){	  	
	    	die("Can't execute query: ".mysql_error());
	  	}
	  	else {
	  		foreach ( $rows as $row ){		
 		    	return $row['name'];
    		}
  		}
	}
	function fetch_table_by_term_name($term_qry, $sqla, $sqlb, $post, $get){ 
		// includes searching for synonyms
		// term query by Id is converted to term name for query
  		$sql = "SELECT str_id_str
    		    FROM mp_nodes_strains WHERE node_id 
        		IN (SELECT DISTINCT nt.node_id 
  		    		FROM (mp_node2term nt LEFT JOIN mp_term_infos ti ON nt.term_id = ti.term_id) 
  		    	LEFT JOIN mp_synonyms sn ON sn.node_id=nt.node_id 
  		    	WHERE sn.syn_name = '$term_qry' OR ti.name = '$term_qry')";
  	
		#echo "$sql<br>";  	
		global $db;
		$rows = $db->db_fetch($sql);
				
		$list = array();	
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){				
      			array_push($list, $row['str_id_str']);
    		}
  		}
  		#echo "list: " . join(",",$list) ."<br>";
  		  		
  		if ( count($list) > 0 ){    
    		$tblClass = 'resultSet';
    		$sql = $sqla . $sqlb . " AND s.id_str in (" . join(",", $list) . ") ";
    		#echo "SQL: $sql<br>";
    		return $this->make_table($sql, $post, $get, $tblClass, $qrystr='');    		
  		}
  		else {  			
  			$DATA['result'] = "<div id='termErr'>Sorry, no EMMA strain has the '<b>$term_qry</b>' phenotype.</div>";
  			return $DATA;
  		}
	}	
	function fetch_allele_info_of_strain($idstr){
		//returns list of emma allele ids
		$sql = "SELECT DISTINCT am.id_allel, a.alls_form
			    FROM strains s, mutations_strains ms, mutations m, alleles a, alleles_mpids am
		    	WHERE s.id_str=ms.str_id_str 
		    	AND ms.mut_id=m.id 
		    	AND m.alls_id_allel=a.id_allel
		    	AND a.id_allel=am.id_allel		    
		    	AND s.id_str=$idstr";
	
		$allel_info = array();
		global $db;
		$rows = $db->db_fetch($sql);
	
		$list = array();	
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){		
    			$allel_info[] = array('id'     => $row['id_allel'], 
    							      'symbol' => $this->superscript_munging($row['alls_form'])
    							);    						
    		}
    		return $allel_info;
  		}
	}	
	function fetch_term_name_by_node_strain_pair($nodeStrain){
		$sql = "SELECT node_id FROM mp_nodeStrain_node WHERE nodeStrain = '$nodeStrain'";

		global $db;
		$rows = $db->db_fetch($sql);	
		
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){	
     			return $this->fetch_term_name_by_node_id($row['node_id']);
    		}
  		}
	}
	function fetch_alls_form_by_gene_symbol($symbol, $id_str) {
		$sql = "SELECT a.alls_form 
				FROM genes g, alleles a, mutations m, mutations_strains ms, strains s 
				WHERE s.id_str=ms.str_id_str 
				AND ms.mut_id=m.id 
				AND m.alls_id_allel=a.id_allel 
				AND g.id_gene=a.gen_id_gene 
				AND g.symbol = \"$symbol\" 
				AND s.id_str= $id_str";
		
		global $db;
		$rows = $db->db_fetch($sql);	
		
		if ($rows == 'ERROR'){			
	  	  die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){	
  		   		#echo "$symbol : " . $row['alls_form'] . "<br>";
    			return $row['alls_form'];
  			}
		}
	}
	function fetch_mgi_allele_id_by_gene_symbol($symbol, $id_str) { 
		$sql = "SELECT a.mgi_ref 
				FROM genes g, alleles a, mutations m, mutations_strains ms, strains s 
				WHERE s.id_str=ms.str_id_str 
				AND ms.mut_id=m.id 
				AND m.alls_id_allel=a.id_allel 
				AND g.id_gene=a.gen_id_gene 
				AND g.symbol = \"$symbol\" 
				AND s.id_str= $id_str";		
		
		global $db;
		$rows = $db->db_fetch($sql);	
		
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){
				return $row['mgi_ref'];
			}
		}  
	}
	function fetch_lab_id_by_strain_id($strain_id){
		$sql = "select lab_id_labo from archive, strains where archive_id=id and id_str = $strain_id";
  		global $db;
		$rows = $db->db_fetch($sql);	
		
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){
				return $row['lab_id_labo'];
			}
		}	
	}   
	function fetch_project_id_by_strain_id($strain_id){
		$sql ="SELECT ps.project_id 
			   FROM strains s, projects_strains ps 
			   WHERE s.id_str=ps.str_id_str 
			   AND s.id_str=$strain_id";
		
		global $db;
		$rows = $db->db_fetch($sql);	
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){
				return $row['project_id'];
			}
		}		
	}	
	function make_tabs($tab_confs, $id_str){
		# make tabs
		$tabs = "<div id='strainDescTabs_$id_str'><ul>";
		$sections;
		foreach ( $tab_confs as $key => $val){	 
			if ( $key !== 'default' ){
	  			$tabs .= "<li><a href='#$val'>$key</a></li>";
				if ($key == 'General Information'){	  			
	  				$sections .= "<div id='$val'>" . $tab_confs['default'] . "</div>";
	  			}
	  			else {
	  				$sections .= "<div id='$val'></div>";	  
	  			}
	  		}	  		  	
		}  
		$tabs .= "</ul>" . $sections . "</div>";	
		$tabs .= "<script type='text/javascript'>$('#strainDescTabs').tabs();</script>";
	
		return $tabs;
	}
	function fetch_material_in_repository_desc($males, $females){
		$males = $males == '' ? 'is NULL' : "='$males'";
		$females = $females == '' ? 'is NULL' : "='$females'";
	
		$sql = "SELECT description 
				  FROM cv_repository_material 
				  WHERE males $males 
				  AND females $females";
		
		global $db;
		$rows = $db->db_fetch($sql);	
		if ($rows == 'ERROR'){		
	  		die("Can't execute query: ".mysql_error());
		}
		else {
			foreach ( $rows as $row ){
				return $row['description'];
			}
		}		        			
	}
	
	
}	
?>
