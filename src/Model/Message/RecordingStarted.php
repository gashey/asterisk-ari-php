<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

namespace AriStasisApp\Model\Message;


use AriStasisApp\Model\LiveRecording;

/**
 * Event showing the start of a recording operation.
 *
 * @package AriStasisApp\Model\Message
 */
class RecordingStarted extends Event
{
    /**
     * @var LiveRecording Recording control object
     */
    private $recording;

    /**
     * @return LiveRecording
     */
    public function getRecording(): LiveRecording
    {
        return $this->recording;
    }

    /**
     * @param LiveRecording $recording
     */
    public function setRecording(LiveRecording $recording): void
    {
        $this->recording = $recording;
    }
}