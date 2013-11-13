<?php

    /*

    TABLE SCHEMA FOR POSTGRES

    CREATE TABLE track
    (
        trac_id INT DEFAULT nextval('track_trac_id_seq'::regclass) NOT NULL,
        trac_artist VARCHAR(128) NOT NULL,
        trac_title VARCHAR(128),
        trac_first_play TIMESTAMP NOT NULL,
        trac_last_play TIMESTAMP NOT NULL,
        trac_play_count INT NOT NULL
    );

    CREATE TABLE playlist
    (
        play_id INT DEFAULT nextval('playlist_play_id_seq'::regclass) NOT NULL,
        trac_id INT NOT NULL,
        play_time TIMESTAMP NOT NULL,
        FOREIGN KEY ( trac_id ) REFERENCES track ( trac_id ) ON UPDATE CASCADE
    );

    ALTER TABLE track ADD CONSTRAINT pk_track PRIMARY KEY (trac_id);

    ALTER TABLE playlist ADD CONSTRAINT pk_playlist PRIMARY KEY (play_id);

    ALTER TABLE playlist ADD CONSTRAINT fk_play_trac FOREIGN KEY (trac_id) REFERENCES track.trac_id ON UPDATE CASCADE ON DELETE RESTRICT;

    */

    class MP3StreamException extends \Exception
    {
        //put your code here
    }

    class MP3Stream
    {
        const DEBUG_MODE = TRUE;

        // Stream user parameters
        private $strStreamAddress = NULL;

        private $intStreamPort = NULL;

        private $strStreamPath = '/;stream.mp3';

        // Postgres user parameters
        private $strSQLAddress = '127.0.0.1';

        private $intSQLPort = 5432;

        private $strSQLDatabase = NULL;

        private $strSQLUser = NULL;

        private $strSQLPassword = NULL;

        private $strSQLSchema = 'public';

        // Sample user parameters
        private $strDirectory = NULL;

        private $strRegExpArtist = NULL;

        private $strRegExpTitle = NULL;

        private $intSaveDuration = 30; // seconds
        private $arrExclusionTimeFrames = array();

        private $intStartDelay = 0;

        private $isFirstMetaSkipped = FALSE;

        // Internal ressources
        private $objPDO = NULL;

        private $resStream = NULL;

        private $resStdErr = NULL;

        private $resSample = NULL;

        // Stream live info
        private $strLiveArtist = '';

        private $strLiveTitle = '';

        private $intLiveTrackID = 0;

        private $arrMetaLive = array();

        private $arrLastMetaLive = array();

        // Internal buffers and counters
        private $intBytesRead = 0;

        private $intFrameCount = 0;

        private $intFramesToSave = 0;

        private $arrMetaInit = array();

        private $intStartTime = 0;

        private $strRawStream = '';

        private $strAudioStream = '';

        private $strAudioFrame = '';

        private $intTick = 0;

        private $arrRates = array(
            '1'   => array(
                'I'   => array(
                    0  => 0,
                    1  => 32,
                    2  => 64,
                    3  => 96,
                    4  => 128,
                    5  => 160,
                    6  => 192,
                    7  => 224,
                    8  => 256,
                    9  => 288,
                    10 => 320,
                    11 => 352,
                    12 => 384,
                    13 => 416,
                    14 => 448,
                    15 => 0,
                ),
                'II'  => array(
                    0  => 0,
                    1  => 32,
                    2  => 48,
                    3  => 56,
                    4  => 64,
                    5  => 80,
                    6  => 96,
                    7  => 112,
                    8  => 128,
                    9  => 160,
                    10 => 192,
                    11 => 224,
                    12 => 256,
                    13 => 320,
                    14 => 384,
                    15 => 0,
                ),
                'III' => array(
                    0  => 0,
                    1  => 32,
                    2  => 40,
                    3  => 48,
                    4  => 56,
                    5  => 64,
                    6  => 80,
                    7  => 96,
                    8  => 112,
                    9  => 128,
                    10 => 160,
                    11 => 192,
                    12 => 224,
                    13 => 256,
                    14 => 320,
                    15 => 0,
                ),
            ),
            '2'   => array(
                'I'   => array(
                    0  => 0,
                    1  => 32,
                    2  => 64,
                    3  => 96,
                    4  => 128,
                    5  => 160,
                    6  => 192,
                    7  => 224,
                    8  => 256,
                    9  => 288,
                    10 => 320,
                    11 => 352,
                    12 => 384,
                    13 => 416,
                    14 => 448,
                    15 => 0,
                ),
                'II'  => array(
                    0  => 0,
                    1  => 32,
                    2  => 48,
                    3  => 56,
                    4  => 64,
                    5  => 80,
                    6  => 96,
                    7  => 112,
                    8  => 128,
                    9  => 160,
                    10 => 192,
                    11 => 224,
                    12 => 256,
                    13 => 320,
                    14 => 384,
                    15 => 0,
                ),
                'III' => array(
                    0  => 0,
                    1  => 8,
                    2  => 16,
                    3  => 24,
                    4  => 32,
                    5  => 64,
                    6  => 80,
                    7  => 56,
                    8  => 64,
                    9  => 128,
                    10 => 160,
                    11 => 112,
                    12 => 128,
                    13 => 256,
                    14 => 320,
                    15 => 0,
                ),
            ),
            '2.5' => array(
                'I'   => array(
                    0  => 0,
                    1  => 32,
                    2  => 64,
                    3  => 96,
                    4  => 128,
                    5  => 160,
                    6  => 192,
                    7  => 224,
                    8  => 256,
                    9  => 288,
                    10 => 320,
                    11 => 352,
                    12 => 384,
                    13 => 416,
                    14 => 448,
                    15 => 0,
                ),
                'II'  => array(
                    0  => 0,
                    1  => 32,
                    2  => 48,
                    3  => 56,
                    4  => 64,
                    5  => 80,
                    6  => 96,
                    7  => 112,
                    8  => 128,
                    9  => 160,
                    10 => 192,
                    11 => 224,
                    12 => 256,
                    13 => 320,
                    14 => 384,
                    15 => 0,
                ),
                'III' => array(
                    0  => 0,
                    1  => 8,
                    2  => 16,
                    3  => 24,
                    4  => 32,
                    5  => 64,
                    6  => 80,
                    7  => 56,
                    8  => 64,
                    9  => 128,
                    10 => 160,
                    11 => 112,
                    12 => 128,
                    13 => 256,
                    14 => 320,
                    15 => 0,
                ),
            )
        );

        private $arrFrequencies = array(
            '1'   => array(
                0 => 44100,
                1 => 48000,
                2 => 32000,
                3 => 0,
            ),
            '2'   => array(
                0 => 22050,
                1 => 24000,
                2 => 16000,
                3 => 0,
            ),
            '2.5' => array(
                0 => 11025,
                1 => 12000,
                2 => 8000,
                3 => 0,
            ),
        );

        private $arrSamples = array(
            '1'   => array(
                'I'   => 384,
                'II'  => 1152,
                'III' => 1152,
            ),
            '2'   => array(
                'I'   => 384,
                'II'  => 1152,
                'III' => 576,
            ),
            '2.5' => array(
                'I'   => 384,
                'II'  => 1152,
                'III' => 576,
            ),
        );


        private function decodeMP3Header($strFrame)
        {
            if (strlen($strFrame) < 4) {
                throw new MP3StreamException("Header: too short");
            } else {
                $arrInfos = array();

                $arrHeader = array();
                foreach (str_split(substr($strFrame, 0, 4)) as $strChar) {
                    array_push($arrHeader, ord($strChar));
                }

                // SYNCHRO
                if (($arrHeader[0] != 255) || ($arrHeader[1] >> 5 != 7)) {
                    throw new MP3StreamException("Header: sync missing");
                }

                // VERSION
                switch (($arrHeader[1] >> 3) & 3) {
                    case 0 :
                        $arrInfos['version'] = '2.5';
                        break;
                    case 1 :
                        throw new MP3StreamException("Header: reserved version");
                        break;
                    case 2 :
                        $arrInfos['version'] = '2';
                        break;
                    case 3 :
                        $arrInfos['version'] = '1';
                        break;
                }

                // LAYER ID
                switch (($arrHeader[1] >> 1) & 3) {
                    case 0 :
                        throw new MP3StreamException("Header: reserved layer ID");
                        break;
                    case 1 :
                        $arrInfos['layer'] = 'III';
                        break;
                    case 2 :
                        $arrInfos['layer'] = 'II';
                        break;
                    case 3 :
                        $arrInfos['layer'] = 'I';
                        break;
                }

                // Protection (ie with CRC)
                $arrInfos['protected'] = (($arrHeader[1] & 1) == 1);

                // Frame rate
                $arrInfos['rate'] = $this->arrRates[$arrInfos['version']][$arrInfos['layer']][$arrHeader[2] >> 4];
                if ($arrInfos['rate'] == 0) {
                    throw new MP3StreamException("Header: unexpected rate");
                }

                // Frequency
                $arrInfos['frequency'] = $this->arrFrequencies[$arrInfos['version']][($arrHeader[2] >> 2) & 3];
                if ($arrInfos['frequency'] == 0) {
                    throw new MP3StreamException("Header: unexpected frequency");
                }

                // Padding
                $arrInfos['padding'] = (($arrHeader[2] & 2) == 2);

                // Private
                $arrInfos['private'] = (($arrHeader[2] & 1) == 1);

                // Mode
                switch ($arrHeader[3] >> 6) {
                    case 0 :
                        $arrInfos['mode'] = 'stereo';
                        break;
                    case 1 :
                        $arrInfos['mode'] = 'joint stereo';
                        break;
                    case 2 :
                        $arrInfos['mode'] = 'dual channel';
                        break;
                    case 3 :
                        $arrInfos['mode'] = 'single channel';
                        break;
                }

                // Extension
                switch (($arrHeader[3] >> 4 & 3)) {
                    case 0 :
                        $arrInfos['stereo'] = array(
                            'intensity' => FALSE,
                            'ms'        => FALSE,
                        );
                        break;
                    case 1 :
                        $arrInfos['stereo'] = array(
                            'intensity' => TRUE,
                            'ms'        => FALSE,
                        );
                        break;
                    case 2 :
                        $arrInfos['stereo'] = array(
                            'intensity' => FALSE,
                            'ms'        => TRUE,
                        );
                        break;
                    case 3 :
                        $arrInfos['stereo'] = array(
                            'intensity' => TRUE,
                            'ms'        => TRUE,
                        );
                        break;
                }

                // Copyright
                $arrInfos['copyright'] = (($arrHeader[3] & 8) == 8);

                // Original
                $arrInfos['original'] = (($arrHeader[3] & 4) == 4);

                // Enphasis
                switch ($arrHeader[3] & 3) {
                    case 0 :
                        $arrInfos['emphasis'] = 'none';
                        break;
                    case 1 :
                        $arrInfos['emphasis'] = '50/15 ms';
                        break;
                    case 2 :
                        throw new MP3StreamException("Header: unexpected emphase");
                        break;
                    case 3 :
                        $arrInfos['emphasis'] = 'CCITT J.17';
                        break;
                }

                // Frame Length
                if ($arrInfos['layer'] == 'I') {
                    $arrInfos['length'] = intval(4 * ((12 * $arrInfos['rate'] * 1000 / $arrInfos['frequency']) + ($arrInfos['padding'] ? 1 : 0)));

                } else {
                    $arrInfos['length'] = intval(144 * $arrInfos['rate'] * 1000 / $arrInfos['frequency']) + ($arrInfos['padding'] ? 1 : 0);
                }

                // Sample per frame
                $arrInfos['samples'] = $this->arrSamples[$arrInfos['version']][$arrInfos['layer']];

                return $arrInfos;
            }
        }

        private function initSQL()
        {
            if (! is_null($this->strSQLAddress) && ! is_null($this->intSQLPort) && ! is_null($this->strSQLDatabase) && ! is_null($this->strSQLUser)) {
                $strDSN = "pgsql:host={$this->strSQLAddress};port={$this->intSQLPort};dbname={$this->strSQLDatabase};user={$this->strSQLUser};" . (! is_null($this->strSQLPassword) ? "password=$this->strSQLPassword;" : '');

                $this->objPDO = new \PDO($strDSN);
                $this->objPDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } else {
                throw new MP3StreamException('SQL attributes not set');
            }
        }

        private function initStream()
        {
            if (! is_null($this->strStreamAddress) && ! is_null($this->intStreamPort) && ! is_null($this->strStreamPath)) {
                if (($this->resStream = fsockopen($this->strStreamAddress, $this->intStreamPort)) !== FALSE) {
                    fputs($this->resStream, "GET {$this->strStreamPath} HTTP/1.0\r\n"); //path in my case is /;stream.mp3
                    fputs($this->resStream, "Host: {$this->strStreamAddress}\r\n");
                    fputs($this->resStream, "User-Agent: WinampMPEG/2.8\r\n");
                    fputs($this->resStream, "Accept: */*\r\n");
                    fputs($this->resStream, "Icy-MetaData:1\r\n");
                    fputs($this->resStream, "Connection: close\r\n\r\n");
                } else {
                    throw new MP3StreamException('Failed to connect to stream');
                }
            } else {
                throw new MP3StreamException('Stream attributes not set');
            }
        }

        private function initMeta()
        {
            if (is_resource($this->resStream)) {
                $this->intStartTime = time();
                $strLine = fgets($this->resStream);

                $this->arrMetaInit = array();

                while ((substr($strLine, 0, 15) == 'HTTP/1.0 200 OK')
                    || (substr($strLine, 0, 10) == 'ICY 200 OK')
                    || (substr($strLine, 0, 7) == 'Server:')
                    || (substr($strLine, 0, 4) == 'icy-')
                    || (substr($strLine, 0, 4) == 'ice-')
                    || (substr($strLine, 0, 13) == 'Cache-Control')
                    || (substr($strLine, 0, 7) == 'Expires')
                    || (substr($strLine, 0, 6) == 'Pragma')
                    || (substr($strLine, 0, 12) == 'content-type')
                    || (substr($strLine, 0, 12) == 'Content-Type')) {

                    if (substr($strLine, 0, 4) == 'icy-') {
                        if (preg_match('/(\w+?):(.*)/', $strLine, $arrMatches)) {
                            $this->arrMetaInit[$arrMatches[1]] = $arrMatches[2];
                        }
                    }
                    $strLine = fgets($this->resStream);
                }
            } else {
                throw new MP3StreamException('Stream not opened');
            }
        }

        private function readStream()
        {
            if ((is_resource($this->resStream)) && ! feof($this->resStream)) {
                $strRawStream = fread($this->resStream, 1024);
                $this->strRawStream .= $strRawStream;
                $this->intBytesRead += strlen($strRawStream);
            } else {
                throw new MP3StreamException('Stream not opened');
            }
        }

        private function isInExclusionTimeFrame()
        {
            $isInExclusionTimeFrame = FALSE;
            foreach ($this->arrExclusionTimeFrames as $arrExclusionTimeFrame) {

                if (! is_null($arrExclusionTimeFrame[0]) && ! is_null($arrExclusionTimeFrame[1])) {
                    $isInExclusionTimeFrame = ((intval(date('i')) >= $arrExclusionTimeFrame[0]) && (intval(date('i')) < $arrExclusionTimeFrame[1]));
                } elseif (is_null($arrExclusionTimeFrame[0])) {
                    $isInExclusionTimeFrame = (intval(date('i')) < $arrExclusionTimeFrame[1]);
                } elseif (is_null($arrExclusionTimeFrame[1])) {
                    $isInExclusionTimeFrame = (intval(date('i')) >= $arrExclusionTimeFrame[0]);
                }
            }

            return $isInExclusionTimeFrame;
        }

        private function filterMeta()
        {
            if (array_key_exists('metaint', $this->arrMetaInit)) {
                if ($this->intBytesRead < $this->arrMetaInit['metaint']) {
                    // No meta in this fragment as raw stream
                    $this->strAudioStream .= $this->strRawStream;
                    $this->strRawStream = '';
                } else {
                    // Meta may be present

                    $intCut = $this->arrMetaInit['metaint'] - $this->intBytesRead + strlen($this->strRawStream);
                    $intMetaLength = ord(substr($this->strRawStream, $intCut, 1)) * 16;

                    // Only cut if buffer contains whole meta, else keep filling raw buffer
                    if (strlen($this->strRawStream) > $intCut + $intMetaLength) {
                        $strRawBufferLegA = substr($this->strRawStream, 0, $intCut);
                        // Skip 1 byte = meta length, gap is length (1) + meta(length*16)
                        $strMeta = trim(substr($this->strRawStream, $intCut + 1, $intMetaLength));
                        $strRawBufferLegB = substr($this->strRawStream, $intCut + 1 + $intMetaLength);

                        $this->strAudioStream .= $strRawBufferLegA . $strRawBufferLegB;
                        $this->strRawStream = '';

                        // Reset bytes read, including meta length + meta, we have to pause after an amount of audio only bytes
                        $this->intBytesRead = $this->intBytesRead - $this->arrMetaInit['metaint'] - $intMetaLength - 1;

                        if (($intMetaLength) > 0) {
                            if (preg_match_all('/(\w+)=\'(.*?)\';/', $strMeta, $arrMatches)) {
                                $this->arrMetaLive = array();
                                foreach ($arrMatches[1] as $intKey => $strKey) {
                                    $this->arrMetaLive[$strKey] = utf8_encode($arrMatches[2][$intKey]);
                                }
                            }
                        }
                    }
                }

            } else {
                // No live meta, only audio stream;
                $this->strAudioStream .= $this->strRawStream;
                $this->strRawStream = '';
            }
        }

        private function processMeta()
        {
            if ($this->arrMetaLive != $this->arrLastMetaLive) {
                $this->arrLastMetaLive = $this->arrMetaLive;

                if (array_key_exists('StreamTitle', $this->arrMetaLive)) {
                    $this->debug("Now playing: {$this->arrMetaLive['StreamTitle']}\n");
                    if (! is_null($this->strRegExpArtist)) {
                        if ((preg_match($this->strRegExpArtist, $this->arrMetaLive['StreamTitle'], $arrMatches)) && (count($arrMatches) > 1)) {
                            $this->strLiveArtist = $arrMatches[1];
                        }
                    } else {
                        $this->strLiveArtist = $this->arrMetaLive['StreamTitle'];
                    }

                    if (! is_null($this->strRegExpTitle)) {
                        if ((preg_match($this->strRegExpTitle, $this->arrMetaLive['StreamTitle'], $arrMatches)) && (count($arrMatches) > 1)) {
                            $this->strLiveTitle = $arrMatches[1];
                        }
                    } else {
                        $this->strLiveTitle = $this->arrMetaLive['StreamTitle'];
                    }
                }

                $this->processSQL();
            }
        }

        private function processSQL()
        {
            if (! $this->isInExclusionTimeFrame() && (($this->intStartTime + $this->intStartDelay) < time()) && ! $this->isFirstMetaSkipped) {
                if (($this->objPDO instanceof \PDO) && ($this->strLiveArtist != '') && ($this->strLiveTitle != '')) {

                    $objStmt = $this->objPDO->prepare("
                        SELECT  trac.trac_id,
                                trac.trac_last_play
                        FROM    {$this->strSQLSchema}.track trac
                        WHERE   trac.trac_artist = ?
                        AND     trac.trac_title = ?
                    ");

                    $objStmt->execute(array(
                        $this->strLiveArtist,
                        $this->strLiveTitle,
                    ));

                    if (($arrData = $objStmt->fetch(\PDO::FETCH_ASSOC)) !== FALSE) {
                        // UPDATE

                        if (strtotime($arrData['trac_last_play']) < (time() - 3600)) {

                            $objStmt = $this->objPDO->prepare("
                                UPDATE  {$this->strSQLSchema}.track
                                SET     trac_last_play = now(),
                                        trac_play_count = trac_play_count + 1
                                WHERE   trac_id = ?
                            ");

                            $objStmt->execute(array($arrData['trac_id']));

                            $objStmt = $this->objPDO->prepare("
                                INSERT INTO {$this->strSQLSchema}.playlist (
                                  trac_id,
                                  play_time
                                ) VALUES (
                                  ?,
                                  (SELECT trac_last_play FROM {$this->strSQLSchema}.track WHERE trac_id = ?)
                                )
                            ");

                            $objStmt->execute(array($arrData['trac_id'], $arrData['trac_id']));

                        }
                    } else {
                        // INSERT and save sample

                        $objStmt = $this->objPDO->prepare("
                            INSERT  INTO {$this->strSQLSchema}.track (
                                trac_artist,
                                trac_title,
                                trac_first_play,
                                trac_last_play,
                                trac_play_count
                            )   VALUES (
                                ?,
                                ?,
                                now(),
                                now(),
                                1
                            )
                        ");

                        $objStmt->execute(array(
                            $this->strLiveArtist,
                            $this->strLiveTitle,
                        ));

                        $this->intLiveTrackID = $this->objPDO->lastInsertId('track_trac_id_seq');

                        $objStmt = $this->objPDO->prepare("
                            INSERT INTO {$this->strSQLSchema}.playlist (
                                trac_id,
                                play_time
                            )   VALUES (
                                ?,
                                (SELECT trac_last_play FROM {$this->strSQLSchema}.track WHERE trac_id = ?)
                            )
                        ");

                        $objStmt->execute(array($this->intLiveTrackID, $this->intLiveTrackID));

                        $this->initSampling();
                    }

                }
            } else {
                $this->debug("Meta ignored: first meta skipping activated, start delay not expired or in exclusion time frame\n");
                $this->isFirstMetaSkipped = FALSE;
            }
        }

        private function initSampling()
        {
            if (is_resource($this->resSample)) {
                fclose($this->resSample);
            }

            if (($this->intSaveDuration > 0) && ! is_null($this->strDirectory)) {
                $strDirectory = $this->strDirectory . '/' . date('Y-W');

                if (! is_dir($strDirectory)) {
                    if (! @mkdir($strDirectory, 0777, TRUE)) {
                        throw new MP3StreamException('Unable to create sample directory');
                    }
                }

                $strFileName = sprintf("%08d - %s - %s.mp3", $this->intLiveTrackID, basename($this->strLiveArtist), basename($this->strLiveTitle));

                if (($this->resSample = @fopen("{$strDirectory}/{$strFileName}", 'w')) !== FALSE) {
                    $arrHeaderInfo = $this->decodeMP3Header($this->strAudioFrame);
                    $this->intFramesToSave = $arrHeaderInfo['frequency'] * $this->intSaveDuration / $arrHeaderInfo['samples'];

                    $this->debug("Sampling started to [{$strDirectory}/{$strFileName}]\n");
                }
            }
        }

        private function processSampling()
        {
            if (($this->intFramesToSave > 0) && (is_resource($this->resSample))) {
                fwrite($this->resSample, $this->strAudioFrame);
                $this->intFramesToSave --;

                $this->debug("#{$this->intFrameCount}/" . floor($this->intFramesToSave) . "  \r");

                if ($this->intFramesToSave <= 0) {
                    fclose($this->resSample);
                    $this->resSample = NULL;
                    $this->debug("Sampling completed                          \n");
                }
            }
        }

        private function syncAudio()
        {
            if (strlen($this->strAudioStream) <= 4) {
                return FALSE;
            } else {
                while (strlen($this->strAudioStream) > 4) {
                    try {
                        $arrHeaderInfo = $this->decodeMP3Header($this->strAudioStream);

                        // Potential valid header found. Check next header to confirm
                        if (strlen($this->strAudioStream) > 4 + $arrHeaderInfo['length']) {
                            // Length enough, examine potential next frame
                            try {
                                $arrNextHeaderInfo = $this->decodeMP3Header(substr($this->strAudioStream, $arrHeaderInfo['length']));
                                // Found a potential header on next frame. Check if data suposed to be constant are the same in both headers
                                if (
                                    ($arrNextHeaderInfo['version'] == $arrHeaderInfo['version'])
                                    && ($arrNextHeaderInfo['layer'] == $arrHeaderInfo['layer'])
                                    && ($arrNextHeaderInfo['frequency'] == $arrHeaderInfo['frequency'])
                                    && ($arrNextHeaderInfo['private'] == $arrHeaderInfo['private'])
                                    && ($arrNextHeaderInfo['mode'] == $arrHeaderInfo['mode'])
                                    && ($arrNextHeaderInfo['copyright'] == $arrHeaderInfo['copyright'])
                                    && ($arrNextHeaderInfo['private'] == $arrHeaderInfo['private'])
                                    && ($arrNextHeaderInfo['emphasis'] == $arrHeaderInfo['emphasis'])
                                ) {
                                    return TRUE;
                                } else {
                                    // Next potential frame has a correct header but constants changed: false positive, current frame not located to header start
                                    $this->debug("False positive\n");
                                    $this->strAudioStream = substr($this->strAudioStream, 1);
                                }

                            } catch (Exception $e) {
                                // Next potential frame has a incorrect header, current frame not located to header start
                                $this->debug("Wrong next header\n");
                                $this->strAudioStream = substr($this->strAudioStream, 1);
                            }
                        } else {
                            return FALSE;
                        }
                    } catch (Exception $e) {
                        $this->strAudioStream = substr($this->strAudioStream, 1);
                    }
                }

                return FALSE;
            }
        }

        private function extractNextFrame()
        {
            if ($this->syncAudio()) {
                $arrHeaderInfo = $this->decodeMP3Header($this->strAudioStream);
                $this->strAudioFrame = substr($this->strAudioStream, 0, $arrHeaderInfo['length']);
                $this->strAudioStream = substr($this->strAudioStream, $arrHeaderInfo['length']);
                $this->intFrameCount ++;

                return TRUE;
            } else {
                return FALSE;
            }
        }

        private function processAudio()
        {
            while ($this->extractNextFrame()) {
                if ($this->intTick != time()) {
                    $this->debug("#{$this->intFrameCount}      \r");
                    $this->intTick = time();
                }
                $this->processSampling();
                echo $this->strAudioFrame;
            }
        }

        private function debug($strMessage)
        {
            if (self::DEBUG_MODE) {
                if (! is_resource($this->resStdErr)) {
                    $this->resStdErr = fopen('php://stderr', 'w');
                }
                fwrite($this->resStdErr, date('H:i:s') . " - $strMessage");
            }
        }


        public function setStreamAddress($strStreamAddress)
        {
            if (is_string($strStreamAddress)) {
                $this->strStreamAddress = $strStreamAddress;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setStreamPort($intStreamPort)
        {
            if (is_numeric($intStreamPort)) {
                $this->intStreamPort = $intStreamPort;
            } else {
                throw new MP3StreamException('Integer expected');
            }
        }

        public function setStreamPath($strStreamPath)
        {
            if (is_string($strStreamPath)) {
                $this->strStreamPath = $strStreamPath;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setSQLAddress($strSQLAddress)
        {
            if (is_string($strSQLAddress)) {
                $this->strSQLAddress = $strSQLAddress;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setSQLPort($intSQLPort)
        {
            if (is_numeric($intSQLPort)) {
                $this->intSQLPort = $intSQLPort;
            } else {
                throw new MP3StreamException('Integer expected');
            }
        }

        public function setSQLDatabase($strSQLDatabase)
        {
            if (is_string($strSQLDatabase)) {
                $this->strSQLDatabase = $strSQLDatabase;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setSQLUser($strSQLUser)
        {
            if (is_string($strSQLUser)) {
                $this->strSQLUser = $strSQLUser;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setSQLPassword($strSQLPassword)
        {
            if (is_string($strSQLPassword)) {
                $this->strSQLPassword = $strSQLPassword;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setSQLSchema($strSQLSchema)
        {
            if (is_string($strSQLSchema)) {
                $this->strSQLSchema = $strSQLSchema;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setDirectory($strDirectory)
        {
            if (is_string($strDirectory)) {
                $this->strDirectory = $strDirectory;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setRegExpArtist($strRegExpArtist)
        {
            if (is_string($strRegExpArtist)) {
                $this->strRegExpArtist = $strRegExpArtist;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setRegExpTitle($strRegExpTitle)
        {
            if (is_string($strRegExpTitle)) {
                $this->strRegExpTitle = $strRegExpTitle;
            } else {
                throw new MP3StreamException('String expected');
            }
        }

        public function setSaveDuration($intSaveDuration)
        {
            if (is_numeric($intSaveDuration)) {
                $this->intSaveDuration = $intSaveDuration;
            } else {
                throw new MP3StreamException('Integer expected');
            }
        }

        public function addExclusionTimeFrame($intStart = NULL, $intEnd = NULL)
        {
            if (
                ((is_numeric($intStart) && ($intStart >= 0) && ($intStart <= 60)) || is_null($intStart))
                && ((is_numeric($intEnd) && ($intEnd >= 0) && ($intEnd <= 60)) || is_null($intEnd))
                && (! is_null($intStart) || ! is_null($intEnd))
            ) {
                array_push($this->arrExclusionTimeFrames, array($intStart, $intEnd));
            }
        }

        public function setStartDelay($intStartDelay)
        {
            if (is_numeric($intStartDelay)) {
                $this->intStartDelay = $intStartDelay;
            } else {
                throw new MP3StreamException('Integer expected');
            }
        }

        public function setFirstFrameSkip($isFirstFrameSkipped)
        {
            $this->isFirstMetaSkipped = ($isFirstFrameSkipped == TRUE);
        }

        public function run()
        {
            $this->initSQL();
            $this->initStream();
            $this->initMeta();
            while (TRUE) {
                $this->readStream();
                $this->filterMeta();
                $this->processMeta();
                $this->processAudio();
            }
        }
    }

