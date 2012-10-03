<?php

$debug_gd = true;

//
// VERSIONE MODIFICATA DELL'IMAGE.PHP CHE PERMETTE DI RESTITUIRE
// IL RESIZE DELL'IMMAGINE, FARE CACHE DEI FILE TRAMITE LE **PECL**
//




// === CONFIGURATION =====================================================
//  location of source images (no trailing /)
$image_path = '.';


//  location of cached images (no trailing /)
$cache_path = './_files/cache';



// tempo dopo il quale la cache viene eliminata
//$refreshTime = 15 * 24 * 60 * 60; // ogni 15 giorni

// === CHECK INPUT =======================================================
// first, check if an image location is given

if (!isset($_SERVER['PATH_INFO'])) {
	die('ERROR: No image specified.');
}

$img = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['ORIG_PATH_INFO'];
$querystring = $_SERVER['QUERY_STRING'];

$image = $image_path.$img;

// next, check if the file exists
if (!file_exists($image)) {
	
	die('ERROR: That image does not exist. '.$image);
}

// se non arriva l'immagine...
if ($image == $image_path."/") {
	$image = $image_path."/null.gif";
	$querystring = "resize(1)";
}


// === PARSE COMMANDS ====================================================
// extract the commands from the query string
// eg.: ?resize(....)+flip+blur(...)
preg_match_all('/\+*(([a-z]+)(\(([^\)]*)\))?)\+*/',
               $querystring,
               $matches, PREG_SET_ORDER);

// concatenate commands for use in cache file name
$cache = $img;

foreach ($matches as $match) {
	$cache .= '%'.$match[2].'_'.$match[4];
}

$cache = str_replace('/','_',$cache);
$cache = $cache_path.'/'.$cache;

/*
// calcola la directory per il file cache
$md5_cache   = md5($cache);
$first_char  = substr($md5_cache, 0, 1);
$second_char = substr($md5_cache, 1, 1);

// crea la prima cartella
if (!file_exists($cache_path.'/'.$first_char.'/'))
	mkdir($cache_path.'/'.$first_char.'/', 0775) or die('Directory creation failed');

// crea la seconda cartella
if (!file_exists($cache_path.'/'.$first_char.'/'.$second_char.'/'))
	mkdir($cache_path.'/'.$first_char.'/'.$second_char.'/', 0775) or die('Directory creation failed');

$cache = $cache_path.'/'.$first_char.'/'.$second_char.'/'.$cache;
*/

// === RUN CONVERT =======================================================
if (!file_exists($cache)) { //  || (filemtime($image) > filemtime($cache)) // refresh alla modifica dell'immagine
	// there is no cached image yet, so we'll need to create it first

	// convert query string to an imagemagick command string
	$commands = '';
	foreach ($matches as $match) {
		// $match[2] is the command name
		// $match[4] the parameter

		// check input
		if (!preg_match('/^[a-z]+$/',$match[2])) {
			die('ERROR: Invalid command.');
		}
		if (!preg_match('/^[a-z0-9\/{}+-<>!@%]+$/',$match[4])) {
			die('ERROR: Invalid parameter.');
		}

		// replace } with >, { with <
		// > and < could give problems when using html
		$match[4] = str_replace('}','>',$match[4]);
		$match[4] = str_replace('{','<',$match[4]);

		// check for special, scripted commands
		switch ($match[2]) {

			case 'part':
				// crops the image to the requested size
				if (!preg_match('/^[0-9]+x[0-9]+$/',$match[4])
					&& !preg_match('/^[0-9]+x[0-9]+x[0-9]+x[0-9]+$/',$match[4])) {
					die('ERROR: Invalid parameter.');
				}
				
				$size = explode('x', $match[4]);
				
				$width  = isset($size[0]) ? (int)$size[0] : 0;
				$height = isset($size[1]) ? (int)$size[1] : 0;
				$cropX  = isset($size[2]) ? (int)$size[2] : -1;
				$cropY  = isset($size[3]) ? (int)$size[3] : -1;
				
				
				if (class_exists("Imagick") && !$debug_gd)
				{
					
					$thumb = new Imagick($image);
					
					$thumb->resampleImage(72, 72, 0, 1);
					
					
					$orig_w  = $thumb->getImageWidth();
					$orig_h = $thumb->getImageHeight();
					
					if ($width > $orig_w)
						$width = $orig_w;
					
					if ($height > $orig_h)
						$height = $orig_h;
						
					if ($cropX > 100)
						$cropX = 100;
						
					if ($cropY > 100)
						$cropY = 100;
					
					
					if ($orig_w/$orig_h > $width/$height) 
					{
						$thumb->scaleImage(null, $height);
						
						$resized_w = ($height/$orig_h) * $orig_w;
						
						$pheight = 0;
						
						if ($cropX < 0)
							$pwidth = round(($resized_w - $width)/2);
						else
							$pwidth = round(($resized_w - $width) * ($cropX/100));
					}
					else
					{
						$thumb->scaleImage($width, null);
						
						$resized_h = ($width/$orig_w) * $orig_h;
						
						if ($cropY < 0)
							$pheight = round(($resized_h - $height)/2);
						else
							$pheight = round(($resized_h - $height) * ($cropY/100));
						
						$pwidth  = 0;
					}
					
					$thumb->cropImage($width, $height, $pwidth, $pheight);
					
					$thumb->setCompression(0);
					$thumb->setImageCompressionQuality(100);
					$thumb->writeImage($cache);
					$thumb->clear();
					$thumb->destroy();
					
				}
				else
				{
					$_GET["image"]      = $image;
					$_GET["width"]      = $width;
					$_GET["height"]     = $height;
					$_GET["cropx"]      = $cropX;
					$_GET["cropy"]      = $cropY;
					$_GET["cropratio"]  = $width.":".$height;
					$_GET["cache"]      = $cache;
					
					include(dirname(__FILE__)."/image_gd.php");
					die();
				}
				
				break;

			case 'resize':

				$size = explode('x', $match[4]);

				$width  = isset($size[0]) && !empty($size[0]) ? (int)$size[0] : 9000;
				$height = isset($size[1]) && !empty($size[1]) ? (int)$size[1] : 9000;
				
				if (class_exists("Imagick") && !$debug_gd)
				{
					
					$thumb = new Imagick($image);
					
					list($newX, $newY) = scaleImage(
						$thumb->getImageWidth(),
						$thumb->getImageHeight(),
						$width,
						$height);
					
					
					$thumb->resampleImage(72, 72, 0, 1);
					
					$thumb->scaleImage($newX, $newY);
					
					$thumb->setCompression(0);
					$thumb->setImageCompressionQuality(100);
					$thumb->writeImage($cache);
					$thumb->clear();
					$thumb->destroy();
					
				}
				else
				{
					$_GET["image"]  = $image;
					$_GET["width"]  = $width;
					$_GET["height"] = $height;
					$_GET["cache"]  = $cache;
					
					include(dirname(__FILE__)."/image_gd.php");
					die();
				}
				
				break;
				
			default:
				die("COMMAND NOT FOUND");
		}
	}

}

// === OUTPUT ============================================================
// there should be a file named $cache now
if (!file_exists($cache)) {
	die('ERROR: Image conversion failed.');
}

// get image data for use in http-headers
$imginfo = getimagesize($cache);
$content_length = filesize($cache);
$last_modified = gmdate('D, d M Y H:i:s',filemtime($cache)).' GMT';

// array of getimagesize() mime types
$getimagesize_mime = array(1=>'image/gif',2=>'image/jpeg',3=>'image/png',
                           4=>'application/x-shockwave-flash',5=>'image/psd',
                           6=>'image/bmp',7=>'image/tiff',8=>'image/tiff',
                           9=>'image/jpeg',
                           13=>'application/x-shockwave-flash',
                           14=>'image/iff');

// did the browser send an if-modified-since request?
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	// parse header
	$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);

	if ($if_modified_since == $last_modified) {
		// the browser's cache is still up to date
		header("HTTP/1.0 304 Not Modified");
		header("Cache-Control: max-age=86400, must-revalidate");
		exit;
	}
}

// send other headers
header('Cache-Control: max-age=86400, must-revalidate');
header('Content-Length: '.$content_length);
header('Last-Modified: '.$last_modified);
if (isset($getimagesize_mime[$imginfo[2]])) {
	header('Content-Type: '.$getimagesize_mime[$imginfo[2]]);
} else {
	// send generic header
	header('Content-Type: application/octet-stream');
}

// and finally, send the image
readfile($cache);


function scaleImage($x,$y,$cx,$cy) {
    list($nx,$ny)=array($x,$y);

    if ($x>=$cx || $y>=$cy) {

        if ($x > 0) $rx=$cx/$x;
        if ($y > 0) $ry=$cy/$y;

        if ($rx>$ry) {
            $r=$ry;
        } else {
            $r=$rx;
        }

        $nx=intval($x*$r);
        $ny=intval($y*$r);
    }

    if ($nx == 0)
		$nx = null;

	if ($ny == 0)
		$ny = null;

    return array($nx,$ny);
}


?>