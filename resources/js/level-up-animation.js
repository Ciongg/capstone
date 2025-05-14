function fireConfetti() {
    const count = 120;
    
    confetti({
        particleCount: count,
        spread: 70,
        origin: { x: -0.05, y: 1.1 },
        angle: 55,
        startVelocity: 80,
        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'],
        zIndex: 10000
    });
    
    confetti({
        particleCount: count,
        spread: 70,
        origin: { x: 1.05, y: 1.1 },
        angle: 125,
        startVelocity: 80,
        colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'],
        zIndex: 10000
    });
}

window.levelUpAnimationTimeoutId = null;

window.showLevelUpAnimation = function(level, title) {
    console.log("Level up animation triggered:", level, title);

    if (window.levelUpAnimationTimeoutId) {
        clearTimeout(window.levelUpAnimationTimeoutId);
        window.levelUpAnimationTimeoutId = null;
        console.log("Cleared existing auto-close timeout");
    }
    
    const existingContainer = document.getElementById('level-up-container');
    if (existingContainer) {
        existingContainer.remove();
    }
    
    // Create a container for the animation
    const container = document.createElement('div');
    container.className = 'fixed inset-0 flex items-center justify-center z-[9999]';
    container.id = 'level-up-container';
    
    // Create the content - simplified white design, backdrop background will be set in CSS
    container.innerHTML = `
        <div class="absolute inset-0" id="level-up-backdrop" onclick="closeAnimation()"></div> 
        <div class="bg-white p-12 rounded-xl shadow-2xl border border-gray-200 text-center relative z-[10000]" id="level-up-modal" style="min-width: 400px; max-width: 90%; width: 600px; min-height: 350px;"> 
            <div class="text-yellow-500 text-6xl font-bold mb-8 animate-congrats-glow animate-text-breath">Congratulations!</div>
            <div class="text-4xl mb-4 animate-text-breath">
                You Leveled Up!
            </div>
            <div class="text-7xl font-bold text-[#03b8ff] mb-10 animate-text-breath">Level ${level}</div> 
            <div class="flex justify-center gap-4 mt-8"> 
                <button class="cursor-pointer bg-[#03b8ff] hover:bg-[#86ddff] text-white font-semibold py-3 px-10 rounded-lg transition text-xl" onclick="closeAnimation()"> 
                    AWESOME!
                </button>
            </div>
        </div>
    `;

    window.closeAnimation = function() {
        console.log("Closing level up animation");
        const container = document.getElementById('level-up-container');
        if (container) {
            container.classList.add('animate-fadeout');
            setTimeout(() => {
                if (document.getElementById('level-up-container')) {
                     document.getElementById('level-up-container').remove();
                }
                const styleTag = document.getElementById('level-up-dynamic-styles');
                if (styleTag) {
                    styleTag.remove();
                }
            }, 500);
        }
    }

    const style = document.createElement('style');
    style.id = 'level-up-dynamic-styles';
    style.textContent = `
        #level-up-container {
            opacity: 0;
            animation: levelup-fadein 0.5s ease-out forwards;
            background-color: transparent; /* Explicitly set parent to transparent */
        }
        #level-up-backdrop {
            background-color: rgba(0, 0, 0, 0.6); /* Directly set opacity black */
        }
        #level-up-modal {
            transform: scale(0.7);
            opacity: 0;
            animation: levelup-scalein 0.7s ease-out 0.3s forwards;
        }
        .animate-fadeout {
            animation: levelup-fadeout 0.5s ease-out forwards !important;
        }
        @keyframes levelup-fadein {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes levelup-scalein {
            0% { transform: scale(0.7); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes levelup-fadeout {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
        
        /* Glowing animation for Congratulations text */
        .animate-congrats-glow {
            animation: congrats-glow 2s ease-in-out infinite alternate;
        }
        @keyframes congrats-glow {
            0% {
                text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #ffd700, 0 0 20px #ffd700, 0 0 25px #ffd700, 0 0 30px #ffd700, 0 0 35px #ffd700;
            }
            100% {
                text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #ffc400, 0 0 40px #ffc400, 0 0 50px #ffc400, 0 0 60px #ffc400, 0 0 70px #ffc400;
            }
        }

        /* Breathing animation for text */
        .animate-text-breath {
            animation: text-breath 2.5s ease-in-out infinite alternate;
        }
        @keyframes text-breath {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.03); /* Slightly larger */
            }
            100% {
                transform: scale(1);
            }
        }

        /* Make sure confetti appears above the backdrop */
        canvas.confetti-canvas {
            position: fixed !important;
            z-index: 10000 !important;
        }
    `;

    document.body.appendChild(style);
    document.body.appendChild(container);

    setTimeout(() => {
        console.log("Firing confetti");
        if (typeof confetti !== 'undefined') {
            fireConfetti();
        }
    }, 500);

    window.levelUpAnimationTimeoutId = setTimeout(() => {
        console.log("Auto-closing level up animation");
        closeAnimation();
        window.levelUpAnimationTimeoutId = null;
    }, 15000);
};
