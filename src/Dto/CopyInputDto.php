<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CopyInputDto
{
    public ?int $id = null;

    #[Assert\NotBlank]
    public ?int $bookId = null;

    #[Assert\NotBlank]
    public ?string $signature = null;
}
