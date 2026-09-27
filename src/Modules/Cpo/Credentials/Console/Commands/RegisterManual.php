<?php

namespace Ocpi\Modules\Cpo\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Ocpi\Models\Party;
use Ocpi\Models\PartyRole;

class RegisterManual extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocpi:credentials:cpo_register_manual
                            {code : Internal code we use to identify this EMSP Party, e.g. NAYAX}
                            {--name= : Display name, e.g. "Nayax"}
                            {--party_id= : Their 3-char OCPI party_id, e.g. NAK}
                            {--country_code= : Their 2-char OCPI country code, e.g. IL}
                            {--version=2.2.1 : Mutual OCPI version to record for this Party}
                            {--locations-url= : Their Locations module endpoint}
                            {--tariffs-url= : Their Tariffs module endpoint}
                            {--sessions-url= : Their Sessions module endpoint}
                            {--cdrs-url= : Their CDRs module endpoint}
                            {--token= : Server token they gave us, sent as Authorization when we call them}
                            {--token-is-base64 : Pass this when --token is already base64-encoded, as given}
                            {--client-token= : Token we want them to present to us; auto-generated if omitted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually register an EMSP Party without performing the OCPI Credentials handshake (for partners who exchange credentials out-of-band)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $code = $this->argument('code');
        $token = $this->option('token');

        if (blank($token)) {
            $this->error('A --token is required.');

            return Command::FAILURE;
        }

        $serverToken = $this->option('token-is-base64')
            ? base64_decode($token, true)
            : $token;

        if ($serverToken === false) {
            $this->error('--token could not be base64-decoded; check --token-is-base64.');

            return Command::FAILURE;
        }

        $connection = DB::connection(config('ocpi.database.connection'));

        try {
            $party = $connection->transaction(function () use ($code, $serverToken) {
                $givenEndpoints = [
                    'locations' => $this->option('locations-url'),
                    'tariffs' => $this->option('tariffs-url'),
                    'sessions' => $this->option('sessions-url'),
                    'cdrs' => $this->option('cdrs-url'),
                ];

                $endpoints = [];
                foreach ($givenEndpoints as $module => $url) {
                    if (filled($url)) {
                        $endpoints[$module] = $url;
                    }
                }

                $party = Party::updateOrCreate(['code' => $code], [
                    'name' => $this->option('name') ?? $code,
                    'version' => $this->option('version'),
                    'registered' => true,
                    'endpoints' => $endpoints,
                    'server_token' => $serverToken,
                ]);

                $party->client_token = $this->option('client-token') ?? $party->client_token ?? $party->generateToken();
                $party->save();

                PartyRole::updateOrCreate(
                    ['party_id' => $party->id, 'code' => $this->option('party_id')],
                    [
                        'role' => 'EMSP',
                        'country_code' => $this->option('country_code'),
                        'business_details' => ['name' => $this->option('name') ?? $code],
                    ]
                );

                return $party;
            });
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->info('EMSP Party "'.$party->code.'" registered manually.');
        $this->info('client_token to hand to them for their inbound Commands calls: '.$party->client_token);

        return Command::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'code' => 'Internal code for this EMSP Party (e.g. NAYAX)?',
        ];
    }
}
