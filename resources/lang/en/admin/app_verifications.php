<?php

return [

'page_title'		=> [
	'index'			=> 'App Verifications',
	'verify'		=> 'Verify App',
	'verify_x'		=> 'Verify :x',

	'view_mode'			=> [
		'all'	=> 'Viewing all apps needing verification',
		'prodi'	=> 'Viewing app verifications list in your study program - :x',
	],
],

'verifications_list'	=> 'App Verifications List',
'no_app_verifications_yet'	=> 'No apps need verifications yet',
'review'				=> 'Review',
'review_app'			=> 'Review App',
'verify'				=> 'Verify',
'verification_history'	=> 'Verification History',
'app_versions'			=> 'App Versions',
'version_x'				=> 'Version :x',
'current'				=> 'current',
'active'				=> 'active',
'reviewed_versions'		=> 'Reviewed versions',
'versions_verified'		=> 'Versions verified',
'verified'				=> 'verified',
'comments'				=> 'Comments',
'other_comments'		=> 'Other comments',
'view_this_item'		=> 'View this item',
'edit_this_item'		=> 'Edit this item',
'view_this_verification'	=> 'View this verification',
'verification_view'		=> 'Verification Details',
'verification_comment'	=> 'Verification Comment',
'look_below'			=> 'look below',
'review_anyway'			=> 'Review anyway',
'last_verification'		=> 'Last verification',
'review_public_page'	=> 'Review public page',

'base_version'			=> 'Base version',
'related_version'		=> 'Related version',
'related_versions'		=> 'Related versions',
'discarded_versions'	=> 'Discarded versions',
'previous_version'		=> 'Previous version',
'this_form_shows_the_app\'s_pending_changes'	=> 'This form shows data from the app\'s pending changes.',
'this_app_does_not_have_any_pending_changes_to_be_reviewed'	=> 'This app does not have any pending changes to be reviewed.',
'this_form_shows_version_x_the_app\'s_current_version'	=> 'This form shows data from Version :x, which is the app\'s version with no changes applied yet.',
'you_are_editing_the_last_verification'	=> 'You are editing the last verification.',

'you_can_add_comments_to_any_related_fields_by_clicking_the_icon'	=> 'You can add comments to any field by clicking the comment icon next to it',

'new_item_submitted'	=> 'New item submitted',
'item_edited'			=> 'Item edited',
'item_deleted'			=> 'Item deleted',
'pending_changes_applied'	=> 'Pending data applied',
'item_published'		=> 'Item published',
'changes_verified'		=> 'Changes verified',
'auto_discard_unapplied_changes'	=> 'Automatic discard of any pending/unapplied changes during version switch',
'switch_version_from_x_to_y'	=> 'Version switch: from Version :x to Version :y',
'new_version_x'			=> 'New version: Version :x',
'this_app_was_unlisted_because_of_reports'	=> 'This app was unlisted because of reports of inappropriate content',
'this_app_was_recently_found_guilty_for_inappropriate_content'	=> 'This app was recently found guilty for having inappropriate contents.',
'please_make_sure_the_offending_contents_have_been_removed'	=> 'Please make sure the offending contents have been removed.',

'titles'		=> [
	'verification'					=> 'Verification',
	'edit_verification'				=> 'Edit Verification',
	'editing_last_verification'		=> 'Editing Last Verification',
	'version_info'					=> 'Version Info',
],

'fields'		=> [
	'attribute_comment'				=> 'Attribute comment',
	'add_comment_to_this_data'		=> 'Comment on this attribute',
	'comment_placeholder'			=> 'Comment...',
	'overall_comments'				=> 'Verification Notes',
	'overall_comments_hint'			=> 'Comments on presented data overall, or additional notes',
	'verification_status'			=> 'Verification Status',
	'verification_result'			=> 'Verification Result',
],

'status'		=> [
	'approved_consequence'			=> 'The app owner will be able to publish any proposed changes.',
	'approved_reported_consequence'	=> 'The existing ban/block for the app will be lifted as well.',
	'rejected_consequence'			=> 'All the proposed changes will be rejected and dropped, and the app owner will have to propose different changes.',
	'revision-needed_consequence'	=> 'The app as well as the proposed changes will be unaffected. Choose this to communicate with the app owner about additional, necessary changes.',
],

'messages'		=> [
	'verify_successful'		=> 'App verification has been saved.',
	'verify_failed'			=> 'Failed to verify app.',
	'verify_edited'			=> 'The selected verification has been revised.',

	'cannot_load_verification_view'	=> 'Error occurred while trying to load verification details.',

	'this_unverified_item_is_new'	=> 'This unverified app is a new item.',

	'app_verification_after_approved'			=> 'The app\'s proposed changes have been approved!',
	'app_verification_after_rejected'			=> 'The app\'s proposed changes have been rejected and dropped.',
	'app_verification_after_revision-needed '	=> 'The app and its proposed changes were unaffected. Wait for further changes by the app\'s owner.',
],

'status_unverified'		=> 'Unverified',
'status_verified'		=> 'Verified',

];
