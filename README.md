# 💳 Gateway Order Manager for PipraPay

**Plugin Name:** Gateway Order Manager  
**Description:** Manage the display order of payment gateways using drag and drop.  
**Version:** 1.2 
**Author:** [Refat Rahman](https://github.com/refatbd)  
**License:** [GPL-2.0+](https://www.gnu.org/licenses/gpl-2.0.html)  
**Requires at least PipraPay version:** 1.0.0  
**Tested up to:** 1.0.0  
**Donate link:** [Donate](https://refat.ovh/donate)

---

## 📌 Key Features

-   **Drag & Drop Interface**: Easily reorder payment gateways by dragging and dropping them in the desired sequence.
-   **AJAX Powered Saving**: The order is saved instantly in the background without needing a page refresh.
-   **Instant Frontend Sorting**: The plugin uses an optimized script to apply the saved order instantly on the public checkout page, eliminating any "flickering".
-   **Seamless Integration**: Integrates directly into the PipraPay admin dashboard.
-   **Lightweight**: Minimal code and no unnecessary features to slow down your site.
-   **Easy to Use**: No complex configuration is required. Just activate, and you're ready to organize your gateways.

---

## 🎯 Why Use This Plugin?

-   **Control Checkout Flow**: Prioritize and arrange payment gateways to guide customers towards your preferred payment methods.
-   **Improved User Experience**: Present payment options in a logical and organized manner on the checkout page without any visual delay.
-   **Effortless Management**: Save time with a simple and intuitive drag-and-drop interface for managing gateway order.
-   **Instant Updates**: Changes are reflected immediately on the frontend.

---

## 📥 Installation

1.  **Download** the plugin from the repository.
2.  **Upload** the `gateway-manager` folder to your PipraPay `Plugin` section (`/pp-content/plugins/modules/`).
3.  **Activate** the plugin from PipraPay's module settings.
4.  Go to **Admin Dashboard → Module → Gateway Order Manager**.
5.  Drag and drop the gateways into your preferred order and click "Save Order".

---

## 📜 Changelog

### [1.2]
- Replaced frontend AJAX call with a server-side data injection.
- Implemented `MutationObserver` for instant, flicker-free reordering of gateways.

### [1.0]
- Initial release.