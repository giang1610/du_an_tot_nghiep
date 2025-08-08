// Hàm định dạng số thành tiền tệ VNĐ (ép kiểu về int, bỏ .00)
export const formatCurrency = (number) => {
  number = Math.round(number); // hoặc Math.round(number)

  if (isNaN(number)) return '0 đ';

  return number.toLocaleString('vi-VN') + ' đ'; // tự thêm 'đ' nếu cần
};
