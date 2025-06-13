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
            const level = event[0].level;
            const rank = event[0].new_rank || event[0].rank;
            const oldRank = event[0].old_rank || null;

            if (typeof level !== 'undefined' && typeof rank !== 'undefined') {
                window.showLevelUpAnimation(level, rank, oldRank);
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
        const formattedRank = rank.charAt(0).toUpperCase() + rank.slice(1);
        const formattedOldRank = oldRank ? oldRank.charAt(0).toUpperCase() + oldRank.slice(1) : null;
        const rankPromotion = oldRank && oldRank !== rank;
        
        const currentRankInfo = RANK_INFO[rank.toLowerCase()] || RANK_INFO.silver;
        const oldRankInfo = oldRank ? RANK_INFO[oldRank.toLowerCase()] : null;
        
        // Fire confetti
        fireConfetti();
        
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
        
        // Show the modal with streamlined options
        Swal.fire({
            html: htmlContent,
           
            showConfirmButton: true,
            confirmButtonText: `<div class="confirm-button">🎊 AWESOME! 🎊</div>`,
            confirmButtonColor: '#03b8ff',
            width: '400px',
            padding: '1em',
            backdrop: `rgba(0,0,0,0.7)`,
            allowOutsideClick: true,
            allowEscapeKey: true,
            timer: 12000,
            timerProgressBar: true,
            customClass: {
                popup: 'level-up-popup',
                confirmButton: 'level-up-confirm-btn'
            },
            didOpen: () => {
                // Add styling
                const style = document.createElement('style');
                style.textContent = `
                    .level-up-popup {
                        border-radius: 15px !important;
                        border: 2px solid #03b8ff !important;
                        animation: fadeIn 0.4s;
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
                    .confirm-button {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px; 
                        font-size: 1rem;
                    }
                    .level-up-confirm-btn {
                        border-radius: 20px !important;
                        padding: 8px 20px !important;
                        font-weight: bold !important;
                    }
                    @keyframes fadeIn {
                        from { opacity: 0; transform: scale(0.8); }
                        to { opacity: 1; transform: scale(1); }
                    }
                `;
                document.head.appendChild(style);
                
                // Force close on backdrop click
                const backdrop = document.querySelector('.swal2-container');
                if (backdrop) {
                    backdrop.addEventListener('click', function(e) {
                        if (e.target === backdrop) Swal.close();
                    });
                }
            },
            willClose: () => {
                // Clean up styles on close
                document.querySelectorAll('style').forEach(style => {
                    if (style.textContent.includes('level-up-popup')) {
                        document.head.removeChild(style);
                    }
                });
            }
        });
    };
</script>