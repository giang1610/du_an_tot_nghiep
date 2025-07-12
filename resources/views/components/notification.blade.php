<div class="dropdown">
  <a href="#" class="btn-icon position-relative" data-bs-toggle="dropdown" id="notificationBell">
    <i class="bi bi-bell fs-4"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
          id="notiCount"
          style="{{ count($latestOrders) ? '' : 'display: none;' }}">
      {{ count($latestOrders) }}
    </span>
  </a>

  <ul class="dropdown-menu dropdown-menu-end shadow p-0"
      id="notiList"
      style="max-height: 320px; overflow-y: auto; width: 320px;">
    
    <li class="dropdown-header p-2 fw-semibold bg-light border-bottom">Thông báo</li>
    <li><hr class="dropdown-divider my-1"></li>

    @forelse($latestOrders as $order)
        <li>
            <a class="dropdown-item noti-item d-flex align-items-center" href="{{ route('orders.show', $order->id) }}">
                <i class="bi bi-receipt text-primary me-2"></i>
                <div>
                    Đơn hàng: <strong>{{ $order->order_number }}</strong>
                </div>
            </a>
        </li>
    @empty
        <li><span class="dropdown-item text-muted">Không có thông báo nào</span></li>
    @endforelse
  </ul>
</div>
