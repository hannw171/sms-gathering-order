import { useSyncExternalStore, useCallback } from 'react';
import { usePage } from '@inertiajs/react';

export function getCartStorageKey(pharmacyId) {
  return pharmacyId ? `sms_gathering_cart_p${pharmacyId}_v1` : 'sms_gathering_cart_guest_v1';
}

function readCartFromStorage(key) {
  try {
    if (typeof window === 'undefined' || !key) return {};

    // One-time legacy migration if unscoped key exists and target key is currently empty
    const legacy = localStorage.getItem('sms_gathering_cart_v1');
    const saved = localStorage.getItem(key);

    let raw = saved;
    if (!saved && legacy) {
      localStorage.setItem(key, legacy);
      localStorage.removeItem('sms_gathering_cart_v1');
      raw = legacy;
    }

    if (!raw) return {};
    const parsed = JSON.parse(raw);
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
          step: parseInt(item.step, 10) || 1,
          min_order: parseInt(item.min_order, 10) || 1,
          quantity: Math.max(1, parseInt(item.quantity, 10) || 1),
        };
      }
    }
    return sanitized;
  } catch {
    return {};
  }
}

// In-memory cache per storageKey
const cartCaches = new Map();
const listeners = new Set();

function getCartForStorage(key) {
  if (!cartCaches.has(key)) {
    cartCaches.set(key, readCartFromStorage(key));
  }
  return cartCaches.get(key);
}

function emitChange() {
  for (const listener of listeners) {
    listener();
  }
}

function updateCartStore(key, updater) {
  if (!key) return;
  const current = getCartForStorage(key);
  const nextCart = typeof updater === 'function' ? updater(current) : updater;
  cartCaches.set(key, nextCart);

  try {
    if (typeof window !== 'undefined') {
      if (Object.keys(nextCart).length === 0) {
        localStorage.removeItem(key);
      } else {
        localStorage.setItem(key, JSON.stringify(nextCart));
      }
      window.dispatchEvent(new CustomEvent('sms-cart-updated', { detail: { key } }));
    }
  } catch (e) {
    console.error('Failed to save cart to localStorage', e);
  }
  emitChange();
}

if (typeof window !== 'undefined') {
  window.addEventListener('storage', (e) => {
    if (e.key && cartCaches.has(e.key)) {
      cartCaches.set(e.key, readCartFromStorage(e.key));
      emitChange();
    }
  });
  window.addEventListener('sms-cart-updated', () => {
    emitChange();
  });
}

function subscribe(listener) {
  listeners.add(listener);
  return () => {
    listeners.delete(listener);
  };
}

const SERVER_SNAPSHOT = {};

export function useCart(explicitPharmacyId) {
  let pharmacyId = explicitPharmacyId;
  try {
    const page = usePage();
    if (!pharmacyId) {
      pharmacyId = page?.props?.guestPharmacyId || page?.props?.pharmacy?.id || null;
    }
  } catch {
    // Outside Inertia context
  }

  const storageKey = getCartStorageKey(pharmacyId);

  const getSnapshot = useCallback(() => {
    return getCartForStorage(storageKey);
  }, [storageKey]);

  const getServerSnapshot = useCallback(() => {
    return SERVER_SNAPSHOT;
  }, []);

  const cart = useSyncExternalStore(subscribe, getSnapshot, getServerSnapshot);

  /**
   * Update quantity of an item with guaranteed data integrity.
   * Handles:
   * 1. Full product objects from Catalog
   * 2. Existing cart item references
   * 3. ID-only updates
   */
  const updateQuantity = useCallback(
    (productOrId, newQty) => {
      const productId =
        typeof productOrId === 'object' && productOrId !== null
          ? productOrId.id ?? productOrId.product_id
          : productOrId;

      if (!productId) return;

      const parsedQty = parseInt(newQty, 10);
      const validQty = isNaN(parsedQty) ? 0 : parsedQty;

      updateCartStore(storageKey, (prev) => {
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
            step: itemObj.step || existing.step || 1,
            min_order: itemObj.min_order || existing.min_order || 1,
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
            step: productOrId.step || 1,
            min_order: productOrId.min_order || 1,
            quantity: validQty,
          };
        }

        return next;
      });
    },
    [storageKey]
  );

  const removeItem = useCallback(
    (productId) => {
      updateCartStore(storageKey, (prev) => {
        const next = { ...prev };
        delete next[productId];
        return next;
      });
    },
    [storageKey]
  );

  const getItemQuantity = useCallback(
    (productId) => {
      return cart[productId]?.quantity || 0;
    },
    [cart]
  );

  const clearCart = useCallback(() => {
    updateCartStore(storageKey, {});
  }, [storageKey]);

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
    storageKey,
  };
}
