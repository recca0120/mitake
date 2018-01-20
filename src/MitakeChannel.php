<?php

namespace Recca0120\Mitake;

use Illuminate\Notifications\Notification;

class MitakeChannel
{
    /**
     * $client.
     *
     * @var \Recca0120\Mitake\Client
     */
    protected $client;

    /**
     * __construct.
     *
     * @param \Recca0120\Mitake\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Recca0120\Mitake\MitakeMessage
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('Mitake')) {
            return;
        }

        $message = $notification->toMitake($notifiable);

        if (is_string($message)) {
            $message = new MitakeMessage($message);
        }

        return $this->client->send([
            'to' => $to,
            'text' => trim($message->content),
        ]);
    }
}
