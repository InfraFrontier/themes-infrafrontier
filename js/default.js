(function($){

	if(typeof(window.EMMA) === 'undefined') {
            window.EMMA = {};          
    }

    EMMA.sliderTimer = null;
    EMMA.logoSliderTimer = null;

// Fix Placeholder
function fixPlaceholder() {
	jQuery.support.placeholder = false;
	var test = document.createElement('input');
	if('placeholder' in test) jQuery.support.placeholder = true;
	if(!$.support.placeholder) { 
		var active = document.activeElement;
		$('input,textarea').focus(function () {
			if ($(this).attr('placeholder') != '' && $(this).val() == $(this).attr('placeholder')) {
				$(this).val('').removeClass('hasPlaceholder');
			}
		}).blur(function () {
			if ($(this).attr('placeholder') != '' && ($(this).val() == '' || $(this).val() == $(this).attr('placeholder'))) {
				$(this).val($(this).attr('placeholder')).addClass('hasPlaceholder');
			}
		});
		$('input,textarea').blur();
		$(active).focus();
		$('form').submit(function () { $(this).find('.hasPlaceholder').each(function() { $(this).val(''); }); });
	}
}

// Slider
function initSlider() {
	// Main Slider
	sliderFunction('.slidecontrol','slidecontainer','slide',638,8000);
	// Logoslider
	sliderFunction('#logoslidecontrols','logoslidercontainer','logoslide',960,16000);
}
function sliderFunction(controls,container,slide,width,interval) {
 
	$(controls+' div').click(function() {   
		if ($(this).hasClass('prev')) {     
			$('#'+container+' .'+slide+':last').detach().prependTo('#'+container);
			$('#'+container).animate({marginLeft: '-='+width+'px'},0);
			$('#'+container).animate({marginLeft: '+='+width+'px'},1000);
		} else {      
			$('#'+container).animate({marginLeft: '-='+width+'px'},1000,function() {
				$('#'+container+' .'+slide+':first').detach().appendTo('#'+container);
				$('#'+container).animate({marginLeft: '+='+width+'px'},0);
			});
		}
	}); 

    // mouseover to stop sliding
    $('div#slider').mouseover(function(){                         
        clearInterval(EMMA.sliderTimer); // stops interval, ie, turn off sliding
    }).mouseout(function(){
        autoSlider('.slidecontrol','slidecontainer','slide',638, interval);
    });

    $('div#logoslider').mouseover(function(){                          
        clearInterval(EMMA.logoSliderTimer); // stops interval, ie, turn off sliding
    }).mouseout(function(){  
        autoSlider('#logoslidecontrols','logoslidercontainer','logoslide',960, interval);
    });  

    // auto slide in defined time interval for both slides and logos    
    if ( slide == 'slide' ){
        autoSlider('.slidecontrol','slidecontainer','slide',638, interval);
    }  
    else {
        autoSlider('#logoslidecontrols','logoslidercontainer','logoslide',960, interval);
    }
}
function autoSlider(controls,container,slide,width,interval){ 

    if (slide == 'slide' ){        
        if(EMMA.sliderTimer){
            clearInterval(EMMA.sliderTimer);
        }
        EMMA.sliderTimer = setInterval(function() {       
            $('#'+container).animate({marginLeft: '-='+width+'px'},1000,function() {
		        $('#'+container+' .'+slide+':first').detach().appendTo('#'+container);
		        $('#'+container).animate({marginLeft: '+='+width+'px'},0);
	        });     
        }, interval);        
    }
    else {
        if(EMMA.logoSliderTimer){
            clearInterval(EMMA.logoSliderTimer);
        }
        EMMA.logoSliderTimer = setInterval(function() {       
            $('#'+container).animate({marginLeft: '-='+width+'px'},1000,function() {
		        $('#'+container+' .'+slide+':first').detach().appendTo('#'+container);
		        $('#'+container).animate({marginLeft: '+='+width+'px'},0);
	        });     
        }, interval);                
    }        
}

// Sidenavi
function initSideNavi() {
	if (!$('html').hasClass('ie7')) {
		// Helper for sidenavi rollover
		$('#block-menu-block-2 .expanded > a').each(function() { $(this).prepend('<div class="helpclicker"></div>'); });	
		$('.helpclicker').live('click',function(e) {
			e.preventDefault();
			$(this).closest('li').toggleClass('active-trail');
		});
		$('.helpclicker').live('mouseover mouseout',function(e) { $(this).closest('li').toggleClass('over'); });
		// Helper for toggling content elements
		$('.togglehead .helper').live('mouseover mouseout',function(e) { $(this).closest('.togglehead').toggleClass('over'); });
	}
}

// various actions
function initActions() {
	// Toggle help box on frontpage
	$('#togglehelp').click(function() { $('#stooltip').toggle(); });
	// Animated Scrolling to top
	$("#toplink").click(function(e){ e.preventDefault(); $('html,body').animate({scrollTop:0}, 1500); });
	// Kill .error when focusing a input field
	$('.error input').live('focus',function() {
		$(this).closest('.form-item').removeClass('error');
	});
}

// Tabcontent
function initTabContent() {
	if ($('.field-collection-item-field-tab').length > 0) {
		var container = $('.field-collection-item-field-tab').eq(0);	
		$(container).before('<div id="tabs" class="tabs"><ul></ul><div class="clear"></div></div>');
		$('.field-collection-item-field-tab').each(function() {
			var title = $('.title',this).text();
			$('#tabs ul').append('<li><a href="#">'+title+'</a></li>');
			$('.title',this).remove();
		});
		$('#tabs a').live('click',function(e) {
			e.preventDefault();
			var index = $(this).index('#tabs a');
			$('#tabs a').removeClass('active');
			$(this).addClass('active');
			$('.field-collection-item-field-tab').hide();
			$('.field-collection-item-field-tab').eq(index).show();
		});
		$('#tabs a:first').trigger('click');
	}
}

// Togglebox
function initToggleContent() {
	$('.togglehead').live('click',function(e) {
		$(this).closest('.toggle').toggleClass('active');
		$(this).next('.togglecontent').toggle('fast');
	});
}

// Autoheight of boxes
function autoHeightFront() {
	var aboutHeight = $('#box-abouttext .boxcontent').innerHeight();
	var smallHeight = (aboutHeight - 105) / 2;
	$('#box-partnership .boxcontent').css('min-height',smallHeight+'px')
}

// Content margin, when no special Boxes inside the main node
function initContentMargin() {
	var headBox   = $('#block-system-main .node:first .head').length;
	var innerBoxes = $('#block-system-main .innerbox').length;
	var teaserBox = $('.view-subpages-teaser').length;
	if (teaserBox == 1 && innerBoxes == 0 && headBox == 0) {
		$('#block-system-main').addClass('margbottom');
	}
}

// span Tooltips
function initTooltips() {
	$('.tooltip').live('mouseenter',function(e) {
		var offset = $(this).offset();
		var spanwidth = $(this).width();
		$('body').append('<div id="tip">'+$(this).attr('data-tooltip')+'<div class="bubble"></div></div>');
		var w = $('#tip').width();
		var h = $('#tip').height();		
		$('#tip').css({top: (offset.top-h-30)+'px',left: (offset.left+(spanwidth/2)-(w/2))+'px'});
		$('#tip .bubble').css({marginLeft: w/2-5+'px'});
	});
	$('.tooltip').live('mouseleave',function() { $('#tip').remove(); });
}

// Parse Contents to improve user experience
function parseContent() {
	// Check if tables in the content have a thead
	$('.region-content table').each(function() {
		if ($('thead',this).length == 0) { $(this).addClass('nothead');	}
	});
	// Count toggleboxes
	$('.toggle:first').addClass('first');
	$('.toggle:last').addClass('last');
}

function customiseReadMoreTextAndLinks(){
	
	// open meetingtool page on a new tab
	if (window.location.pathname == '/' ){
		$('ul.menu li').find('a[href*="meetingtool"]').attr({'target':'__blank'});
	}	

	// hacking 'Read more' text for some pages
	var readMores = {
		'biomart': {
			'path': '/resources-and-services/access-emma-mouse-resources',
			'url': '/biomart/martview/',
			'label': 'Search EMMA Biomart',
			'title': 'Advanced BioMart search'
		},		
		'submission': {
			'path': '/resources-and-services/deposit-mice-emma-repository',
			'url': '/emma/publicSubmission/submissionForm.emma',
			'label': 'Submit mice to EMMA',
			'title': 'Submission form'
		},
        	'meetings': {
			'path': '/infrafrontier-research-infrastructure/public-relations',
			'url': '/meetingtool',
			'label': 'Register to a meeting',
			'title': 'Meeting registration'
		},	
	};
	for ( var i in readMores ){ 
		if ( window.location.pathname == readMores[i].path ){   
			$('ul.menu li').find('a[href*="biomart/martview"]').attr({'target':'__blank', 'href': '/biomar/martview'});		      
		    	$('div.view-content div h3').find('a').each(function(){          
		        	if ( $(this).text() == readMores[i].title ){
				    //resources-and-services/access-emma-mouse-resources/advanced-biomart-search
				    var url = readMores[i].url;
				    $(this).attr('href', url);
				    $(this).parent().siblings('p.more').find('a').text(readMores[i].label).attr({'href': url, 'target':'__blank'}); 		               
		       		} 
		    	});

		   	$('ul.menu li').find('a[href*="meetingtool"]').attr({'target':'__blank'}); 
		}		
	}	
	
}
function hide_imprint_login_register(){
	$('div#tn').find('.hide_this').hide();
}
function do_NKI_cells_page() {
	if (window.location.pathname == '/resources-and-services/access-emma-mouse-resources/nki-gemm-esc-archive' ){
		// load NKI cells dataTable		
		$('div.content').append("<div id='nki'></div>");
		var url = fetch_url() + "?nki=true";
	
		$.get(url, function(data){  
			var data = eval('(' + data + ')');
			
			$('div.content div.toggle.first .togglecontent').append(data.subTable);
			//$('div#nki').html(data.fullTable);   
			
       		$('span.nkiShort').click(function(){
				
				if ( $(this).hasClass('display') ){
					$(this).removeClass('display');
					$(this).siblings('.nkiLong').addClass('display');
				}
			});
			$('span.nkiLong').click(function(){
				
				if ( $(this).hasClass('display') ){
					$(this).removeClass('display');
					$(this).siblings('.nkiShort').addClass('display');
				}
			});
		});
	}
}
function redirectLoggedInUserByRoles(){
	
	var oUser = Drupal.settings.infrafrontier.drupaluser;
	
	if (window.location.pathname == '/users/' + oUser.name){		
		var aRoles = ['infrafrontier', 'Infrafrontier GmbH', 'Infrafrontier_I3', 'EMMA', 'InfraCoMP', 'emma_infra13', 'rome_meeting'];			
		var oUser_roles = oUser.roles;	

		// check user roles for page redirect
		for (var r in oUser_roles ){		
			if ( inArray(oUser_roles[r], aRoles) ){				
				window.location.href = "/internal";		
				break;
			}
		}	
	}
}
function inArray(item, list) {
    var length = list.length;
    for(var i = 0; i < length; i++) {
        if(list[i] == item){
			return true;
		}
    }
    return false;
}

$(document).ready(function() {	
	
	initTabContent();
	initToggleContent();
	initSideNavi();
	initContentMargin();
	initSlider();
	initActions();
	initTooltips();
	parseContent();
	fixPlaceholder();
	customiseReadMoreTextAndLinks();
	hide_imprint_login_register();
	do_NKI_cells_page();	
	redirectLoggedInUserByRoles();

	if ($('body').hasClass('front')) { autoHeightFront(); }

});

})(window.jQuery);
window.jQuery.noConflict();
