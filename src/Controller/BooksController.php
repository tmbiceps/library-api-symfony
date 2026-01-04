<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BooksController extends AbstractController
{
    public function __construct(
        private readonly BookRepository $books,
        private readonly AuthorRepository $authors,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/books', name: 'books_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $authorId = $request->query->get('authorId');
        if ($authorId !== null && $authorId !== '') {
    $items = $this->books->findBy(['author' => (int)$authorId], ['id' => 'ASC']);
} else {
    $items = $this->books->findBy([], ['id' => 'ASC']);
}


        $out = array_map(fn(Book $b) => $this->bookToArray($b), $items);
        return $this->json($out, 200);
    }

    #[Route('/books/{id}', name: 'books_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $book = $this->books->find($id);
        if (!$book) {
            return $this->json(['error' => 'not_found'], 404);
        }
        return $this->json($this->bookToArray($book), 200);
    }

    #[Route('/books', name: 'books_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $title = trim((string)($data['title'] ?? ''));
        $year = $data['year'] ?? null;
        $authorId = $data['authorId'] ?? null;

        if ($title === '' || !is_numeric($year) || (int)$year < 0 || !is_numeric($authorId)) {
            return $this->json(['error' => 'validation_failed'], 400);
        }

        $author = $this->authors->find((int)$authorId);
        if (!$author) {
            return $this->json(['error' => 'validation_failed'], 400);
        }

        $book = new Book();
        $book->setTitle($title);
        $book->setYear((int)$year);
        $book->setAuthor($author);

        $this->em->persist($book);
        $this->em->flush();

        return $this->json($this->bookToArray($book), 201);
    }

    #[Route('/books/{id}', name: 'books_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): Response
    {
        $book = $this->books->find($id);
        if (!$book) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $title = trim((string)($data['title'] ?? ''));
        $year = $data['year'] ?? null;
        $authorId = $data['authorId'] ?? null;

        if ($title === '' || !is_numeric($year) || (int)$year < 0 || !is_numeric($authorId)) {
            return $this->json(['error' => 'validation_failed'], 400);
        }

        $author = $this->authors->find((int)$authorId);
        if (!$author) {
            return $this->json(['error' => 'validation_failed'], 400);
        }

        $book->setTitle($title);
        $book->setYear((int)$year);
        $book->setAuthor($author);
        $this->em->flush();

        return new Response('', 204);
    }

    #[Route('/books/{id}', name: 'books_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $book = $this->books->find($id);
        if (!$book) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $this->em->remove($book);
        $this->em->flush();

        return new Response('', 204);
    }

    private function bookToArray(Book $b): array
    {
        return [
            'id' => $b->getId(),
            'title' => $b->getTitle(),
            'year' => $b->getYear(),
            'author' => $this->authorToArray($b->getAuthor()),
        ];
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
