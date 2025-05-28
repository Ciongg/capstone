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

   
});

// DO NOT explicitly start Alpine here if using Livewire's scripts
// Alpine.start();

