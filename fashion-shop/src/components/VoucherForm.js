import React, { useState } from 'react';
import { Form, Button, Alert } from 'react-bootstrap';
import axios from 'axios';

const VoucherForm = ({ subtotal, token, onApplied, appliedVoucher, onClear }) => {
  const [voucherCode, setVoucherCode] = useState('');
  const [voucherInfo, setVoucherInfo] = useState(appliedVoucher || null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const applyVoucher = async () => {
    setError('');
    setSuccess('');

    if (!voucherCode.trim()) {
      setError('Vui lòng nhập mã giảm giá.');
      return;
    }

    try {
      const res = await axios.post(
        `${process.env.REACT_APP_API_URL}/vouchers/apply`,
        { code: voucherCode, total: subtotal },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (res.data && res.data.success) {
        setVoucherInfo(res.data);
        setSuccess('Áp dụng mã giảm giá thành công!');
        setVoucherCode('');
        onApplied(res.data);
      } else {
        setError(res.data.message || 'Mã giảm giá không hợp lệ.');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Không thể áp dụng mã giảm giá.');
    }
  };

  const clearVoucher = () => {
    setVoucherInfo(null);
    setVoucherCode('');
    setSuccess('');
    setError('');
    onClear(); // notify parent to clear voucher
  };

  return (
    <div>
      {!voucherInfo && (
        <Form
          className="d-flex mb-3"
          onSubmit={(e) => {
            e.preventDefault();
            applyVoucher();
          }}
        >
          <Form.Control
            type="text"
            placeholder="Nhập mã giảm giá"
            value={voucherCode}
            onChange={(e) => setVoucherCode(e.target.value)}
            className="me-2"
          />
          <Button variant="success" onClick={applyVoucher}>
            Áp dụng
          </Button>
        </Form>
      )}

      {voucherInfo && (
        <Alert variant="success" className="d-flex justify-content-between align-items-center mb-3">
          <div>
            ✅ Đã áp dụng mã: <strong>{voucherInfo.code}</strong><br />
            {voucherInfo.type === 'percent' && `Giảm ${voucherInfo.value}%`}
            {voucherInfo.type === 'fixed' && `Giảm ${voucherInfo.value.toLocaleString()} đ`}
          </div>
          <Button
            variant="outline-danger"
            size="sm"
            onClick={clearVoucher}
          >
            Hủy
          </Button>
        </Alert>
      )}

      {error && <Alert variant="danger">{error}</Alert>}
      {success && <Alert variant="success">{success}</Alert>}
    </div>
  );
};

export default VoucherForm;
