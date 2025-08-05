import axios from 'axios';

export default function PaymentVNPay({ formData }) {
  const handleVnpayPayment = async () => {
    try {
      const response = await axios.post(
        'http://localhost:8000/api/vnpay/process',
        {
          shipping_address: formData.shipping_address,
          billing_address: formData.billing_address || formData.shipping_address,
          customer_phone: formData.customer_phone,
          notes: formData.notes || '',
        },
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );

      const { payment_url } = response.data.data;
      window.location.href = payment_url;
    } catch (error) {
      console.error('Lỗi thanh toán VNPay:', error.response?.data || error.message);
      alert('Không thể khởi tạo thanh toán VNPay.');
    }
  };

  return (
    <button className="btn btn-danger mt-3 w-100" onClick={handleVnpayPayment}>
      Thanh toán qua VNPay
    </button>
  );
}