<?php
/*
 * Use regex to implement wildcards in fieldnames
 */

$drupalauto = array(
	'uid',
	'nid',
	'eid',
	'entity_id',
	'gid',
	'vid',
	'tid',
	'env_id',
	'fid',
	'rid',
	'sid',
	);

$drupalcache = array(
	'cache',
	'cache_(.*)',
	);

$drupal6content = array(
	'accesslog',
	'apachesolr_(.*)',
	'authmap',
	'comments',
	'content_access',
	'content_complete',
	'content_field(.*)',
	//'content_node_field(.*)',
	'content_type(.*)',
	'facebook_status(.*)',
	'fbsmp(.*)',
	'fbss_comments',
	'files',
	'flag_content',
	'history',
	'legal_accepted',
	'media_youtube_node_data',
	'node',
	'node_access',
	'node_comment_statistics',
	'node_counter',
	'node_import_tasks',
	'node_revisions',
	'notifications(.*)',
	'og(.*)',
	'panels_node',
	'pm_(.*)',
	'profile_values',
	'realname',
	'search_(.*)',
	'serial_organisatie_field_id_organisation',
	'sessions',
	'statspro(.*)',
	'taxonomy_(.*)',
	'term_(.*)',
	'users(.*)',
	'url_alias',
	'uuid_(.*)',
	'vocabulary(.*)',
	'watchdog',
);
$drupal7content = array(
	'field_collection(.*)',
	'field_data(.*)',
	'field_revision(.*)',
	'file(.*)',
	'node',
	'node_access',
	'node_comment_statistics',
	'node_revision',
	'og_membership',
	'og_users_roles',
	'realname',
	'sessions',
	'taxonomy(.*)',
	'users',
	'users_roles',
	'webform_submissions',
	'webform_submitted_data',
);
