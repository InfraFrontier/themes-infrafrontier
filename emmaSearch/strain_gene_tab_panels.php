<div id='strainTabs'>

   <ul>
     <li><a href="#browse_by_strains">Browse by Strains</a></li>
     <li><a href="#browse_by_genes">Browse by Genes</a></li>   
     <li><a href="#browse_by_human_diseases">Browse by Human Diseases</a></li>     
   </ul>

  <div id='browse_by_strains'>  
    <div id='strain_menu'>
      <?php include("strain_menu.php"); ?>
     
    </div>
    <div id='strain_data'><span class='useMsg'>Please hover over the submenu to start</span></div>
  </div> <!-- end of browse_by_strains -->

  <div id='browse_by_genes'>
     <div class='letters'>
      <?php
      $letters;
      $ltr_array = range('A','Z');
      array_push($ltr_array, "0-9");
      foreach ( $ltr_array as $val ){
		$letters .= "<a class='ltrLink' href='#' id='$val'>$val</a>";
      }

      echo $letters;
      ?>
    </div>
    <div id='gene_data'><span class='useMsg'>Please click on one of the alphanumeric buttons to start</span></div>
  </div> <!-- end of browse_by_genes -->   
 
  <div id='browse_by_human_diseases'>
  	 <div class='letters'>
      <?php
      $letters = false;
      $ltr_array = range('A','Z');
      array_push($ltr_array, "0-9");
      foreach ( $ltr_array as $val ){
		$letters .= "<a class='omLtrLink' href='#' id='$val'>$val</a>";
      }
      echo $letters;
      ?>   
     </div>	 
     <div id='omim_emma'><span class='useMsg'>Please click on one of the alphanumeric buttons to start</span></div>    
  </div> <!-- end of browse_by_human_diseases -->
   
</div> <!-- end of strainTabs -->
