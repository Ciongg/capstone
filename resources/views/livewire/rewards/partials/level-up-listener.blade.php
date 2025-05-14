{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\rewards\partials\level-up-listener.blade.php --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('level-up', (event) => {
            // In Livewire 3, named parameters are usually in event.detail
            // If event itself is the object (older Livewire or specific dispatch)
            // let level = event.level;
            // let title = event.title;

            // More robust check for Livewire 3 event data structure
            let level, title;

            if (event && typeof event.level !== 'undefined' && typeof event.title !== 'undefined') {
                // Case 1: event directly contains level and title (e.g. event[0] from older dispatch)
                level = event.level;
                title = event.title;
            } else if (event && event[0] && typeof event[0].level !== 'undefined' && typeof event[0].title !== 'undefined') {
                // Case 2: event is an array, and the first element is the object with level and title
                level = event[0].level;
                title = event[0].title;
            }
            // It's important to ensure that the 'level' and 'title' are directly accessible from the event object
            // or from event[0] if Livewire wraps the dispatched array.
            // For named parameters like $this->dispatch('level-up', level: $level, title: $title)
            // they would be event.level and event.title directly on the event object passed to the listener.

            if (typeof level !== 'undefined' && typeof title !== 'undefined') {
                console.log('Listener received level-up:', level, title);
                window.showLevelUpAnimation(level, title);
            } else {
                console.error('Level-up event data is missing or malformed. Event data:', event);
                // Fallback or default behavior if data is not as expected
                // window.showLevelUpAnimation('N/A', 'Error'); 
            }
        });

        // New event listener for reward purchase
        Livewire.on('reward-purchased', (event) => {
            console.log('Reward purchased!', event);
            window.showPurchaseConfetti();
        });
    });

    // Replace the confetti function with one that launches from bottom edges
    window.showPurchaseConfetti = function() {
        // Simultaneous confetti from bottom left
        confetti({
            particleCount: 50,
            spread: 80,
            origin: { x: 0, y: 1 },  // Off-screen bottom left
            angle: 80, // Upward angle
            startVelocity: 50,
            colors: ['#FFB349', '#03b8ff', '#4CAF50', '#FF5722', '#FFEB3B', '#9C27B0'],
            zIndex: 10000
        });
        
        // Simultaneous confetti from bottom right
        confetti({
            particleCount: 50,
            spread: 80,
            origin: { x: 1, y: 1 },  // Off-screen bottom right
            angle: 100, // Upward angle
            startVelocity: 50,
            colors: ['#FFB349', '#03b8ff', '#4CAF50', '#FF5722', '#FFEB3B', '#9C27B0'],
            zIndex: 10000
        });
        
        // Add extra confetti burst from middle bottom after a tiny delay
        setTimeout(() => {
            confetti({
                particleCount: 50,
                spread: 120,
                origin: { x: 0.5, y: 1 },  // Off-screen bottom center
                angle: 90, // Straight up
                startVelocity: 60,
                colors: ['#FFB349', '#03b8ff', '#4CAF50', '#FF5722', '#FFEB3B', '#9C27B0'],
                zIndex: 10000
            });
        }, 100);
    };
</script>