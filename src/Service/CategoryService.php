<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Найти категорию по eId или создать новую
     */
    public function findOrCreateCategory(int $eId, string $title): Category
    {
        $category = $this->categoryRepository->findOneBy(['eId' => $eId]);

        if (!$category) {
            $category = new Category();
            $category->setEId($eId);
            $category->setTitle($title);

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException((string) $errors);
            }

            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }

        return $category;
    }

    /**
     * Обновить существующую категорию
     */
    public function updateCategory(int $eId, string $title): Category
    {
        $category = $this->categoryRepository->findOneBy(['eId' => $eId]);

        if ($category) {
            $category->setTitle($title);

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                throw new \InvalidArgumentException((string) $errors);
            }

            $this->entityManager->flush();
        } else {
            // Если не найдена, создаем новую
            $category = $this->findOrCreateCategory($eId, $title);
        }

        return $category;
    }
}
