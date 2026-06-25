<?php

namespace Ocpi\Modules\Cpo\Credentials\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Ocpi\Models\Party;

class Initialize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocpi:credentials:cpo_initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize a new "Sender" Party to start credentials exchange';

    /**
     * Execute the console command.
     */
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
        $input['client_token'] = $this->ask('Offline generated Credential Token');

        try {
            $party = Party::create($input);
        } catch (Exception $e) {
            $this->error('Error creating Party.');
            $this->newLine(2);
            $this->error($e);

            return Command::FAILURE;
        }

        $this->info('Party "'.$party->code.'" created successfully.');

        return Command::SUCCESS;
    }
}
