import React from 'react';
import UserVoucherList from '../components/UserVoucherList';
import { useAuth } from '../context/AuthContext';

const MyVouchers = () => {
  const { token } = useAuth();

  return (
    <div className="container">
      <h2>Voucher của tôi</h2>
      <UserVoucherList />
    </div>
  );
};

export default MyVouchers;
