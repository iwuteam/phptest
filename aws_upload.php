<?php

function upload_file_to_s3( $post_id, $data = null, $file_path = null, $force_new_s3_client = false, $remove_local_files = true ) {
	$return_metadata = null;
	if ( is_null( $data ) ) {
		$data = wp_get_attachment_metadata( $post_id, true );
	} else {
		// As we have passed in the meta, return it later
		$return_metadata = $data;
	}
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	// Allow S3 upload to be hijacked / cancelled for any reason
	$pre = apply_filters( 'as3cf_pre_upload_attachment', false, $post_id, $data );
	if ( false !== $pre ) {
		if ( ! is_null( $return_metadata ) ) {
			// If the attachment metadata is supplied, return it
			return $data;
		}
		$error_msg = is_string( $pre ) ? $pre : __( 'Upload aborted by filter \'as3cf_pre_upload_attachment\'', 'amazon-s3-and-cloudfront' );
		return $this->return_upload_error( $error_msg );
	}
	if ( is_null( $file_path ) ) {
		$file_path = get_attached_file( $post_id, true );
	}
	// Check file exists locally before attempting upload
	if ( ! file_exists( $file_path ) ) {
		$error_msg = sprintf( __( 'File %s does not exist', 'amazon-s3-and-cloudfront' ), $file_path );
		return $this->return_upload_error( $error_msg, $return_metadata );
	}
	$file_name     = basename( $file_path );
	$type          = get_post_mime_type( $post_id );
	$allowed_types = $this->get_allowed_mime_types();
	// check mime type of file is in allowed S3 mime types
	if ( ! in_array( $type, $allowed_types ) ) {
		$error_msg = sprintf( __( 'Mime type %s is not allowed', 'amazon-s3-and-cloudfront' ), $type );
		return $this->return_upload_error( $error_msg, $return_metadata );
	}
	$acl = self::DEFAULT_ACL;
	// check the attachment already exists in S3, eg. edit or restore image
	if ( ( $old_s3object = $this->get_attachment_s3_info( $post_id ) ) ) {
		// use existing non default ACL if attachment already exists
		if ( isset( $old_s3object['acl'] ) ) {
			$acl = $old_s3object['acl'];
		}
		// use existing prefix
		$prefix = dirname( $old_s3object['key'] );
		$prefix = ( '.' === $prefix ) ? '' : $prefix . '/';
		// use existing bucket
		$bucket = $old_s3object['bucket'];
		// get existing region
		if ( isset( $old_s3object['region'] ) ) {
			$region = $old_s3object['region'];
		};
	} else {
		// derive prefix from various settings
		if ( isset( $data['file'] ) ) {
			$time = $this->get_folder_time_from_url( $data['file'] );
		} else {
			$time = $this->get_attachment_folder_time( $post_id );
			$time = date( 'Y/m', $time );
		}
		$prefix = $this->get_file_prefix( $time );
		// use bucket from settings
		$bucket = $this->get_setting( 'bucket' );
		$region = $this->get_setting( 'region' );
		if ( is_wp_error( $region ) ) {
			$region = '';
		}
	}
	$acl = apply_filters( 'as3cf_upload_acl', $acl, $data, $post_id );
	$s3object = array(
		'bucket' => $bucket,
		'key'    => $prefix . $file_name,
		'region' => $region,
	);
	// store acl if not default
	if ( $acl != self::DEFAULT_ACL ) {
		$s3object['acl'] = $acl;
	}
	$s3client = $this->get_s3client( $region, $force_new_s3_client );
	$args = array(
		'Bucket'       => $bucket,
		'Key'          => $prefix . $file_name,
		'SourceFile'   => $file_path,
		'ACL'          => $acl,
		'ContentType'  => $type,
		'CacheControl' => 'max-age=31536000',
		'Expires'      => date( 'D, d M Y H:i:s O', time() + 31536000 ),
	);
	$args = apply_filters( 'as3cf_object_meta', $args, $post_id );
	$files_to_remove = array();
	if ( file_exists( $file_path ) ) {
		$files_to_remove[] = $file_path;
		try {
			$s3client->putObject( $args );
		} catch ( Exception $e ) {
			$error_msg = sprintf( __( 'Error uploading %s to S3: %s', 'amazon-s3-and-cloudfront' ), $file_path, $e->getMessage() );
			return $this->return_upload_error( $error_msg, $return_metadata );
		}
	}
	delete_post_meta( $post_id, 'amazonS3_info' );
	add_post_meta( $post_id, 'amazonS3_info', $s3object );
	$file_paths        = $this->get_attachment_file_paths( $post_id, true, $data );
	$additional_images = array();
	$filesize_total             = 0;
	$remove_local_files_setting = $this->get_setting( 'remove-local-file' );
	if ( $remove_local_files_setting ) {
		$bytes = filesize( $file_path );
		if ( false !== $bytes ) {
			// Store in the attachment meta data for use by WP
			$data['filesize'] = $bytes;
			if ( is_null( $return_metadata ) ) {
				// Update metadata with filesize
				update_post_meta( $post_id, '_wp_attachment_metadata', $data );
			}
			// Add to the file size total
			$filesize_total += $bytes;
		}
	}
	foreach ( $file_paths as $file_path ) {
		if ( ! in_array( $file_path, $files_to_remove ) ) {
			$additional_images[] = array(
				'Key'        => $prefix . basename( $file_path ),
				'SourceFile' => $file_path,
			);
			$files_to_remove[] = $file_path;
			if ( $remove_local_files_setting ) {
				// Record the file size for the additional image
				$bytes = filesize( $file_path );
				if ( false !== $bytes ) {
					$filesize_total += $bytes;
				}
			}
		}
	}
	if ( $remove_local_files ) {
		if ( $remove_local_files_setting ) {
			// Allow other functions to remove files after they have processed
			$files_to_remove = apply_filters( 'as3cf_upload_attachment_local_files_to_remove', $files_to_remove, $post_id, $file_path );
			// Remove duplicates
			$files_to_remove = array_unique( $files_to_remove );
			// Delete the files
			$this->remove_local_files( $files_to_remove );
		}
	}
	// Store the file size in the attachment meta if we are removing local file
	if ( $remove_local_files_setting ) {
		if ( $filesize_total > 0 ) {
			// Add the total file size for all image sizes
			update_post_meta( $post_id, 'wpos3_filesize_total', $filesize_total );
		}
	} else {
		if ( isset( $data['filesize'] ) ) {
			// Make sure we don't have a cached file sizes in the meta
			unset( $data['filesize'] );
			if ( is_null( $return_metadata ) ) {
				// Remove the filesize from the metadata
				update_post_meta( $post_id, '_wp_attachment_metadata', $data );
			}
			delete_post_meta( $post_id, 'wpos3_filesize_total' );
		}
	}
	if ( ! is_null( $return_metadata ) ) {
		// If the attachment metadata is supplied, return it
		return $data;
	}
	return $s3object;
}
