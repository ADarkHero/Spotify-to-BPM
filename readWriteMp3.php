<?php

require_once 'inc/spotifyFunctions.php';
require_once('getid3/getid3/getid3.php');
require_once('getid3/getid3/write.php');



$authorization = "Authorization: Bearer ".getAccessToken();

$folder = "mp3";
$folderlen = strlen($folder);
$search = getAllMp3InFolder($folder);

echo "Artist;Title;BPM<br>";
foreach ($search as $value) {
    $mp3ArtistTitle = getArtistTitleFromMP3($value);
    $mp3ArtistTitleConverted = convertArtistTitle($mp3ArtistTitle, "");
    $bpm = searchForBPM($mp3ArtistTitleConverted, $authorization);
    //writeBPMToFile($value, $bpm);
}


/*
 * Searches folder for mp3 files and returns an array with it.
 */
function getAllMp3InFolder($filepath){
    $query = $filepath."/*.mp3";
    $files = glob($query, GLOB_BRACE);


    return $files;
}

function getArtistTitleFromMP3($file){    
    $getID3 = new getID3;
    $ThisFileInfo = $getID3->analyze($file); //Analyze mp3 tags
    
    //    print "<pre>";
    //    print_r($ThisFileInfo);
    //    print "</pre>";
        
    //Put artist and title in one string
    if(isset($ThisFileInfo["tags"]["id3v2"]["artist"][0])){
        $currentArtistTitle = $ThisFileInfo["tags"]["id3v2"]["artist"][0]
            ." "
            .$ThisFileInfo["tags"]["id3v2"]["title"][0];
    }
    else if(isset($ThisFileInfo["tags"]["id3v1"]["artist"][0])){
        $currentArtistTitle = $ThisFileInfo["tags"]["id3v1"]["artist"][0]
            ." "
            .$ThisFileInfo["tags"]["id3v1"]["title"][0];
    }
    else{
        $currentArtistTitle = "";
    }
    
        
    
    return $currentArtistTitle;
}

function writeBPMToFile($file, $bpm){
    $TextEncoding = 'UTF-8';

    $getID3 = new getID3;
    $getID3->setOption(array('encoding'=>$TextEncoding));
    
    $tagwriter = new getid3_writetags;
    $tagwriter->filename = $file;
    $tagwriter->tagformats = array('id3v2.4');

    $tagwriter->overwrite_tags    = true; 
    $tagwriter->remove_other_tags = false;  
    $tagwriter->tag_encoding      = $TextEncoding;
    
    $TagData = array(
	'bpm'                  => array($bpm)
    );
    $tagwriter->tag_data = $TagData;
    
    if ($tagwriter->WriteTags()) {
	if (!empty($tagwriter->warnings)) {
		echo 'There were some warnings:<br>'.implode('<br><br>', $tagwriter->warnings);
	}
    } else {
            echo 'Failed to write tags!<br>'.implode('<br><br>', $tagwriter->errors);
    }
}