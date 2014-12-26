<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'wp_site_url' => 'http://test.artmedia.ee/silmaring_wp/navigations_for_backend.php',
    'wp_site_token' => '610b98e423b0a2d91c00ada37df3a26f1fbf55342bd3e81d56364227cccaa897',
    'google_api_key' => 'AIzaSyDQKydQ5MNYnpDGkjmoOZMGzbcI6CWV2-I',
    'redirect_after_login' => 'dashboard',
    'default_role_social_login' => 'v_student',
    'static_salt' => 'ASC3EgfeGi433FiXHJj',
    'profile_image_dir' => './data/tmpuploads/profile/images/',
    'files_dir' => 'data/uploads',
    'files_public_dir' => '/files',
    'school_classes' => 12,
    'school_classes_letters' => array('a', 'b', 'c', 'd'),
    'file_types' => 'doc,docx,xls,xlsx,pdf,txt,odt,rtf,ppt,odp,ods',
    'image_file_types' => 'jpg,jpeg,png,gif',
    'image_mime_types' => 'image/jpg,image/jpeg,image/gif,image/png',
    'file_mime_types' => 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-office,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf,text/plain,application/vnd.oasis.opendocument.text,application/rtf,application/vnd.ms-powerpoint,application/vnd.oasis.opendocument.presentation,application/vnd.oasis.opendocument.spreadsheet',
    'max_image_width' => 3000,
    'max_image_height' => 3000,
);
