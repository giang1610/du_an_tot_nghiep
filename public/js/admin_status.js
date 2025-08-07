window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('hello ưin');
    
});

window.Echo.channel('admin_status')
    .listen('.order.status', (data) => {
        const order = data;
        console.log('data', data);
        console.log(` Nhận đơn hàng bị hủy: #${order.id} `);

            const statusTd = document.getElementById(`order-status-${order.id}`);
            switch(order.status){
                case 'cancelled':
                 if (statusTd) {
                statusTd.innerHTML = `
                    <span class="badge bg-danger">
                        <i class="fas fa-times-circle me-1"></i> Đã hủy
                    </span>
                `;
                const updateButton = document.querySelector(`#order-status-${order.id}`)
                ?.closest('tr')
                ?.querySelector('a.btn-outline-success');
                if (updateButton) {
                updateButton.remove(); 
                }
                } 
                break;
                case'return_requested':
                if (statusTd) {
                statusTd.innerHTML = `
                     <span class="badge bg-info">
                                    <i class="fas fa-exchange-alt me-1"></i> Yêu cầu hoàn hàng
                     </span>
                `;
                }
                break;
            
            }

           
        }
    );
