<?php

namespace Ocpi\Modules\Shared\Versions\Actions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Ocpi\Models\Party;
use Ocpi\Support\Client\Client as OcpiClient;

class PartyInformationAndDetailsSynchronizeAction
{
    /**
     * Sync the Party versions and endpoints. A preferred version (e.g. the one a Party used to call
     * our Credentials endpoint) wins over the latest mutual version when both sides support it.
     */
    public function handle(Party $party, string $configKey = 'ocpi-emsp', ?string $preferredVersion = null): Party
    {
        // OCPI GET call for Versions Information of the Party, store OCPI version and URL.
        Log::channel('ocpi')->info('Party '.$party->code.' - OCPI GET call for Versions Information of the Party on '.$party->url);
        [$ocpiClient, $versionList] = $this->versionsInformation($party);
        throw_if(
            ! is_array($versionList),
            new Exception('Party '.$party->code.' - Empty or invalid response for Versions Information.')
        );

        // Find supported OCPI versions.
        $supportedVersionList = array_keys(config($configKey.'.versions', []));
        throw_if(
            count($supportedVersionList) === 0,
            new Exception('No supported version found.')
        );

        // Find party OCPI versions.
        $partyVersionList = Arr::sortDesc(
            Arr::keyBy(
                Arr::where($versionList, function ($version) {
                    return Arr::has($version, ['version', 'url']);
                }),
                'version')
        );
        throw_if(
            count($partyVersionList) === 0,
            new Exception('Party '.$party->code.' - No valid version found for Party.')
        );

        // Find preferred, otherwise latest mutual OCPI version.
        $latestMutualVersion = null;
        if ($preferredVersion !== null && in_array($preferredVersion, $supportedVersionList) && isset($partyVersionList[$preferredVersion])) {
            $latestMutualVersion = $partyVersionList[$preferredVersion];
        } else {
            foreach ($partyVersionList as $version => $item) {
                if (in_array($version, $supportedVersionList)) {
                    $latestMutualVersion = $item;
                    break;
                }
            }
        }
        throw_if(
            $latestMutualVersion === null,
            new Exception('Party '.$party->code.' - No mutual version found.')
        );

        Log::channel('ocpi')->info('Party '.$party->code.' - Set Party OCPI version to '.$latestMutualVersion['version']);
        $party->version = $latestMutualVersion['version'];
        $party->version_url = $latestMutualVersion['url'];
        throw_if(
            ! $party->save(),
            new Exception('Party '.$party->code.' - Error updating Party OCPI version.')
        );

        // OCPI GET call for Versions Details of the Party, store OCPI endpoints.
        Log::channel('ocpi')->info('Party '.$party->code.' - OCPI GET call for Versions Details of the Party for version '.$party->version);
        $ocpiClient->module('versions.details');
        $versionDetails = $ocpiClient->versions()->details();
        throw_if(
            ! is_array($versionDetails) || ! isset($versionDetails['version']) || ! is_array($versionDetails['endpoints'] ?? null),
            new Exception('Party '.$party->code.' - Empty or invalid response for Versions Details.')
        );
        throw_if(
            $versionDetails['version'] !== $party->version,
            new Exception('Party '.$party->code.' - Version mismatch for Versions Details: requested '.$party->version.' / received '.$versionDetails['version'].'.')
        );

        // Set Party OCPI endpoints for version.
        Log::channel('ocpi')->info('Party '.$party->code.' - Set OCPI endpoints for version '.$party->version);
        $party->endpoints = $this->endpointUrls($versionDetails['endpoints'], $configKey, $party->version);
        throw_if(
            ! Arr::has($party->endpoints, 'credentials'),
            new Exception('Party '.$party->code.' - Missing required `credentials` Module endpoint.')
        );

        throw_if(
            ! $party->save(),
            new Exception('Party '.$party->code.' - Error updating Party OCPI endpoints.')
        );

        return $party;
    }

    /**
     * The first call to a party is made before its version is known, so the
     * token goes out as is. A 2.2+ party expects it base64 encoded: when the
     * plain token is refused, it is tried once more encoded.
     *
     * @return array{0: OcpiClient, 1: mixed}
     */
    private function versionsInformation(Party $party): array
    {
        $ocpiClient = new OcpiClient($party, 'versions.information');

        try {
            return [$ocpiClient, $ocpiClient->versions()->information()];
        } catch (Exception $exception) {
            if ($party->version !== null) {
                throw $exception;
            }

            Log::channel('ocpi')->info('Party '.$party->code.' - plain token refused, trying it base64 encoded (OCPI 2.2+)');
            $party->version = '2.2';
            $ocpiClient = new OcpiClient($party, 'versions.information');

            return [$ocpiClient, $ocpiClient->versions()->information()];
        }
    }

    /**
     * Endpoint URL per module. From 2.2 a party may list a module twice, as
     * SENDER and as RECEIVER: the one we talk to is the counterpart of our own
     * role for that module (we push to its RECEIVER, pull from its SENDER).
     *
     * @param  array<int, array{identifier: string, url: string, role?: string}>  $endpoints
     * @return array<string, string>
     */
    private function endpointUrls(array $endpoints, string $configKey, string $version): array
    {
        $urls = [];
        $counterpartRoles = ['SENDER' => 'RECEIVER', 'RECEIVER' => 'SENDER'];

        foreach ($endpoints as $endpoint) {
            $identifier = $endpoint['identifier'];
            // versions are keyed "2.1.1": read the list, the dots are not config nesting
            $ourRole = config("{$configKey}.versions")[$version]['modules'][$identifier]['role'] ?? null;
            $wantedRole = $counterpartRoles[$ourRole] ?? null;

            // Keep the first URL, unless this one has the role we want.
            if (! isset($urls[$identifier]) || ($wantedRole !== null && ($endpoint['role'] ?? null) === $wantedRole)) {
                $urls[$identifier] = $endpoint['url'];
            }
        }

        return $urls;
    }
}
