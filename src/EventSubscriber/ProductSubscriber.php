<?php

namespace App\EventSubscriber;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsEntityListener(event: Events::postPersist, entity: Product::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Product::class)]
class ProductSubscriber
{
    public function __construct(
        private MailerInterface $mailer,
        private string $notificationEmail,
    ) {
    }

    public function postPersist(Product $product): void
    {
        $this->sendNotification($product, 'created');
    }

    public function postUpdate(Product $product): void
    {
        $this->sendNotification($product, 'updated');
    }

    private function sendNotification(Product $product, string $action): void
    {
        $email = (new Email())
            ->from('noreply@example.com')
            ->to($this->notificationEmail)
            ->subject("Product {$action}: {$product->getTitle()}")
            ->text(sprintf(
                "Product has been %s:\n\nID: %d\nTitle: %s\nPrice: %.2f\neId: %s",
                $action,
                $product->getId(),
                $product->getTitle(),
                $product->getPrice(),
                $product->getEId() ?? 'N/A'
            ));

        $this->mailer->send($email);
    }
}