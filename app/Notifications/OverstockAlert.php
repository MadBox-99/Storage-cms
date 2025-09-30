<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OverstockAlert extends Notification implements ShouldQueue
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
            ->subject('Overstock Alert: '.$this->stock->product->name)
            ->line('The stock level for **'.$this->stock->product->name.'** exceeds the maximum threshold.')
            ->line('**Warehouse:** '.$this->stock->warehouse->name)
            ->line('**Current Stock:** '.$this->stock->quantity)
            ->line('**Maximum Stock:** '.$this->stock->maximum_stock)
            ->line('**Excess Quantity:** '.($this->stock->quantity - $this->stock->maximum_stock))
            ->action('View Stock Details', url('/admin/stocks/'.$this->stock->id))
            ->line('Consider reducing inventory or redistributing to other warehouses.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'overstock',
            'stock_id' => $this->stock->id,
            'product_id' => $this->stock->product_id,
            'product_name' => $this->stock->product->name,
            'product_sku' => $this->stock->product->sku,
            'warehouse_id' => $this->stock->warehouse_id,
            'warehouse_name' => $this->stock->warehouse->name,
            'current_quantity' => $this->stock->quantity,
            'maximum_stock' => $this->stock->maximum_stock,
            'excess_quantity' => $this->stock->quantity - $this->stock->maximum_stock,
        ];
    }
}
