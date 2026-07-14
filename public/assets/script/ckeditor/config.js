/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	 config.language = 'fa';
	// config.uiColor = '#AADC6E';
    // config.skin = 'moonocolor';
     config.extraPlugins = 'autogrow,html5video,toc';
    config.skin = 'office2013';

    // Route EVERY editor's image "Upload" tab through the Spatie-backed endpoint
    // (CkeditorController -> Ckeditor model -> storage/app/public/ckeditor). Applied
    // globally here so all instances get it without per-view config. Instances that
    // set their own filebrowserUploadUrl still override this. Token read from the
    // page's csrf-token meta tag (present on every dashboard page).
    var __csrf = document.querySelector('meta[name="csrf-token"]');
    config.filebrowserUploadUrl = '/dashboard/ckeditor/upload?_token=' + (__csrf ? __csrf.getAttribute('content') : '');
    config.filebrowserUploadMethod = 'form';

};
