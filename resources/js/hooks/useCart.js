import { useState, useEffect } from 'react';

const CART_STORAGE_KEY = 'sms_gathering_cart_v1';

export function useCart() {
  const [cart, setCart] = useState(() => {
    try {
      const saved = localStorage.getItem(CART_STORAGE_KEY);
      return saved ? JSON.parse(saved) : {};
    } catch {
      return {};
    }
  });

  useEffect(() => {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    } catch (e) {
      console.error('Failed to save cart to localStorage', e);
    }
  }, [cart]);

  const updateQuantity = (product, newQty) => {
    setCart((prev) => {
      const next = { ...prev };
      if (newQty <= 0) {
        delete next[product.id];
      } else {
        next[product.id] = {
          product_id: product.id,
          name: product.name,
          sku: product.sku,
          uom: product.uom,
          price: parseFloat(product.price),
          manufacturer_name: product.manufacturer?.name || '',
          quantity: newQty,
        };
      }
      return next;
    });
  };

  const getItemQuantity = (productId) => {
    return cart[productId]?.quantity || 0;
  };

  const clearCart = () => {
    setCart({});
    localStorage.removeItem(CART_STORAGE_KEY);
  };

  const cartItems = Object.values(cart);
  const totalItems = cartItems.reduce((acc, item) => acc + item.quantity, 0);
  const totalAmount = cartItems.reduce((acc, item) => acc + item.price * item.quantity, 0);

  return {
    cart,
    cartItems,
    totalItems,
    totalAmount,
    getItemQuantity,
    updateQuantity,
    clearCart,
  };
}
