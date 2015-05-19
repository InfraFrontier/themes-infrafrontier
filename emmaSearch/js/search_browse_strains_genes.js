$(document).ready(function(){

    var regex = /infrafrontier.eu\/$|infrafrontier.eu\/emma/;
    //var regex = /infrafrontier.eu/;
    var domainMatch = regex.exec(window.location.href);

    if ( domainMatch ){
       var srchInput = $('div#emmastrains-searchbox input[type=text]');
        var srchSubmit = $('div#emmastrains-searchbox input[type=submit]');

        srchSubmit.click(function(){
            var sInput = srchInput.val();
            if ( sInput == 'Search strains...' ){
                alert('Sorry, but the search keyword is missing ...');
                return false;
            }
            else {            	
            	window.location.href = '/search?keyword=' +  sInput;  
            	return false;  // so that Drupal does not go to new page for this link
            }
        });      

   }
   else if ( window.location.href.indexOf('/search') != -1 ){ 

    if(typeof(window.EMMA) === 'undefined') {
            window.EMMA = {};          
    }
   
    EMMA.oCode_types = {
        "all_targeted_mutants":"TM", 
        "knock_out":"TMKO",
        "targeted_conditional":"TMTC",
        "targeted_non_conditional":"TMTNC",
        "knock_in":"TMKI",
        "gene_trap":"TMGT",
        "point_mutation":"TMPM",
        "conditional_mutation":"TMCM",
        "other_targeted":"TMOTH",
        "gene_trap":"GT",
        "transgenic":"TG",
        "all_induced_mutants":"IN",
        "chemically_induced":"INCH",
        "radiation_induced":"INXray",
        "chromosomal_anomalies":"CH",
        "spontaneous":"SP",
        "other":"XX",
        "cre_lines":"Cre",
        "flp_lines":"FLP",
        "tet_expression_system":"TET",
        "deltagen":"DEL",
        "lexicon":"LEX",
        "eucomm":"EUC",
        "full_list":"ALL",
	"eucommtoolscre":"EUCre"
    };

	// clear text onclick
	if ( !$.browser.msie ){     
        $('#searchInputBox').corner('3px');
    }

	$('#searchInputBox').click(function(){
		$(this).val('');		
	});
	
    $('a.icon.help').toggle(
        function(){
            $('div#srchTooltip').show();
        },
        function(){
             $('div#srchTooltip').hide();
        }
    );

	// provide a keyword parameter in the URL as a bookmark for quick access
	var keyword = $('div#hiddenKW').text();
	if ( keyword !== '__ajax__' && keyword !== ''  ){
		//alert(keyword);
		geneStrainSearch(keyword);
	}   
	
    // browse by strain menu control
    $('span.submenu').click(function(){        
        var oUl = $(this).parent().find('ul');
        if ( oUl.is(':visible') ){               
            oUl.hide();
            $(this).removeClass('ulOpen').addClass('ulClose');
        }
        else {
            oUl.show();
            $(this).removeClass('ulClose').addClass('ulOpen');
        }
    });
    
    $('ul.menu ul li a').live('click', function(){  
        $('ul.menu ul').hide();
        $('span.submenu').removeClass('ulOpen').addClass('ulClose');
    });

    $('ul.menu li.menu').mouseover(function(){         
        //$('ul.menu ul').hide();
        $(this).find('>ul').show();
    }).mouseout(function(){
        $(this).find('>ul').hide();
    });
    
    if ( !$.browser.msie ){
        $('ul.menu ul').corner('3px');  
    }	
    }
 
});

function fetch_data_by_sublist(sublist){	

    var containerId = 'strain_data';
    var tableId = sublist;
    $('#'+containerId).html("<div class='loader'><img src=\""+Loader+"\" /><span>loading ...</span></div>"); 	

    var aTableIds = [];
    if ( sublist == 'TM' || sublist == 'IN' || sublist == 'ALL'){// || sublist == 'Cre' ){    
        var url1 = fetch_url() + "?subType=" + sublist; 
       
        $.get(url1, function(data){
            aTableIds = JSON.parse(data);                              
            var url2 = fetch_url() + "?sublist=" + sublist;               
            loadDataTable(url2, containerId, aTableIds, 6);
        });   
    }
    else { 
alert(sublist);			
	    var url = fetch_url() + "?sublist=" + sublist;
        aTableIds.push(sublist);
        loadDataTable(url, containerId, aTableIds, 6);	
    }
   	
}

// -------- functions ----------------

function geneStrainSearch(keyword){
   
	var srchTxt;
	if ( typeof(keyword) == 'undefined' ){
		srchTxt = $('#searchInputBox').val();
	}
	else {	
		srchTxt = keyword;
		$('#searchInputBox').val(srchTxt);
	}
//  alert(EMMA.oCode_types[srchTxt.toLowerCase()]);
    if ( typeof EMMA.oCode_types[srchTxt.toLowerCase()] != 'undefined' ){
        $('div#strainTabs').tabs("select" , 0);
        fetch_data_by_sublist(EMMA.oCode_types[srchTxt.toLowerCase()]);  
    } 
    else if ( srchTxt.toLowerCase() == 'browse_strain_types' ){
        $('div#strainTabs').tabs("select" , 0);			
	}     
    else if ( srchTxt.toLowerCase() == 'browse_genes' ){
        $('div#strainTabs').tabs("select" , 1);	
		/*var url = fetch_url() + "?letter=A";
        var containerId = 'gene_data';              
        var tableId = 'geneNameA';
        var aTableIds = [];
        aTableIds.push(tableId);
        loadDataTable(url, containerId, aTableIds, 6);
        $('div#browse_by_genes a#A').css({'border':'1px solid gray', 'text-decoration':'none','padding':'0 2px'});*/
	} 
    else if ( srchTxt.toLowerCase() == 'browse_human_diseases' ){
        $('div#strainTabs').tabs("select" , 2);	
		/*var url = fetch_url() + "?omLetter=A";
        var containerId = 'omim_emma';              
        var tableId = 'omimA';
        var aTableIds = [];
        aTableIds.push(tableId);
        loadDataTable(url, containerId, aTableIds, 7);
        $('div#browse_by_human_diseases a#A').css({'border':'1px solid gray', 'text-decoration':'none','padding':'0 2px'});*/
	} 
	else {
        if ( srchTxt == '' ){
          alert('Sorry, but the search keyword is missing');  
        }
        else {
		    var url = fetch_url() + "?query="+ srchTxt;

	        // will then trigger the add option of tabs() (declared in mutant_types.php) 
	        // to add closable button to new tab
	        if (srchTxt != ''){        
		        $('#strainTabs').tabs('add', url, srchTxt); 
	        }
        }	
	}
}

function invokeDataTable (oInfos){   	   	
    console.log(JSON.stringify(oInfos, null, 2));
    console.log($('table#' + oInfos.mode).size());

   	var oDtable = $('table#' + oInfos.mode).dataTable({
        //"sDom": "<'row-fluid'<'#foundEntries'><'span6'f>r>t<'row-fluid'<'#tableShowAllLess'><'span6'p>>",
		"sPaginationType": "bootstrap",
		"bProcessing": true,
		"bSortClasses": false,	
		"oLanguage": {
			"sSearch": "Filter data in table:"
		},				 	
       	"bSort" : true,       	
       	"bServerSide": true,	    		
       	//"sDom": "<'row-fluid'<'span6'><'span6'>>t<'row-fluid'<'span6'i><'span6'p>>",
       	"sDom": "<'row-fluid'<'#foundEntries'><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",    		    			
       	"fnDrawCallback": function( oSettings ) {  // when dataTable is loaded    		   			  			  			  		
    	},
  		"sAjaxSource": oInfos.ajaxSrc,    		
   		"fnServerParams": function ( aoData ) {
    			aoData.push(	    			
   			    {"name": oInfos.mode,    				
   				 "value": JSON.stringify(oInfos, null, 2)
   				}	    
   			)		
   		}
    });  
}

function loadDataTable(url, containerId, tblIds, iCols){       

    var aAoColumnConf = [];          
    for( var i=0; i<iCols; i++){
        if ( i == iCols - 2 ){
           aAoColumnConf.push({ "sType": "alt-string", "bSearchable" : false });
        }
        else {
           aAoColumnConf.push({ "sType": "string" }); 
        }
    }
    var iNoSort = iCols - 1; 

    if ( !url ){
        var oRecord = $('div#'+containerId).find('p#records');
        $('div#'+containerId).find('p#records').hide();               
        makeTable(tblIds[0], aAoColumnConf, iNoSort, $(oRecord).text());  
    }
    else {
        $.get(url, function(data){        
            var data = JSON.parse(data);                   
           
            if ( data ){
                $('#'+containerId).html(data.result); 

                for( var i=0; i< tblIds.length; i++ ){
                    var tblId = tblIds[i]; 
                    var record;
                    if ( typeof data.records != 'undefined' ){
                        var oTblId_record = JSON.parse(data.records);
                        record = oTblId_record[tblId];               
                    }          
                    else {
                        record = data.record;
                    } 
                    makeTable(tblId, aAoColumnConf, iNoSort, record);  // need to move ajax out of this loop     
                }
            }
            else {
                $('#' + containerId).html("<span class='useMsg'>INFO: your search keyword returns nothing in the database</span>");   
            }
        });
    }
}
function makeTable(tblId, aAoColumnConf, iNoSort, record) {
    EMMA.multiOmimRowId = false;
    var oTable = $('table#'+ tblId).dataTable({ 
       
                "bSortClasses": false,
                "aoColumnDefs": [
                    { "bSortable": false, "aTargets": [ iNoSort ] }
                ],
                "aoColumns": aAoColumnConf,	
                "aaSorting": [[1, 'asc']],
                "sPaginationType": "full_numbers",                
                "oLanguage": {
                    "sSearch": "Filter: "
                },
                "sDom": "<'row-fluid'<'#foundEntries'><'span9'lf>r>t<'row-fluid'<'span6'i><'span6'p>>"   
                //"sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>"   
                
    });  
 
    $('div#' + tblId + '_wrapper').find('#foundEntries').html(record).addClass('span3');  
   
	var oAaccordion;

    oTable.find('td.toggle span img').live('click', function () {
        
        var nTr = this.parentNode.parentNode.parentNode;
        var allTrs = this.parentNode.parentNode.parentNode.parentNode.childNodes;
       
        for ( var i=0; i<allTrs.length; i++){           
            if ( $(allTrs[i]).attr('class') && nTr != allTrs[i] && $(allTrs[i]).attr('class').indexOf('selectedTr') != -1  ){         
                $(allTrs[i]).removeClass('selectedTr').find('td.toggle img').attr('src', fetch_EMMA_drupal_path() + "/images/plus.png");
                oTable.fnClose(allTrs[i]);
            }
        }
        // change tr bg color to mark for selected row
        $('table#'+tblId + ' tr').removeClass('selectedTr');
        $(nTr).addClass('selectedTr');

        if ( this.src.match('plus') ){
            /* This row is not yet open - open it */
                           
            this.src = fetch_EMMA_drupal_path() + "/images/minus.png";
            var id_str = this.id;
        
            // fetch strain desc
            var url2 = fetch_url() + "?id_str=" + id_str; 
            $.get(url2, function(data){                  

                oTable.fnOpen( nTr, data ); 
            
                oAaccordion = $('div#descAccordion'+id_str).accordion({ autoHeight: false,  collapsible: true, active: false }); 

                // apply JS to loaded accordion tabs
                injectJsToAccordionTabs(oAaccordion);
        
                // apply JS to loaded action rows
                injectJsToActionRows(id_str, oAaccordion);                
            });	                    
        }
        else {
            $(nTr).removeClass('selectedTr');
            /* close this row */
            this.src = fetch_EMMA_drupal_path() + "/images/plus.png";
            oTable.fnClose( nTr );
        }
    }); 
        
    // toggle show/hide of hidden dataTable row   
    // use live so that opening new hidden row will work with pagination                   
    oTable.find('td.emmaID').live('click', function () {
        var nTr = this.parentNode;
        var id = $(this).parent().attr('id');
        var img = $('td.toggle').find('img#' + id);
        var src = img.attr('src');
    
        if ( src.indexOf('plus.png') != -1 ){
            // open hidden tr           
            img.click();  // reuse already defined event                                         
        } 
        else {       
            $(nTr).removeClass('selectedTr');
            // close this row 
            img.attr('src', fetch_EMMA_drupal_path() + "/images/plus.png");
            oTable.fnClose( nTr );
        }
    }); 

    $('span.omimToggleAll').live('click', function(){   
        var id = $(this).attr('id');
        var img = $('td.toggle').find('img#' + id);
        var src = img.attr('src');
    
        if ( src.indexOf('plus.png') != -1 ){
            // open hidden tr
            EMMA.multiOmimRowId = id;
            img.click();  // reuse already defined event                                         
        } 
        else {
            if ( (typeof oAaccordion.accordion("option", "active") == 'number' && oAaccordion.accordion("option", "active") != 2) ||
                 (typeof oAaccordion.accordion("option", "active") == 'boolean') ){         
                oAaccordion.accordion('activate', 2); // show 3rd tab: information from EMMA
            }
        }       
    });	

    activate_tooltip(oTable);
}
function activate_tooltip(oTable){
    oTable.find('td.order img, td.emmaID span, td.toggle img').live('mouseover', function(){      
        $(this).siblings('span.instantToolTip').show();
    }).live('mouseout', function(){      
        $(this).siblings('span.instantToolTip').hide()
    });  

    oTable.find('span.mta, span.genotyping, span.availabilities').live('mouseover', function(){    
        $(this).find('span.instantToolTip').show();
    }).live('mouseout', function(){      
        $(this).find('span.instantToolTip').hide()
    });   
}
function injectJsToAccordionTabs(obj){
   
    obj.find('span.qc').toggle(
        function(){
              $(this).parent().siblings('div#qcData').show();
              $(this).text('Hide Data');
        },
        function(){
              $(this).parent().siblings('div#qcData').hide();
              $(this).text('Show Data');
        }
    ).css('cursor','pointer');
}
function injectJsToActionRows(id_str, oAaccordion) {
  
    var oAR = $("div.descActionRow[id='" + id_str + "']");  
    if ( !$.browser.msie) {
	oAR.find('span').corner('3px');       
    }	

    oAR.find('span.infoSheet').click(function(){
        show_pdf(id_str);       
    });

    oAR.find('span.mgiLink').click(function(){
        var obj = oAaccordion.find('td.gSymLinks');
        var links = obj.html();
        
        if ( links.indexOf(',') == -1 ){
            window.open(obj.find('a').attr('href'), '_blank');  // opens in new tab           
        }
        else {
            // we have multiple links  
            if ( $(this).find('div').is(':visible') ){       
                $(this).find('div').hide();
            }
            else {
                $(this).find('div').html('Multiple genes are associated with this strain:<br>' + links).show();
		if (! $.browser.msie){
			$(this).find('div').corner('3px');
		}
            }
        }
    });
    
    oAR.find('span.availabilities').click(function(){ 
        // this button, when not grayout, toggles the first accordion tab, but don't close it if already opened        
        if ( $(this).attr('class').indexOf('grayout') == -1 ){
            if ( (typeof oAaccordion.accordion("option", "active") == 'number' && oAaccordion.accordion("option", "active") != 0) ||
                 (typeof oAaccordion.accordion("option", "active") == 'boolean') ){         
                oAaccordion.accordion('activate', 0); // show first tab: general information         
            }
        }
    });

    if ( EMMA.multiOmimRowId == id_str ){
        oAaccordion.accordion('activate', 2); // show 3rd tab: information from EMMA  
    }


    // dynamic mice order button label on the action button rows of the detailed view  
    var link = $('tr#' + id_str).find('td.order').attr('rel');  
    // dynamically change text of order button
    if (link.indexOf("&wr=1") != -1){
        oAR.find('span.order').text('Register interest');
    }
    oAR.find('span.order').click(function(){           
        window.open(link, '_blank');  // opens in new tab    
    }); 
}
//HIGHLIGHT FCT
$.fn.dataTableExt.oApi.fnSearchHighlighting = function(oSettings) {
    // Initialize regex cache
    oSettings.oPreviousSearch.oSearchCaches = {};
      
    oSettings.oApi._fnCallbackReg( oSettings, 'aoRowCallback', function( nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        // Initialize search string array
        var searchStrings = [];
        var oApi = this.oApi;
        var cache = oSettings.oPreviousSearch.oSearchCaches;
        // Global search string
        // If there is a global search string, add it to the search string array
        if (oSettings.oPreviousSearch.sSearch) {
            searchStrings.push(oSettings.oPreviousSearch.sSearch);
        }
        // Individual column search option object
        // If there are individual column search strings, add them to the search string array
        if ((oSettings.aoPreSearchCols) && (oSettings.aoPreSearchCols.length > 0)) {
            for (var i in oSettings.aoPreSearchCols) {
                if (oSettings.aoPreSearchCols[i].sSearch) {
                searchStrings.push(oSettings.aoPreSearchCols[i].sSearch);
                }
            }
        }
        // Create the regex built from one or more search string and cache as necessary
        if (searchStrings.length > 0) {
            var sSregex = searchStrings.join("|");
            if (!cache[sSregex]) {
                // This regex will avoid in HTML matches
                cache[sSregex] = new RegExp("("+sSregex+")(?!([^<]+)?>)", 'i');
            }
            var regex = cache[sSregex];
        }
        // Loop through the rows/fields for matches
        $('td', nRow).each( function(i) {
        	
            // Take into account that ColVis may be in use
            var j = oApi._fnVisibleToColumnIndex( oSettings,i);
            // Only try to highlight if the cell is not empty or null
            if (aData[j]) {
                // If there is a search string try to match
                if ((typeof sSregex !== 'undefined') && (sSregex)) {                	
                    this.innerHTML = aData[j].replace( regex, function(matched) {
                        return "<span class='hit'>"+matched+"</span>";
                    });
                }
                // Otherwise reset to a clean string
                else {
                    this.innerHTML = aData[j];
                }
            }
        });
        return nRow;
    }, 'row-highlight');
    return this;
};


/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings ){		
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
		"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
}

/* Bootstrap style pagination control */
$.extend( $.fn.dataTableExt.oPagination, {
	"bootstrap": {
		"fnInit": function( oSettings, nPaging, fnDraw ) {
			var oLang = oSettings.oLanguage.oPaginate;
			var fnClickHandler = function ( e ) {
				e.preventDefault();
				if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
					fnDraw( oSettings );
				}
			};

			$(nPaging).addClass('pagination pagination-small').append(
					'<ul>'+
					'<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
					'<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
					'</ul>'
			);
			var els = $('a', nPaging);
			$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
			$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
		},

		"fnUpdate": function ( oSettings, fnDraw ) {
			var iListLength = 5;
			var oPaging = oSettings.oInstance.fnPagingInfo();
			var an = oSettings.aanFeatures.p;
			var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

			if ( oPaging.iTotalPages < iListLength) {
				iStart = 1;
				iEnd = oPaging.iTotalPages;
			}
			else if ( oPaging.iPage <= iHalf ) {
				iStart = 1;
				iEnd = iListLength;
			} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
				iStart = oPaging.iTotalPages - iListLength + 1;
				iEnd = oPaging.iTotalPages;
			} else {
				iStart = oPaging.iPage - iHalf + 1;
				iEnd = iStart + iListLength - 1;
			}

			for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
				// Remove the middle elements
				$('li:gt(0)', an[i]).filter(':not(:last)').remove();

				// Add the new list items and their event handlers
				for ( j=iStart ; j<=iEnd ; j++ ) {
					sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
					$('<li '+sClass+'><a href="#">'+j+'</a></li>')
						.insertBefore( $('li:last', an[i])[0] )
						.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
							fnDraw( oSettings );
						} );
				}

				// Add / remove disabled classes from the static elements
				if ( oPaging.iPage === 0 ) {
					$('li:first', an[i]).addClass('disabled');
				} else {
					$('li:first', an[i]).removeClass('disabled');
				}

				if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
					$('li:last', an[i]).addClass('disabled');
				} else {
					$('li:last', an[i]).removeClass('disabled');
				}
			}
		}
	}
} );

$.extend( $.fn.dataTableExt.oStdClasses, {
    "sWrapper": "dataTables_wrapper form-inline"
});

// Sort image columns based on the content of the title tag
$.extend( $.fn.dataTableExt.oSort, {
    "alt-string-pre": function ( a ) {
        return a.match(/alt="(.*?)"/)[1].toLowerCase();
    },

    "alt-string-asc": function( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },

    "alt-string-desc": function(a,b) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
}); 
function fetch_EMMA_drupal_path(){
        return '/sites/infrafrontier.eu/themes/custom/infrafrontier/emmaSearch';
}
function fetch_url(){
	// make sure url is correct for production/test env	    
	return fetch_EMMA_drupal_path() + "/search_browse_emmastr_db.php";	
}
function show_pdf(idstr){
	//don't want to use window.open(url,'_blank',''); as POST is not supported	
	var url = fetch_EMMA_drupal_path() + '/create_pdf.php';
	var dt = getClientLocalTime();
	$('body').append("<form id='pdf' target='_blank' action=" 
					 + url 
					 + " method='post'>"
					 + "<input type='text' name='idstr' value="
					 + idstr
					 + " />" 
				   	 + "<input type='text' name='datetime' value="
				   	 + escape(dt)
				   	 + " />" 
				   	 + "<input type='submit' />"
				   	 + "</form>"
				   	 );	
	$('form#pdf').hide();
	$('form#pdf').submit();
	$('form#pdf').remove();
}
function getClientLocalTime() {
    var d = new Date();
    var dd = add_leading_zero(d.getDate(), 2);
    var mm = add_leading_zero(parseInt(d.getMonth())+1, 2);
    var yy = d.getFullYear();
    var hr = add_leading_zero(d.getHours(), 2);   
    var mn = add_leading_zero(d.getMinutes(), 2);
    var ss = add_leading_zero(d.getSeconds(), 2);
    //console.log(yy + '-' + mm + '-' + dd + ' ' + hr + ':' + mn + ':' + ss);
    return yy + '-' + mm + '-' + dd + ' ' + hr + ':' + mn + ':' + ss;    
}
function add_leading_zero(num, digits){
	sNum = num + "";
	var offset = digits - sNum.length;
	var prefix = '';
	for (var i=0; i<offset; i++){
		prefix += '0';
	}	
	return prefix+num;
}
