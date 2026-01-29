<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    #[Assert\NotBlank(message: 'Название товара не может быть пустым')]
    #[Assert\Length(
        min: 3,
        max: 12,
        minMessage: 'Название должно быть минимум {{ limit }} символа',
        maxMessage: 'Название не может быть длиннее {{ limit }} символов'
    )]
    private ?string $title = null;


    #[ORM\Column]
    #[Assert\NotBlank(message: 'Цена не может быть пустой')]
    #[Assert\Range(
        min: 0,
        max: 200,
        notInRangeMessage: 'Цена должна быть от {{ min }} до {{ max }}',
    )]
    private ?float $price = null;


    #[ORM\Column(nullable: true)]
    private ?int $eId = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getEId(): ?int
    {
        return $this->eId;
    }

    public function setEId(?int $eId): static
    {
        $this->eId = $eId;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
