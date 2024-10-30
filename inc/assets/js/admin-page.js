/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since 1.0.0
 */
var ColorwaySitesAjaxQueue = (function () {

    var requests = [];

    return {
        /**
         * Add AJAX request
         *
         * @since 1.0.0
         */
        add: function (opt) {
            requests.push(opt);
        },
        /**
         * Remove AJAX request
         *
         * @since 1.0.0
         */
        remove: function (opt) {
            if (jQuery.inArray(opt, requests) > -1)
                requests.splice($.inArray(opt, requests), 1);
        },
        /**
         * Run / Process AJAX request
         *
         * @since 1.0.0
         */
        run: function () {
            var self = this,
                    oriSuc;

            if (requests.length) {
                oriSuc = requests[0].complete;

                requests[0].complete = function () {
                    if (typeof (oriSuc) === 'function')
                        oriSuc();
                    requests.shift();
                    self.run.apply(self, []);
                };

                jQuery.ajax(requests[0]);

            } else {

                self.tid = setTimeout(function () {
                    self.run.apply(self, []);
                }, 1000);
            }
        },
        /**
         * Stop AJAX request
         *
         * @since 1.0.0
         */
        stop: function () {

            requests = [];
            clearTimeout(this.tid);
        }
    };

}());

(function ($) {

    var ColorwaySSEImport = {
        complete: {
            posts: 0,
            media: 0,
            users: 0,
            comments: 0,
            terms: 0,
        },
        updateDelta: function (type, delta) {
            this.complete[ type ] += delta;

            var self = this;
            requestAnimationFrame(function () {
                self.render();
            });
        },
        updateProgress: function (type, complete, total) {
            var text = complete + '/' + total;

            if ('undefined' !== type && 'undefined' !== text) {
                total = parseInt(total, 10);
                if (0 === total || isNaN(total)) {
                    total = 1;
                }
                var percent = parseInt(complete, 10) / total;
                var progress = Math.round(percent * 100) + '%';
                var progress_bar = percent * 100;
            }
        },
        render: function () {
            var types = Object.keys(this.complete);
            var complete = 0;
            var total = 0;

            for (var i = types.length - 1; i >= 0; i--) {
                var type = types[i];
                this.updateProgress(type, this.complete[ type ], this.data.count[ type ]);

                complete += this.complete[ type ];
                total += this.data.count[ type ];
            }
            
             var percent = parseInt(complete, 10) / total;
                var progress = Math.round(percent * 100) + '%';
                var progress_bar = Math.round(percent * 100);
                $('#progress_section').css('display', 'block'); 
                $('#progress_section #prog_bar').text(progress_bar + '%');
                $('#progress_section #prog_bar').css('width', '' + progress_bar + '%'); 
                
                
            this.updateProgress('total', complete, total);
        }
    };

    ColorwaySitesAdmin = {
        log_file: '',
        customizer_data: '',
        wxr_url: '',
        options_data: '',
        widgets_data: '',
        init: function ()
        {
            this._resetPagedCount();
            this._bind();
        },
        /**
         * Debugging.
         * 
         * @param  {mixed} data Mixed data.
         */
        _log: function (data) {
            if (colorwaySitesAdmin.debug) {

                var date = new Date();
                var time = date.toLocaleTimeString();

                if (typeof data == 'object') {
                    console.log('%c ' + JSON.stringify(data) + ' ' + time, 'background: #ededed; color: #444');
                } else {
                    console.log('%c ' + data + ' ' + time, 'background: #ededed; color: #444');
                }


            }
        },
        /**
         * Binds events for the Colorway Sites.
         *
         * @since 1.0.0
         * @access private
         * @method _bind
         */
        _bind: function ()
        {
            $(document).on('click', '.devices button', ColorwaySitesAdmin._previewDevice);
            $(document).on('click', '.theme-browser .theme-screenshot, .theme-browser .more-details, .theme-browser .install-theme-preview', ColorwaySitesAdmin._preview);
            $(document).on('click', '.licensebtn', ColorwaySitesAdmin._checkLicense);
            $(document).on('click', '#reset_license', ColorwaySitesAdmin._resetLicense);
            $(document).on('click', '.next-theme', ColorwaySitesAdmin._nextTheme);
            $(document).on('click', '.previous-theme', ColorwaySitesAdmin._previousTheme);
            $(document).on('click', '.collapse-sidebar', ColorwaySitesAdmin._collapse);
            $(document).on('click', '.colorway-demo-import', ColorwaySitesAdmin._importDemo);
            $(document).on('click', '.install-now', ColorwaySitesAdmin._installNow);
            $(document).on('click', '.close-full-overlay', ColorwaySitesAdmin._fullOverlay);
            $(document).on('click', '.activate-now', ColorwaySitesAdmin._activateNow);
            $(document).on('wp-plugin-installing', ColorwaySitesAdmin._pluginInstalling);
            $(document).on('wp-plugin-install-error', ColorwaySitesAdmin._installError);
            $(document).on('wp-plugin-install-success', ColorwaySitesAdmin._installSuccess);

            $(document).on('colorway-sites-import-set-site-data-done', ColorwaySitesAdmin._importCustomizerSettings);
            $(document).on('colorway-sites-import-customizer-settings-done', ColorwaySitesAdmin._importPrepareXML);
            $(document).on('colorway-sites-import-xml-done', ColorwaySitesAdmin._importSiteOptions);
            $(document).on('colorway-sites-import-options-done', ColorwaySitesAdmin._importWidgets);
            $(document).on('colorway-sites-import-widgets-done', ColorwaySitesAdmin._importEnd);
        },
        /**
         * 5. Import Complete.
         */
        _importEnd: function (event) {

            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'colorway-sites-import-end',
                },
                beforeSend: function () {
                    $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.importComplete);
                }
            })
                    .fail(function (jqXHR) {
                        ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText);
                        ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                    })
                    .done(function (data) {

                        // 5. Fail - Import Complete.
                        if (false === data.success) {
                            ColorwaySitesAdmin._importFailMessage(data.data);
                            ColorwaySitesAdmin._log(data.data);
                        } else {

                            $('body').removeClass('importing-site');
                            $('.previous-theme, .next-theme').removeClass('disabled');
                            $('#progress_section').empty();
                            $('#progress_section').css('display','none');
                            // 5. Pass - Import Complete.
                            ColorwaySitesAdmin._importSuccessMessage();
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.success + ' ' + colorwaySitesAdmin.siteURL);
                        }
                    });
        },
        /**
         * 4. Import Widgets.
         */
        _importWidgets: function (event) {

            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'colorway-sites-import-widgets',
                    widgets_data: ColorwaySitesAdmin.widgets_data,
                },
                beforeSend: function () {
                    ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importWidgets);
                    $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.importingWidgets);
                },
            })
                    .fail(function (jqXHR) {
                        ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText);
                        ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                    })
                    .done(function (widgets_data) {

                        // 4. Fail - Import Widgets.
                        if (false === widgets_data.success) {
                            ColorwaySitesAdmin._importFailMessage(widgets_data.data);
                            ColorwaySitesAdmin._log(widgets_data.data);

                        } else {

                            // 4. Pass - Import Widgets.
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importWidgetsSuccess);
                            $(document).trigger('colorway-sites-import-widgets-done');
                        }
                    });
        },
        /**
         * 3. Import Site Options.
         */
        _importSiteOptions: function (event) {

            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'colorway-sites-import-options',
                    options_data: ColorwaySitesAdmin.options_data,
                },
                beforeSend: function () {
                    ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importOptions);
                    $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.importingOptions);
                },
            })
                    .fail(function (jqXHR) {
                        ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText);
                        ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                    })
                    .done(function (options_data) {

                        // 3. Fail - Import Site Options.
                        if (false === options_data.success) {
                            ColorwaySitesAdmin._log(options_data);
                            ColorwaySitesAdmin._importFailMessage(options_data.data);
                            ColorwaySitesAdmin._log(options_data.data);

                        } else {

                            // 3. Pass - Import Site Options.
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importOptionsSuccess);
                            $(document).trigger('colorway-sites-import-options-done');
                        }
                    });
        },
        /**
         * 2. Prepare XML Data.
         */
        _importPrepareXML: function (event) {

            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'colorway-sites-import-prepare-xml',
                    wxr_url: ColorwaySitesAdmin.wxr_url,
                },
                beforeSend: function () {
                    ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importXMLPrepare);
                    $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.importXMLPreparing);
                },
            })
                    .fail(function (jqXHR) {
                        ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText);
                        ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                    })
                    .done(function (xml_data) {
//                                   console.log(xml_data);
                        // 2. Fail - Prepare XML Data.
                        if (false === xml_data.success) {
                            ColorwaySitesAdmin._log(xml_data);
                            ColorwaySitesAdmin._importFailMessage(xml_data.data);
                            ColorwaySitesAdmin._log(xml_data.data);

                        } else {

                            // 2. Pass - Prepare XML Data.
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importXMLPrepareSuccess);

                            // Import XML though Event Source.
                            ColorwaySSEImport.data = xml_data.data;
                            ColorwaySSEImport.render();

                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importXML);
                            $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.importingXML);

                            var evtSource = new EventSource(ColorwaySSEImport.data.url);
                            evtSource.onmessage = function (message) {
                                var data = JSON.parse(message.data);                                
                                switch (data.action) {
                                    case 'updateDelta':
                                        ColorwaySSEImport.updateDelta(data.type, data.delta);
                                        break;

                                    case 'complete':
                                        evtSource.close();

                                        // 2. Pass - Import XML though "Source Event".
                                        ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importXMLSuccess);
                                        ColorwaySitesAdmin._log('----- SSE - XML import Complete -----');

                                        $(document).trigger('colorway-sites-import-xml-done');

                                        break;
                                }
                            };
                            evtSource.addEventListener('log', function (message) {
                                var data = JSON.parse(message.data);
                                ColorwaySitesAdmin._log(data.level + ' ' + data.message);
                            });
                        }
                    });
        },
        /**
         * 1. Import Customizer Options.
         */
        _importCustomizerSettings: function (event) {

            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'colorway-sites-import-customizer-settings',
                    customizer_data: ColorwaySitesAdmin.customizer_data,
                },
                beforeSend: function () {
                    ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importCustomizer);
                    $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.importingCustomizer);
                },
            })
                    .fail(function (jqXHR) {
                        ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText);
                        ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                    })
                    .done(function (customizer_data) {
//                        console.log(customizer_data);
                        // 1. Fail - Import Customizer Options.
                        if (false === customizer_data.success) {
                            ColorwaySitesAdmin._importFailMessage(customizer_data.data);
                            ColorwaySitesAdmin._log(customizer_data.data);
                        } else {

                            // 1. Pass - Import Customizer Options.
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importCustomizerSuccess);

                            $(document).trigger('colorway-sites-import-customizer-settings-done');
                        }
                    });
        },
        /**
         * Import Success Button.
         * 
         * @param  {string} data Error message.
         */
        _importSuccessMessage: function () {

            $('.colorway-demo-import').removeClass('updating-message installing')
                    .removeAttr('data-import')
                    .addClass('view-site')
                    .removeClass('colorway-demo-import')
                    .text(colorwaySitesAdmin.strings.viewSite)
                    .attr('target', '_blank')
                    .append('<i class="dashicons dashicons-external"></i>')
                    .attr('href', colorwaySitesAdmin.siteURL);
        },
        /**
         * Preview Device
         */
        _previewDevice: function (event) {
            var device = $(event.currentTarget).data('device');

            $('.theme-install-overlay')
                    .removeClass('preview-desktop preview-tablet preview-mobile')
                    .addClass('preview-' + device)
                    .data('current-preview-device', device);

            ColorwaySitesAdmin._tooglePreviewDeviceButtons(device);
        },
        /**
         * Toggle Preview Buttons
         */
        _tooglePreviewDeviceButtons: function (newDevice) {
            var $devices = $('.wp-full-overlay-footer .devices');

            $devices.find('button')
                    .removeClass('active')
                    .attr('aria-pressed', false);

            $devices.find('button.preview-' + newDevice)
                    .addClass('active')
                    .attr('aria-pressed', true);
        },
        /**
         * Import Error Button.
         * 
         * @param  {string} data Error message.
         */
        _importFailMessage: function (message, from) {

            $('.colorway-demo-import')
                    .addClass('go-pro button-primary')
                    .removeClass('updating-message installing')
                    .removeAttr('data-import')
                    .attr('target', '_blank')
                    .append('<i class="dashicons dashicons-external"></i>')
                    .removeClass('colorway-demo-import');

            // Add the doc link due to import log file not generated.
            if ('undefined' === from) {

                $('.wp-full-overlay-header .go-pro').text(colorwaySitesAdmin.strings.importFailedBtnSmall);
                $('.wp-full-overlay-footer .go-pro').text(colorwaySitesAdmin.strings.importFailedBtnLarge);
                $('.go-pro').attr('href', colorwaySitesAdmin.log.serverConfiguration);

                // Add the import log file link.
            } else {

                $('.wp-full-overlay-header .go-pro').text(colorwaySitesAdmin.strings.importFailBtn);
                $('.wp-full-overlay-footer .go-pro').text(colorwaySitesAdmin.strings.importFailBtnLarge)

                // Add the import log file link.
                if ('undefined' !== ColorwaySitesAdmin.log_file_url) {
                    $('.go-pro').attr('href', ColorwaySitesAdmin.log_file_url);
                } else {
                    $('.go-pro').attr('href', colorwaySitesAdmin.log.serverConfiguration);
                }
            }

            var output = '<div class="colorway-api-error notice notice-error notice-alt is-dismissible">';
            output += '	<p>' + message + '</p>';
            output += '	<button type="button" class="notice-dismiss">';
            output += '		<span class="screen-reader-text">' + commonL10n.dismiss + '</span>';
            output += '	</button>';
            output += '</div>';

            // Fail Notice.
            $('.install-theme-info').append(output);


            // !important to add trigger.
            // Which reinitialize the dismiss error message events.
            $(document).trigger('wp-updates-notice-added');
        },
        /**
         * Install Now
         */
        _installNow: function (event)
        {
            event.preventDefault();
//            console.log('_installNow: '+event)
            var $button = jQuery(event.target),
                    $document = jQuery(document);

            if ($button.hasClass('updating-message') || $button.hasClass('button-disabled')) {
                return;
            }

            if (wp.updates.shouldRequestFilesystemCredentials && !wp.updates.ajaxLocked) {
                wp.updates.requestFilesystemCredentials(event);

                $document.on('credential-modal-cancel', function () {
                    var $message = $('.install-now.updating-message');

                    $message.removeClass('updating-message').text(wp.updates.l10n.installNow);

                    wp.a11y.speak(wp.updates.l10n.updateCancel, 'polite');
                });
            }

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.installingPlugin + ' ' + $button.data('slug'));

            wp.updates.installPlugin({
                slug: $button.data('slug')
            });
        },
        /**
         * Install Success
         */
        _installSuccess: function (event, response) {
//            console.log('_installSuccess: '+response);
            event.preventDefault();

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.installed + ' ' + response.slug);

            var $message = jQuery('.plugin-card-' + response.slug).find('.button');
            var $siteOptions = jQuery('.wp-full-overlay-header').find('.colorway-site-options').val();
            var $enabledExtensions = jQuery('.wp-full-overlay-header').find('.colorway-enabled-extensions').val();

            // Transform the 'Install' button into an 'Activate' button.
            var $init = $message.data('init');

            $message.removeClass('install-now installed button-disabled updated-message')
                    .addClass('updating-message')
                    .html(colorwaySitesAdmin.strings.btnActivating);

            // Reset not installed plugins list.
            var pluginsList = colorwaySitesAdmin.requiredPlugins.notinstalled;
            colorwaySitesAdmin.requiredPlugins.notinstalled = ColorwaySitesAdmin._removePluginFromQueue(response.slug, pluginsList);

            // WordPress adds "Activate" button after waiting for 1000ms. So we will run our activation after that.
            setTimeout(function () {

                $.ajax({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        'action': 'colorway-required-plugin-activate',
                        'init': $init,
                        'options': $siteOptions,
                        'enabledExtensions': $enabledExtensions,
                    },
                })
                        .done(function (result) {
//                            console.log(result);
                            if (result.success) {

                                var pluginsList = colorwaySitesAdmin.requiredPlugins.inactive;

                                // Reset not installed plugins list.
                                colorwaySitesAdmin.requiredPlugins.inactive = ColorwaySitesAdmin._removePluginFromQueue(response.slug, pluginsList);

                                $message.removeClass('button-primary install-now activate-now updating-message')
                                        .attr('disabled', 'disabled')
                                        .addClass('disabled')
                                        .text(colorwaySitesAdmin.strings.btnActive);

                                // Enable Demo Import Button
                                ColorwaySitesAdmin._enable_demo_import_button();

                            } else {

                                $message.removeClass('updating-message');

                            }

                        });

            }, 1200);

        },
        /**
         * Plugin Installation Error.
         */
        _installError: function (event, response) {

            var $card = jQuery('.plugin-card-' + response.slug);

            ColorwaySitesAdmin._log(response.errorMessage + ' ' + response.slug);

            $card
                    .removeClass('button-primary')
                    .addClass('disabled')
                    .html(wp.updates.l10n.installFailedShort);

            ColorwaySitesAdmin._importFailMessage(response.errorMessage);
        },
        /**
         * Installing Plugin
         */
        _pluginInstalling: function (event, args) {
            event.preventDefault();

            var $card = jQuery('.plugin-card-' + args.slug);
            var $button = $card.find('.button');

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.installingPlugin + ' ' + args.slug);

            $card.addClass('updating-message');
            $button.addClass('already-started');

        },
        /**
         * Render Demo Preview
         */
        _activateNow: function (eventn) {

            event.preventDefault();

            var $button = jQuery(event.target),
                    $init = $button.data('init'),
                    $slug = $button.data('slug');

            if ($button.hasClass('updating-message') || $button.hasClass('button-disabled')) {
                return;
            }

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.activating + ' ' + $slug);

            $button.addClass('updating-message button-primary')
                    .html(colorwaySitesAdmin.strings.btnActivating);

            var $siteOptions = jQuery('.wp-full-overlay-header').find('.colorway-site-options').val();
            var $enabledExtensions = jQuery('.wp-full-overlay-header').find('.colorway-enabled-extensions').val();

            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                data: {
                    'action': 'colorway-required-plugin-activate',
                    'init': $init,
                    'options': $siteOptions,
                    'enabledExtensions': $enabledExtensions,
                },
            })
                    .done(function (result) {
//                        console.log(result);
                        if (result.success) {

                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.activated + ' ' + $slug);

                            var pluginsList = colorwaySitesAdmin.requiredPlugins.inactive;

                            // Reset not installed plugins list.
                            colorwaySitesAdmin.requiredPlugins.inactive = ColorwaySitesAdmin._removePluginFromQueue($slug, pluginsList);

                            $button.removeClass('button-primary install-now activate-now updating-message')
                                    .attr('disabled', 'disabled')
                                    .addClass('disabled')
                                    .text(colorwaySitesAdmin.strings.btnActive);

                            // Enable Demo Import Button
                            ColorwaySitesAdmin._enable_demo_import_button();

                        }

                    })
                    .fail(function () {
                    });

        },
        /**
         * Full Overlay
         */
        _fullOverlay: function (event) {
            event.preventDefault();

            // Import process is started?
            // And Closing the window? Then showing the warning confirm message.
            if ($('body').hasClass('importing-site') && !confirm(colorwaySitesAdmin.strings.warningBeforeCloseWindow)) {
                return;
            }

            $('body').removeClass('importing-site');
            $('.previous-theme, .next-theme').removeClass('disabled');
            $('.theme-install-overlay').css('display', 'none');
            $('.theme-install-overlay').remove();
            $('.theme-preview-on').removeClass('theme-preview-on');
            $('html').removeClass('colorway-site-preview-on');
        },
        /**
         * Bulk Plugin Active & Install
         */
        _bulkPluginInstallActivate: function ()
        {
            if (0 === colorwaySitesAdmin.requiredPlugins.length) {
                return;
            }

            jQuery('.required-plugins')
                    .find('.install-now')
                    .addClass('updating-message')
                    .removeClass('install-now')
                    .text(wp.updates.l10n.installing);

            jQuery('.required-plugins')
                    .find('.activate-now')
                    .addClass('updating-message')
                    .removeClass('activate-now')
                    .html(colorwaySitesAdmin.strings.btnActivating);

            var not_installed = colorwaySitesAdmin.requiredPlugins.notinstalled || '';
            var activate_plugins = colorwaySitesAdmin.requiredPlugins.inactive || '';
            var filename = colorwaySitesAdmin.requiredPlugins.filename || '';

            // First Install Bulk.
            if (not_installed.length > 0) {
                ColorwaySitesAdmin._installAllPlugins(not_installed, filename);
            }
            // Second Activate Bulk.
            if (activate_plugins.length > 0) {
                ColorwaySitesAdmin._activateAllPlugins(activate_plugins, filename);
            }

        },
        /**
         * Activate All Plugins.
         */
        _activateAllPlugins: function (activate_plugins, filename) {

            // Activate ALl Plugins.
            ColorwaySitesAjaxQueue.stop();
            ColorwaySitesAjaxQueue.run();

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.bulkActivation);

            $.each(activate_plugins, function (index, single_plugin) {
                var $card = jQuery('.plugin-card-' + single_plugin),
                        $button = $card.find('.button'),
                        $siteOptions = jQuery('.wp-full-overlay-header').find('.colorway-site-options').val(),
                        $enabledExtensions = jQuery('.wp-full-overlay-header').find('.colorway-enabled-extensions').val();

                $button.addClass('updating-message');

                ColorwaySitesAjaxQueue.add({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        'action': 'colorway-required-plugin-activate',
                        'init': single_plugin + '/' + filename[index] + '.php',
                        'options': $siteOptions,
                        'enabledExtensions': $enabledExtensions,
                    },
                    success: function (result) {
//                        console.log(result);
                        if (result.success) {

                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.activate + ' ' + single_plugin);

                            var $card = jQuery('.plugin-card-' + single_plugin);
                            var $button = $card.find('.button');
                            if (!$button.hasClass('already-started')) {
                                var pluginsList = colorwaySitesAdmin.requiredPlugins.inactive;

                                // Reset not installed plugins list.
                                colorwaySitesAdmin.requiredPlugins.inactive = ColorwaySitesAdmin._removePluginFromQueue(single_plugin.slug, pluginsList);
                            }

                            $button.removeClass('button-primary install-now activate-now updating-message')
                                    .attr('disabled', 'disabled')
                                    .addClass('disabled')
                                    .text(colorwaySitesAdmin.strings.btnActive);

                            // Enable Demo Import Button
                            ColorwaySitesAdmin._enable_demo_import_button();
                        } else {
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.activationError + ' - ' + single_plugin.slug);
                        }
                    }
                });
            });
        },
        /**
         * Install All Plugins.
         */
        _installAllPlugins: function (not_installed) {
            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.bulkInstall);

            $.each(not_installed, function (index, single_plugin) {
//                var lastSlash = single_plugin.lastIndexOf("/");
//                var single_plugin = single_plugin.substring(0, lastSlash);
                var $card = jQuery('.plugin-card-' + single_plugin),
                        $button = $card.find('.button');
                if (!$button.hasClass('already-started')) {

                    // Add each plugin activate request in Ajax queue.
                    // @see wp-admin/js/updates.js
                    wp.updates.queue.push({
                        action: 'install-plugin', // Required action.
                        data: {
                            slug: single_plugin
                        }
                    });
                }
            });

            // Required to set queue.
            wp.updates.queueChecker();
        },
        /**
         * Fires when a nav item is clicked.
         *
         * @since 1.0
         * @access private
         * @method _importDemo
         */
        _importDemo: function ()
        {
            var $this = jQuery(this),
                    $theme = $this.closest('.colorway-sites-preview').find('.wp-full-overlay-header'),
                    apiURL = $theme.data('demo-api') || '',
                    plugins = $theme.data('required-plugins');

            var disabled = $this.attr('data-import');

            if (typeof disabled !== 'undefined' && disabled === 'disabled' || $this.hasClass('disabled')) {

                $('.colorway-demo-import').addClass('updating-message installing')
                        .text(wp.updates.l10n.installing);

                /**
                 * Process Bulk Plugin Install & Activate
                 */
                ColorwaySitesAdmin._bulkPluginInstallActivate();

                return;
            }

            // Proceed?
            if (!confirm(colorwaySitesAdmin.strings.importWarning)) {
                return;
            }

            $('body').addClass('importing-site');
            $('.previous-theme, .next-theme').addClass('disabled');

            // Remove all notices before import start.
            $('.install-theme-info > .notice').remove();

            $('.colorway-demo-import').attr('data-import', 'disabled')
                    .addClass('updating-message installing')
                    .text(colorwaySitesAdmin.strings.importingDemo);

            $this.closest('.theme').focus();

            var $theme = $this.closest('.colorway-sites-preview').find('.wp-full-overlay-header');

            var apiURL = $theme.data('demo-api') || '';

            // Site Import by API URL.
            if (apiURL) {
                ColorwaySitesAdmin._importSite(apiURL);
            }

        },
        /**
         * Start Import Process by API URL.
         * 
         * @param  {string} apiURL Site API URL.
         */
        _importSite: function (apiURL) {

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.api + ' : ' + apiURL);
            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.importing);

            $('.button-hero.colorway-demo-import').text(colorwaySitesAdmin.log.gettingData);

            // 1. Request Site Import
            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    'action': 'colorway-sites-import-set-site-data',
                    'api_url': apiURL,
                },
            })
                    .fail(function (jqXHR) {
                        ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText);
                        ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                    })
                    .done(function (demo_data) {
//                            console.log(demo_data.data['site-customizer-data']);
                        // 1. Fail - Request Site Import
                        if (false === demo_data.success) {
                            ColorwaySitesAdmin._importFailMessage(demo_data.data);

                        } else {

                            // Set log file URL.
                            if ('log_file' in demo_data.data) {
                                ColorwaySitesAdmin.log_file_url = decodeURIComponent(demo_data.data.log_file) || '';
                            }

                            // 1. Pass - Request Site Import
                            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.processingRequest);

                            ColorwaySitesAdmin.customizer_data = JSON.stringify(demo_data.data['site-customizer-data']) || '';
                            ColorwaySitesAdmin.wxr_url = encodeURI(demo_data.data['site-wxr-path']) || '';
                            ColorwaySitesAdmin.options_data = JSON.stringify(demo_data.data['site-options-data']) || '';
                            ColorwaySitesAdmin.widgets_data = JSON.stringify(demo_data.data['site-widgets-data']) || '';
                            $(document).trigger('colorway-sites-import-set-site-data-done');
                        }

                    });

        },
        /**
         * Collapse Sidebar.
         */
        _collapse: function () {
            event.preventDefault();

            overlay = jQuery('.wp-full-overlay');

            if (overlay.hasClass('expanded')) {
                overlay.removeClass('expanded');
                overlay.addClass('collapsed');
                return;
            }

            if (overlay.hasClass('collapsed')) {
                overlay.removeClass('collapsed');
                overlay.addClass('expanded');
                return;
            }
        },
        /**
         * Previous Theme.
         */
        _previousTheme: function (event) {
            event.preventDefault();

            currentDemo = jQuery('.theme-preview-on');
            currentDemo.removeClass('theme-preview-on');
            prevDemo = currentDemo.prev('.theme');
            prevDemo.addClass('theme-preview-on');
            var siteType = prevDemo[0].dataset.demoType;
            jQuery('#licenseform').empty();
            if (siteType !== 'free') {
                data = {
                    action: 'colorway-license-checker',
                    _ajax_nonce: colorwaySitesAdmin._ajax_nonce,
                };
                $.ajax({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: data,
                })
                        .done(function (response) {
//                            jQuery('#licenseform').empty();
                            if (JSON.parse(response).status == 'ok' || JSON.parse(response).status == 'exists' || JSON.parse(response).status == true) {
                                jQuery('#licenseform').empty();
                                jQuery('.go-pro')
                                        .removeAttr('href')
                                        .removeClass('go-pro')
                                        .addClass('colorway-demo-import')
                                        .text(colorwaySitesAdmin.strings.importDemo);
                                jQuery('.install-theme-info h4').empty();
                                jQuery('.install-theme-info').prepend('<h4 style="color:green">' + JSON.parse(response).message + '</h4>');
                            } else if(JSON.parse(response).status == "license_disabled" || JSON.parse(response).status == "license_expired"){ 
                                jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                                jQuery('#licenseform').html("<h4 style='color:red'>" + JSON.parse(response).message + "</h4>");
                            }else {
                                 jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                                jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary'>Submit</button></form>");
                            }
                        })
            } else {
                jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary'>Submit</button></form>");
            }
            ColorwaySitesAdmin._renderDemoPreview(prevDemo);
        },
        /**
         * Next Theme.
         */
        _nextTheme: function (event) {
            event.preventDefault();
            currentDemo = jQuery('.theme-preview-on')
            currentDemo.removeClass('theme-preview-on');
            nextDemo = currentDemo.next('.theme');
            nextDemo.addClass('theme-preview-on');
            var siteType = nextDemo[0].dataset.demoType;
            jQuery('#licenseform').empty();
            if (siteType !== 'free') {
                data = {
                    action: 'colorway-license-checker',
                    _ajax_nonce: colorwaySitesAdmin._ajax_nonce,
                };
                $.ajax({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: data,
                })
                        .done(function (response) {
                            if (JSON.parse(response).status == 'ok' || JSON.parse(response).status == 'exists' || JSON.parse(response).status == true) {
                                jQuery('#licenseform').empty();
                                jQuery('.go-pro')
                                        .removeAttr('href')
                                        .removeClass('go-pro')
                                        .addClass('colorway-demo-import')
                                        .text(colorwaySitesAdmin.strings.importDemo);
                                jQuery('.install-theme-info h4').empty();
                                jQuery('.install-theme-info').prepend('<h4 style="color:green">' + JSON.parse(response).message + '</h4>');
                            } else if(JSON.parse(response).status == "license_disabled" || JSON.parse(response).status == "license_expired"){ 
                                jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                                jQuery('#licenseform').html("<h4 style='color:red'>" + JSON.parse(response).message + "</h4>");
                            }else {
                                 jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                                jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary'>Submit</button></form>");
                            }
                        })
            } else {
                jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary'>Submit</button></form>");
            }
            ColorwaySitesAdmin._renderDemoPreview(nextDemo);
        },
        /**
         * Individual Site Preview
         *
         * On click on image, more link & preview button.
         */
        _preview: function (event) {
            event.preventDefault();
            var self = jQuery(this).parents('.theme');

            self.addClass('theme-preview-on');
            var siteType = self[0].dataset.demoType;
            jQuery('html').addClass('colorway-site-preview-on');

            if (siteType !== 'free') {
                data = {
                    action: 'colorway-license-checker',
                    _ajax_nonce: colorwaySitesAdmin._ajax_nonce,
                };
                $.ajax({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: data,
                })
                        .done(function (response) {
                            //jQuery('#licenseform').empty();
                        //console.log(JSON.parse(response));
                            if (JSON.parse(response).status == 'ok' || JSON.parse(response).status == 'exists' || JSON.parse(response).status == true) {
                                jQuery('#licenseform').empty();
                                jQuery('.go-pro')
                                        .removeAttr('href')
                                        .removeClass('go-pro')
                                        .addClass('colorway-demo-import')
                                        .text(colorwaySitesAdmin.strings.importDemo);
                                jQuery('.install-theme-info h4').empty();
                                jQuery('.install-theme-info').prepend('<h4 style="color:green">' + JSON.parse(response).message + '</h4>');
                            }else if(JSON.parse(response).status == "license_disabled" || JSON.parse(response).status == "license_expired"){ 
                                jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                                jQuery('#licenseform').html("<h4 style='color:red'>" + JSON.parse(response).message + "</h4>");
                            }else {
                                jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                                jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary key-bttn' name=submit123' type='submit' value='submit'>Submit</button></form>");
                            }
                        })
            } else {
                jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary'>Submit</button></form>");
            }
            ColorwaySitesAdmin._renderDemoPreview(self);
        },
        /**
         * Check Next Previous Buttons.
         */
        _checkNextPrevButtons: function () {
            currentDemo = jQuery('.theme-preview-on');
            nextDemo = currentDemo.nextAll('.theme').length;
            prevDemo = currentDemo.prevAll('.theme').length;

            if (nextDemo == 0) {
                jQuery('.next-theme').addClass('disabled');
            } else if (nextDemo != 0) {
                jQuery('.next-theme').removeClass('disabled');
            }

            if (prevDemo == 0) {
                jQuery('.previous-theme').addClass('disabled');
            } else if (prevDemo != 0) {
                jQuery('.previous-theme').removeClass('disabled');
            }

            return;
        },
        /**
         * Check InkThemes License Function
         */
        _checkLicense: function (event) {
            event.preventDefault();
            var keyinput = jQuery('[name=license_key]').val();
            if (keyinput != null || keyinput != '') {
                data = {
                    action: 'colorway-license-checker',
                    _ajax_nonce: colorwaySitesAdmin._ajax_nonce,
                    keyinput: keyinput,
                };
                $.ajax({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: data,
                })
                        .done(function (response) {
//                       console.log(JSON.parse(response).status);
                            if (typeof JSON.parse(response).status !== 'undefined' && JSON.parse(response).status !== false) {                              
                              
                                    jQuery('.go-pro')
                                            .removeAttr('href')
                                            .removeClass('go-pro')                                            
                                            .addClass('colorway-demo-import')
                                            .text(colorwaySitesAdmin.strings.importDemo);                              
                            
                                jQuery('.install-theme-info h4').empty();
                                jQuery('.install-theme-info').prepend('<h4 style="color:green">' + JSON.parse(response).message + '</h4>');
                                jQuery('#licenseform').empty();
                            } else {
//                               console.log(JSON.parse(response));                             
                                jQuery('.install-theme-info h4').empty();
                                jQuery('.install-theme-info').prepend('<h4 style="color:red">' + JSON.parse(response).message + '</h4>');
                            }
                        })

            }
        },
        /**
         * Check InkThemes License Function
         */
        _resetLicense: function (event) {
            var deleteLicense = jQuery('#reset_license').text();
            data = {
                action: 'colorway-license-reset',
                _ajax_nonce: colorwaySitesAdmin._ajax_nonce,
                deleteLicense: deleteLicense,
            };
            $.ajax({
                url: colorwaySitesAdmin.ajaxurl,
                type: 'POST',
                data: data,
            })
                    .done(function (response) {
//                            console.log(response);
                        jQuery('.install-theme-info h4').empty();
                        jQuery('.install-theme-info').prepend('<h4 style="color:red">' + response + '</h4>');
                        jQuery('#licenseform').html("<h4>Enter Licence Key:</h4><form method=POST><input name=license_key><button class='licensebtn button-primary'>Submit</button></form>");
                        jQuery('.colorway-demo-import')
                                .addClass('go-pro button-primary')
                                .removeClass('colorway-demo-import')
                                .attr('target', '_blank')
                                .attr('href', colorwaySitesAdmin.getProURL)
                                .text(colorwaySitesAdmin.getProText)
                                .append('<i class="dashicons dashicons-external"></i>');
                    })
        },
        /**
         * Render Demo Preview
         */
        _renderDemoPreview: function (anchor) {
            var demoId = anchor.data('id') || '',
                    apiURL = anchor.data('demo-api') || '',
                    demoType = anchor.data('demo-type') || '',
                    license = anchor.data('license') || '',
                    demoURL = anchor.data('demo-url') || '',
                    screenshot = anchor.data('screenshot') || '',
                    demo_name = anchor.data('demo-name') || '',
                    demo_slug = anchor.data('demo-slug') || '',
                    content = anchor.data('content') || '',
                    requiredPlugins = anchor.data('required-plugins') || '',
                    colorwaySiteOptions = anchor.find('.colorway-site-options').val() || '';
            colorwayEnabledExtensions = anchor.find('.colorway-enabled-extensions').val() || '';

            ColorwaySitesAdmin._log(colorwaySitesAdmin.log.preview + ' "' + demo_name + '" URL : ' + demoURL);

            var template = wp.template('colorway-site-preview');

            templateData = [{
                    id: demoId,
                    colorway_demo_type: demoType,
                    colorway_demo_url: demoURL,
                    demo_api: apiURL,
                    screenshot: screenshot,
                    demo_name: demo_name,
                    slug: demo_slug,
                    content: content,
                    required_plugins: JSON.stringify(requiredPlugins),
                    colorway_site_options: colorwaySiteOptions,
                    colorway_enabled_extensions: colorwayEnabledExtensions,
                }];

            // delete any earlier fullscreen preview before we render new one.
            jQuery('.theme-install-overlay').remove();

            jQuery('#colorway-sites-menu-page').append(template(templateData[0]));
            jQuery('.theme-install-overlay').css('display', 'block');
            ColorwaySitesAdmin._checkNextPrevButtons();

            var desc = jQuery('.theme-details');
            var descHeight = parseInt(desc.outerHeight());
            var descBtn = jQuery('.theme-details-read-more');

            if ($.isArray(requiredPlugins)) {

//                console.log(requiredPlugins);

                if (descHeight >= 57) {

                    // Show button.
                    descBtn.css('display', 'inline-block');

                    // Set height upto 3 line.
                    desc.css('max-height', 85);
                    desc.css('overflow-y', 'scroll');                    

                    // Button Click.
                    descBtn.click(function (event) {

                        if (descBtn.hasClass('open')) {
                            desc.animate({height: 57},
                                    300, function () {
                                        descBtn.removeClass('open');
                                        descBtn.html(colorwaySitesAdmin.strings.DescExpand);
                                    });
                        } else {
                            desc.animate({height: descHeight},
                                    300, function () {
                                        descBtn.addClass('open');
                                        descBtn.html(colorwaySitesAdmin.strings.DescCollapse);
                                    });                                    
                        }

                    });
                }

                // or
                var $pluginsFilter = jQuery('#plugin-filter'),
                        data = {
                            action: 'colorway-required-plugins',
                            _ajax_nonce: colorwaySitesAdmin._ajax_nonce,
                            required_plugins: requiredPlugins
                        };

                // Add disabled class from import button.
                $('.colorway-demo-import')
                        .addClass('disabled not-click-able')
                        .removeAttr('data-import');

                $('.required-plugins').addClass('loading').html('<span class="spinner is-active"></span>');

                // Required Required.
                $.ajax({
                    url: colorwaySitesAdmin.ajaxurl,
                    type: 'POST',
                    data: data,
                })
                        .fail(function (jqXHR) {

                            // Remove loader.
                            jQuery('.required-plugins').removeClass('loading').html('');

                            ColorwaySitesAdmin._importFailMessage(jqXHR.status + ' ' + jqXHR.responseText, 'plugins');
                            ColorwaySitesAdmin._log(jqXHR.status + ' ' + jqXHR.responseText);
                        })
                        .done(function (response) {
//                            console.log(response);
                            // Release disabled class from import button.
                            $('.colorway-demo-import')
                                    .removeClass('disabled not-click-able')
                                    .attr('data-import', 'disabled');

                            // Remove loader.
                            $('.required-plugins').removeClass('loading').html('');

                            /**
                             * Count remaining plugins.
                             * @type number
                             */
                            var remaining_plugins = 0;

                            /**
                             * Not Installed
                             *
                             * List of not installed required plugins.
                             */

                            if (typeof response.data.notinstalled !== 'undefined') {
                                // Add not have installed plugins count.
                                remaining_plugins += parseInt(response.data.notinstalled.length);
//                                console.log(response.data.filename);
                                jQuery(response.data.notinstalled).each(function (index, plugin) {
                                                                   
                                    var output = '<div class="plugin-card ';
                                    output += 'plugin-card-' + plugin + '"';
                                    output += '	data-slug="' + plugin + '"';
                                    output += '	data-init="' + plugin + '/' + response.data.filename[index] + '.php">';
                                    output += '	<span class="title">' + prettify(plugin) + '</span>';
                                    output += '	<button class="button install-now"';
                                    output += '	data-init="' + plugin + '/' + response.data.filename[index] + '.php"';
                                    output += '	data-slug="' + plugin + '"';
                                    output += '	data-name="' + plugin + '">';
                                    output += wp.updates.l10n.installNow;
                                    output += '	</button>';
                                    // output += '	<span class="dashicons-no dashicons"></span>';
                                    output += '</div>';
                                    
                                    function prettify(str) {
                                        var words = str.match(/([^-]+)/g) || [];
                                        words.forEach(function (word, i) {
                                            words[i] = word[0].toUpperCase() + word.slice(1);
                                        });
                                        return words.join(' ');
                                    }
                                    jQuery('.required-plugins').append(output);

                                });
                            }

                            /**
                             * Inactive
                             *
                             * List of not inactive required plugins.
                             */
                            if (typeof response.data.inactive !== 'undefined') {
                                // console.log(response.data);
                                // Add inactive plugins count.
                                remaining_plugins += parseInt(response.data.inactive.length);

                                jQuery(response.data.inactive).each(function (index, plugin) {
                                    //console.log(response.data.filename);                                 
                                    var output = '<div class="plugin-card ';
                                    output += 'plugin-card-' + plugin + '"';
                                    output += ' data-slug="' + plugin + '"';
                                    output += ' data-init="' + plugin + '/' + response.data.filename[index] + '.php">';
                                    output += '	<span class="title">' + prettify(plugin) + '</span>';
                                    output += '	<button class="button activate-now button-primary"';
                                    output += '	data-init="' + plugin + '/' + response.data.filename[index] + '.php"';
                                    output += '	data-slug="' + plugin + '"';
                                    output += '	data-name="' + plugin + '">';
                                    output += wp.updates.l10n.activatePlugin;
                                    output += '	</button>';
                                    // output += '	<span class="dashicons-no dashicons"></span>';
                                    output += '</div>';

                                    function prettify(str) {
                                        var words = str.match(/([^-]+)/g) || [];
                                        words.forEach(function (word, i) {
                                            words[i] = word[0].toUpperCase() + word.slice(1);
                                        });
                                        return words.join(' ');
                                    }

                                    jQuery('.required-plugins').append(output);

                                });
                            }

                            /**
                             * Active
                             *
                             * List of not active required plugins.
                             */
                            if (typeof response.data.active !== 'undefined') {
                                jQuery(response.data.active).each(function (index, plugin) {
//                                 
                                    var output = '<div class="plugin-card ';
                                    output += ' plugin-card-' + plugin + '"';
                                    output += ' data-slug="' + plugin + '"';
                                    output += ' data-init="' + plugin + '/' + response.data.filename[index] + '.php">';
                                    output += '	<span class="title">' + prettify(plugin) + '</span>';
                                    output += '	<button class="button disabled"';
                                    output += '	data-init="' + plugin + '/' + response.data.filename[index] + '.php"';
                                    output += '	data-slug="' + plugin + '"';
                                    output += '	data-name="' + plugin + '">';
                                    output += colorwaySitesAdmin.strings.btnActive;
                                    output += '	</button>';
                                    // output += '	<span class="dashicons-yes dashicons"></span>';
                                    output += '</div>';
                                    function prettify(str) {
                                        var words = str.match(/([^-]+)/g) || [];
                                        words.forEach(function (word, i) {
                                            words[i] = word[0].toUpperCase() + word.slice(1);
                                        });
                                        return words.join(' ');
                                    }
                                    jQuery('.required-plugins').append(output);

                                });
                            }

                            /**
                             * Enable Demo Import Button
                             * @type number
                             */
                            colorwaySitesAdmin.requiredPlugins = response.data;
                            ColorwaySitesAdmin._enable_demo_import_button(demoType);

                        });

            } else {

                // Enable Demo Import Button
                ColorwaySitesAdmin._enable_demo_import_button(demoType);
                jQuery('.required-plugins-wrap').remove();
            }

            return;
        },
        /**
         * Enable Demo Import Button.
         */
        _enable_demo_import_button: function (type) {
            type = (undefined !== type) ? type : 'licensed';
//            console.log("enable button type:"+type);
            switch (type) {

                case 'free':
                    var all_buttons = parseInt(jQuery('.plugin-card .button').length) || 0,
                            disabled_buttons = parseInt(jQuery('.plugin-card .button.disabled').length) || 0;

                    if (all_buttons === disabled_buttons) {

                        jQuery('.colorway-demo-import')
                                .removeAttr('data-import')
                                .removeClass('installing updating-message')
                                .addClass('button-primary')
                                .text(colorwaySitesAdmin.strings.importDemo);
                    }

                    break;

                case 'upgrade':
                    var demo_slug = jQuery('.wp-full-overlay-header').attr('data-demo-slug');

                    jQuery('.colorway-demo-import')
                            .addClass('go-pro button-primary')
                            .removeClass('colorway-demo-import')
                            .attr('target', '_blank')
                            .attr('href', colorwaySitesAdmin.getUpgradeURL)
                            .text(colorwaySitesAdmin.getUpgradeText)
                            .append('<i class="dashicons dashicons-external"></i>');
                    break;

                case 'premium':
                    var demo_slug = jQuery('.wp-full-overlay-header').attr('data-demo-slug');

                    jQuery('.colorway-demo-import')
                            .addClass('go-pro button-primary')
                            .removeClass('colorway-demo-import')
                            .attr('target', '_blank')
                            .attr('href', colorwaySitesAdmin.getProURL)
                            .text(colorwaySitesAdmin.getProText)
                            .append('<i class="dashicons dashicons-external"></i>');
                    break;
                case 'licensed':
                    var all_buttons = parseInt(jQuery('.plugin-card .button').length) || 0,
                            disabled_buttons = parseInt(jQuery('.plugin-card .button.disabled').length) || 0;

                    if (all_buttons === disabled_buttons) {

                        jQuery('.colorway-demo-import')
                                .removeAttr('data-import')
                                .removeClass('installing updating-message')
                                .addClass('button-primary')
                                .text(colorwaySitesAdmin.strings.importDemo);
                    }

                    break;
                default:
                    var demo_slug = jQuery('.wp-full-overlay-header').attr('data-demo-slug');

                    jQuery('.colorway-demo-import')
                            .addClass('go-pro button-primary')
                            .removeClass('colorway-demo-import')
                            .attr('target', '_blank')
                            .attr('href', colorwaySitesAdmin.getProURL)
                            .text(colorwaySitesAdmin.getProText)
                            .append('<i class="dashicons dashicons-external"></i>');

                    break;
            }

        },
        /**
         * Update Page Count.
         */
        _updatedPagedCount: function () {
            paged = parseInt(jQuery('body').attr('data-colorway-demo-paged'));
            jQuery('body').attr('data-colorway-demo-paged', paged + 1);
            window.setTimeout(function () {
                jQuery('body').data('scrolling', false);
            }, 800);
        },
        /**
         * Reset Page Count.
         */
        _resetPagedCount: function () {

            $('body').addClass('loading-content');
            $('body').attr('data-colorway-demo-last-request', '1');
            $('body').attr('data-colorway-demo-paged', '1');
            $('body').attr('data-colorway-demo-search', '');
            $('body').attr('data-scrolling', false);

        },
        /**
         * Remove plugin from the queue.
         */
        _removePluginFromQueue: function (removeItem, pluginsList) {
            return jQuery.grep(pluginsList, function (value) {
                return value.slug != removeItem;
            });
        }

    };

    /**
     * Initialize ColorwaySitesAdmin
     */
    $(function () {
        ColorwaySitesAdmin.init();
    });

})(jQuery);