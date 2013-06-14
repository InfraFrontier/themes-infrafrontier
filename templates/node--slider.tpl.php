<div class="slide <?php if (!isset($content['body'])) { print 'bright'; } ?>">
	
    <div class="slideimage"><?php print render($content['field_slideimage']) ?></div>
    
    <div class="slidedescription">
    	<?php
		print render($content['body']);
		?>
    </div>
    
    <div class="slidecontrol">
        <div class="prev"></div>
        <div class="next"></div>
    </div>
    
    <?php if (isset($content['field_link'])): ?>
    <div class="learnmore">
        <?php print render($content['field_link']) ?>
    </div>
    <?php endif; ?>
        
</div>
