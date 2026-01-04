<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AuthorInputDto
{
    public ?int $id = null;

    #[Assert\NotBlank]
    public ?string $first_name = null;

    #[Assert\NotBlank]
    public ?string $last_name = null;
}
