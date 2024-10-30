
(function($){

	ColorwaySitesAPI = {

		_api_url  : ColorwaySitesAPI.ApiURL,

		/**
		 * API Request
		 */
		_api_request: function( args ) {

			// Set API Request Data.
			var data = {
				url: ColorwaySitesAPI._api_url + args.slug,
				cache: false,
			};

			if( colorwayRenderGrid.headers ) {
				data.headers = colorwayRenderGrid.headers;
			}

			$.ajax( data )
			.done(function( items, status, XHR ) {    
                            
				if( 'success' === status && XHR.getResponseHeader('x-wp-total') ) {
                                    
					var data = {
						args 		: args,
						items 		: items,
						items_count	: XHR.getResponseHeader('x-wp-total') || 0,
					};
                                       
					if( 'undefined' !== args.trigger && '' !== args.trigger ) {
						$(document).trigger( args.trigger, [data] );
					}

				} else {                                   
					$(document).trigger( 'colorway-sites-api-request-error' );
				}

			})
			.fail(function( jqXHR, textStatus ) {

				$(document).trigger( 'colorway-sites-api-request-fail' );

			})
			.always(function() {                            
				$(document).trigger( 'colorway-sites-api-request-always' );

			});

		},

	};

})(jQuery);