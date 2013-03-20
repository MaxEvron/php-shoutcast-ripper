php-shoutcast-ripper
====================

This project makes you able to connect a Shoutcast server, fetch the stream and create samples of any new track played based on ICY metadata. All played tracks are collected in order to generate play statistics.

Requirements:
- PHP CLI
- PostgresSQL server

This software is freeware.

Version history
===============

Version 0.1 :
- First release

Version 0.2 :
- Added exclusion time frames in order meta to be ignored at certain moments (example : last and first minutes of each hour)
- Revamped active sampling debug message
- Revamped sample name (added leading zeros to track ID)
- Fixed the update condition in order to prevent duplicates when restarting the script after a short stop
- Fixed the regular expression used to split metas in order not to match quotes within StreamTitle (example "let's go")