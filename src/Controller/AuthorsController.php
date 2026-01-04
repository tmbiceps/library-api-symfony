<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthorsController extends AbstractController
{
    public function __construct(
        private readonly AuthorRepository $authors,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/authors', name: 'authors_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = array_map(fn(Author $a) => $this->authorToArray($a), $this->authors->findAll());
        return $this->json($items, 200);
    }

    #[Route('/authors/{id}', name: 'authors_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $author = $this->authors->find($id);
        if (!$author) {
            return $this->json(['error' => 'not_found'], 404);
        }
        return $this->json($this->authorToArray($author), 200);
    }

    #[Route('/authors', name: 'authors_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $first = trim((string)($data['first_name'] ?? ''));
        $last  = trim((string)($data['last_name'] ?? ''));

        if ($first === '' || $last === '') {
            return $this->json(['error' => 'validation_failed'], 400);
        }

        $author = new Author();
        $author->setFirstName($first);
        $author->setLastName($last);

        $this->em->persist($author);
        $this->em->flush();

        return $this->json($this->authorToArray($author), 201);
    }

    #[Route('/authors/{id}', name: 'authors_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $author = $this->authors->find($id);
        if (!$author) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $first = trim((string)($data['first_name'] ?? ''));
        $last  = trim((string)($data['last_name'] ?? ''));

        if ($first === '' || $last === '') {
            return $this->json(['error' => 'validation_failed'], 400);
        }

        $author->setFirstName($first);
        $author->setLastName($last);
        $this->em->flush();

        return new Response('', 204);
    }

    #[Route('/authors/{id}', name: 'authors_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $author = $this->authors->find($id);
        if (!$author) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $this->em->remove($author);
        $this->em->flush();

        return new Response('', 204);
    }

    private function authorToArray(Author $a): array
    {
        return [
            'id' => $a->getId(),
            'first_name' => $a->getFirstName(),
            'last_name' => $a->getLastName(),
        ];
    }
}
