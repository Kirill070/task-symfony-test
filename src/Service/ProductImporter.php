<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryService $categoryService,
        private ProductService $productService,
        private ProductRepository $productRepository,
    ) {
    }

    /**
     * @return array{categories: array{created: int, updated: int, skipped: int}, products: array{created: int, updated: int, skipped: int}}
     */
    public function import(string $categoriesFile, string $productsFile): array
    {
        $stats = [
            'categories' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'products' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
        ];

        // Импорт категорий
        $stats['categories'] = $this->importCategories($categoriesFile);

        // Импорт товаров
        $stats['products'] = $this->importProducts($productsFile);

        return $stats;
    }

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    private function importCategories(string $filePath): array
    {
        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        if ($data === null) {
            throw new \RuntimeException("Failed to parse JSON file: $filePath");
        }

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $processedEIds = [];

        foreach ($data as $item) {
            $eId = $item['eId'] ?? null;
            $title = $item['title'] ?? '';

            if ($eId === null) {
                $stats['skipped']++;
                continue;
            }

            // Пропускаем дубликаты eId в файле
            if (in_array($eId, $processedEIds)) {
                $stats['skipped']++;
                continue;
            }

            try {
                $existingCategory = $this->em->getRepository(\App\Entity\Category::class)
                    ->findOneBy(['eId' => $eId]);

                if ($existingCategory) {
                    $this->categoryService->updateCategory($eId, $title);
                    $stats['updated']++;
                } else {
                    $this->categoryService->findOrCreateCategory($eId, $title);
                    $stats['created']++;
                }

                $processedEIds[] = $eId;
            } catch (\InvalidArgumentException $e) {
                // Ошибка валидации — пропускаем
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    private function importProducts(string $filePath): array
    {
        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        if ($data === null) {
            throw new \RuntimeException("Failed to parse JSON file: $filePath");
        }

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($data as $item) {
            $eId = $item['eId'] ?? null;
            $title = $item['title'] ?? '';
            $price = $item['price'] ?? 0;

            // Поддержка обоих вариантов названия поля
            $categoryEIds = $item['categoriesEId'] ?? $item['categoryEId'] ?? [];

            if ($eId === null) {
                $stats['skipped']++;
                continue;
            }

            try {
                $existingProduct = $this->productRepository->findOneBy(['eId' => $eId]);

                if ($existingProduct) {
                    $this->productService->updateProduct($existingProduct, [
                        'title' => $title,
                        'price' => $price,
                        'categoryEIds' => $categoryEIds,
                    ]);
                    $stats['updated']++;
                } else {
                    $this->productService->createProduct($title, $price, $eId, $categoryEIds);
                    $stats['created']++;
                }
            } catch (\InvalidArgumentException $e) {
                // Ошибка валидации — пропускаем
                $stats['skipped']++;
            }
        }

        return $stats;
    }
}