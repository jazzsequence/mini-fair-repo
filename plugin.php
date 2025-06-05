<?php
/**
 * Plugin Name: Mini FAIR
 * Author: Contributors
 */

namespace MiniFAIR;

require __DIR__ . '/inc/namespace.php';
require __DIR__ . '/inc/admin/namespace.php';
require __DIR__ . '/inc/api/namespace.php';
require __DIR__ . '/inc/git-updater/namespace.php';
require __DIR__ . '/inc/keys/namespace.php';
require __DIR__ . '/inc/plc/namespace.php';
require __DIR__ . '/inc/plc/util.php';
require __DIR__ . '/vendor/autoload.php';

bootstrap();
