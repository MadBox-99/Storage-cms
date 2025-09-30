<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ReorderPointReached extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Reorder Point Reached: '.$this->product->name)
            ->line('The stock level for **'.$this->product->name.'** has reached the reorder point.')
            ->line('**Current Total Stock:** '.$this->product->getTotalStock())
            ->line('**Reorder Point:** '.$this->product->reorder_point)
            ->line('**Recommended Reorder Quantity:** '.$this->product->calculateReorderQuantity())
            ->line('**Supplier:** '.$this->product->supplier->company_name)
            ->action('Create Purchase Order', url('/admin/orders/create'))
            ->line('Please create a purchase order to replenish this item.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reorder_point',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'current_total_stock' => $this->product->getTotalStock(),
            'reorder_point' => $this->product->reorder_point,
            'recommended_quantity' => $this->product->calculateReorderQuantity(),
            'supplier_id' => $this->product->supplier_id,
            'supplier_name' => $this->product->supplier->company_name,
        ];
    }
}
