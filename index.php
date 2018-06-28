<?php
/*
 * Generates an access token
 */


$authorization = "Authorization: Bearer ".getAccessToken();


$search = readFromTextFile();
foreach ($search as $value) {
    searchForBPM($value, $authorization);
}





/*
 * Generates a spotify access token
 * Thanks/Credits to ahallora on GitHub
 */
function getAccessToken(){
    $client_id = '1f5840ab9ac2425591bc324b2034a38c'; 
    $client_secret = 'd76421addcee4d299f7b7dd2a08b4ce1'; 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,            'https://accounts.spotify.com/api/token' );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS,     'grant_type=client_credentials' ); 
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Authorization: Basic '.base64_encode($client_id.':'.$client_secret))); 

    $result=curl_exec($ch);

    $token = json_decode($result, true);
    return $token['access_token'];
}

/*
 * Calls the spotify api and return a decoded json array
 */
function makeSpotifyCall($spotifyURL, $authorization){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $spotifyURL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:x.x.x) Gecko/20041107 Firefox/x.x");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json = curl_exec($ch);
    $jsondecoded = json_decode($json, true);
    curl_close($ch);
    return $jsondecoded;
}

/*
 * Calls the spotify api and return a decoded json array
 */
function readFromTextFile(){
    $searcharray = [];
    
    //TODO: LOOP
    $artist = 'Dragonforce';
    $title = 'fire and the flames';
    $search = convertArtistTitle($artist, $title);
    array_push($searcharray, $search);    

    return $searcharray;
}

function convertArtistTitle($artist, $title){
    //Combines artist and title and lowercases them
    $search = $artist." ".$title;
    $searchToLower = strtolower($search);
    
    //Replace space with ---
    //This is used to "add spaces" to the regular expression below
    $searchWOSpaces = str_replace(" ", "---", $searchToLower); 
    
    //Removes special characters
    //The spotify api doesn't like special characters
    //Normally, the search should work without them
    $searchWOSpecialChars = preg_replace('/[^A-Za-z0-9\-]/', '', $searchWOSpaces); 

    //Replace --- with %20
    //Spotify wants spaces as %20
    $searchFinal = str_replace("---", "%20", $searchWOSpecialChars); 
    
    return $searchFinal;
}

function searchForBPM($search, $authorization){
    //Get the track, we searched for
    $spotifyURL = 'https://api.spotify.com/v1/search?q='.$search.'&type=track&market=DE&limit=1';
    $jsonTrack = makeSpotifyCall($spotifyURL, $authorization);
    $track = $jsonTrack['tracks']['items']['0']['id'];
    $artist = $jsonTrack['tracks']['items']['0']['artists']['0']['name'];
    $title = $jsonTrack['tracks']['items']['0']['name'];

    //Get the tracktempo
    $spotifyURL = 'https://api.spotify.com/v1/audio-analysis/'.$track;
    $jsonTempo = makeSpotifyCall($spotifyURL, $authorization);
    $bpm = intval($jsonTempo['track']['tempo']);


    echo $artist.";".$title.";".$bpm."<br>";
}

