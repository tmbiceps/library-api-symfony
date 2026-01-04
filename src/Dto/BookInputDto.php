<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BookInputDto
{
    public ?int $id = null;

    #[Assert\NotBlank]
    public ?string $title = null;

    #[Assert\PositiveOrZero]
    public ?int $year = null;

    #[Assert\NotBlank]
    public ?int $authorId = null;
}
