<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

namespace AriStasisApp\Model\Message;


/**
 * Notification that another WebSocket has taken over for an application.
 * An application may only be subscribed to by a single WebSocket at a time.
 * If multiple WebSockets attempt to subscribe to the same application,
 * the newer WebSocket wins, and the older one receives this event.
 *
 * @package AriStasisApp\Model\Message
 */
class ApplicationReplaced extends Event
{
}