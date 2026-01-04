<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationErrorResponder
{
    public function badRequest(string $message, array $details = []): JsonResponse
    {
        return new JsonResponse([
            'error' => $message,
            'details' => $details,
        ], 400);
    }

    public function fromViolations(ConstraintViolationListInterface $violations): JsonResponse
    {
        $details = [];
        foreach ($violations as $violation) {
            $details[] = [
                'field' => (string) $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        return $this->badRequest('Validation failed', $details);
    }
}
