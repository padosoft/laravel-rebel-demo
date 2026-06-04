<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Step-up Drivers
    |--------------------------------------------------------------------------
    |
    | Toggle individual step-up drivers that this bridge exposes.
    |
    | Key                 Default  Effect
    | ─────────────────── ─────── ─────────────────────────────────────────────
    | drivers.otpz        true     Register the OtpzStepUpDriver into the Rebel
    |                              DriverRegistry when benbjurstrom/otpz is also
    |                              installed. Set false to disable without
    |                              uninstalling the bridge.
    |
    */

    'drivers' => [
        'otpz' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Options
    |--------------------------------------------------------------------------
    |
    | These options are forwarded to (or supplement) the benbjurstrom/otpz
    | configuration. The source of truth for expiration/throttle lives in the
    | otpz package's own config/otpz.php; the keys here are extras that the
    | bridge layer consumes.
    |
    | Key                 Default  Effect
    | ─────────────────── ─────── ─────────────────────────────────────────────
    | options.max_attempts  3      Maximum number of failed verification
    |                              attempts before the OTP is invalidated.
    |                              Mirrors otpz's hard-coded value; surfaced
    |                              here so the bridge can document/override it
    |                              in one place.
    |
    */

    'options' => [
        'max_attempts' => 3,
    ],

];
