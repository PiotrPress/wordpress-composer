<?php declare( strict_types = 1 );

namespace PiotrPress\Composer\WordPress;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;

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
        $json = @\file_get_contents( $url );
        return @\json_decode( $json, true ) ?? [];
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
                $versions = $this->getData(
                    $this->getApiUrl() .
                    '/themes/info/1.1/?action=theme_information&request[fields][versions]=1&request[slug]=' .
                    $this->getSlug( $require )
                );
                return $versions[ 'versions' ] ?? [];

            case 'wordpress-plugin' :
                $data = $this->getData(
                    $this->getApiUrl() .
                    '/plugins/info/1.2/?action=plugin_information&request[fields][versions]=1&request[slug]=' .
                    $this->getSlug( $require )
                );
                return $data[ 'versions' ] ?? [];

            default :
                return [];
        }
    }

    public function deactivate( Composer $composer, IOInterface $io ) {}
    public function uninstall( Composer $composer, IOInterface $io ) {}
}