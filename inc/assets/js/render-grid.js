(function($){
	ColorwayRender = {

		_ref			: null,

		/**
		 * _api_params = {
		 * 		'search'                  : '',
		 * 		'per_page'                : '',
		 * 		'site-category'     : '',
		 * 		'site-page-builder' : '',
		 * 		'page'                    : '',
		 *   };
		 *
		 * E.g. per_page=<page-id>&site-category=<category-ids>&site-page-builder=<page-builder-ids>&page=<page>
		 */
		//_api_params		: {},
                _api_params : {},
		_breakpoint		: 768,
	
		init: function()
		{
			this._resetPagedCount();
			this._bind();
			this._showFilters();
                        this._showSites();
		},

		/**
		 * Binds events for the Colorway Sites.
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('colorway-sites-api-request-error'   , ColorwayRender._addSuggestionBox );
			$( document ).on('colorway-sites-api-request-fail'    , ColorwayRender._addSuggestionBox );
			$( document ).on('colorway-api-post-loaded-on-scroll' , ColorwayRender._reinitGridScrolled );
			$( document ).on('colorway-api-post-loaded'           , ColorwayRender._reinitGrid );
			$( document ).on('colorway-api-category-loaded'       , ColorwayRender._addFilters );
			$( document ).on('colorway-api-all-category-loaded'   , ColorwayRender._loadFirstGrid );
			
			// Event's for API request.
			$( document ).on('click'                           , '.filter-links a', ColorwayRender._filterClick );
			$( document ).on('keyup input'                     , '#wp-filter-search-input', ColorwayRender._search );
			$( document ).on('scroll'                          , ColorwayRender._scroll );

		},

		/**
		 * On Filter Clicked
		 *
		 * Prepare Before API Request:
		 * - Empty search input field to avoid search term on filter click.
		 * - Remove Inline Height
		 * - Added 'hide-me' class to hide the 'No more sites!' string.
		 * - Added 'loading-content' for body.
		 * - Show spinner.
		 */
		_filterClick: function( event ) {                    
			event.preventDefault();
                       
			if( $( this ).parents('.site-category').length && ! $('body').hasClass('page-builder-selected') ) {
				return;
			}

			$(this).parents('.filter-links').find('a').removeClass('current');
			$(this).addClass('current');

			// Prepare Before Search.
			$('.no-more-demos').addClass('hide-me');
			$('.colorway-sites-suggestions').remove();

			// Empty the search input only click on category filter not on page builder filter.
			if( $(this).parents('.filter-links').hasClass('site-category') ) {
				$('#wp-filter-search-input').val('');
			}
			$('#colorway-sites').hide().css('height', '');

			$('body').addClass('loading-content');
			$('#colorway-sites-admin').find('.spinner').removeClass('hide-me');

	        // Show sites.
			ColorwayRender._showSites();
		},

		/**
		 * Search Site.
		 *
		 * Prepare Before API Request:
		 * - Remove Inline Height
		 * - Added 'hide-me' class to hide the 'No more sites!' string.
		 * - Added 'loading-content' for body.
		 * - Show spinner.
		 */
		_search: function() {

			if( ! $('body').hasClass('page-builder-selected') ) {
				return;
			}

			$this = jQuery('#wp-filter-search-input').val();

			// Prepare Before Search.
			$('#colorway-sites').hide().css('height', '');			
			$('.no-more-demos').addClass('hide-me');
			$('.colorway-sites-suggestions').remove();

			$('body').addClass('loading-content');
			$('#colorway-sites-admin').find('.spinner').removeClass('hide-me');

			window.clearTimeout(ColorwayRender._ref);
			ColorwayRender._ref = window.setTimeout(function () {
				ColorwayRender._ref = null;

				ColorwayRender._resetPagedCount();
				jQuery('body').addClass('loading-content');
				jQuery('body').attr('data-colorway-demo-search', $this);

				ColorwayRender._showSites();

			}, 500);

		},

		/**
		 * On Scroll
		 */
		_scroll: function(event) {

			if( ! $('body').hasClass('page-builder-selected') ) {
				return;
			}

			if( ! $('body').hasClass('listed-all-sites') ) {

				var scrollDistance = jQuery(window).scrollTop();

				var themesBottom = Math.abs(jQuery(window).height() - jQuery('#colorway-sites').offset().top - jQuery('#colorway-sites').height());
				themesBottom = themesBottom - 100;

				ajaxLoading = jQuery('body').data('scrolling');

				if (scrollDistance > themesBottom && ajaxLoading == false) {
					ColorwayRender._updatedPagedCount();

					if( ! $('#colorway-sites .no-themes').length ) {
						$('#colorway-sites-admin').find('.spinner').addClass('is-active');
					}

					jQuery('body').data('scrolling', true);
					
					/**
					 * @see _reinitGridScrolled() which called in trigger 'colorway-api-post-loaded-on-scroll'
					 */
					ColorwayRender._showSites( false, 'colorway-api-post-loaded-on-scroll' );
				}
			}
		},

		_apiAddParam_status: function() {
			if( colorwayRenderGrid.sites && colorwayRenderGrid.sites.status ) {
				ColorwayRender._api_params['status'] = colorwayRenderGrid.sites.status;
			}
		},

		// Add 'search'
		_apiAddParam_search: function() {
			var search_val = jQuery('#wp-filter-search-input').val() || '';
			if( '' !== search_val ) {
				ColorwayRender._api_params['search'] = search_val;
			}
		},

		_apiAddParam_per_page: function() {
			// Add 'per_page'
			var per_page_val = 100;
			if( colorwayRenderGrid.sites && colorwayRenderGrid.sites["par-page"] ) {
				per_page_val = parseInt( colorwayRenderGrid.sites["par-page"] );
			}
			ColorwayRender._api_params['per_page'] = per_page_val;
		},

		_apiAddParam_colorway_site_category: function() {
			// Add 'site-category'
			var selected_category_id = jQuery('.filter-links.site-category').find('.current').data('group') || '';
			if( '' !== selected_category_id && 'all' !== selected_category_id ) {
				ColorwayRender._api_params['site-category'] =  selected_category_id;
			} else if( 'site-category' in colorwayRenderGrid.settings ) {

				if( $.inArray('all', colorwayRenderGrid.settings['site-category']) !== -1 ) {
					var storedCategories = colorwayRenderGrid.settings['site-category'];
					storedCategories = jQuery.grep(storedCategories, function(value) {
						return value != 'all';
					});
					ColorwayRender._api_params['site-category'] =  storedCategories.join(',');
				}
			}
		},

		_apiAddParam_colorway_site_page_builder: function() {
			// Add 'site-page-builder'
			var selected_page_builder_id = jQuery('.filter-links.site-page-builder').find('.current').data('group') || '';
			if( '' !== selected_page_builder_id && 'all' !== selected_page_builder_id ) {
				ColorwayRender._api_params['site-page-builder'] =  selected_page_builder_id;
			} else if( 'site-page-builder' in colorwayRenderGrid.settings ) {
				if( $.inArray('all', colorwayRenderGrid.settings['site-page-builder']) !== -1 ) {
					var storedBuilders = colorwayRenderGrid.settings['site-page-builder'];
					storedBuilders = jQuery.grep(storedBuilders, function(value) {
						return value != 'all';
					});
					ColorwayRender._api_params['site-page-builder'] = storedBuilders.join(',');
				}
			}
		},

		_apiAddParam_page: function() {
			// Add 'page'
			var page_val = parseInt(jQuery('body').attr('data-colorway-demo-paged')) || 1;
			ColorwayRender._api_params['page'] = page_val;
		},

		_apiAddParam_purchase_key: function() {
			if( colorwayRenderGrid.sites && colorwayRenderGrid.sites.purchase_key ) {
				ColorwayRender._api_params['purchase_key'] = colorwayRenderGrid.sites.purchase_key;
			}
		},

		_apiAddParam_site_url: function() {
			if( colorwayRenderGrid.sites && colorwayRenderGrid.sites.site_url ) {
				ColorwayRender._api_params['site_url'] = colorwayRenderGrid.sites.site_url;
			}
		},

		/**
		 * Show Sites
		 * 
		 * 	Params E.g. per_page=<page-id>&site-category=<category-ids>&site-page-builder=<page-builder-ids>&page=<page>
		 *
		 * @param  {Boolean} resetPagedCount Reset Paged Count.
		 * @param  {String}  trigger         Filtered Trigger.
		 */
		_showSites: function( resetPagedCount, trigger ) {                   
                  	if( undefined === resetPagedCount ) {
				resetPagedCount = true
			}
			if( undefined === trigger ) {
				trigger = 'colorway-api-post-loaded';
			}

			if( resetPagedCount ) {
				ColorwayRender._resetPagedCount();
			}
                      
			// Add Params for API request.
			ColorwayRender._api_params = {};

			ColorwayRender._apiAddParam_status();
			ColorwayRender._apiAddParam_search();
			ColorwayRender._apiAddParam_per_page();
			ColorwayRender._apiAddParam_colorway_site_category();
			ColorwayRender._apiAddParam_colorway_site_page_builder();
			ColorwayRender._apiAddParam_page();
			ColorwayRender._apiAddParam_site_url();
			ColorwayRender._apiAddParam_purchase_key();

			// API Request.
			var api_post = {
				slug: 'market?_embed&site-page-builder=25679&' + decodeURIComponent( $.param( ColorwayRender._api_params ) ),
				trigger: trigger,
			};

			ColorwaySitesAPI._api_request( api_post );

		},

		/**
		 * Get Category Params
		 * 
		 * @param  {string} category_slug Category Slug.
		 * @return {mixed}               Add `include=<category-ids>` in API request.
		 */
		_getCategoryParams: function( category_slug ) {

			// Has category?
			if( category_slug in colorwayRenderGrid.settings ) {

				var storedBuilders = colorwayRenderGrid.settings[ category_slug ];

				// Remove `all` from stored list?
				storedBuilders = jQuery.grep(storedBuilders, function(value) {
					return value != 'all';
				});

				return '?include='+storedBuilders.join(',');
			}

			return '';
		},

		/**
		 * Get All Select Status
		 * 
		 * @param  {string} category_slug Category Slug.
		 * @return {boolean}              Return true/false.
		 */
		_getCategoryAllSelectStatus: function( category_slug ) {	

			// Has category?
			if( category_slug in colorwayRenderGrid.settings ) {

				// Has `all` in stored list?
				if( $.inArray('all', colorwayRenderGrid.settings[ category_slug ]) === -1 ) {
					return false;
				}
			}

			return true;
		},

		/**
		 * Show Filters
		 */
		_showFilters: function() {

			/**
			 * Categories
			 */
			var category_slug = 'site-page-builder';
			var category = {
				slug          : category_slug + ColorwayRender._getCategoryParams( category_slug ),
				id            : category_slug,
				class         : category_slug,
				trigger       : 'colorway-api-category-loaded',
				wrapper_class : 'filter-links',
				show_all      : false,
			};

			ColorwaySitesAPI._api_request( category );

			/**
			 * Page Builder
			 */
			var category_slug = 'site-category';
			var category = {
				slug          : category_slug + ColorwayRender._getCategoryParams( category_slug ),
				id            : category_slug,
				class         : category_slug,
				trigger       : 'colorway-api-all-category-loaded',
				wrapper_class : 'filter-links',
				show_all      : ColorwayRender._getCategoryAllSelectStatus( category_slug ),
			};

			ColorwaySitesAPI._api_request( category );
		},

		/**
		 * Load First Grid.
		 *
		 * This is triggered after all category loaded.
		 * 
		 * @param  {object} event Event Object.
		 */
		_loadFirstGrid: function( event, data ) {
			ColorwayRender._addFilters( event, data );
			setTimeout(function() {
				$('body').removeClass('loading-content');
			}, 100);
		},

		/**
		 * Append filters.
		 * 
		 * @param  {object} event Object.
		 * @param  {object} data  API response data.
		 */
		_addFilters: function( event, data ) {
			event.preventDefault();

			if( jQuery('#' + data.args.id).length ) {
				var template = wp.template('colorway-site-filters');
				jQuery('#' + data.args.id).html(template( data ));
			}

		},

		/**
		 * Append sites on scroll.
		 * 
		 * @param  {object} event Object.
		 * @param  {object} data  API response data.
		 */
		_reinitGridScrolled: function( event, data ) {

			var template = wp.template('colorway-sites-list');

			if( data.items.length > 0 ) {

				$('body').removeClass( 'loading-content' );
				$('.filter-count .count').text( data.items_count );

				setTimeout(function() {
					jQuery('#colorway-sites').append(template( data ));

					ColorwayRender._imagesLoaded();
				}, 800);
			} else {

				$('body').addClass('listed-all-sites');

				// $('#colorway-sites-admin').find('.spinner').removeClass('is-active');
			}

		},

		/**
		 * Update  Colorway sites list.
		 * 
		 * @param  {object} event Object.
		 * @param  {object} data  API response data.
		 */
		_reinitGrid: function( event, data ) {                   
			var template = wp.template('colorway-sites-list');
                        
			$('body').addClass( 'page-builder-selected' );
			$('body').removeClass( 'loading-content' );
			$('.filter-count .count').text( data.items_count );

			jQuery('body').attr('data-colorway-demo-last-request', data.items_count);

			jQuery('#colorway-sites').show().html(template( data ));

			ColorwayRender._imagesLoaded();

			$('#colorway-sites-admin').find('.spinner').removeClass('is-active');

			if( data.items_count <= 0 ) {
				$('#colorway-sites-admin').find('.spinner').removeClass('is-active');
				$('.no-more-demos').addClass('hide-me');
				$('.colorway-sites-suggestions').remove();

			} else {
				$('body').removeClass('listed-all-sites');
			}


		},

		/**
		 * Check image loaded with function `imagesLoaded()`
		 */
		_imagesLoaded: function() {

			var self = jQuery('#sites-filter.execute-only-one-time a');
			
			$('.colorway-sites-grid').imagesLoaded()
			.always( function( instance ) {
				if( jQuery( window ).outerWidth() > ColorwayRender._breakpoint ) {
					// $('#colorway-sites').masonry('reload');
				}

				$('#colorway-sites-admin').find('.spinner').removeClass('is-active');
			})
			.progress( function( instance, image ) {
				var result = image.isLoaded ? 'loaded' : 'broken';
			});

		},

		/**
		 * Add Suggestion Box
		 */
		_addSuggestionBox: function() {
			$('#colorway-sites-admin').find('.spinner').removeClass('is-active').addClass('hide-me');

			$('#colorway-sites-admin').find('.no-more-demos').removeClass('hide-me');
			var template = wp.template('colorway-sites-suggestions');
			if( ! $( '.colorway-sites-suggestions').length ) {
				$('#colorway-sites').append( template );
			}
		},

		/**
		 * Update Page Count.
		 */
		_updatedPagedCount: function() {
			paged = parseInt(jQuery('body').attr('data-colorway-demo-paged'));
			jQuery('body').attr('data-colorway-demo-paged', paged + 1);
			window.setTimeout(function () {
				jQuery('body').data('scrolling', false);
			}, 800);
		},

		/**
		 * Reset Page Count.
		 */
		_resetPagedCount: function() {

			jQuery('body').attr('data-colorway-demo-last-request', '1');
			jQuery('body').attr('data-colorway-demo-paged', '1');
			jQuery('body').attr('data-colorway-demo-search', '');
			jQuery('body').attr('data-scrolling', false);

		}

	};

	/**
	 * Initialize ColorwayRender
	 */
	$(function(){
		ColorwayRender.init();
	});

})(jQuery);