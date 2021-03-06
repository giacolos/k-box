<?php

return [

    /*
    |--------------------------------------------------------------------------
    | K-Link DMS Version (aka Application version)
    |--------------------------------------------------------------------------
     */
    'version' => 'BUILDVERSION',
    
    'build' => 'BUILDCODE',
    
    /*
    |--------------------------------------------------------------------------
    | K-Link DMS Edition. Used when upgrading for a version to a new version
    |--------------------------------------------------------------------------
     */
    'edition' => 'project',

    /*
    |--------------------------------------------------------------------------
    | DMS Identifier
    |--------------------------------------------------------------------------
    |
    | The unique identifier for the DMS instance
    |
    | @var string
    */

    'identifier' => getenv('DMS_IDENTIFIER') ?: '4815162342',

    /*
    |--------------------------------------------------------------------------
    | Institution Identifier
    |--------------------------------------------------------------------------
    |
    | The institution identifier that is required for communicating with the
    | K-Link Core
    |
    | @var string
    */

    'institutionID' => getenv('DMS_INSTITUTION_IDENTIFIER') ?: 'DMS',

    /*
    |--------------------------------------------------------------------------
    | Guest public searches
    |--------------------------------------------------------------------------
    |
    | Tell if the DMS will allow guest user to perform public search over K-Link
    |
    | @var boolean
    */
    'are_guest_public_search_enabled' => getenv('DMS_ARE_GUEST_PUBLIC_SEARCH_ENABLED') ?: true,

    'core' => [

        /*
        |--------------------------------------------------------------------------
        | K-Link K-Search URL
        |--------------------------------------------------------------------------
        |
        | The url of the Private K-Search
        |
        | @var string
        */

        'address' => getenv('DMS_CORE_ADDRESS') ?: null,

    ],

    /*
    |--------------------------------------------------------------------------
    | Enable user activity tracking
    |--------------------------------------------------------------------------
    |
    | Enable or Disable the user activity tracking
    | K-Link Core
    |
    | default: true
    |
    | @var boolean
    */

    'enable_activity_tracking' => ! ! getenv('DMS_ENABLE_ACTIVITY_TRACKING') ?: true,

    /*
    |--------------------------------------------------------------------------
    | Number of items to display per page
    |--------------------------------------------------------------------------
    |
    |
    | default: 12
    |
    | @var integer
    */

    'items_per_page' => getenv('DMS_ITEMS_PER_PAGE') ?: 12,

    /*
    |--------------------------------------------------------------------------
    | Upload folder
    |--------------------------------------------------------------------------
    |
    | Where the files will be uploaded
    |
    | default: /storage/uploads
    |
    | @var string
    */

    'upload_folder' => getenv('DMS_UPLOAD_FOLDER') ?: storage_path('documents/'),

    /*
    |--------------------------------------------------------------------------
    | File Upload Maximum size (in KB)
    |--------------------------------------------------------------------------
    |
    | The maximum size of the file allowed for upload in kilobytes
    |
    | default: 30000
    |
    | @var integer
    */

    'max_upload_size' => getenv('DMS_MAX_UPLOAD_SIZE') ?: 204800,

    /*
    |--------------------------------------------------------------------------
    | Allowed File Upload types
    |--------------------------------------------------------------------------
    |
    | A comma separated list of allowed file types to be uploaded
    |
    | default: docx,doc,xlsx,xls,pptx,ppt,pdf,txt,jpg,gif,png,odt,odp,ods
    |
    | @var string
    */

    'allowed_file_types' => getenv('DMS_ALLOWED_FILE_TYPES') ?: 'docx,doc,xlsx,xls,pptx,ppt,pdf,txt,jpg,gif,png,odt,odp,ods,md,txt,rtf,kmz,kml,gdoc,gslides,gsheet',

    /*
    |--------------------------------------------------------------------------
    | Use HTTPS as default url schema
    |--------------------------------------------------------------------------
    |
    | Use HTTPS for serving the pages
    |
    | default: true
    |
    | @var boolean
    */

    'use_https' => getenv('DMS_USE_HTTPS') ?: true,
    
    /*
    |--------------------------------------------------------------------------
    | Support Widget Token
    |--------------------------------------------------------------------------
    |
    | The UserVoice support key
    |
    | default: null, support is not configured and not enabled
    |
    | @var string
    */
    
    'support_token' => getenv('SUPPORT_TOKEN') ?: null,
    
    
    /*
    |--------------------------------------------------------------------------
    | Limit languages filters to
    |--------------------------------------------------------------------------
    |
    | Use this option to limit the usable language filters
    |
    | default: false
    | acceptable value: array of comma separated language codes e.g. en,ru,de
    |
    | @var boolean|string
    */
    
    'limit_languages_to' => getenv('DMS_LIMIT_LANGUAGES_TO') ?: 'en,de,it,fr,ky,ru',
    
    /*
    |--------------------------------------------------------------------------
    | Recent section personalization
    |--------------------------------------------------------------------------
    */

    'recent' => [

        /*
        |--------------------------------------------------------------------------
        | The maximum number of elements in the recent list
        |--------------------------------------------------------------------------
        |
        | Use this option to limit the number of documents that can be showed
        | in the recent documents
        |
        | default: 1000
        |
        | @var int
        */

        'limit' => getenv('DMS_RECENT_LIMIT') ?: 1000,

        /*
        |--------------------------------------------------------------------------
        | The maximum number of weeks to consider a document recent
        |--------------------------------------------------------------------------
        |
        | Use this option to limit how old a document could be in order to be
        | considered a recent document.
        | Unit: weeks. A document updated more than 3 weeks before the current date
        | will not be considered recent
        |
        | default: 3 weeks
        |
        | @var int
        */

        'time_limit' => getenv('DMS_RECENT_TIMELIMIT') ?: 3,

    ],
];
