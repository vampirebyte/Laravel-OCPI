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
    protected $description = 'Initialize a new "Receiver" Party to start credentials exchange';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $input = [];

        $input['name'] = $this->ask('EMSP Party name');
        $input['code'] = $this->ask('EMSP Party ID or code');

        if (Party::where('code', $input['code'])->exists()) {
            $this->error('EMS Party already exists.');

            return Command::FAILURE;
        }

        $input['url'] = $this->ask('EMS URL of API versions endpoint');
        $input['client_token'] = $this->ask('Token Used by EMSP to identify this CPO');

        try {
            $party = Party::create($input);
        } catch (Exception $e) {
            $this->error('Error creating EMSP Party.');
            $this->newLine(2);
            $this->error($e);

            return Command::FAILURE;
        }

        $this->info('EMSP Party "'.$party->code.'" created successfully.');

        return Command::SUCCESS;
    }
}
