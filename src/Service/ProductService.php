<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Создание нового товара
     */
    public function createProduct(string $title, float $price, ?int $eId = null, array $categoryEIds = []): Product
    {
        $product = new Product();
        $product->setTitle($title);
        $product->setPrice($price);
        $product->setEId($eId);

        // Добавляем категории по их eId
        foreach ($categoryEIds as $categoryEId) {
            $category = $this->categoryRepository->findOneBy(['eId' => $categoryEId]);
            if ($category) {
                $product->addCategory($category);
            }
        }

        // Валидация
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * Обновление существующего товара
     */
    public function updateProduct(Product $product, array $data): Product
    {
        if (isset($data['title'])) {
            $product->setTitle($data['title']);
        }

        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }

        if (isset($data['eId'])) {
            $product->setEId($data['eId']);
        }

        if (isset($data['categoryEIds'])) {
            // Очищаем старые категории
            foreach ($product->getCategories() as $category) {
                $product->removeCategory($category);
            }

            // Добавляем новые
            foreach ($data['categoryEIds'] as $categoryEId) {
                $category = $this->categoryRepository->findOneBy(['eId' => $categoryEId]);
                if ($category) {
                    $product->addCategory($category);
                }
            }
        }

        // Валидация
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->entityManager->flush();

        return $product;
    }

    /**
     * Удаление товара
     */
    public function deleteProduct(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }
}
