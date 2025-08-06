<div class="dropdown">
  <a href="#" class="btn-icon position-relative" data-bs-toggle="dropdown" id="notificationBell">
    <i class="bi bi-bell fs-4"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
          id="notiCount"
          style="display: none;">0</span>
  </a>

  <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3"
      id="notiList"
      style="max-height: 400px; overflow-y: auto; width: 39 0px; background: #ffffff;">

    <li class="dropdown-header px-3 py-2 fw-bold text-dark border-bottom bg-light">
      <i class="bi bi-bell-fill me-1 text-warning"></i> Thông báo
    </li>
    <li><hr class="dropdown-divider my-1"></li>

    @forelse($latestOrders as $order)
      @continue(in_array($order->payment_method, ['vnpay', 'momo']) && $order->payment_status === 'pending')
        <li>
            <a class="dropdown-item noti-item d-flex align-items-start gap-2 px-3 py-2" href="{{ route('orders.show', $order->id) }}">
                <i class="bi bi-receipt text-primary fs-5"></i>
                <div class="flex-grow-1">
                    <div>Đơn hàng: <strong>{{ $order->order_number }}</strong></div>
                    @switch($order->status)
                        @case('pending')
                            <span class="badge bg-info">đơn hàng mới</span>
                            @break
                        @case('cancelled')
                            <small class="text-white badge bg-danger">Đơn hàng hủy</small>
                            @break
                        @endswitch
                        
                </div>

                <span class="dot-unread d-none"></span>
            </a>
        </li>
    @empty
        <li><span class="dropdown-item text-muted px-3">Không có thông báo nào</span></li>
    @endforelse
  </ul>
</div>

<style>
  .noti-item.unread {
    font-weight: 600;
    background-color: #77e286ff;
    position: relative;
  }

  .noti-item.unread .dot-unread {
    display: inline-block !important;
    width: 8px;
    height: 8px;
    background-color: #ef4444;
    border-radius: 50%;
    margin-left: auto;
    margin-top: 6px;
  }

  .noti-item:hover {
    background-color: #f1f5f9;
    text-decoration: none;
  }

  .bell-animate {
    animation: bellShake 0.7s ease;
  }

  @keyframes bellShake {
    0% { transform: rotate(0deg); }
    20% { transform: rotate(-10deg); }
    40% { transform: rotate(10deg); }
    60% { transform: rotate(-6deg); }
    80% { transform: rotate(6deg); }
    100% { transform: rotate(0deg); }
  }
</style>