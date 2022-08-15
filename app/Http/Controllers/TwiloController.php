<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;

class TwiloController extends Controller
{

    public function notify()
    {
        // $number = '9818155424';
        // $message = "HI";
        // return $this->client->messages->create($number, [
        //     'from' => config('laratwilio.sms_from'),

        //     'body' => $message
        // ]);

        $account_sid = 'ACfd7c7d1807877a9776862fb26696e814';
        $auth_token = '5728c708124470965450faebe0ba643f';
// In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]

// A Twilio number you own with SMS capabilities
        $twilio_number = "+16185185932";

        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            // Where to send a text message (your cell phone?)
            '+9779818155424',
            array(
                'from' => $twilio_number,
                'body' => 'I sent this message in under 10 minutes!',
            )
        );
    }
}
