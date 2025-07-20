<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order; // Đảm bảo bạn đã import đúng model Order

class OrderPlaced extends Mailable implements ShouldQueue // Nên thêm implements ShouldQueue để email được gửi qua hàng đợi
{
    use Queueable, SerializesModels;

    /**
     * Thuộc tính công khai để dữ liệu có thể truy cập được trong Blade.
     * @var \App\Models\Order
     */
    public $order;

    /**
     * @var \Illuminate\Support\Collection|array
     */
    public $items; // Biến này cần được truyền vào constructor

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Order $order Đối tượng đơn hàng.
     * @param mixed $items Các mặt hàng trong đơn hàng (có thể là Collection hoặc array).
     */
    public function __construct(Order $order, $items)
    {
        $this->order = $order;
        $this->items = $items; // Gán dữ liệu items vào thuộc tính công khai
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đơn hàng #' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.placed', // Đây là đường dẫn tới file Blade
            with: [
                'order' => $this->order,
                'items' => $this->items,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
