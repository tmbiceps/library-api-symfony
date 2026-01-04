<?php

namespace App\Controller;

use App\Dto\CopyInputDto;
use App\Entity\Copy;
use App\Repository\BookRepository;
use App\Repository\CopyRepository;
use App\Service\JsonBody;
use App\Service\ValidationErrorResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CopiesController extends AbstractController
{
    public function __construct(
        private readonly CopyRepository $copies,
        private readonly BookRepository $books,
        private readonly EntityManagerInterface $em,
        private readonly JsonBody $jsonBody,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorResponder $errorResponder,
    ) {}

    #[Route('/copies', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->copies->findBy([], ['id' => 'ASC']);
        $out = array_map(fn(Copy $c) => $this->copyToArray($c), $items);
        return $this->json($out, 200);
    }

    #[Route('/copies/{id}', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $copy = $this->copies->find($id);
        if (!$copy) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }
        return $this->json($this->copyToArray($copy), 200);
    }

    #[Route('/copies', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->jsonBody->decode($request);
        $dto = new CopyInputDto();
        $dto->bookId = isset($data['bookId']) ? (int) $data['bookId'] : null;
        $dto->signature = $data['signature'] ?? null;

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return $this->errorResponder->fromViolations($violations);
        }

        $book = $this->books->find((int) $dto->bookId);
        if (!$book) {
            return $this->errorResponder->badRequest('Book not found');
        }

        $copy = (new Copy())
            ->setBook($book)
            ->setSignature((string) $dto->signature);

        $this->em->persist($copy);
        $this->em->flush();

        return $this->json($this->copyToArray($copy), 201);
    }

    #[Route('/copies/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $copy = $this->copies->find($id);
        if (!$copy) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $data = $this->jsonBody->decode($request);
        $dto = new CopyInputDto();
        $dto->id = isset($data['id']) ? (int) $data['id'] : null;
        $dto->bookId = isset($data['bookId']) ? (int) $data['bookId'] : null;
        $dto->signature = $data['signature'] ?? null;

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            return $this->errorResponder->fromViolations($violations);
        }

        $book = $this->books->find((int) $dto->bookId);
        if (!$book) {
            return $this->errorResponder->badRequest('Book not found');
        }

        $copy->setBook($book);
        $copy->setSignature((string) $dto->signature);
        $this->em->flush();

        return new Response('', 204);
    }

    #[Route('/copies/{id}', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $copy = $this->copies->find($id);
        if (!$copy) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $this->em->remove($copy);
        $this->em->flush();

        return new Response('', 204);
    }

    private function copyToArray(Copy $c): array
    {
        $b = $c->getBook();
        return [
            'id' => $c->getId(),
            'signature' => $c->getSignature(),
            'book' => $b ? [
                'id' => $b->getId(),
                'title' => $b->getTitle(),
                'year' => $b->getYear(),
            ] : null,
        ];
    }
}
