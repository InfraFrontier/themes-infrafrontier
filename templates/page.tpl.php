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
        
        	<?php print $breadcrumb; ?>
                       
        	<?php print $messages; ?> 
            
            <?php if (isset($field_headimage)): ?>            
            <div class="headimage">           
            	<?php print $field_headimage; ?>
            </div>          
            <?php endif; ?>
            
            <div class="boxcontainer">
            
                <div class="box w640">
                	<?php print render($tabs); ?>
                	<?php print render($page['content']); ?> 
                </div>
				
                <?php if ($page['sidebar_first']): ?>
                <div class="box w300">
                	<?php print render($page['sidebar_first']); ?>
                </div>
                <?php endif; ?>
                
                <div class="clear"></div>
                
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
                		&copy; INFRAFRONTIER <?php print date('Y') ?> - all rights reserved
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
