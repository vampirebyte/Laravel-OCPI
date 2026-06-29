<?php

namespace Ocpi\Modules\Emsp\Commands\Server\Controllers\V2_1_1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Ocpi\Models\Commands\Command;
use Ocpi\Models\Commands\Enums\CommandResultType;
use Ocpi\Modules\Emsp\Commands\Events;
use Ocpi\Support\Enums\OcpiClientErrorCode;
use Ocpi\Support\Server\Controllers\Controller;

class PostController extends Controller
{
    public function __invoke(
        Request $request,
        string $type,
        ?string $id = null,
    ): JsonResponse {
        try {
            $command = Command::query()
                ->with(['party_role'])
                ->where('id', $id)
                ->where('type', $type)
                ->first();

            if (! $command) {
                Log::channel('ocpi')->error('Unknown Command '.$type.':'.$id);

                return $this->ocpiClientErrorResponse(
                    statusCode: OcpiClientErrorCode::InvalidParameters,
                    statusMessage: 'Unknown Command.',
                );
            }

            $result = $request->input('result');
            $commandResultType = CommandResultType::fromName($result);
            if (! $commandResultType) {
                throw new Exception('Unknown CommandResultType '.$result.' for Command '.$type.':'.$id);
            }

            $command->result = $commandResultType->name;
            $command->save();

            if (
                $commandResultType === CommandResultType::ACCEPTED
                || $commandResultType === CommandResultType::CANCELED_RESERVATION
            ) {
                Events\CommandResultSucceeded::dispatch($command->party_role->id, $command->id, $command->type->name);
            } else {
                Events\CommandResultError::dispatch($command->party_role->id, $command->id, $command->type->name, $command->payload);
            }

            return $this->ocpiSuccessResponse();
        } catch (Exception $e) {
            Log::channel('ocpi')->error($e->getMessage());

            return $this->ocpiServerErrorResponse();
        }
    }
}
