<?php
# Alert the user that this is not a valid access point to MediaWiki if they
# try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/cedarinstruments/cedarinstruments.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits[ 'specialpage' ][] = array(
        'path' => __FILE__,
        'name' => 'CedarInstruments',
        'author' => 'Patrick West',
        'url' => 'http://cedarweb.hao.ucar.edu/cedaradmin/index.php/Extensions:cedarinstruments',
        'descriptionmsg' => 'cedarinstruments-desc',
        'version' => '1.0.1',
);
 
$wgAutoloadClasses[ 'CedarInstruments' ] = __DIR__ .  '/CedarInstruments_body.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'CedarInstruments' ] = __DIR__ .  '/CedarInstruments.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
$wgSpecialPages[ 'CedarInstruments' ] = 'CedarInstruments'; # Tell MediaWiki about the new special page and its class name
$wgGroupPermissions['sysop']['cedar_admin'] = true;

