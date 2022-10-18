<?php

return [

'_self'			=> 'App',

'page_title'	=> [
	'index'				=> 'Apps List',
	'detail'			=> 'View & Manage App',
	'add'				=> 'Add a New App',
	'edit'				=> 'Edit App',
	'visuals'			=> 'Edit Visuals',
	'changes'			=> 'App Changes History',
	'verifications'		=> 'App Verifications History',
	'review_changes'	=> 'Review Changes',
	'publish_changes'	=> 'Publish Changes',
	'publish_app'		=> 'Publish App',

	'view_mode'			=> [
		'all'	=> 'Viewing all apps in the system',
		'prodi'	=> 'Viewing your apps and apps in your study program - :x',
		'owned'	=> 'Viewing your apps',
	],
],

'tab_title'		=> [
	'detail'			=> 'Manage :x',
	'edit'				=> 'Edit App - :x',
	'visuals'			=> 'Edit Visuals - :x',
	'changes'			=> 'Changes History - :x',
	'verifications'		=> 'Verifications History - :x',
	'review_changes'	=> 'Review Changes - :x',
	'publish_changes'	=> 'Publish Changes - :x',
	'publish_app'		=> 'Publish App - :x',
],

'titles'		=> [
	'app_info'		=> 'App Info',
],

'apps_list'				=> 'List of Apps',
'verified_apps'			=> 'Verified Apps',
'app_submissions'		=> 'App Submissions',
'submit_an_app'			=> 'Add a product',
'submit_app'			=> 'Submit app',
'resubmit_app'			=> 'Resubmit app',
'edit_app_info'			=> 'Edit app info',
'edit_visuals'			=> 'Edit visual media',
'show_pending_changes'	=> 'Show pending changes',
'changelog'				=> 'View item history',
'history'				=> 'History',
'view_changes_history'	=> 'Changes history',
'view_verifications'	=> 'View verifications',
'max_visuals'			=> 'Maximum number of visual media',
'add_more_visuals'		=> 'Add more visual media',
'view_public_page'		=> 'View app public page',
'verification'			=> 'Verification',
'verifications'			=> 'Verifications',
'this_new_item_is_waiting_verification'	=> 'This item is new and awaits verification',
'app_had_been_verified'	=> 'This app had been verified and is publicly listed',
'app_has_pending_changes'	=> 'This app has pending changes waiting to be reviewed',
'app_has_approved_changes'	=> 'This app\'s has changes which has been reviewed and approved',
'app_has_rejected_changes'	=> 'This app\'s last changes was rejected',
'app_changes_needs_revision_to_be_approved'	=> 'This app needs revision to be approved',
'app_is_public'				=> 'App is public',
'app_is_not_public'			=> 'App is not public',
'app_was_reported'			=> 'App was reported',
'your_app\'s_edits_version_x_has_been_approved'	=> 'Your app\'s edits (up to Version :x) has been approved! <br>You can now publish your edits.',
'publish_edits'			=> 'Publish edits now',
'compare'				=> 'Compare',
'view_your_app'			=> 'View your app',
'back_to_app_details'	=> 'Back to app details',
'no_app_submissions_yet'	=> 'There are\'nt any app submissions yet',
'no_apps_matches'			=> 'No apps match your search',
'you_own_this_app'			=> 'You own this app',
'app_is_private'		=> 'App is private',
'app_is_not_private'	=> 'App is not private',

'man_pan'		=> [
	'title'					=> 'Management Panel',

	'app_is_private'		=> 'App is private (hidden from public list, only owner can see)',
	'app_is_not_private'	=> 'App is not private (publicly listed)',
	'make_private'			=> 'Make this app private (i.e hidden, just for you): :name ?',
	'make_not_private'		=> 'Make this app not private (i.e publicly visible): :name ?',

	'app_is_published'		=> 'App is published (its data have been verified)',
	'app_is_not_published'	=> 'App is not published',
	'make_published'		=> 'Publish this app, and make it possible to show publicly: :name ?',
	'make_not_published'	=> 'Unpublish this app, and prevent it from showing up in the public listing: :name ?',
],

'status'	=> [
	'is_published'		=> 'Published',
	'is_private'		=> 'Private',
	'is_reported'		=> 'Reported',
	'is_verified'		=> 'Verified',
	'is_unverified'		=> 'Unverified',
	'my_apps'			=> 'My apps',
	'other_apps'		=> 'Other apps',
],

'fields'	=> [
	'owner'						=> 'Owner',
	'name'						=> 'Name',
	'name_placeholder'			=> 'App name',
	'short_name'				=> 'Short Name',
	'short_name_placeholder'	=> 'Easy-to-remember short name',
	'has_short_name?'			=> 'Has a short name?',
	'description'				=> 'Description',
	'description_placeholder'	=> 'Extensive description of the app',
	'url'						=> 'URL',
	'url_placeholder'			=> 'https://.....',
	'url_hint'					=> 'Link to the app\'s website or social media, or etc.',
	'logo'						=> 'Logo',
	'logo_hint'					=> 'Logo recommendations: <br>JPG type, aspect ratio of 1:1, size up to 300x300 pixels <br>Max file size of 2MB',
	'current_logo'				=> 'Current Logo',
	'remove_logo?'				=> 'Remove logo?',
	'change_logo'				=> 'Change logo',
	'visuals'					=> 'Visual Media',
	'categories'				=> 'Categories',
	'categories_placeholder'	=> 'Categories the app belongs to',
	'tags'						=> 'Tags',
	'tags_hint'					=> 'You can add your own tag by typing it in, items are separated with a space or comma',
	'tags_placeholder'			=> 'App topics',
	'status'					=> 'Status',
	'order'						=> 'Order',
	'caption'					=> 'Caption',
	'caption_placeholder'		=> 'Visual media caption...',
	'upload_image'				=> 'Upload image(s) here',
	'upload_image_hint'			=> 'Recommended image types: PNG or JPG<br>Other types will be converted into JPG',
	'or_add_other_visual_types'	=> '...or add other visual media types',
	'choose_other_visuals_type'			=> 'Choose type',
	'visuals_other_value_placeholder'	=> 'Visual media',
	'submission_status'			=> 'Submission Status',
	'is_published'				=> 'Published',
	'is_private'				=> 'Is Private?',
	'filter_is_owned'			=> 'Whose Apps',
],

'changes'	=> [
	'there_are_no_changes_yet'		=> 'There are no changes yet...',
	'version'						=> 'Version',
	'version_x'						=> 'Version :x',
	'x_changes_in_this_version'		=> 'There are :x change(s) in this version',
	'there_are_x_changes'			=> ':x thing(s) were changed',
	'new_item'						=> 'Item creation',
	'visuals_comparison'			=> 'Visuals comparison',
	'cannot_load_visuals_comparison'	=> 'A problem occurred while loading visuals comparison',
	'old_value'						=> 'old value',
	'new_value'						=> 'new value',
	'old_logo'						=> 'old logo',
	'new_logo'						=> 'new logo',
	'is_current_version'			=> 'This is the current version',
	'pending'						=> 'pending changes',
	'pending_changes'				=> 'Pending changes',
	'list_of_pending_changes'		=> 'List of pending changes',
	'_compared_to_current_version_x'	=> 'compared to the current version (:x)',
	'no_pending_changes'			=> 'No pending changes',
	'no_versions_found'				=> 'No versions found',
	'view_this_version'				=> 'Full view of this version',
	'version_preview'				=> 'App Version Preview',
	'pending_changes_view'			=> 'App Pending Changes View',
	'show_current_version'			=> 'Look at the app\'s current version',
	'based_on'						=> 'Based on',
	'this_version_is_based_on'		=> 'This version is based on',
	'summary_of_changes'			=> 'Summary of changes',
	'detailed_information_on_the_changes'	=> 'Detailed information on the changes',
	'version_status'				=> 'Version status',
	'version_x_status'				=> 'Version :x status',
	'publish_changes_now'			=> 'Publish changes now',
	'publish_item'					=> 'Publish item',
	'apply_changes_without_publishing'	=> 'Apply changes without publishing',
	'cannot_load_version_preview'	=> 'Error occurred while trying to load version preview',

	'statuses'	=> [
		'pending'	=> 'Pending',
		'rejected'	=> 'Rejected',
		'approved'	=> 'Approved',
		'committed'	=> 'Applied',
	],
],

'visuals'	=> [
	'types'	=> [
		'logo'		=> 'Logo',
		'image'		=> 'Image',
		'video'		=> 'Video',
		'video_youtube'	=> 'YouTube video',
	],
	'meta'	=> [
		'dimensions'	=> 'Dimension',
		'size'			=> 'File size',
		'extension'		=> 'File extension',
		'youtube_id'	=> 'YouTube ID',
		'url'			=> 'URL',
	],
	'new_visuals'				=> 'New visuals',
	'old_visuals'				=> 'Old visuals',
	'visual_comparison_detail'	=> 'Visual media comparison',
	'no_visuals'				=> 'No visual media',
],

'messages'	=> [
	'create_successful'			=> 'A new app has been created!',
	'create_successful_pending'	=> 'Your new app will have to wait for verification first before it goes public.',
	'create_failed'				=> 'An error occurred while creating new app.',
	'update_successful'			=> 'App was updated!',
	'update_successful_pending'	=> 'Your edits have been submitted for approval.',
	'update_failed'				=> 'An error occurred while submitting app data.',
	'delete_successful'			=> 'Successfully deleted the app!',
	'delete_failed'				=> 'An error occurred while trying to delete the app.',

	'form_showing_pending_changes'			=> 'This form shows your pending changes',
	'new_items_will_be_staged'				=> 'Apps will not become public immediately, and has to go through verification first',
	'edits_will_be_staged'					=> 'Any edits will be staged for verification and won\'t take immediate effect',

	'app_was_unlisted_for_inappropriate_contents'	=> 'This app had been unlisted for having inappropriate content.',
	'to_unblock_app_please_remove_inappropriate_contents'	=> 'To unblock this app, please remove the inappropriate contents and wait for further verification processes.',
	'app_ban_will_be_lifted_after_publish'	=> 'The ban/block on the app will be removed after publishing the approved changes.',

	'last_verification_revision-needed'		=> 'Your pending changes need some adjustments to be approved.',
	'last_verification_rejected'			=> 'The pending changes you requested were rejected.',
	'last_verification_approved'			=> "Your pending changes had been approved! \nYou can finalize the edits and make it public now.",
	'check_verification_details'			=> 'Check verification details',
	'the_changelogs_data_are_corrupted'		=> "The changes cannot be applied because the data were corrupted. \nPlease contact the administrator to sort this problem out.",

	'congrats!'								=> 'Congrats!',
	'your_changes_have_been_published!'		=> 'Your changes have been published!',
	'your_changes_have_been_applied!'		=> 'Your changes have been applied!',

	'app_was_made_private'		=> 'App has been set to be private.',
	'app_was_made_not_private'	=> 'App has been set to publicly available.',
	'app_was_published'			=> 'App has been published.',
	'app_was_unpublished'		=> 'App has been unpublished.',
],

];
