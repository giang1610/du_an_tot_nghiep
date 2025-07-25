// Hàm định dạng số thành định dạng tiền tệ VNĐ
export const formatCurrency = (number) => {
  if (typeof number !== 'number') {
    number = Number(number);
    if (isNaN(number)) return '0 ₫';
  }

  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(number);
};
