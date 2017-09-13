<?php
$base = dirname(__FILE__);
$e = explode('/plugins/zip-your-media',$base);
$zym_dirname = $e[0].'/uploads/zym';

$filename = $zym_dirname.'/'.$_GET['file'];
$fs = filesize($zym_dirname.'/'.$_GET['file']);
// required for IE, otherwise Content-disposition is ignored

if ( ini_get( 'zlib.output_compression' ) )
  ini_set( 'zlib.output_compression', 'Off' );

header("Pragma: no-cache" );
header( "Expires: 0" );
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public" );
header( "Cache-Control: private", false ); // required for certain browsers 
header( "Content-Type: application/force-download" );
header( "Content-Disposition: attachment; filename=\"" . basename($filename) . "\";" );
header( "Content-Transfer-Encoding: binary" );
header( "Content-Length: ". $fs );
readfile( "$filename" );
exit();