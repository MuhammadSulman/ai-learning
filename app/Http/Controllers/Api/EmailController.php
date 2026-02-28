<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateEmailRequest;
use App\Services\EmailGeneratorService;

class EmailController extends Controller
{
    public function __construct(
        private EmailGeneratorService $emailService
    ) {}

    public function generate(GenerateEmailRequest $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        if ($data['stream']) {
            return $this->emailService->stream(
                $data['subject'],
                $data['context'],
                $data['temperature'],
                $data['max_tokens'],
            );
        }

        return $this->emailService->generate(
            $data['subject'],
            $data['context'],
            $data['temperature'],
            $data['max_tokens'],
        );
    }
}
