<?php
/**
 * Shortcode Markup
 *
 * TMPL - Single Demo Preview
 * TMPL - No more demos
 * TMPL - Filters
 * TMPL - List
 *
 * @package Colorway Sites
 * @since 1.0.0
 */
defined('ABSPATH') or exit;
?>

<div class="wrap" id="colorway-sites-admin">

    <div id="colorway-sites-filters" class="wp-filter hide-if-no-js">

        <div class="section-left">

            <!-- All Filters -->
            <div class="filter-count">
                <span class="count"></span>
            </div>
            <div class="filters-wrap">
                <div id="site-page-builder" style="display:none"></div>
            </div>

            <div class="filters-wrap">
                <div id="site-category"></div>
            </div>

          <!--  <div class="filters-wrap">
                <div id="site-category">

                    <ul><li>
                            <a class="customized button button-success" href="https://forms.pabbly.com/form/share/IIdE-401322" target="_blank"> Get Customized Templates </a>
                        </li></ul>
                </div>
            </div>
            
            <div class="filters-wrap">
                <div id="site-category">
                    <ul><li>
                            <a class="free-trial button button-primary" href="https://www.inkthemes.com/members/signup/Urr433E5" target="_blank"> Get Premium Trial Now! </a>
                        </li></ul>
                </div>
            </div>-->

        </div>

        <div class="section-right">
            <div class="search-form">
                <label><?php _e('Enter Search Keyword', 'colorway-sites'); ?></label>&nbsp;
                <input type="search" aria-describedby="live-search-desc" id="wp-filter-search-input" class="wp-filter-search" value="">
            </div>
        </div>

    </div>

    <?php do_action('colorway_sites_before_site_grid'); ?>

    <div class="theme-browser rendered">
        <div id="colorway-sites" class="themes wp-clearfix"></div>
    </div>

    <div class="select-page-builder">
        <div class="note-wrap">

        </div>

    </div>

    <div class="spinner-wrap">
        <span class="spinner"></span>
    </div>

    <?php do_action('colorway_sites_after_site_grid'); ?>

</div>

<?php
/**
 * TMPL - Single Demo Preview
 */
?>
<script type="text/template" id="tmpl-colorway-site-preview">
    <# var sitedemotype = data.colorway_demo_type;     
    if(sitedemotype === 'premium'){ #>
    <?php if (get_option('colorway-sites_license_key') != '') { ?>
        <# sitedemotype = 'licensed'; #>
    <?php } else { ?>
        <# sitedemotype = data.colorway_demo_type; #>
    <?php } ?>
    <# } #>
    <div class="colorway-sites-preview theme-install-overlay wp-full-overlay expanded">
    <div class="wp-full-overlay-sidebar">
    <div class="wp-full-overlay-header"
    data-demo-id="{{{data.id}}}"
    data-demo-type="{{{sitedemotype}}}"
    data-demo-url="{{{data.colorway_demo_url}}}"
    data-demo-api="{{{data.demo_api}}}"
    data-demo-name="{{{data.demo_name}}}"
    data-demo-slug="{{{data.slug}}}"
    data-screenshot="{{{data.screenshot}}}"
    data-content="{{{data.content}}}"
    data-required-plugins="{{data.required_plugins}}">
    <input type="hidden" class="colorway-site-options" value="{{data.colorway_site_options}}" >
    <input type="hidden" class="colorway-enabled-extensions" value="{{data.colorway_enabled_extensions}}" >
    <button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e('Close', 'colorway-sites'); ?></span></button>
    <button class="previous-theme"><span class="screen-reader-text"><?php esc_html_e('Previous', 'colorway-sites'); ?></span></button>
    <button class="next-theme"><span class="screen-reader-text"><?php esc_html_e('Next', 'colorway-sites'); ?></span></button>
    <a class="button hide-if-no-customize colorway-demo-import" href="#" data-import="disabled"><?php esc_html_e('Install Plugins', 'colorway-sites'); ?></a>

    </div>
    <div class="wp-full-overlay-sidebar-content">
    <div class="install-theme-info">

    <span class="site-type {{{data.colorway_demo_type}}}">{{{sitedemotype}}}</span>
    <h3 class="theme-name">{{{data.demo_name}}}</h3>
    <div id="licenseform"></div>
    <div id="progress_section">
    <div id="prog_bar"></div>
    </div>

    <# if ( data.screenshot.length ) { #>
    <img class="theme-screenshot" src="{{{data.screenshot}}}" alt="">
    <# } #>

    <div class="theme-details">
    {{{data.content}}}
    </div>
    <!--<a href="#" class="theme-details-read-more"><?php _e('Read more', 'colorway-sites'); ?> &hellip;</a>-->

    <div class="required-plugins-wrap">
    <h4><?php _e('Required Plugins', 'colorway-sites'); ?> </h4>
    <div class="required-plugins"></div>

    </div>

    </div>

    </div>

    <div class="wp-full-overlay-footer">
    <div class="footer-import-button-wrap">
    <a class="button button-hero hide-if-no-customize colorway-demo-import" href="#" data-import="disabled">
    <?php esc_html_e('Install Plugins', 'colorway-sites'); ?>
    </a>
    </div>
    <button type="button" class="collapse-sidebar button" aria-expanded="true"
    aria-label="Collapse Sidebar">
    <span class="collapse-sidebar-arrow"></span>
    <span class="collapse-sidebar-label"><?php esc_html_e('Collapse', 'colorway-sites'); ?></span>
    </button>

    <div class="devices-wrapper">
    <div class="devices">
    <button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
    <span class="screen-reader-text"><?php _e('Enter desktop preview mode', 'colorway-sites'); ?></span>
    </button>
    <button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
    <span class="screen-reader-text"><?php _e('Enter tablet preview mode', 'colorway-sites'); ?></span>
    </button>
    <button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
    <span class="screen-reader-text"><?php _e('Enter mobile preview mode', 'colorway-sites'); ?></span>
    </button>
    </div>
    </div>

    </div>
    </div>
    <div class="wp-full-overlay-main">
    <iframe src="{{{data.colorway_demo_url}}}" title="<?php esc_attr_e('Preview', 'colorway-sites'); ?>"></iframe>
    </div>
    </div>
</script>

<?php
/**
 * TMPL - No more demos
 */
?>
<script type="text/template" id="tmpl-colorway-site-api-request-failed">
    <div class="no-themes">
    <?php
    /* translators: %1$s & %2$s are a Demo API URL */
    printf(__('<p> It seems the demo data server, <i><a href="%1$s">%2$s</a></i> is unreachable from your site.</p>', 'colorway-sites'), esc_url(Colorway_Sites::$api_url), esc_url(Colorway_Sites::$api_url));

    _e('<p class="left-margin"> 1. Sometimes, simple page reload fixes any temporary issues. No kidding!</p>', 'colorway-sites');

    _e('<p class="left-margin"> 2. If that does not work, you will need to talk to your server administrator and check if demo server is being blocked by the firewall!</p>', 'colorway-sites');

    /* translators: %1$s is a support link */
    printf(__('<p>If that does not help, please open up a <a href="%1$s" target="_blank">Support Ticket</a> and we will be glad take a closer look for you.</p>', 'colorway-sites'), esc_url('https://wpcolorway.com/support/?utm_source=demo-import-panel&utm_campaign=colorway-sites&utm_medium=api-request-failed'));
    ?>
    </div>
</script>

<?php
/**
 * TMPL - Filters
 */
?>
<script type="text/template" id="tmpl-colorway-site-filters">

    <# if ( data ) { #>

    <ul class="{{ data.args.wrapper_class }} {{ data.args.class }}">

    <# if ( data.args.show_all ) { #>
    <li>
    <a href="#" data-group="all"> All </a>
    </li>
    <# } #>

    <# for ( key in data.items ) { #>
    <# if ( data.items[ key ].count ) { #>
    <li>
    <a href="#" data-group='{{ data.items[ key ].id }}' class="{{ data.items[ key ].name }}">
    {{ data.items[ key ].name }}
    </a>
    </li>
    <# } #>
    <# } #>

    </ul>
    <# } #>
</script>

<?php
/**
 * TMPL - List
 */
?>
<script type="text/template" id="tmpl-colorway-sites-list">
    <# 
    //console.log(data.items[0]['_embedded']['wp:featuredmedia'][0]['source_url']);
    //console.log(data.items[2]['acf']['about_site_content']);   
    if ( data.items.length ) { #>
    <# for ( key in data.items ) { #>  
    <#  var sitedemotype = data.items[ key ]['acf']['colorway-site-type'];    
    if(sitedemotype === 'premium'){ #>
    <?php if (get_option('colorway-sites_license_key') != '') { ?>
        <# sitedemotype = 'licensed'; #>
    <?php } else { ?>
        <# sitedemotype = data.items[ key ]['acf']['colorway-site-type']; #>
    <?php } ?>
    <# } #>

    <div class="theme colorway-theme site-single {{ data.items[ key ].status }}" tabindex="0" aria-describedby="colorway-theme-action colorway-theme-name"
    data-demo-id="{{{ data.items[ key ].id }}}"
    data-demo-type="{{{ sitedemotype }}}"
    data-demo-url="{{{ data.items[key]['acf']['live_demo'] }}}"
    data-demo-api="{{{ data.items[ key ]['_links']['self'][0]['href'] }}}"
    data-demo-name="{{{  data.items[ key ].title.rendered }}}"
    data-demo-slug="{{{  data.items[ key ].slug }}}"
    data-screenshot="{{{ data.items[key]['_embedded']['wp:featuredmedia'][0]['source_url']}}}"
    data-content="{{{ data.items[ key ]['acf']['about_site_content'] }}}"
    data-required-plugins="{{ JSON.stringify( data.items[key]['acf']['required-plugins'] ) }}"				
    data-groups=["{{ data.items[ key ].tags }}"]>
    <input type="hidden" class="colorway-site-options" value="{{ JSON.stringify(data.items[ key ]['colorway-site-options-data'] ) }}" />
    <input type="hidden" class="colorway-enabled-extensions" value="{{ JSON.stringify(data.items[ key ]['colorway-enabled-extensions'] ) }}" />

    <div class="inner">
    <span class="site-preview" data-href="{{ data.items[ key ]['colorway-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="{{ data.items[ key ].title.rendered }}">
    <div class="theme-screenshot">
    <# if( '' !== data.items[ key ]['acf']['featured-image-url'] ) { #>
    <img src="{{ data.items[key]['_embedded']['wp:featuredmedia'][0]['source_url'] }}" />
    <# } #>
    </div>
    </span>
    <span class="more-details"> <?php esc_html_e('Details &amp; Preview', 'colorway-sites'); ?> </span>
    <# if ( data.items[ key ]['acf']['colorway-site-type'] ) { #>
    <# var type = ( data.items[ key ]['acf']['colorway-site-type'] !== 'premium' ) ? ( data.items[ key ]['acf']['colorway-site-type'] ) : 'premium'; #>
    <span class="site-type {{data.items[ key ]['acf']['colorway-site-type']}}">{{ type }}</span>
    <# } #>
    <# if ( data.items[ key ].status ) { #>
    <span class="status {{data.items[ key ].status}}">{{data.items[ key ].status}}</span>
    <# } #>
    <div class="theme-id-container">
    <h3 class="theme-name" id="colorway-theme-name"> {{{ data.items[ key ].title.rendered }}} </h3>
    <div class="theme-actions">
    <button class="button preview install-theme-preview"><?php esc_html_e('Preview', 'colorway-sites'); ?></button>
    </div>
    </div>
    </div>
    </div>
    <# } #>
    <# } else { #>
    <p class="no-themes" style="display:block;">
    <?php _e('No Demos found, Try a different search.', 'colorway-sites'); ?>
    <span class="description">
    <?php
    /* translators: %1$s External Link */
    printf(__('Don\'t see a site that you would like to import?<br><a target="_blank" href="%1$s">Please suggest us!</a>', 'colorway-sites'), esc_url('https://www.inkthemes.com/feedback/'));
    ?>
    </span>
    </p>
    <# } #>
</script>

<?php
/**
 * TMPL - List
 */
?>
<script type="text/template" id="tmpl-colorway-sites-suggestions">
    <div class="theme colorway-theme site-single colorway-sites-suggestions">
    <a target="_blank" href="https://forms.pabbly.com/form/share/IIdE-401322">
    <div class="inner" style="background-image: url(https://lh3.googleusercontent.com/-jIPEQVsn9Rk/XRXQRAcp-aI/AAAAAAAAcAc/8cwEfDvpPusKjVHHO8q6L6U7jHm2fNxCgCK8BGAs/s0/2019-06-28.png)">
<!--    <button type="button" class="btn btn-primary">Click here for Customization</button>-->
    </div>
    </a>
    </div>
</script>
<?php
wp_print_admin_notice_templates();
