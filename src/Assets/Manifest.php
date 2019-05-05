<?php
namespace WPSentry\Assets;

// Exit if plugin isn't running
defined( 'WP_SENTRY_EXISTS' ) || exit;

/**
 * Class Manifest.
 *
 * A class for fetching dynamic assets from a json manifest.
 *
 * @package WPSentry\Assets;
 * @since   3.0.0
 */

class Manifest{

  /**
   * The content of the manifest we are reading from.
   *
   * @since 3.0.0
   * @var string
   */
  private $manifest_content;

  /**
   * The path of the manifest file we are reading from.
   *
   * @since 3.0.0
   * @var string
   */
  private $manifest_path;

  /**
   * The URL of the plugin's `dist` folder where our final assets live.
   *
   * @since 3.0.0
   * @var string
   */
  private $dist_url;


  /**
   * Class Constructor
   */
  public function __construct(){

    $this->manifest_content = null;
    $this->manifest_path = defined( 'WP_SENTRY_ASSET_MANIFEST_PATH') ? WP_SENTRY_ASSET_MANIFEST_PATH : null;
    $this->dist_url = defined( 'WP_SENTRY_ASSET_DIST_URL') ? WP_SENTRY_ASSET_DIST_URL : null;
    $this->set_manifest_content( $this->get_manifest_path() );

  }

  /**
   * Fetch a dynamic asset from the manifest by passing in the name of the
   * asset key we want to resolve in the manifest.
   *
   * @param string $asset_file_name - the name of the asset to fetch from the manifest
   * @return string
   * @since 3.0.0
   */
  public function get_asset_url( string $asset_file_name ){
    return $this->get_dist_url() . $this->get_asset_from_key( $asset_file_name );
  }

  /**
   * Resolve and return the name or "value" of the asset in the manifest
   * based on the specified asset file name (manifest key).
   *
   * If the specified key does not exist in the manifest, we are simply
   * returning the name passed into the method.
   *
   * @param string $asset_file_name - the file name of the asset
   * @return string - the final name of the asset resolved from the manifest
   * @since 3.0.0
   */
  private function get_asset_from_key( string $asset_file_name ){

    if( array_key_exists( $asset_file_name, $this->get_manifest_content() ) ){
      return $this->get_manifest_content()[ $asset_file_name ];
    }

    return $asset_file_name;

  }

  /**
   * Get the full URL of our `dist` folder that holds our compiled assets.
   *
   * @return string - the full URL of our `dist` folder.
   * @since 3.0.0
   */
  private function get_dist_url(){
    return $this->dist_url;
  }

  /**
   * Get the path of the asset manifest
   *
   * @return string - the asset manifest path
   * @since 3.0.0
   */
  private function get_manifest_path(){
    return $this->manifest_path;
  }

  /**
   * Read the asset manifest and retrieve its contents,
   * then assign the content to a class property.
   *
   * @param string $manifest_path
   * @since 3.0.0
   */
  private function set_manifest_content( string $manifest_path ){

    // We can't set the manifest if it's location hasn't been defined
    if( ! $manifest_path )
      return;

    // If it resolves, decode it and get the contents
    $this->manifest_content = file_exists( $manifest_path )
      ? (array) json_decode( file_get_contents( $manifest_path ), true )
      : null;

  }

  /**
   * Get the contents of the manifest
   *
   * @return string - the content of the manifest
   * @since 3.0.0
   */
  private function get_manifest_content(){
    return $this->manifest_content;
  }

}
