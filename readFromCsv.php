<?php
require_once 'inc/spotifyFunctions.php';

$authorization = "Authorization: Bearer ".getAccessToken();

$search = readFromTextFile("songs.csv");

echo "Artist;Title;BPM<br>";
foreach ($search as $value) {
    searchForBPM($value, $authorization);
}



/*
 * Calls the spotify api and return a decoded json array
 */
function readFromTextFile($filename){
    $searcharray = [];
    
    //Opens file and loops through it line by line
    $file = fopen($filename, 'r');
    while (($line = fgetcsv($file, 1000, ";")) !== FALSE) {
        $search = convertArtistTitle($line[0], $line[1]);
        array_push($searcharray, $search);   
    }
    fclose($file);
    
     
    return $searcharray;
}



