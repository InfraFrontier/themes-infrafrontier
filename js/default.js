(function($){
	
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
	/* Main Slider */
	sliderFunction('.slidecontrol','slidecontainer','slide',638);
	// Logoslider
	sliderFunction('#logoslidecontrols','logoslidercontainer','logoslide',960);
}
function sliderFunction(controls,container,slide,width) {
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
function biomartHack(){
   console.log($('#main .title').text());

}
function customiseReadMoreText(){
	
	// hacking 'Read more' text for some pages
	var readMores = {
		'biomart': {
			'path': '/resources-and-services/access-emma-mouse-resources',
			'url': 'https://www.emmanet.org/biomart/martview/',
			'label': 'Search EMMA Biomart',
			'title': 'Advanced BioMart search'
		},		
		'submission': {
			'path': '/resources-and-services/deposit-mice-emma-repository',
			'url': 'http://dev.infrafrontier.eu/emma/publicSubmission/submissionForm.emma',
			'label': 'Submit mice to EMMA',
			'title': 'Submission form'
		}	
	};
	for ( var i in readMores ){
		if ( window.location.pathname == readMores[i].path ){  
		    $('div.view-content div h3').find('a').each(function(){          
		        if ( $(this).text() == readMores[i].title ){
		            //resources-and-services/access-emma-mouse-resources/advanced-biomart-search
		            var url = readMores[i].url;
		            $(this).attr('href', url);
		            $(this).parent().siblings('p.more').find('a').text(readMores[i].label).attr('href', url);                
		       } 
		    });
		}		
	}
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
	customiseReadMoreText();
	
	if ($('body').hasClass('front')) { autoHeightFront(); }

});

})(jQuery);
jQuery.noConflict();
