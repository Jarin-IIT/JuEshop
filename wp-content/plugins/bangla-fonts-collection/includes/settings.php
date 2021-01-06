<?php
/* Bangla Fonts Settings Page */

class banglafonts_Settings_Page {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'bfc_create_settings' ) );
		add_action( 'admin_init', array( $this, 'bfc_setup_sections' ) );
		add_action( 'admin_init', array( $this, 'bfc_setup_fields' ) );
	}
	public function bfc_create_settings() {
		$page_title = __('Bangla Fonts Collection', 'banglafontscol');
		$menu_title = __('Bangla Fonts', 'banglafontscol');
		$capability = 'manage_options';
		$slug = 'banglafonts';
        $callback = array($this, 'bfc_settings_content');
        $icon = 'dashicons-translation';
		$position = 60;
		add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);
	
	}
	public function bfc_settings_content() { ?>
		<div class="wrap">
			<h1><?php _e('Bangla Fonts Collection','banglafontscol') ?></h1>
			<?php settings_errors(); ?>
			<form method="POST" action="options.php">
				<?php
					settings_fields( 'banglafonts' );
					do_settings_sections( 'banglafonts' );
					submit_button();
				?>
			</form>
			<div class="samplecode">
			<h4><?php _e('Sample CSS selector to set font','banglafontscol') ?></h4>
			<code>
			body{
				font-family: AdorshoLipi, arial, sans-serif !important;
			}
			</code>
			</div>
			<div class="fontlist">
			<h4><?php _e('Useable fonts list','banglafontscol') ?></h4>
			<dl>
				<dt><?php _e('AdorshoLipi','banglafontscol')?></dt>
				<dd><code>font-family: AdorshoLipi, arial, sans-serif !important;</code></dd>
				<dt><?php _e('Kalpurush','banglafontscol')?></dt>
				<dd><code>font-family: Kalpurush, arial, sans-serif !important;</code></dd>
				<dt><?php _e('EkusheyLohit','banglafontscol')?></dt>
				<dd><code>font-family: EkusheyLohit, arial, sans-serif !important;</code></dd>
				<dt><?php _e('BenSen','banglafontscol')?></dt>
				<dd><code>font-family: BenSen, arial, sans-serif !important;</code></dd>
				<dt><?php _e('Bangla','banglafontscol')?></dt>
				<dd><code>font-family: Bangla, arial, sans-serif !important;</code></dd>
				<dt><?php _e('AponaLohit','banglafontscol')?></dt>
				<dd><code>font-family: AponaLohit, arial, sans-serif !important;</code></dd>
				<dt><?php _e('CharuChandanHardStroke','banglafontscol')?></dt>
				<dd><code>font-family: CharuChandanHardStroke, arial, sans-serif !important;</code></dd>
				<dt><?php _e('SolaimanLipi','banglafontscol')?></dt>
				<dd><code>font-family: SolaimanLipi, arial, sans-serif !important;</code></dd>
				<dt><?php _e('CharuChandan3D','banglafontscol')?></dt>
				<dd><code>font-family: CharuChandan3D, arial, sans-serif !important;</code></dd>
				<dt><?php _e('CharukolaUltraLight','banglafontscol')?></dt>
				<dd><code>font-family: CharukolaUltraLight, arial, sans-serif !important;</code></dd>
				<dt><?php _e('Mukti','banglafontscol')?></dt>
				<dd><code>font-family: Mukti, arial, sans-serif !important;</code></dd>
				<dt><?php _e('NotoSansBengali','banglafontscol')?></dt>
				<dd><code>font-family: 'NotoSansBengali', arial, sans-serif !important;</code></dd>
				
			</dl>
			</div>
		</div>
	
	<?php
	}
	public function bfc_setup_sections() {
		add_settings_section( 'banglafonts_section', __('Bangla Fonts Settings', 'banglafontscol'), array(), 'banglafonts' );
	}
	public function bfc_setup_fields() {
		$fields = array(
			array(
				'label' => __('CSS for your fonts', 'banglafontscol'),
				'id' => 'banglafontselectors',
				'type' => 'textarea',
				'section' => 'banglafonts_section',
			),
		);
		foreach( $fields as $field ){
			add_settings_field( $field['id'], $field['label'], array( $this, 'bfc_field_callback' ), 'banglafonts', $field['section'], $field );
			register_setting( 'banglafonts', $field['id'] );
		}
	}
	public function bfc_field_callback( $field ) {
		$value = get_option( $field['id'] );
		switch ( $field['type'] ) {
				case 'textarea':
				printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
					$field['id'],
					isset($field['placeholder']) ? $field['placeholder'] : '',
					$value
					);
					break;
			default:
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
					$field['id'],
					$field['type'],
					isset($field['placeholder']) ? $field['placeholder'] : '',
					$value
				);
		}
		if( $desc = isset($field['desc']) ? $field['desc'] : ''  ) {
			printf( '<p class="description">%s </p>', $desc );
		}
	}
}
new banglafonts_Settings_Page();