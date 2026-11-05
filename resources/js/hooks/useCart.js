import { useSyncExternalStore, useCallback } from 'react';

const CART_STORAGE_KEY = 'sms_gathering_cart_v1';

function readCartFromStorage() {
  try {
    if (typeof window === 'undefined') return {};
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
}

// In-memory singleton state shared across all components
let currentCart = readCartFromStorage();
const listeners = new Set();

function emitChange() {
  for (const listener of listeners) {
    listener();
  }
}

function updateCartStore(updater) {
  const nextCart = typeof updater === 'function' ? updater(currentCart) : updater;
  currentCart = nextCart;
  try {
    if (typeof window !== 'undefined') {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(currentCart));
      window.dispatchEvent(new Event('sms-cart-updated'));
    }
  } catch (e) {
    console.error('Failed to save cart to localStorage', e);
  }
  emitChange();
}

function subscribe(listener) {
  listeners.add(listener);
  const handleStorage = (e) => {
    if (e.key === CART_STORAGE_KEY) {
      currentCart = readCartFromStorage();
      emitChange();
    }
  };
  if (typeof window !== 'undefined') {
    window.addEventListener('storage', handleStorage);
  }
  return () => {
    listeners.delete(listener);
    if (typeof window !== 'undefined') {
      window.removeEventListener('storage', handleStorage);
    }
  };
}

function getSnapshot() {
  return currentCart;
}

function getServerSnapshot() {
  return {};
}

export function useCart() {
  const cart = useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);

  /**
   * Update quantity of an item with guaranteed data integrity.
   * Handles:
   * 1. Full product objects from Catalog
   * 2. Existing cart item references
   * 3. ID-only updates
   */
  const updateQuantity = useCallback((productOrId, newQty) => {
    const productId =
      typeof productOrId === 'object' && productOrId !== null
        ? productOrId.id ?? productOrId.product_id
        : productOrId;

    if (!productId) return;

    const parsedQty = parseInt(newQty, 10);
    const validQty = isNaN(parsedQty) ? 0 : parsedQty;

    updateCartStore((prev) => {
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
  }, []);

  const removeItem = useCallback((productId) => {
    updateCartStore((prev) => {
      const next = { ...prev };
      delete next[productId];
      return next;
    });
  }, []);

  const getItemQuantity = useCallback(
    (productId) => {
      return cart[productId]?.quantity || 0;
    },
    [cart]
  );

  const clearCart = useCallback(() => {
    updateCartStore({});
  }, []);

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
