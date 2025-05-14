import './bootstrap';
import Alpine from 'alpinejs';
import './level-up-animation.js';

window.Alpine = Alpine;

// Wait for Alpine to initialize before defining the store and hook
document.addEventListener('alpine:init', () => {
    // Add an Alpine store to persist textarea heights across Livewire refreshes
    Alpine.store('textareaHeights', {
        heights: {},
        get(id) {
            // console.log(`Store GET: ${id} -> ${this.heights[id] || 'auto'}`); // Debugging
            return this.heights[id] || 'auto';
        },
        set(id, height) {
            // console.log(`Store SET: ${id} -> ${height}`); // Debugging
            this.heights[id] = height;
        }
    });

    // Livewire hook for handling textarea resizing after updates
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('message.processed', (message, component) => {
            // Wait for the DOM to fully update using requestAnimationFrame
            requestAnimationFrame(() => {
                // Find all textareas with data-autoresize attribute
                document.querySelectorAll('textarea[data-autoresize]').forEach(textarea => {
                    const id = textarea.id;
                    if (!id) {
                        console.warn('Textarea missing ID for autoresize:', textarea);
                        return; // Skip if no ID
                    }

                    // Get height from store if available
                    const storedHeight = Alpine.store('textareaHeights').get(id);

                    // Apply height from store or recalculate if needed
                    if (storedHeight && storedHeight !== 'auto') {
                        // console.log(`Applying stored height to ${id}: ${storedHeight}`); // Debugging
                        textarea.style.height = storedHeight;
                    } else {
                        // console.log(`Recalculating height for ${id}`); // Debugging
                        // Reset height to auto to get proper scrollHeight
                        textarea.style.height = 'auto';
                        // Calculate new height and store it
                        const newHeight = `${textarea.scrollHeight}px`;
                        textarea.style.height = newHeight;
                        Alpine.store('textareaHeights').set(id, newHeight); // Store the calculated height
                    }
                });
            });
        });
    } else {
        console.warn('Livewire not defined when setting up hooks.');
    }
});

// DO NOT explicitly start Alpine here if using Livewire's scripts
// Alpine.start();

