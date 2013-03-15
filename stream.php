#! /usr/bin/php
<?php

// IMPORTANT: this script will output the MP3 data to stdout. Think about redirecting the stream to a MP3 decoder.
// Example:
// $ php stream.php | mpg123 -

require_once('mp3stream.class.php');

$objMP3Stream = new MP3Stream();

// Shoutcast IP address and port
$objMP3Stream->setStreamAddress('127.0.0.1');
$objMP3Stream->setStreamport(8000);

// Postgres configuration
$objMP3Stream->setSQLAddress('127.0.0.1');              // Optional, default to 127.0.0.1
$objMP3Stream->setSQLPort(5432);                        // Optional, default to 5432
$objMP3Stream->setSQLDatabase('your_database_name');
$objMP3Stream->setSQLUser('username');
$objMP3Stream->setSQLPassword('password');              // Optional is trusted mode set in pg_hba.conf
$objMP3Stream->setSQLSchema('public');                  // Optional, default to public

// Sample configuration
$objMP3Stream->setDirectory('/home/me/samples');
$objMP3Stream->setSaveDuration(30);                     // Optional, default to 30 seconds

// Optional, regular expression to extract artist and title from raw "StreamTitle" ICY meta
// first capture expression is used to extract info
$objMP3Stream->setRegExpArtist('/AnyRadioName:(.*?) - .*/');
$objMP3Stream->setRegExpTitle('/AnyRadioName :.*? - (.*)/');

// Start the show!
$objMP3Stream->run();
