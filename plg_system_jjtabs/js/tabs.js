/* ----------------
ResponsiveTabs.js
Author: Pete Love | www.petelove.com
Version: 1.9
------------------- */
/* ----------------
This file has been substantially altered by JoomJunk.
Any broken/faulty code is the responsibility of JoomJunk.
------------------- */

var RESPONSIVEUI = {};

(function($) {
	RESPONSIVEUI.responsiveTabs = function (options) {
		var $tabSets = $('.responsive-tabs');
		var cookies = options.useStorage;
		var click = options.useClick;
		
		if(click=="true") {
			var functiontype = 'click';
		}
		else {
			var functiontype = 'hover';
		}
		
		if(cookies=="true") {
			if ($.cookie('tabs')) {
				var startoffset = parseInt($.cookie('tabs'));
			} else {
				if (options.display) {
					var startoffset = options.display;
				} else {
					var startoffset = 0;
				}
			}
		} else {
			if (options.display) {
				var startoffset = options.display;
			} else {
				var startoffset = 0;
			}
		}
		
		// Add on 1 to offset to account for the initial div for
		// Joomla construction purposes
		startoffset = startoffset + 1;

		if (!$tabSets.hasClass('responsive-tabs--enabled')) {	// if we haven't already called this function and enabled tabs
			$tabSets.addClass('responsive-tabs--enabled'); 

			//loop through all sets of tabs on the page
			var tablistcount = 1;

			$tabSets.each(function() {

				var $tabs = $(this);

				// add tab heading and tab panel classes
				$tabs.children('h1,h2,h3,h4,h5,h6').addClass('responsive-tabs__heading');
				$tabs.children('div').addClass('responsive-tabs__panel');

				// determine if markup already identifies the active tab panel for this set of tabs
				// if not then set first heading and tab to be the active one
				var $activePanel = $tabs.find('.responsive-tabs__panel--active');
				if(!$activePanel.length) {
					$activePanel = $tabs.find('.responsive-tabs__panel').eq(startoffset).addClass('responsive-tabs__panel--active');
				}
					
				$tabs.find('.responsive-tabs__panel').not('.responsive-tabs__panel--active').hide().attr('aria-hidden','true'); //hide all except active panel
				$activePanel.attr('aria-hidden', 'false');
				
				/* make active tab panel hidden for mobile */
				$activePanel.addClass('responsive-tabs__panel--closed-accordion-only');

				// wrap tabs in container - to be dynamically resized to help prevent page jump
				var $tabsWrapper = $('<div/>', {'class': 'responsive-tabs-wrapper' });
				$tabs.wrap($tabsWrapper);

				var highestHeight = 0;

				// determine height of tallest tab panel. Used later to prevent page jump when tabs are clicked
				$tabs.find('.responsive-tabs__panel').each(function() {
					var tabHeight = $(this).height();
					if (tabHeight > highestHeight) {
						highestHeight = tabHeight;
					}
				});

				//create the tab list
				var $tabList = $('<ul/>', { 'class': 'responsive-tabs__list', 'role': 'tablist' });

				//loop through each heading in set
				var tabcount = 1;
				$tabs.find('.responsive-tabs__heading').each(function() {

					var $tabHeading = $(this);
					var $tabPanel = $(this).next();

					$tabHeading.attr('tabindex', 0);

					// CREATE TAB ITEMS (VISIBLE ON DESKTOP)
					// create tab list item from heading
					// associate tab list item with tab panel
					var options = {};
					options["keydown"] = function (objEvent) {
						if (objEvent.keyCode === 13) { // if user presses 'enter'
							$tabListItem.click();
						}
					}		
					options["class"] = 'responsive-tabs__list__item ' + $tabPanel.attr('data-tabcolour');
					options["id"] = $($tabPanel).attr('id');
					options["aria-controls"] = 'tablist' + tablistcount +'-panel' + tabcount;
					options["data-tabref"] = 'tablist' + tablistcount + '-tab' + tabcount;
					options["role"] = 'tab';
					options["text"] = $tabHeading.text();
					options["tabindex"] = 0;
					options[functiontype] = function(){
							$tabsWrapper.css('height', highestHeight);
							$tabs.find('.responsive-tabs__panel--closed-accordion-only').removeClass('responsive-tabs__panel--closed-accordion-only');
							$tabs.find('.responsive-tabs__panel--active').toggle().removeClass('responsive-tabs__panel--active').attr('aria-hidden','true').prev().removeClass('responsive-tabs__heading--active');
							$tabPanel.toggle().addClass('responsive-tabs__panel--active').attr('aria-hidden','false');
							$tabHeading.addClass('responsive-tabs__heading--active');
							$olditem = $tabList.find('.responsive-tabs__list__item--active');
							$olditem.removeClass('responsive-tabs__list__item--active').removeClass($olditem.attr('data-tabcolour')+'--active');
							$tabListItem.addClass('responsive-tabs__list__item--active').addClass($tabListItem.attr('data-tabcolour')+'--active');
							if(cookies=="true") {
								var index = (($('.responsive-tabs__panel--active').index()-3)/2);
								if($.cookie('tabs')) {
									$.removeCookie('tabs');
									$.cookie('tabs', index, { expires: 7 });
								} else {
									$.cookie('tabs', index, { expires: 7 });
								}
							}
							$tabsWrapper.css('height', 'auto');
					}
					var $tabListItem = $('<li/>', options);
					
					//associate tab panel with tab list item
					$tabPanel.attr({
						'role': 'tabpanel',
						'aria-labelledby': $tabListItem.attr('data-tabref'),
						'data-panelref': 'tablist' + tablistcount + '-panel' + tabcount
					});

					// if this is the active panel then make it the active tab item
					if($tabPanel.hasClass('responsive-tabs__panel--active')) {
						$tabListItem.addClass('responsive-tabs__list__item--active');
						$tabListItem.addClass($tabPanel.attr('data-tabcolour') + '--active');
					}

					// Remove tab colour param now its in the li element
					$tabPanel.removeAttr("data-tabcolour");

					// add tab item
					$tabList.append($tabListItem);


					// TAB HEADINGS (VISIBLE ON MOBILE)
					// if user presses 'enter' on tab heading trigger the click event
					$tabHeading.keydown(function(objEvent) {
						if (objEvent.keyCode === 13) {
							$tabHeading.click();
						}
					});

					//toggle tab panel if click heading (on mobile)
					$tabHeading.on(functiontype, function(){

						// remove any hidden mobile class
						$tabs.find('.responsive-tabs__panel--closed-accordion-only').removeClass('responsive-tabs__panel--closed-accordion-only');

						// if this isn't currently active
						if (!$tabHeading.hasClass('responsive-tabs__heading--active')){

							var oldActivePos;
							// if there is an active heading, get its position
							if($('.responsive-tabs__heading--active').length) {
								oldActivePos = $('.responsive-tabs__heading--active').offset().top;
							}

							// close currently active panel and remove active state from any hidden heading
							$tabs.find('.responsive-tabs__panel--active').slideToggle().removeClass('responsive-tabs__panel--active').prev().removeClass('responsive-tabs__heading--active');

							//close all tabs
							$tabs.find('.responsive-tabs__panel').hide().attr('aria-hidden','true');

							//open this panel
							$tabPanel.slideToggle().addClass('responsive-tabs__panel--active').attr('aria-hidden','false');

							// make this heading active
							$tabHeading.addClass('responsive-tabs__heading--active');

							var $currentActive = $tabs.find('.responsive-tabs__list__item--active');

							//set the active tab list item (for desktop)
							$currentActive.removeClass('responsive-tabs__list__item--active').removeClass($currentActive.attr('data-tabcolour')+'--active');
							var panelId = $tabPanel.attr('data-panelref');
							var tabId = panelId.replace('panel','tab');
							$tabList.find('[data-tabref="' + tabId + '"]').addClass('responsive-tabs__list__item--active').addClass($tabList.find('[data-tabref="' + tabId + '"]').attr('data-tabcolour')+'--active');

							//scroll to active heading only if it is below previous one
							var tabsPos = $('.responsive-tabs').offset().top;
							var newActivePos = ($('.responsive-tabs__heading--active').offset().top) - 15;
							if(oldActivePos < newActivePos) {
								$('html, body').animate({ scrollTop: tabsPos }, 0).animate({ scrollTop: newActivePos }, 400);
							}

						}

						// if this tab panel is already active
						else {

							// hide panel but give it special responsive-tabs__panel--closed-accordion-only class so that it can be visible at desktop size
							$tabPanel.removeClass('responsive-tabs__panel--active').slideToggle(function () { $(this).addClass('responsive-tabs__panel--closed-accordion-only'); });

							//remove active heading class
							$tabHeading.removeClass('responsive-tabs__heading--active');

							//don't alter classes on tabs as we want it active if put back to desktop size
						}

					});

					tabcount ++;

				});

				// add finished tab list to its container
				$tabs.prepend($tabList);

				// next set of tabs on page
				tablistcount ++;
			});
		}
	};
})(jQuery);
