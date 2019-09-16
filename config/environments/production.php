<?php
/**
 * Configuration overrides for WP_ENV === 'production'
 */

use Roots\WPConfig\Config;

Config::define('AS3CF_SETTINGS', serialize([
  // Storage Provider ('aws', 'do', 'gcp')
  'provider' => 'aws',
  // Use IAM Roles on Amazon Elastic Compute Cloud (EC2) or Google Compute Engine (GCE)
  'use-server-roles' => true,
  // Bucket to upload files to
  'bucket' => 'static.babypad.jp',
  // Bucket region (e.g. 'us-west-1' - leave blank for default region)
  'region' => 'ap-northeast-1',
  // Automatically copy files to bucket on upload
  'copy-to-s3' => true,
  // Rewrite file URLs to bucket
  'serve-from-s3' => true,
  // Bucket URL format to use ('path', 'cloudfront')
  'domain' => 'cloudfront',
  // Custom domain if 'domain' set to 'cloudfront'
  'cloudfront' => 'static.babypad.jp',
  // Enable object prefix, useful if you use your bucket for other files
  'enable-object-prefix' => true,
  // Object prefix to use if 'enable-object-prefix' is 'true'
  'object-prefix' => 'BASE/uploads/',
  // Organize bucket files into YYYY/MM directories
  'use-yearmonth-folders' => true,
  // Serve files over HTTPS
  'force-https' => true,
  // Remove the local file version once offloaded to bucket
  'remove-local-file' => true,
  // Append a timestamped folder to path of files offloaded to bucket
  'object-versioning' => true,
]));
