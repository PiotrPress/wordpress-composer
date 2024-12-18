<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\WordPress;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\Version\VersionParser;

class Plugin implements PluginInterface {
    public function activate( Composer $composer, IOInterface $io ) {
        foreach ( \array_keys( $composer->getPackage()->getRequires() ) as $require )
            if ( $repository = $this->createRepository( $require ) )
                $composer->getRepositoryManager()->addRepository(
                    $composer->getRepositoryManager()->createRepository( 'package', $repository )
                );
    }

    protected function createRepository( string $require ) : array {
        return ( $package = $this->createPackage( $require ) ) ? [
            'type' => 'package',
            'package' => $package
        ] : [];
    }

    protected function createPackage( string $require ) : array {
        $package = [];

        foreach ( $this->getVersions( $require ) as $version => $url ) {
            try { ( new VersionParser )->normalize( $version ); }
            catch ( \Exception $exception ) { continue; }

            $package[] = [
                'name' => $require,
                'type' => $this->getType( $require ),
                'version' => $version,
                'dist' => [
                    'type' => 'zip',
                    'url' => $url
                ]
            ];
        }

        return $package;
    }

    protected function getApiUrl() : string {
        return 'https://api.wordpress.org';
    }

    protected function getCoreUrl( string $version, string $slug ) : string {
        switch ( true ) {
            case ! \in_array( $slug, [ 'full', 'no-content', 'new-bundled' ] ) :
                return '';
            case 'full' === $slug :
                return "https://downloads.wordpress.org/release/wordpress-{$version}.zip";
            default :
                return "https://downloads.wordpress.org/release/wordpress-{$version}-{$slug}.zip";
        }
    }

    protected function getSlug( string $require ) : string {
        return \basename( $require );
    }

    protected function getType( string $require ) : string {
        return \dirname( $require );
    }

    protected function getData( string $url ) : array {
        return @\json_decode( @\file_get_contents( $url ), true ) ?? [];
    }

    protected function getVersions( string $require ) : array {
        switch ( $this->getType( $require ) ) {

            case 'wordpress-core' :
                $versions = [];
                foreach ( \array_keys( $this->getData(
                    $this->getApiUrl() .
                    '/core/stable-check/1.0/'
                ) ) as $version )
                    if ( $coreUrl = $this->getCoreUrl( $version, $this->getSlug( $require ) ) )
                        $versions[ $version ] = $coreUrl;
                return $versions;

            case 'wordpress-theme' :
                $data = $this->getData(
                    $this->getApiUrl() .
                    '/themes/info/1.1/?action=theme_information&request[fields][versions]=1&request[slug]=' .
                    $this->getSlug( $require )
                );
                return $data[ 'versions' ] ?: [
                    $data[ 'version' ] => $data[ 'download_link' ]
                ];

            case 'wordpress-plugin' :
            case 'wordpress-muplugin' :
                $data = $this->getData(
                    $this->getApiUrl() .
                    '/plugins/info/1.2/?action=plugin_information&request[fields][versions]=1&request[slug]=' .
                    $this->getSlug( $require )
                );
                return $data[ 'versions' ] ?: [
                    $data[ 'version' ] => $data[ 'download_link' ]
                ];

            default :
                return [];
        }
    }

    public function deactivate( Composer $composer, IOInterface $io ) {}
    public function uninstall( Composer $composer, IOInterface $io ) {}
}