{{-- filepath: c:\Users\sharp\OneDrive\Desktop\Formigo\formigo\resources\views\livewire\rewards\partials\level-up-listener.blade.php --}}
<script>
    // Rank configuration data - defined once outside functions for efficiency
    const RANK_INFO = {
        'silver': { 
            color: '#C0C0C0', 
            gradient: 'linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 50%, #A8A8A8 100%)',
            icon: '🥈',
            next: 'Gold', 
            threshold: 11 
        },
        'gold': { 
            color: '#FFD700', 
            gradient: 'linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FF8C00 100%)',
            icon: '🥇',
            next: 'Diamond', 
            threshold: 21
        },
        'diamond': { 
            color: '#B9F2FF', 
            gradient: 'linear-gradient(135deg, #B9F2FF 0%, #66D9EF 50%, #1E90FF 100%)',
            icon: '💎',
            next: null, 
            threshold: null
        }
    };

    // Helper function to get next rank message - defined once outside the main function
    function getNextRankMessage(currentLevel, currentRank) {
        const rankInfo = RANK_INFO[currentRank.toLowerCase()];
        
        if (!rankInfo || !rankInfo.next) {
            return '💎 You\'ve reached the highest rank - Diamond!';
        }
        
        const levelsLeft = rankInfo.threshold - currentLevel;
        
        if (levelsLeft <= 0) {
            return `🎯 Ready for ${rankInfo.next} rank promotion!`;
        }
        
        const levelText = levelsLeft === 1 ? 'level' : 'levels';
        return `🎯 ${levelsLeft} ${levelText} left until ${rankInfo.next} rank!`;
    }
    
    // Helper function for firing confetti
    function fireConfetti() {
        const commonSettings = {
            particleCount: 120,
            spread: 70,
            startVelocity: 80,
            zIndex: 10000,
            colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff']
        };
        
        // Left side burst
        confetti({
            ...commonSettings,
            origin: { x: -0.05, y: 1.1 },
            angle: 55
        });
        
        // Right side burst
        confetti({
            ...commonSettings,
            origin: { x: 1.05, y: 1.1 },
            angle: 125
        });
    }

    document.addEventListener('livewire:initialized', () => {
        // Level-up event handler
        Livewire.on('level-up', (event) => {
            console.log('Level-up event received:', event);
            const level = event[0].level;
            const rank = event[0].new_rank || event[0].rank;
            const oldRank = event[0].old_rank || null;

            console.log('Level:', level, 'Rank:', rank, 'Old Rank:', oldRank);

            if (typeof level !== 'undefined' && typeof rank !== 'undefined') {
                console.log('Calling showLevelUpAnimation...');
                window.showLevelUpAnimation(level, rank, oldRank);
            } else {
                console.log('Invalid level-up data');
            }
        });

        // Reward purchase event handler
        Livewire.on('reward-purchased', window.showPurchaseConfetti);
    });

    // Purchase confetti function
    window.showPurchaseConfetti = function() {
        const commonSettings = {
            particleCount: 50,
            spread: 80,
            startVelocity: 50,
            colors: ['#FFB349', '#03b8ff', '#4CAF50', '#FF5722', '#FFEB3B', '#9C27B0'],
            zIndex: 10000
        };
        
        // Left and right burst
        confetti({ ...commonSettings, origin: { x: 0, y: 1 }, angle: 80 });
        confetti({ ...commonSettings, origin: { x: 1, y: 1 }, angle: 100 });
        
        // Center burst delayed
        setTimeout(() => {
            confetti({
                ...commonSettings,
                spread: 120,
                origin: { x: 0.5, y: 1 },
                angle: 90,
                startVelocity: 60
            });
        }, 100);
    };

    // Main level-up animation function
    window.showLevelUpAnimation = window.showLevelUpAnimation || function(level, rank, oldRank) {
        console.log('showLevelUpAnimation called with:', { level, rank, oldRank });
        
        const formattedRank = rank.charAt(0).toUpperCase() + rank.slice(1);
        const formattedOldRank = oldRank ? oldRank.charAt(0).toUpperCase() + oldRank.slice(1) : null;
        const rankPromotion = oldRank && oldRank !== rank;
        
        const currentRankInfo = RANK_INFO[rank.toLowerCase()] || RANK_INFO.silver;
        const oldRankInfo = oldRank ? RANK_INFO[oldRank.toLowerCase()] : null;
        
        // Fire confetti with higher z-index to appear over the modal
        const commonSettings = {
            particleCount: 120,
            spread: 70,
            startVelocity: 80,
            zIndex: 10002, // Higher than the modal z-index
            colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff']
        };
        
        // Left side burst
        confetti({
            ...commonSettings,
            origin: { x: -0.05, y: 1.1 },
            angle: 55
        });
        
        // Right side burst
        confetti({
            ...commonSettings,
            origin: { x: 1.05, y: 1.1 },
            angle: 125
        });
        
        // Create HTML for level up section
        let htmlContent = `
            <div style="text-align: center; padding: 10px;">
                <!-- Level Up Section -->
                <div class="level-section">
                    <div style="font-size: 3rem; margin-bottom: 10px;">⭐</div>
                    <h2 style="font-size: 2rem; font-weight: bold; color: #03b8ff; margin-bottom: 10px;">LEVEL UP!</h2>
                    <div class="level-badge">${level}</div>
                </div>
        `;
        
        // Add rank section based on promotion status
        if (rankPromotion) {
            htmlContent += `
                <!-- Rank Promotion Section -->
                <div class="rank-promotion">
                    <h3>RANK PROMOTION!</h3>
                    <div class="rank-transition">
                        <div class="old-rank-badge">
                            ${formattedOldRank}
                        </div>
                        <div class="arrow">➜</div>
                        <div class="new-rank-badge">
                            ${formattedRank}
                        </div>
                    </div>
                    <p>You've been promoted to ${formattedRank} rank!</p>
                </div>
            `;
        } else {
            htmlContent += `
                <!-- Current Rank Section -->
                <div class="current-rank">
                    <div class="rank-badge-simple">
                        ${formattedRank} Rank
                    </div>
                </div>
            `;
        }
        
        // Add achievement text
        htmlContent += `
                <!-- Achievement Text -->
                <div class="next-goal">
                    <p>${getNextRankMessage(level, rank)}</p>
                </div>
            </div>
        `;
        
        // Create a custom modal instead of using SweetAlert to avoid conflicts
        console.log('Creating custom level-up modal...');
        
        // Create modal container
        const modalContainer = document.createElement('div');
        modalContainer.className = 'level-up-modal-container';
        modalContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            animation: none;
        `;
        
        // Create modal content
        const modalContent = document.createElement('div');
        modalContent.className = 'level-up-modal-content';
        modalContent.style.cssText = `
            background: white;
            border-radius: 15px;
            border: 2px solid #03b8ff;
            padding: 2em;
            max-width: 400px;
            width: 90%;
            text-align: center;
            position: relative;
            z-index: 1;
            opacity: 0;
            transform: scale(0);
            animation: fadeIn 0.4s 0.15s forwards;
        `;
        
        modalContent.innerHTML = htmlContent + `
            <button class="level-up-confirm-btn" style="
                background: #03b8ff;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 20px;
                font-weight: bold;
                font-size: 1rem;
                cursor: pointer;
                margin-top: 20px;
                margin-left:auto;
                margin-right:auto;
                display:block;
                position: relative;
                z-index: 2;
                pointer-events: auto;
            " onclick="window.closeModalSmoothly(this.closest('.level-up-modal-container'))">
                🎊 AWESOME! 🎊
            </button>
        `;
        
        modalContainer.appendChild(modalContent);
        document.body.appendChild(modalContainer);
        
        // Add click outside to close with smooth animation
        modalContainer.addEventListener('click', (e) => {
            if (e.target === modalContainer) {
                window.closeModalSmoothly(modalContainer);
            }
        });
        
        // Add CSS styles for the custom modal
        const style = document.createElement('style');
        style.textContent = `
            .level-up-modal-container {
                position: fixed;
                top: 0; left: 0; width: 100%; height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10001;
                pointer-events: auto;
            }
            .level-up-modal-container::before {
                content: '';
                position: absolute;
                top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 0;
                pointer-events: auto;
                animation: fadeOverlayIn 0.4s forwards;
            }
            .level-up-modal-container.closing::before {
                animation: fadeOverlayOut 0.3s forwards;
            }
            .level-up-modal-content {
                position: relative;
                z-index: 1;
                background: white !important;
                opacity: 1 !important;
            }
            .level-up-confirm-btn {
                background: #03b8ff;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 20px;
                font-weight: bold;
                font-size: 1rem;
                cursor: pointer;
                margin-top: 20px;
                margin-left: auto;
                margin-right: auto;
                display: block;
                position: relative;
                z-index: 2;
                pointer-events: auto;
                transition: all 0.2s ease;
            }
            .level-up-confirm-btn:hover {
                background: #0288cc !important;
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(3, 184, 255, 0.4);
            }
            .level-up-confirm-btn:active {
                transform: scale(0.98);
            }
            .level-badge {
                background: linear-gradient(135deg, #03b8ff 0%, #0ea5e9 100%);
                color: white;
                padding: 10px 20px;
                border-radius: 30px;
                font-size: 1.8rem;
                font-weight: bold;
                margin: 15px auto;
                box-shadow: 0 4px 12px rgba(3, 184, 255, 0.3);
                display: inline-block;
            }
            .rank-badge-simple {
                background: ${currentRankInfo.gradient};
                color: ${rank.toLowerCase() === 'silver' ? '#333' : 'white'};
                padding: 10px 15px;
                border-radius: 20px;
                font-size: 1.2rem;
                font-weight: bold;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 15px 0;
            }
            .rank-promotion {
                margin-top: 20px;
                padding: 20px;
                background: white;
                border-radius: 12px;
                color: #333;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(0, 0, 0, 0.05);
            }
            .rank-promotion h3 {
                font-size: 1.3rem;
                margin: 5px 0 15px;
                font-weight: bold;
                color: #333;
            }
            .rank-transition {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 15px;
                margin: 15px 0;
            }
            .old-rank-badge {
                background: ${oldRankInfo ? oldRankInfo.gradient : RANK_INFO.silver.gradient};
                color: ${oldRank && oldRank.toLowerCase() === 'silver' ? '#333' : 'white'};
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 1rem;
                font-weight: bold;
                min-width: 80px;
                text-align: center;
            }
            .new-rank-badge {
                background: ${currentRankInfo.gradient};
                color: ${rank.toLowerCase() === 'silver' ? '#333' : 'white'};
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 1rem;
                font-weight: bold;
                min-width: 80px;
                text-align: center;
            }
            .arrow {
                font-size: 1.5rem;
                color: #666;
            }
            .rank-promotion p {
                font-size: 1rem;
                margin: 15px 0 0;
                color: #666;
            }
            .next-goal {
                margin-top: 15px;
            }
            .next-goal p {
                font-size: 1rem;
                color: #666;
                margin: 0;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: scale(0); }
                to { opacity: 1; transform: scale(1); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0); }
            }
            @keyframes fadeOverlayIn {
                from { opacity: 0; }
                to { opacity: 0.7; }
            }
            @keyframes fadeOverlayOut {
                from { opacity: 0.7; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
        console.log('Custom level-up modal created successfully!');
    };

    // Global function to close modal smoothly
    window.closeModalSmoothly = function(container) {
        container.classList.add('closing');
        const card = container.querySelector('.level-up-modal-content');
        if (card) {
            card.style.animation = 'none';
            card.style.animation = 'fadeOut 0.3s forwards';
        }
        setTimeout(() => {
            container.remove();
        }, 300);
    };
</script>