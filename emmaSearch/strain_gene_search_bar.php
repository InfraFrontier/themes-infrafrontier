<?php

$srchTip = <<<TIP
<div class="helpBox">
	<p><a href="#" class="icon help" id="togglehelp">Help</a></p>
</div>

<div id="srchTooltip">
    <h6>You could search the following</h6>
    <ul>
	    <li>Affected gene <br><span>e.g. Sema3e or semaphorin 3E</span></li>
	    <li>International strain designation <br><span>e.g. B6.129P2-Sema3e<tm1Ddg</span></li>
	    <li>Common strain name<br><span>e.g. Sema3E</span></li>
	    <li>Phenotype description<br><span>e.g. open field test</span></li>
	    <li>EMMA ID<br><span>e.g. EM:01373</span></li>
    </ul>
    <p>The search returns a list of strains whose description contains one or more matches to your query. The search is case insensitive and partial terms can be entered.</p>
</div>
TIP;

/*
 <a class="searchtip" href="#" title="You could search the following:|
                <ul id='srchtip'>
                    <li>Affected Gene e.g. Sema3e or semaphorin 3E,</li> 
                 <li>International Strain Designation e.g. B6.129P2-Sema3e<tm1Ddg></li> 
                    <li>Common Strain Name e.g. Sema3E</li> 
                    <li>Phenotype Description e.g. open field test</li> 
                    <li>EMMA ID e.g. EM:01373</li>
                    <br>
                    <b>Search returns a list of strains whose description contains one or more matches to your query. The search is case <span id='case'>insensitive</span> and partial terms can be entered.</b>
                </ul>">Help</a>
*/

$srchbar =<<<SRCH
    <div class='row-fluid'>		
		<div id="srchBlkL" class='span6'>	  
            <form id='geneStrainSearch' onsubmit="geneStrainSearch(); return false;">    
	            <input id='searchInputBox' type='text' placeholder="Search strain ..." name='searchbox' /><p>  
                <input class="loupe" type="submit" value="">               
            </form>
            <!--<div id='searchHint'>Search by gene name, strain name, phenotype, EMMA_id, OMIM name/ID </div>-->  
        </div>
        <div id="srchBlkC1" class='span1'>$srchTip</div>	        
        <div id='srchBlkC2' class='span3'>
            <ul class='order'>
                <li id='greenDot'>Order</li>
                <li id='orangeDot'>Order (only small colony available)</li>
                <li id='redDot'>Register interest</li>
            </ul>
        </div>    
         <div id='srchBlkR' class='span2'><a href='/infrafrontier-research-infrastructure/international-collaborations-and-projects/european-mouse'><img src='http://dev.emmanet.org/images/logo-emma.pngs' /></a></div>
    </div>    
SRCH;

echo $srchbar;

?>

