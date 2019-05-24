<?php

/** @copyright 2019 ng-voice GmbH */

namespace NgVoice\AriClient\Models\Message;

use NgVoice\AriClient\Models\Channel;

/**
 * Channel changed location in the dialplan.
 *
 * @package NgVoice\AriClient\Models\Message
 */
final class ChannelDialplan extends Event
{
    /**
     * @var string The data to be passed to the application.
     */
    private $dialplan_app_data;

    /**
     * @var Channel The channel that changed dialplan location.
     */
    private $channel;

    /**
     * @var string The application about to be executed.
     */
    private $dialplan_app;

    /**
     * @return string
     */
    public function getDialplanAppData(): string
    {
        return $this->dialplan_app_data;
    }

    /**
     * @param string $dialplan_app_data
     */
    public function setDialplanAppData(string $dialplan_app_data): void
    {
        $this->dialplan_app_data = $dialplan_app_data;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     */
    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getDialplanApp(): string
    {
        return $this->dialplan_app;
    }

    /**
     * @param string $dialplan_app
     */
    public function setDialplanApp(string $dialplan_app): void
    {
        $this->dialplan_app = $dialplan_app;
    }
}