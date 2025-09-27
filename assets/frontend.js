/**
 * Gateway Order Manager - v1.2 (MutationObserver)
 * This script reorders payment gateways instantly when they appear on the page.
 */
(() => {
    // Stop if the ordered list of slugs isn't available
    if (!window.gatewayOrder || window.gatewayOrder.length === 0) {
        return;
    }

    const orderedSlugs = window.gatewayOrder;

    /**
     * The main reordering function.
     * It finds all gateway elements, identifies them by their slug,
     * and appends them back to their parent in the correct order.
     */
    const reorderGateways = (container) => {
        const gateways = Array.from(container.children);
        const taggedGateways = [];

        // Loop through each gateway div to find and tag its slug
        gateways.forEach(gw => {
            const onclickAttr = gw.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('?method=')) {
                const slug = onclickAttr.split("?method=")[1].replace(/['"]/g, "");
                gw.dataset.gatewaySlug = slug; // Use dataset for cleaner code
                taggedGateways.push(gw);
            }
        });
        
        // Append elements back to the container in the specified order
        orderedSlugs.forEach(slug => {
            const gatewayElement = taggedGateways.find(el => el.dataset.gatewaySlug === slug);
            if (gatewayElement) {
                container.appendChild(gatewayElement);
            }
        });

        // Add a class to confirm the reorder has been done.
        container.classList.add('gateways-reordered');
    };

    // --- The MutationObserver ---
    // This is the "instant trigger" that watches for the gateway container.
    const observer = new MutationObserver((mutationsList, obs) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                const gatewayContainer = document.querySelector('.grid-wrapper:not(.gateways-reordered)');
                
                // If the container is found and has children, reorder it.
                if (gatewayContainer && gatewayContainer.children.length > 0) {
                    reorderGateways(gatewayContainer);
                    
                    // Optional: If you expect only one container, you can stop observing.
                    // obs.disconnect(); 
                    // return;
                }
            }
        }
    });

    // Start observing the entire document body for changes.
    observer.observe(document.body, { childList: true, subtree: true });

    // Fallback: In case the elements were already there before the script ran.
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.grid-wrapper:not(.gateways-reordered)').forEach(container => {
             if (container.children.length > 0) {
                reorderGateways(container);
             }
        });
    });
})();