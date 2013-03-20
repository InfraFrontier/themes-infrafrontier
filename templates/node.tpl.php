<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>">

	<?php hide($content['field_headimage']); ?>
	    
	<?php if ($page && isset($content['body'])): ?>
    	
        <div class="content">
            <h1><?php print $title ?></h1>
			<?php print render($content); ?>
        </div>
        
	<?php elseif ($page): ?>
    
    	<div class="head nonodecontent"><?php print $title; ?></div>
            
    <?php elseif (!$page): ?>
    
        <div class="content">
            
			<?php print render($content); ?>
			
			<?php if (user_access('administer nodes')): ?>
            	<p class="editlink"><a class="editlink" href="/node/<?php print $node->nid; ?>/edit" title="Edit">edit</a></p>
            <?php endif; ?>
            
        </div>
        
    <?php endif; ?>

</div>