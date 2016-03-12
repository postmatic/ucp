function ucp_developer_lightbox() {
	(function($){
		function make_colorbox( href, transition ) {
			$.colorbox({
				inline: true,
				href: href,
				title: ucp_developer_i18n.lightbox_title,
				innerWidth: '90%',
				maxHeight: '100%',
				transition: transition
			});
		}

		make_colorbox( '#ucp-developer-setup-dialog-step-1', 'none' );

		$('#ucp-developer-setup-dialog-step-1-form').submit( function(e) {
			var form = this;

			$('#ucp-developer-setup-dialog-step-1-submit').val( ucp_developer_i18n.saving );

			if ( 'yes' != ucp_developer_i18n.go_to_step_2 )
				return;

			e.preventDefault();

			$.post( ajaxurl, $(form).serialize() )
				.success( function( result ) {
					// If there was an error with the AJAX save, then do a normal POST
					if ( '-1' == result ) {
						location.href = 'options-general.php?page=' + ucp_developer_i18n.settings_slug + '&ucpdev_errorsaving=1';
						return;
					}

					// AJAX says no step 2 needed, so head to the settings page
					if ( 'redirect' == result ) {
						location.href = 'options-general.php?page=' + ucp_developer_i18n.settings_slug + '&updated=1';
						return;
					}

					// Display the AJAX reponse
					$('#ucp-developer-setup-dialog-step-2').html( result );
					make_colorbox( '#ucp-developer-setup-dialog-step-2' );
				})
			;
		});
	})(jQuery);
}

function ucp_developer_bind_events() {
	(function($){
		$('.ucp-developer-button-install').click( function() {
			var button = this;

			$(button).html( ucp_developer_i18n.installing );

			$.post( ajaxurl, {
				'action': 'ucp_developer_install_plugin',
				'_ajax_nonce': $(button).attr('data-nonce'),
				'plugin_slug': $(button).attr('data-pluginslug')
			} )
				.success( function( result ) {
					if ( '1' === result ) {
						$( button )
							.html( ucp_developer_i18n.installed )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$(button).unbind('click').prop('disabled', true);
					} else {
						$( button )
							.html( ucp_developer_i18n.ERROR )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$( button ).after( '<span class="ucp-developer-action-result error">' + result + '</span>' );
					}
				})
				.error( function( response ) {
					$( button )
						.html( ucp_developer_i18n.ERROR )
						.nextAll( '.ucp-developer-action-result' )
						.remove();

					$( button ).after( '<span class="ucp-developer-action-result error">' + response.statusText + ': ' + response.responseText + '</span>' );
				})
			;
		});

		$('.ucp-developer-button-activate').click( function() {
			var button = this;

			$(button).html( ucp_developer_i18n.activating );

			$.post( ajaxurl, {
				'action': 'ucp_developer_activate_plugin',
				'_ajax_nonce': $(button).attr('data-nonce'),
				'path': $(button).attr('data-path')
			} )
				.success( function( result ) {
					if ( '1' === result ) {
						$( button )
							.html( ucp_developer_i18n.activated )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$(button).unbind('click').prop('disabled', true);
					} else {
						$( button )
							.html( ucp_developer_i18n.ERROR )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$( button ).after( '<span class="ucp-developer-action-result error">' + result + '</span>' );
					}
				})
				.error( function( response ) {
					$( button )
						.html( ucp_developer_i18n.ERROR )
						.nextAll( '.ucp-developer-action-result' )
						.remove();

					$( button ).after( '<span class="ucp-developer-action-result error">' + response.statusText + ': ' + response.responseText + '</span>' );
				})
			;
		});

		$( '.ucp-developer-button-close' ).on( 'click', function() {
			$.colorbox.close();
		});
	})(jQuery);
}

function ucp_developer_bind_settings_events() {
	(function($){
		$('.ucp-developer-button-install').click( function() {
			var button = this;

			$(button).html( ucp_developer_i18n.installing );

			$.post( ajaxurl, {
				'action': 'ucp_developer_install_plugin',
				'_ajax_nonce': $(button).attr('data-nonce'),
				'plugin_slug': $(button).attr('data-pluginslug')
			} )
				.success( function( result ) {
					if ( '1' == result ) {
						$( button )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$( button ).replaceWith( "<span class='ucp-developer-active'>" + ucp_developer_i18n.ACTIVE + "</span>" );
					} else {
						$( button )
							.html( ucp_developer_i18n.ERROR )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$( button ).after( '<span class="ucp-developer-action-result error">' + result + '</span>' );
					}
				})
				.error( function( response ) {
					$( button )
						.html( ucp_developer_i18n.ERROR )
						.nextAll( '.ucp-developer-action-result' )
						.remove();

					$( button ).after( '<span class="ucp-developer-action-result error">' + response.statusText + ': ' + response.responseText + '</span>' );
				})
			;

			return false;
		});

		$('.ucp-developer-button-activate').click( function() {
			var button = this;

			$(button).html( ucp_developer_i18n.activating );

			$.post( ajaxurl, {
				'action': 'ucp_developer_activate_plugin',
				'_ajax_nonce': $(button).attr('data-nonce'),
				'path': $(button).attr('data-path')
			} )
				.success( function( result ) {
					if ( '1' == result ) {
						$( button )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$( button ).replaceWith( "<span class='ucp-developer-active'>" + ucp_developer_i18n.ACTIVE + "</span>" );
					} else {
						$( button )
							.html( ucp_developer_i18n.ERROR )
							.nextAll( '.ucp-developer-action-result' )
							.remove();

						$( button ).after( '<span class="ucp-developer-action-result error">' + result + '</span>' );
					}
				})
				.error( function( response ) {
					$( button )
						.html( ucp_developer_i18n.ERROR )
						.nextAll( '.ucp-developer-action-result' )
						.remove();

					$( button ).after( '<span class="ucp-developer-action-result error">' + response.statusText + ': ' + response.responseText + '</span>' );
					
				})
			;

			return false;
		});
	})(jQuery);
}