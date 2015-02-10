<?php header('Content-Type:text/html; charset=UTF-8');
    $url = "/search?keyword=";
?>

<!-- strain browing menu layout -->
<!-- links are processed by JS, so that tabs can be programmatically selected -->

<ul class='menu'>
 <li  class='menu'><span>Mutation Types</span>
  <ul>
   <li class='liMenu'><span class='submenu'>Targeted Mutant Strains</span>
    <ul class='sublist'>
     <li><a href="<?php echo ${url} ?>all_targeted_mutants">all of the targeted</a></li>
     <li><a href="<?php echo ${url} ?>knock_out">knock-out</a></li>
     <li><a href="<?php echo ${url} ?>knock_in">knock-in</a></li>
     <li><a href="<?php echo ${url} ?>targeted_conditional">targeted conditional</a></li>
     <li><a href="<?php echo ${url} ?>targeted_non_conditional">targeted non-conditional</a></li>
     <li><a href="<?php echo ${url} ?>point_mutation">point mutation</a></li> 
     <li><a href="<?php echo ${url} ?>conditional_mutation">conditional mutation</a></li>
     <li><a href="<?php echo ${url} ?>other_targeted">other targeted</a></li>     
    </ul>
   </li>
   <li class='singleton'><a href="<?php echo ${url} ?>gene_trap">Gene trap strains</a></li>  
   <li class='singleton'><a href="<?php echo ${url} ?>transgenic">Transgenic strains</a></li>
   <li class='liMenu'><span class='submenu'>Chemically- / radiation-induced strains</span>
    <ul class='sublist'>
     <li><a href="<?php echo ${url} ?>all_induced_mutants">all of the induced</a></li>   
     <li><a href="<?php echo ${url} ?>chemically_induced">chemically</a></li>
     <li><a href="<?php echo ${url} ?>radiation_induced">radiation</a></li>   
    </ul>
   </li>
   <li class='singleton'><a href="<?php echo ${url} ?>chromosomal_anomalies">Chromosome anomalies</a></li>
   <li class='singleton'><a href="<?php echo ${url} ?>spontaneous">Spontaneous</a></li>
   <li class='singleton'><a href="<?php echo ${url} ?>other">Other</a></li>
  </ul>
 </li>

  <li class='menu'><span>Research Tools</span>
   <ul>     
    <li class='singleton'><a href="<?php echo ${url} ?>cre_lines">Cre recombinase expressing strains</a></li>
    <li class='singleton'><a href="<?php echo ${url} ?>tet_expression_system">Strains with TET expression system</a></li>
    <li class='singleton'><a href="<?php echo ${url} ?>flp_lines">FLP recombinase expressing strains</a></li>
   </ul>
  </li>

  <li class='menu'><span>Wellcome Trust knockout mice</span>
   <ul>  
    <li class='singleton'><a href="<?php echo ${url} ?>deltagen">Deltagen strains</a></li>
    <li class='singleton'><a href="<?php echo ${url} ?>lexicon">Lexicon strains</a></li>
   </ul>
  </li>

  <li><a href="<?php echo ${url} ?>eucomm" class='top_level_link'>IKMC/IMPC strains</a></li>
<li><a href="<?php echo ${url} ?>EUCOMMToolsCre" class='top_level_link'>EUCOMMTools Cre strains</a></li>
  <li><a href="<?php echo ${url} ?>full_list" class='top_level_link'>Full strain list</a></li>
</ul>




