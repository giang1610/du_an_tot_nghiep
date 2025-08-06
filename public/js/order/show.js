

window.Echo.channel('admin_status')
    .listen('.order.status', (data) => {
        const order = data;
      


        const badge = document.getElementById('order-status-badge');
        if (!badge) return;

        const statusMap = {
            cancelled:   { text: 'Đã hủy',       classes: ['bg-danger'] },
            default:     { text: order.status,   classes: ['bg-light', 'text-dark'] }
        };

        const { text, classes } = statusMap[order.status] || statusMap.default;

        badge.textContent = text;
        badge.className = 'badge ms-2 order-status-badge';
        badge.classList.add(...classes);
    

        // xử lý cập nhật button
            switch(order.status){
                case 'cancelled':

            const submitButtonContainer = document.getElementById('text');
            if (submitButtonContainer) {
                submitButtonContainer.remove();
            }

                break;
                case'':
            
            }

           
        }
    );
