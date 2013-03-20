<div id="<?php print $block_html_id; ?>" class="<?php print $classes; ?>">
   
	<h1>Form Example Block (Test)</h1>
	
    <ul class="progress clearfix">
    	<li class="first active">First step &rsaquo;</li>
        <li>Second step &rsaquo;</li>
        <li class="last">And the last one</li>
    </ul>    
    
    <div class="form visible">
    	
        <h4>Here is just an example headline</h4>
        
        <p>Block with text fields</p>
        
        <div class="boxcontainer">
        
            <div class="box half first">
                
                <div class="form-item form-type-textfield">
                    <input class="form-text required" type="text" placeholder="This is a input field" />
                </div>
                
                <div class="form-item form-type-textfield">
                    <input class="form-text" type="text" placeholder="Another test" />
                </div>
                
                <div class="form-item form-type-textfield error">
                    <input class="form-text" type="text" placeholder="Please type something in" value="Example for a validation error (focus and it goes away)" />
                </div>
                
                <div class="form-item form-type-textfield">
                    <input class="form-text" type="text" placeholder="Input textfield" />
                </div>
                
                <div class="form-item form-type-textfield">
                    <input class="form-text" type="text" placeholder="Please type something in" />
                </div>
                                
            </div>
            
            <div class="box half last">
                
                <div class="form-item form-type-textfield">
                    <input class="form-text required" type="text" placeholder="This is a input field" />
                </div>
                
                <div class="form-item form-type-textfield">
                    <input class="form-text" type="text" placeholder="Another test" />
                </div>
                
                <div class="form-item form-type-textfield">
                    <input class="form-text" type="text" placeholder="Please type something in" />
                </div>
                
                <p><b>This is a dummy description for the textarea:</b></p>
                
                <div class="form-item form-type-textarea">
                    <textarea class="form-textarea" placeholder="I am a placeholder"></textarea>
                </div>
                
            </div>
            
            <div class="clear"></div>
        
        </div>
        
        <div class="boxcontainer">
        
            <h4>Another headline explaining a form section</h4>
            
            <div class="box half first">
            	<div class="form-item form-type-select">
                	<select class="form-select">
                    	<option value="">Please select</option>
                    	<option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Ms">Ms</option>
                        <option value="Dr">Dr</option>
                        <option value="Prof">Prof</option>
                    </select>
                </div>
                <div class="form-item form-type-select">
                	<select class="form-select">
                    	<option value="">Please select</option>
                    	<option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Ms">Ms</option>
                        <option value="Dr">Dr</option>
                        <option value="Prof">Prof</option>
                    </select>
                </div>
            </div>
            
            <div class="box half last">
            	<div class="form-item form-type-select">
                	<select class="form-select">
                    	<option value="">Please select</option>
                    	<option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Ms">Ms</option>
                        <option value="Dr">Dr</option>
                        <option value="Prof">Prof</option>
                    </select>
                </div>
            </div>
            
            <div class="clear"></div>
        
        </div>
        
        <div class="boxcontainer">
        
            <h4>Lorem ipsum dolor sit amet</h4>
            
            <div class="box half first">
            	
                <p><b>Some Checkboxes:</b></p>
                
                <div class="form-item form-type-checkbox">
 					<input type="checkbox" /> Yes
                </div>
                
                <div class="form-item form-type-checkbox">
 					<input type="checkbox" /> No
                </div>
                
                <div class="form-item form-type-checkbox">
 					<input type="checkbox" /> Maybe
                </div>
                
            </div>
            
            <div class="box half last">
				
                <p><b>Lorem ipsum dolor sit amet:</b></p>
                
                <div class="form-item form-type-checkbox">
 					<input type="radio" name="testradio[]" /> Only one is possible
                </div>
                
                <div class="form-item form-type-checkbox">
 					<input type="radio" name="testradio[]" /> Choose one
                </div>
                
                <div class="form-item form-type-checkbox">
 					<input type="radio" name="testradio[]" /> Last but not least
                </div>
                
                <p>In this paragraph, <span class="tooltip" data-tooltip="<p><b>Sample headline</b><br />This tooltip contains some information</p>">there is a tooltip</span> displaying further information.</p>
                
            </div>
            
            <div class="clear"></div>
        
        </div>
        
        <div class="boxcontainer">
        	
            <p><a class="btn big">Add Mutation</a> &nbsp; <b>You can add some mutations here</b></p>
            
        	<h4>Mutations for submissions</h4>
        	
            <div class="box full">
            	
                <table width="100%">                	
                    <thead>
                    	<tr>
                            <th>Original Background</th>
                            <th>Mutation Type</th>
                            <th>Mutation Subtype</th>
                            <th>Dominance Pattern</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    	<tr>
                        	<td>NOD (35)</td>
                            <td>GT</td>
                            <td></td>
                            <td>X-linked</td>
                            <td><a class="icon edit">Edit</a> <a class="icon remove">Remove</a></td>
                        </tr>
                        <tr>
                        	<td>129P2/OlaHsd x C5/BL (2861)</td>
                            <td>TM</td>
                            <td></td>
                            <td>semidominant</td>
                            <td><a class="icon edit">Edit</a> <a class="icon remove">Remove</a></td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
            
        </div>
        
        <div class="boxcontainer">
        	
            <div class="box half first">
            	<p><a class="btn big">Previous</a> &nbsp; <a class="btn big">Next</a></p>
            </div>
            
            <div class="box half last">
            	<p class="txtright"><a class="btn big">Cancel</a></p>
            </div>
            
            <div class="clear"></div>
            
        </div>
        
    </div>
   
</div>