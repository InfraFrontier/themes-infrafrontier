<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?>">

	<?php if ($teaser): ?>
    	
        <h3><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h3>
        <p class="date"><?php print render($content['field_date']); ?></p>
        <?php print render($content['body']); ?>
        <p><a class="icon more" href="<?php print $node_url; ?>">Read more</a></p>
        
    <?php else: ?>
    
    	<div class="content">
			<h1><?php print $title; ?></h1>
            <p class="date"><?php print render($content['field_date']); ?></p>
			<?php print render($content['body']); ?>
        </div>
        
    <?php endif; ?>

</div>
