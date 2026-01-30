<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository
    ) {
    }

    /**
     * Получить список всех товаров
     */
    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $products = $this->productRepository->findAll();

        return $this->json(array_map(fn(Product $p) => $this->serialize($p), $products));
    }

    /**
     * Получить один товар по ID
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serialize($product));
    }

    /**
     * Создать новый товар
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $product = $this->productService->createProduct(
                $data['title'] ?? '',
                $data['price'] ?? 0,
                $data['eId'] ?? null,
                $data['categoryEIds'] ?? []
            );

            return $this->json($this->serialize($product), Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Обновить товар
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $product = $this->productService->updateProduct($product, $data);

            return $this->json($this->serialize($product));
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Удалить товар
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['error' => 'Товар не найден'], Response::HTTP_NOT_FOUND);
        }

        $this->productService->deleteProduct($product);

        return $this->json(['message' => 'Товар удалён']);
    }

    /**
     * Преобразовать Product в массив для JSON
     */
    private function serialize(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'price' => $product->getPrice(),
            'eId' => $product->getEId(),
            'categories' => $product->getCategories()->map(fn($c) => [
                'id' => $c->getId(),
                'title' => $c->getTitle(),
            ])->toArray()
        ];
    }
}
