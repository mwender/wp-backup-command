<?php

if ( ! class_exists( 'WP_CLI' ) ) {
  return;
}

/**
 * Backup a plugin or theme by creating a zip or tar.gz file.
 */
class WP_Backup_Command {

  /**
   * Backup a plugin.
   *
   * ## OPTIONS
   *
   * <plugin-slug>
   * : The slug of the plugin to backup.
   */
  public function plugin( $args ) {
    list( $slug ) = $args;
    $this->backup_item( $slug, 'plugin' );
  }

  /**
   * Backup a theme.
   *
   * ## OPTIONS
   *
   * <theme-slug>
   * : The slug of the theme to backup.
   */
  public function theme( $args ) {
    list( $slug ) = $args;
    $this->backup_item( $slug, 'theme' );
  }

  /**
   * Backup the specified plugin or theme.
   */
  private function backup_item( $slug, $type ) {
    // Get the current working directory
    $original_dir = getcwd();

    $path = ( $type === 'plugin' ) ? WP_PLUGIN_DIR . "/$slug" : get_theme_root() . "/$slug";
    $parent_dir = ( $type === 'plugin' ) ? WP_PLUGIN_DIR : get_theme_root();

    if ( ! file_exists( $path ) ) {
      WP_CLI::error( ucfirst( $type ) . " '$slug' not found." );
    }

    // Get version
    $data = ( $type === 'plugin' ) ? get_plugin_data( "$path/$slug.php" ) : wp_get_theme( $slug );
    $version = $data['Version'];

    // Create backup filename
    $backup_filename = "{$slug}_{$version}";

    // Check if zip is installed
    if ( $this->is_command_available( 'zip' ) ) {
      WP_CLI::log( 'Creating zip backup...' );
      $backup_file = $this->create_zip( $slug, $parent_dir, $backup_filename );
    } elseif ( $this->is_command_available( 'tar' ) ) {
      WP_CLI::log( 'Creating tar.gz backup...' );
      $backup_file = $this->create_tar_gz( $slug, $parent_dir, $backup_filename );
    } else {
      WP_CLI::error( 'Neither zip nor tar is available on the server.' );
    }

    // Move the backup file to the original directory
    if ( file_exists( "$parent_dir/$backup_file" ) ) {
      rename( "$parent_dir/$backup_file", "$original_dir/$backup_file" );
      WP_CLI::log( "Backup moved to: $original_dir/$backup_file" );
    }

    // Return to the original directory
    chdir( $original_dir );

    WP_CLI::success( ucfirst( $type ) . " '$slug' backed up successfully." );
  }

  /**
   * Check if a command is available on the server.
   */
  private function is_command_available( $command ) {
    $result = shell_exec( "command -v $command" );
    return ! empty( $result );
  }

  /**
   * Create a zip archive.
   */
  private function create_zip( $slug, $parent_dir, $filename ) {
    $zipfile = $filename . '.zip';

    // Change to parent directory and create the zip with the relative path
    $command = "cd $parent_dir && zip -r $zipfile $slug";
    shell_exec( $command );

    WP_CLI::log( "Backup created: $zipfile" );
    return $zipfile;
  }

  /**
   * Create a tar.gz archive.
   */
  private function create_tar_gz( $slug, $parent_dir, $filename ) {
    $tarfile = $filename . '.tar.gz';

    // Change to parent directory and create the tar.gz with the relative path
    $command = "cd $parent_dir && tar -czvf $tarfile $slug";
    shell_exec( $command );

    WP_CLI::log( "Backup created: $tarfile" );
    return $tarfile;
  }
}

WP_CLI::add_command( 'plugin backup', [ 'WP_Backup_Command', 'plugin' ] );
WP_CLI::add_command( 'theme backup', [ 'WP_Backup_Command', 'theme' ] );
