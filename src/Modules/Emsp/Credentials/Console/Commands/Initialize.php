<?php

namespace Ocpi\Modules\Emsp\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Ocpi\Models\Party;

class Initialize extends Command
{
    protected $signature = 'ocpi:credentials:initialize';

    protected $description = 'Initialize a new "Sender" Party to start credentials exchange';

    public function handle()
    {
        $input = [];

        $input['name'] = $this->ask('Party name');
        $input['code'] = $this->ask('Party ID or code');

        if (Party::where('code', $input['code'])->exists()) {
            $this->error('Party already exists.');

            return Command::FAILURE;
        }

        $input['url'] = $this->ask('URL of API versions endpoint');
        $input['server_token'] = $this->ask('Credentials Token');

        try {
            $party = Party::create($input);
        } catch (Exception $e) {
            $this->error('Error creating Party.');
            $this->newLine(2);
            $this->error($e);

            return Command::FAILURE;
        }

        $this->info('Party "'.$party->code.'" created successfully.');
        $this->info('Credentials exchange can be launch executing: php artisan ocpi:credentials:register '.$party->code);

        return Command::SUCCESS;
    }
}
