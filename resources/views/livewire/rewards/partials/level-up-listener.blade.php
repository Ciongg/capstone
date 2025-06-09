{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\rewards\partials\level-up-listener.blade.php --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('level-up', (event) => {
            // In Livewire 3, named parameters are usually in event.detail
            let level = event[0].level;
            let rank = event[0].new_rank || event[0].rank; // Get rank instead of title

            if (typeof level !== 'undefined' && typeof rank !== 'undefined') {
                console.log('Listener received level-up:', level, rank);
                window.showLevelUpAnimation(level, rank);
            } else {
                console.error('Level-up event data is missing or malformed. Event data:', event);
            }
        });

        // New event listener for reward purchase
        Livewire.on('reward-purchased', (event) => {
            console.log('Reward purchased!', event);
            window.showPurchaseConfetti();
        });
    });

    
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