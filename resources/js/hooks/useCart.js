import { useState, useEffect } from 'react';

const CART_STORAGE_KEY = 'sms_gathering_cart_v1';

export function useCart() {
  const [cart, setCart] = useState(() => {
    try {
      const saved = localStorage.getItem(CART_STORAGE_KEY);
      if (!saved) return {};
      const parsed = JSON.parse(saved);
      // Clean and sanitize stored items to ensure data integrity
      const sanitized = {};
      for (const [id, item] of Object.entries(parsed)) {
        if (item && item.name && !isNaN(item.price) && item.quantity > 0) {
          sanitized[id] = {
            product_id: parseInt(item.product_id || id, 10),
            name: String(item.name),
            sku: item.sku || '',
            uom: item.uom || 'Box',
            price: Number(item.price) || 0,
            manufacturer_name: item.manufacturer_name || '',
            quantity: Math.max(1, parseInt(item.quantity, 10) || 1),
          };
        }
      }
      return sanitized;
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

  /**
   * Update quantity of an item with guaranteed data integrity.
   * Handles:
   * 1. Full product objects from Catalog
   * 2. Existing cart item references
   * 3. ID-only updates
   */
  const updateQuantity = (productOrId, newQty) => {
    const productId =
      typeof productOrId === 'object' && productOrId !== null
        ? productOrId.id ?? productOrId.product_id
        : productOrId;

    if (!productId) return;

    const parsedQty = parseInt(newQty, 10);
    const validQty = isNaN(parsedQty) ? 0 : parsedQty;

    setCart((prev) => {
      const next = { ...prev };
      const existing = prev[productId];

      if (validQty <= 0) {
        delete next[productId];
        return next;
      }

      // If the item is already in the cart, ALWAYS preserve existing fields!
      if (existing) {
        const itemObj = typeof productOrId === 'object' && productOrId !== null ? productOrId : {};
        next[productId] = {
          product_id: productId,
          name: itemObj.name || existing.name,
          sku: itemObj.sku || existing.sku,
          uom: itemObj.uom || existing.uom,
          price: !isNaN(parseFloat(itemObj.price)) ? parseFloat(itemObj.price) : existing.price,
          manufacturer_name:
            itemObj.manufacturer?.name || itemObj.manufacturer_name || existing.manufacturer_name,
          quantity: validQty,
        };
      } else if (typeof productOrId === 'object' && productOrId !== null && productOrId.name) {
        // First-time addition from catalog
        next[productId] = {
          product_id: productId,
          name: productOrId.name,
          sku: productOrId.sku || '',
          uom: productOrId.uom || 'Box',
          price: !isNaN(parseFloat(productOrId.price)) ? parseFloat(productOrId.price) : 0,
          manufacturer_name: productOrId.manufacturer?.name || productOrId.manufacturer_name || '',
          quantity: validQty,
        };
      }

      return next;
    });
  };

  const removeItem = (productId) => {
    setCart((prev) => {
      const next = { ...prev };
      delete next[productId];
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
  const totalItems = cartItems.reduce((acc, item) => acc + (parseInt(item.quantity, 10) || 0), 0);
  const totalAmount = cartItems.reduce(
    (acc, item) => acc + (parseFloat(item.price) || 0) * (parseInt(item.quantity, 10) || 0),
    0
  );

  return {
    cart,
    cartItems,
    totalItems,
    totalAmount,
    getItemQuantity,
    updateQuantity,
    removeItem,
    clearCart,
  };
}
