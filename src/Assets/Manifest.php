<?php
namespace WPSentry\Assets;

class Manifest{

  private $manifest;

  private $manifest_path;

  private $dist_url;


  public function __construct(){

    $this->manifest = null;
    $this->manifest_path = defined( 'WP_SENTRY_ASSET_MANIFEST_PATH') ? WP_SENTRY_ASSET_MANIFEST_PATH : null;
    $this->dist_url = defined( 'WP_SENTRY_ASSET_DIST_URL') ? WP_SENTRY_ASSET_DIST_URL : null;
    $this->set_manifest( $this->get_manifest_path() );

  }

  public function get_asset_path( string $asset_name ){
    return $this->get_dist_url() . $this->get_resolved_asset_from_key( $asset_name );
  }

  private function get_resolved_asset_from_key( string $asset_name ){

    if( array_key_exists( $asset_name, $this->get_manifest() ) ){
      return $this->get_manifest()[ $asset_name ];
    }

    return $asset_name;

  }

  private function get_dist_url(){
    return $this->dist_url;
  }

  private function get_manifest_path(){
    return $this->manifest_path;
  }

  private function set_manifest( string $manifest_path ){

    // We can't set the manifest if it's location hasn't been defined
    if( ! $manifest_path )
      return;

    // If it resolves, decode it and get the contents
    $this->manifest = file_exists( $manifest_path )
      ? (array) json_decode( file_get_contents( $manifest_path ), true )
      : null;

  }

  private function get_manifest(){
    return $this->manifest;
  }

}
