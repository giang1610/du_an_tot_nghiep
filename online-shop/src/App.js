  import React from "react";
  import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
  import Header from "./components/Header";
  import Footer from "./components/Footer";
  import HomePage from "./pages/HomePage";
  import ProductDetailPage from "./pages/ProductDetailPage";
  import AllProducts from "./pages/AllProducts";
  import Login from "./pages/Login";
  import Register from "./pages/Register";
  import ForgotPassword from "./pages/ForgotPassword";
  import ChangePassword from "./pages/ChangePassword";
  import { CartProvider } from "./contexts/CartContext";
import CartPage from "./pages/CartPage";
import { AuthProvider } from "./contexts/AuthContext";

  function App() {
    return (
      <AuthProvider>
      <CartProvider>
      <Router>
        <Header />
        <Routes>

          <Route path="/" element={<HomePage />} />
          <Route path="/products" element={<AllProducts/>} />
          <Route path="/products/:id" element={<ProductDetailPage />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/reset-password/:token" element={<ChangePassword />} />
          <Route path="/cart" element={<CartPage />} />

          <Route
            path="*"
            element={
              <div className="container mt-5">
                <h2>Trang không tồn tại</h2>
              </div>
            }
          />
        </Routes>
        <Footer />
      </Router>
      </CartProvider>
      </AuthProvider>
    );
  }

  export default App;
