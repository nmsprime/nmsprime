<?php

namespace Modules\HfcBase\Helpers;

class IcingaApi
{
    /**
     * Base url to Icinga2 API - can be changed in hfcbase config.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Icinga2 API user - can be changed in hfcbase config.
     *
     * @var string
     */
    protected $icingaApiUser;

    /**
     * Icinga2 API password - can be changed in hfcbase config.
     *
     * @var string
     */
    protected $icingaApiPassword;

    /**
     * The request payload.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $payload;

    /**
     * The response from the Icinga2 API.
     *
     * @var array
     */
    protected $results;

    /**
     * Setup Connection to the Icinga2 API.
     *
     * @param string $type Should be 'Service' or 'Host'
     * @param string $filter Icinga2 API filter (see Icinga2 API doc)
     * @param array $options Additional options for Icinga2 API
     */
    public function __construct(string $type, string $filter, array $options = [])
    {
        $defaultOptions = collect(['sticky' => false, 'notify' => true, 'pretty' => false]);

        $this->icingaApiUser = config('hfcbase.icinga.api.user');
        $this->icingaApiPassword = config('hfcbase.icinga.api.password');
        $this->baseUrl = config('hfcbase.icinga.api.url');
        $this->payload = collect(['type' => $type, 'filter' => $filter])
            ->merge($defaultOptions)
            ->merge($options);
    }

    /**
     * Call to Icinga2 Api to Acknowlegdge a Problem
     *
     * @return array
     */
    public function acknowledgeProblem(\Carbon\Carbon $expiry = null)
    {
        $this->payload = $this->payload->merge([
            'author' => auth()->user()->first_name.' '.auth()->user()->last_name,
            'comment' => 'Acknowledged by NMS Prime via Trouble Dashboard.',
        ])->when($expiry, function ($payload) use ($expiry) {
            return $payload->merge([
                'expiry' => $expiry->timestamp,
            ]);
        });

        $this->url = $this->baseUrl.'actions/acknowledge-problem';

        $this->curlRequest();

        return $this->results;
    }

    /**
     * Call to Icinga2 Api to Remove the Acknowledgement
     *
     * @return array
     */
    public function removeAcknowledgement()
    {
        $this->url = $this->baseUrl.'actions/remove-acknowledgement';
        $this->curlRequest();

        return $this->results;
    }

    /**
     * The actual curl (HTTP) Request to Icinga2
     *
     * @return void
     */
    protected function curlRequest()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_URL => $this->url,
            CURLOPT_USERPWD => $this->icingaApiUser.':'.$this->icingaApiPassword,
            CURLOPT_POSTFIELDS => $this->payload->toJson(),
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $this->results = json_decode(curl_exec($curl), true);
        curl_close($curl);
    }
}
