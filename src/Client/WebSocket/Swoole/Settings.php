<?php

/** @copyright 2020 ng-voice GmbH */

declare(strict_types=1);

namespace OpiyOrg\AriClient\Client\WebSocket\Swoole;

/**
 * Settings for the Swoole ARI web socket client implementation.
 *
 * @package OpiyOrg\AriClient\Client\WebSocket\Swoole
 *
 * @author Lukas Stermann <lukas@ng-voice.com>
 */
class Settings
{
    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
