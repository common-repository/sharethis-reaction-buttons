<?php
/**
 * Instantiates the Sharethis Reaction Buttons plugin
 *
 * @package SharethisReactionButtons
 */

namespace SharethisReactionButtons;

global $sharethis_reaction_buttons_plugin;

require_once __DIR__ . '/php/class-plugin-base.php';
require_once __DIR__ . '/php/class-plugin.php';

$sharethis_reaction_buttons_plugin = new Plugin();

/**
 * Sharethis Reaction Buttons Plugin Instance
 *
 * @return Plugin
 */
function sharethis_reaction_get_plugin_instance() {
	global $sharethis_reaction_buttons_plugin;
	return $sharethis_reaction_buttons_plugin;
}
