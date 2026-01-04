<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class JsonBody
{
    /**
     * @return array<string, mixed>
     */
    public function decode(Request $request): array
    {
        $raw = $request->getContent();
        if ($raw === '' || $raw === null) {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
