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
	'flag_counts',
	'history',
	'legal_accepted',
	'media_youtube_node_data',
	'messaging_store',
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
	'poll(.*)',
	'profile_values',
	'realname',
	'search_(.*)',
	'serial_organisatie_field_id_organisation',
	'sessions',
	'signup(.*)',
	'statspro(.*)',
	'taxonomy_(.*)',
	'term_(.*)',
	'users(.*)',
	'url_alias',
	'uuid_(.*)',
	'visitors',
	'vocabulary(.*)',
	'watchdog',
	'webform_emails',
	'webform_submissions',
	'webform_submitted_data',
	'xmlsitemap',
	'xmlsitemap_node',
);
$drupal7content = array(
	'entityform',
	'feeds_item',
	'feeds_log',
	'flag_counts',
	'flagging',
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
