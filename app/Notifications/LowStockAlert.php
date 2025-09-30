<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Stock $stock) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Low Stock Alert: '.$this->stock->product->name)
            ->line('The stock level for **'.$this->stock->product->name.'** is below the minimum threshold.')
            ->line('**Warehouse:** '.$this->stock->warehouse->name)
            ->line('**Current Stock:** '.$this->stock->quantity)
            ->line('**Minimum Stock:** '.$this->stock->minimum_stock)
            ->line('**Available (unreserved):** '.$this->stock->getAvailableQuantity())
            ->action('View Stock Details', url('/admin/stocks/'.$this->stock->id))
            ->line('Please take action to replenish this item.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'stock_id' => $this->stock->id,
            'product_id' => $this->stock->product_id,
            'product_name' => $this->stock->product->name,
            'product_sku' => $this->stock->product->sku,
            'warehouse_id' => $this->stock->warehouse_id,
            'warehouse_name' => $this->stock->warehouse->name,
            'current_quantity' => $this->stock->quantity,
            'minimum_stock' => $this->stock->minimum_stock,
            'available_quantity' => $this->stock->getAvailableQuantity(),
        ];
    }
}
