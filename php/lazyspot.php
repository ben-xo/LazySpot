#!/usr/bin/env php
<?php

require_once 'Autoloader.php';
function __autoload($class)
{
    $al = new Autoloader();
    $al->load($class);
}

class LazySpot
{
    protected
        $api_key = 'adf3ef9c3e288c3d54d0a35683fe2e26', 
        $api_secret = 'aa1be50e8285106ce75ece21a2b940da'
    ;
    
    public function __construct()
    {
        $caller = CallerFactory::getDefaultCaller();
        $caller->setApiKey($this->api_key);
        $caller->setApiSecret($this->api_secret);    
    }
    
    public function run(array $argv, $argc)
    {
        echo "LazySpot GO!\n\n";
        try {
            
            if(empty($argv[1])) throw new Exception("Missing Last.fm username arg");
            $username = $argv[1];
            
            echo "Getting your session...\n";
            $session = $this->getSession($username);
            
            echo "Getting your recommended artists and albums...\n";
            $artists = User::getRecommendedArtists(10, 0, $session);
            $spotify_albums = array();
            
            foreach($artists as $artist)
            {
                /* @var $artist Artist */
                echo "* " . $artist->getName() . "\n";
                $artist_albums = Artist::getTopAlbums($artist->getName());
                $count = 0;
                foreach($artist_albums as $album)
                {
                    if($count > 2) break;
                    
                    /* @var $album Album */
                    if($this->spotifyHasAlbum($artist, $album, $spotify_albums))
                    {
                        echo "  * Spotify found album " . $album->getName() . "\n";
                        $count++;
                    }
                }
            }
            
            $uris = array();
            foreach($spotify_albums as $k => $artist) 
            {
                foreach($artist as $l => $album)
                {
                    foreach($album as $m => $spotify_album)
                    {
                        /* @var $spotify_album MT\Album */
                        $uris[] = $spotify_album->getURI();
                    }
                }
            }
            
            echo "\n\n";
            echo implode("\n", $uris);
            echo "\n";
            echo "This is a total of " . count($uris) . " albums.\n";
        }
        catch(Exception $e)
        {
            echo "Fail: " . $e->getMessage() . "\n";
        }
    }
    
    protected function spotifyHasAlbum(Artist $artist, Album $album, array &$spotify_albums)
    {
        $metatune = MT\MetaTune::getInstance();
        $normal_form = implode(' ', array(
        	'artist:' . $artist->getName(), 
        	'album:' . $album->getName()
        ));
        
        try {
            $results = $metatune->searchAlbum($normal_form);
        } catch(Exception $e) {
            echo "!!: " . $e;
            return false;
        }
        
        if($results) {
            echo "  --> Found " . count($results) . " matching albums on Spotify\n";
            $spotify_albums[$artist->getName()][$album->getArtist()->getName().' - '.$album->getName()] = $results;
            return true;
        }
        return false;
    }
    
    protected function getSession($username)
    {
        $session_file = $username.'.object.txt'; 
        if(file_exists($session_file))
        {
            return unserialize(file_get_contents($session_file));
        }
        else
        {
            $token = Auth::getToken();
            
            $url = 'http://www.last.fm/api/auth?api_key=' . $this->api_key . '&token=' . $token;
            $ui = new UI;
            $ui->readline("Press enter after visiting {$url} to auth the app...");
            
            $session = Auth::getSession($token);
            file_put_contents($session_file, serialize($session));
            return $session;
        }
    }
}

$lazyspot = new LazySpot();
$lazyspot->run($argv, $argc);