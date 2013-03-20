<?php
// bugfix for the 'meta tags' module to work properly on frontpage
// (justs renders the content, but doesn't print it)
render($page['content']);
?>

<div id="wrapper">

	<header id="header">        
        <div id="innerheader">    
            <div id="logo">
                <a href="/" title="Infrafrontier"><img src="/<?php print $directory ?>/img/logo-infrafrontier.png" /></a>
            </div>            
            <nav id="mn">
            	<?php print render($page['mainnavi']); ?>
            </nav>            
            <div class="clear"></div>        
        </div>    
    </header>
	
    <div id="container">
        
        <section id="content"> 
                               
        	<?php print $messages; ?> 
            
            <?php print render($tabs); ?>
                        
            <div class="boxcontainer">
        
                <div class="box w640">
                    <div class="boxcontent nopadding">
                    	<?php print views_embed_view('slider'); ?>               	
                    </div>
                </div>
                
                <div id="emmastrains-searchbox" class="box w300">
                    <div class="head">EMMA strains</div>
                    <div class="boxcontent">
                        <h6>Search EMMA strains</h6>
                        <p><form><input type="text" placeholder="Search strains..." /><input class="loupe" type="submit" value="" /></form></p>
                        <p><i><b>Search by:</b> Gene name/symbol, strain name, EMMA ID, OMIM name/ID, phenotype, ...</i></p>
                        <h4>Browse EMMA strains</h4>
                        <p><a class="btn" href="#">Genes</a> <a class="btn" href="#">Phenotypes</a> <a class="btn" href="#">Human Diseases</a></p>
                    	<p>&nbsp;</p>
                        <div class="splithalf">
                        	<div class="box half">
                            	<p><a id="togglehelp" class="icon help" href="#">Help</a></p>
                            </div>
                            <div class="box half">
                            	<p align="right"><img src="/<?php print $directory ?>/img/icon/emma-logo-soft.png" /></p>
                            </div>
                        </div>                       
                    </div>
                </div>
                
                <div id="stooltip">
                	<h6>You could search the following</h6>
                    <ul>
                    	<li>Affected gene<br /><span>e.g. Sema3e or semaphorin 3E</span></li>
                        <li>International strain designation<br /><span>e.g. B6.129P2-Sema3e&lt;tm1Ddg</li>
                        <li>Common strain name<br /><span>e.g. Sema3E</span></li>
                        <li>Phenotype description<br /><span>e.g. open field test</span></li>
                        <li>EMMA ID<br /><span>e.g. EM:01373</span></li>
                    </ul>
                    <p>The search returns a list of strains whose description contains one or more matches to your query. The search is case insensitive and partial terms can be entered.</p>
                </div>
                
                <div class="clear"></div>
            
            </div>
                        
            <div class="boxcontainer">
            
                <div class="box w640 floatingboxes">
                	
                    <?php $fn_rs = field_get_items('node', $node, 'field_txt_rs'); ?>
                    
                    <div class="head">Resources and Services</div>
                    
                    <div class="floatbox">
                    	<div class="boxicon"><img src="/<?php print $directory ?>/img/icon/boxicon/deposit.png" /></div>
                        <?php print render(field_view_value('node', $node, 'field_txt_rs', $fn_rs[0])); ?>
                    </div>
                    
                    <div class="floatbox middle">
                    	<div class="boxicon"><img src="/<?php print $directory ?>/img/icon/boxicon/order.png" /></div>
                        <?php print render(field_view_value('node', $node, 'field_txt_rs', $fn_rs[1])); ?>
                    </div>
                    
                    <div class="floatbox">
                    	<div class="boxicon"><img src="/<?php print $directory ?>/img/icon/boxicon/axenic.png" /></div>
                        <?php print render(field_view_value('node', $node, 'field_txt_rs', $fn_rs[2])); ?>
                    </div>
                                        
                    <div class="floatbox">
                    	<div class="boxicon"><img src="/<?php print $directory ?>/img/icon/boxicon/production.png" /></div>
                        <?php print render(field_view_value('node', $node, 'field_txt_rs', $fn_rs[3])); ?>
                    </div>
                    
                    <div class="floatbox middle">
                    	<div class="boxicon"><img src="/<?php print $directory ?>/img/icon/boxicon/phenotyping.png" /></div>
                        <?php print render(field_view_value('node', $node, 'field_txt_rs', $fn_rs[4])); ?>
                    </div>
                    
                    <div class="floatbox">
                    	<div class="boxicon"><img src="/<?php print $directory ?>/img/icon/boxicon/training.png" /></div>
                        <?php print render(field_view_value('node', $node, 'field_txt_rs', $fn_rs[5])); ?>
                    </div>
                    
                </div>
                
                <div class="box w300">
                	<?php print views_embed_view('news','block_1'); ?> 
                </div>
                
                <div class="clear"></div>
            
            </div>
            
            <div class="boxcontainer">
            
            	<?php $fn_3boxes = field_get_items('node', $node, 'field_3boxes'); ?>
            
                <div class="box w310">
                    <div class="head">Procedures</div>
					<div class="boxcontent"><?php print render(field_view_value('node', $node, 'field_3boxes', $fn_3boxes[0])); ?></div>
                </div>
                
                <div class="box w310">
                	<div class="head">Knowledgebase</div>
                    <div class="boxcontent"><?php print render(field_view_value('node', $node, 'field_3boxes', $fn_3boxes[1])); ?></div>
                </div>
                
                <div class="box w300">
                	<div class="head">Projects</div>
                	<div class="boxcontent"><?php print render(field_view_value('node', $node, 'field_3boxes', $fn_3boxes[2])); ?></div> 
                </div>
                
                <div class="clear"></div>
            
            </div>
            
            <div class="boxcontainer">
            
            	<div id="box-abouttext" class="box w640">
                	<div class="head">About Infrafrontier</div>
					<div class="boxcontent"><?php print render(field_view_field('node', $node, 'body')); ?></div>
                </div>
                
                <div id="box-partnership" class="box w300">
                    <div class="head">European Partnership</div>
                    <?php $fn_ep = field_get_items('node', $node, 'field_txt_ep'); ?>
                    <div class="boxcontent">
                    	<?php print render(field_view_value('node', $node, 'field_txt_ep', $fn_ep[0])); ?>
                    </div>
                    <div class="boxcontent">
                    	<?php print render(field_view_value('node', $node, 'field_txt_ep', $fn_ep[1])); ?>
                    </div>
                </div>
                
                <div class="clear"></div>
            
            </div>
            
            <div id="logoslider">
                <div id="logoslidecontrols">
                    <div class="prev"></div>
                    <div class="next"></div>
                </div>            	
                <div id="logoslidercontainer">
                    <div class="logoslide"><img src="/<?php print $directory ?>/img/logoslider/1.jpg" /></div>
                    <div class="logoslide"><img src="/<?php print $directory ?>/img/logoslider/1.jpg" /></div>
                </div>
            </div>
            
        </section>
    
	</div>
    
    <footer id="footer">
    
    	<div class="innerfooter">        
        	<div id="toplink"><a href="#top">to top</a></div>        	
            <div id="fn">
            	<?php print render($page['footernavi']); ?>              
                <div class="clear"></div>
            </div>            
            <div id="tn">
            	<?php print render($page['usernavi']); ?>
            </div>        
        </div>
        
        <div id="footerline">        	
            <div class="innerfooter">
            	<div class="splithalf">
                	<div class="half">
                		&copy; Infrafrontier <?php print date('Y') ?> - all rights reserved
                    </div>
                    <div class="half">
                    	<div id="bn">
                            <?php print render($page['usernavi']); ?>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
        
    </footer>
    
</div>