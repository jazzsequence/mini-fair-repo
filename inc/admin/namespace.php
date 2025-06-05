<?php

namespace MiniFAIR\Admin;

use MiniFAIR;
use MiniFAIR\PLC\DID;

const PAGE_SLUG = 'minifair';

function bootstrap() {
	// Register the admin menu and page before the PLC DID post type is registered.
	add_action( 'admin_menu', __NAMESPACE__ . '\\add_admin_menu', 9 );
}

function add_admin_menu() {
	// add top level page
	$hook = add_menu_page(
		__( 'Mini FAIR', 'minifair' ),
		__( 'Mini FAIR', 'minifair' ),
		'manage_options',
		PAGE_SLUG,
		__NAMESPACE__ . '\\render_settings_page'
	);
	add_action( 'load-' . $hook, __NAMESPACE__ . '\\load_settings_page' );
}

function load_settings_page() {
}

function render_settings_page() {
	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'minifair' ) );
	}

	$providers = MiniFAIR\get_providers();
	$packages = MiniFAIR\get_available_packages();

	$invalid = [];
	foreach ( $providers as $provider ) {
		$invalid = array_merge( $invalid, $provider->get_invalid() );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mini FAIR', 'minifair' ); ?></h1>

		<p><?php
			printf(
				__( 'Mini FAIR is active on your site. View your active packages at <a href="%1$s"><code>%1$s</code></a>', 'minifair' ),
				rest_url( '/minifair/v1/packages' )
			);
		?></p>

		<h2><?php esc_html_e( 'Active Packages', 'minifair' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Package ID', 'minifair' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Name', 'minifair' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $packages as $package_id ) : ?>
					<tr>
						<?php
						$did = DID::get( $package_id );
						if ( ! $did ) {
							continue;
						}
						$data = MiniFAIR\get_package_metadata( $did );
						?>
						<td><code><?php echo esc_html( $package_id ); ?></code></td>
						<td><?php echo esc_html( $data->name ); ?></td>
					</tr>
				<?php endforeach; ?>
		</table>

		<?php if ( ! empty( $invalid ) ) : ?>
			<h2><?php esc_html_e( 'Invalid Packages', 'minifair' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Package ID', 'minifair' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Error', 'minifair' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $invalid as $id => $error ) : ?>
						<tr>
							<td><code><?php echo esc_html( $id ); ?></code></td>
							<td><?php echo esc_html( $error->get_error_message() ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
