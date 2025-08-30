import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import CustomNavbar from "./components/CustomNavbar";
import Footer from "./components/Footer";
import Home from "./pages/Home";
import ProductDetail from "./pages/ProductDetail";
import Cart from "./pages/Cart";
import Register from "./pages/Register";
import Login from "./pages/Login";
import AllProductsPage from "./pages/AllProductsPage";
import MyOrdersPage from "./pages/MyOrdersPage";
import ForgotPassword from "./pages/ForgotPassword";
import ResendVerification from "./pages/ResendEmailPage";
import Checkout from "./pages/Checkout";
import Profile from "./pages/Profile";
import OrderDetailPage from "./pages/OrderDetailPage";
import AboutPage from "./pages/AboutPage";
import ContactPage from "./pages/ContactPage";
import ChangePasswordPage from "./pages/ChangePasswordPage";
import MomoReturn from "./pages/MomoReturn";
import ResetPassword from "./pages/ResetPassword";
import ProductReview from "./components/ProductReview";
import ChatApp from "./components/ChatApp";
import MyVouchers from "./pages/MyVouchers";
import VnpayReturn from "./pages/VnpayReturnPage";
import ContinuePaymentMomo from "./components/ContinuePaymentMomo";
import ContinuePaymentVnpay from "./components/ContinuePaymentVnpay";

function App() {
  return (
    <Router>
      <div className="app-wrapper">
        <CustomNavbar />
        <main className="content">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/products" element={<AllProductsPage />} />
            <Route path="/products/:slug" element={<ProductDetail />} />
            <Route path="/cart" element={<Cart />} />
            <Route path="/checkout" element={<Checkout />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/orders" element={<MyOrdersPage />} />
            <Route path="/orders/:id" element={<OrderDetailPage />} />
            <Route path="/about" element={<AboutPage />} />
            <Route path="/contact" element={<ContactPage />} />
            <Route path="/resend-verification" element={<ResendVerification />} />
            <Route path="/profile" element={<Profile />} />
            <Route path="/forgot-password" element={<ForgotPassword />} />
            <Route path="/change-password" element={<ChangePasswordPage />} />
            <Route path="/reset-password" element={<ResetPassword />} />
            <Route path="/review/:orderId" element={<ProductReview />} />
            <Route path="/my-vouchers" element={<MyVouchers />} />
            <Route path="/vnpay-return" element={<VnpayReturn />} />
            <Route path="/momo-return" element={<MomoReturn />} />
            <Route
              path="/continue-payment/momo/:orderId"
              element={<ContinuePaymentMomo />}
            />
            <Route
              path="/continue-payment/vnpay/:orderId"
              element={<ContinuePaymentVnpay />}
            />
            <Route
              path="*"
              element={<h1 className="text-center mt-5">Trang không tìm thấy</h1>}
            />
          </Routes>
        </main>
        <ChatApp />
        <Footer />
      </div>
    </Router>
  );
}

export default App;
