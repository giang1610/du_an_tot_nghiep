import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import ProductDetail from './pages/ProductDetail';
import ProductsPage from './pages/ProductsPage';
import CartPage from './pages/CartPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import ForgotPasswordPage from './pages/ForgotPasswordPage';
import CheckoutPage from './pages/CheckoutPage';
import MyOrdersPage from './components/MyOrdersPage';
import OrderDetailPage from './pages/OrderDetailPage';




function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/products" element={<ProductsPage />} />
       <Route path="/products/:slug" element={<ProductDetail />} />
        <Route path="/cart" element={<CartPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/forgot-password" element={<ForgotPasswordPage />} />
        <Route path="/orders" element={<MyOrdersPage />} />
         <Route path="/checkout" element={<CheckoutPage />} />
         <Route path="/orders" element={<MyOrdersPage />} />
         <Route path="/orders/:id" element={<OrderDetailPage />} />


      </Routes>
    </Router>
  );
}

export default App;
