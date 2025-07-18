// import { Form, Button } from 'react-bootstrap';

// export default function CheckoutForm({
//   shippingAddress,
//   setShippingAddress,
//   customerPhone,
//   setCustomerPhone,
//   paymentMethod,
//   setPaymentMethod,
//   onSubmit
// }) {
//   return (
//     <div className="mt-4 p-3 border rounded bg-light">
//       <h5>Thông tin giao hàng</h5>
//       <Form.Group className="mb-3">
//         <Form.Label>Địa chỉ giao hàng</Form.Label>
//         <Form.Control
//           type="text"
//           placeholder="Nhập địa chỉ"
//           value={shippingAddress}
//           onChange={e => setShippingAddress(e.target.value)}
//         />
//       </Form.Group>
//       <Form.Group className="mb-3">
//         <Form.Label>Số điện thoại</Form.Label>
//         <Form.Control
//           type="text"
//           placeholder="Nhập số điện thoại"
//           value={customerPhone}
//           onChange={e => setCustomerPhone(e.target.value)}
//         />
//       </Form.Group>
//       <Form.Group className="mb-3">
//         <Form.Label>Phương thức thanh toán</Form.Label>
//         <Form.Select
//           value={paymentMethod}
//           onChange={e => setPaymentMethod(e.target.value)}
//         >
//           <option value="cod">Thanh toán khi nhận hàng (COD)</option>
//           <option value="momo">Ví MoMo</option>
         
//         </Form.Select>
//       </Form.Group>
//       <Button variant="success" onClick={onSubmit}>
//         Xác nhận đặt hàng
//       </Button>
//     </div>
//   );
// }
