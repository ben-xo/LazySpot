<?php
require_once("lib/config.php");
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="no-NB">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="keywords" content="Spotify Metadata API, Mikael Brevik, PHP Lib, Spotify" />
        <meta name="description" content="Test av MBSpotifyLib." />
        <title>MetaTune - Demo Details</title>
        <link type="text/css" rel="stylesheet" href="./demo.css" />
    </head>
    <body>
        <div id="wrapper">
            <?php
            if (DEBUG) {
                $start = microtime();
            }

            $spotify = SpotifySearch::getInstance();
            try {
                $tracks = $spotify->searchTrack("Superfamily - The Dive");
            } catch (MBSpotifyException $ex) {
                die("<pre>Error\n" . $ex . "</pre>");
            }
            $firstTrack = $tracks[0];

            // Might have more than one artist. Save the poissible array
            $artist = $firstTrack->getArtist();

            $out = "<ul>\n";
            $out .= '<li><strong>Song:</strong> <a href="' . $firstTrack->getURL() . '">' . $firstTrack->getTitle() . '</a></li>' . "\n";
            $out .= '<li><strong>Artist:</strong> <a href="' . ((is_array($artist)) ? $artist[0]->getURL() : $artist->getURL()) . '">' . $firstTrack->getArtistAsString() . '</a></li>' . "\n";
            $out .= '<li><strong>Album:</strong> <a href="' . $firstTrack->getAlbum()->getURL() . '">' . $firstTrack->getAlbum() . '</a></li>' . "\n";
            $out .= '<li><strong>Duration:</strong> ' . $firstTrack->getLengthInMinutesAsString() . '</li>' . "\n";
            $out .= '<li><strong>Popularity:</strong> ' . $firstTrack->getPopularityAsPercent() . '%</li>' . "\n";
            $out .= "</ul>\n";
            echo $out;
            ?>
        </div>
    </body>
</html>