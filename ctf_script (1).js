document.addEventListener('DOMContentLoaded', () => {
    /*** MATRIX EFFECT ***/
    const matrixCanvas = document.getElementById('matrixCanvas');
    const ctxMatrix = matrixCanvas.getContext('2d');

    function resizeCanvas() {
        matrixCanvas.width = window.innerWidth;
        matrixCanvas.height = window.innerHeight;
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Hexadecimal characters for memory addresses, format like 0xABCD
    const hexChars = '0123456789ABCDEF';
    const fontSize = 18;
    const columns = matrixCanvas.width / fontSize;
    const drops = Array.from({ length: columns }).fill(1);

    function drawMatrixEffect() {
        ctxMatrix.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctxMatrix.fillRect(0, 0, matrixCanvas.width, matrixCanvas.height);
        ctxMatrix.fillStyle = '#FFC100';  // Set text color to white (same as "COMING SOON")
        ctxMatrix.font = `${fontSize}px monospace`;


        for (let i = 0; i < drops.length; i++) {
            // Generate a random hexadecimal memory address-like string, e.g., 0xABCD
            const hexValue = Array.from({ length: 1 }).map(() => hexChars[Math.floor(Math.random() * hexChars.length)]).join('');
            ctxMatrix.fillText(hexValue, i * fontSize, drops[i] * fontSize);
            if (drops[i] * fontSize > matrixCanvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    }

    setInterval(drawMatrixEffect, 33); // Matrix effect animation

    //  Coming Soon text animation  
    const comingSoonElement = document.querySelector('.coming-soon-title');
    const comingSoonText = 'COMING SOON';
    
    function scrambleText() {
        let scrambled = '';
        for (let i = 0; i < comingSoonText.length; i++) {
            scrambled += Math.random() > 0.5 ? comingSoonText[i] : String.fromCharCode(Math.floor(Math.random() * 94) + 33);  // random char
        }
        comingSoonElement.textContent = scrambled;
    }

    let scrambleInterval = setInterval(scrambleText, 100); // Scrambling effect
    setTimeout(() => {
        clearInterval(scrambleInterval);
        comingSoonElement.textContent = comingSoonText; // Set text to original after scrambling
    }, 5000); // Stop scrambling after 5 seconds

    //  Pixelated Image Effect on Canvas (glitchCanvas)  
    const glitchCanvas = document.getElementById('glitchCanvas');
    const ctxGlitch = glitchCanvas.getContext('2d');
    const image = new Image();
    image.src = 'LOGO WRECKIT-GO-02-01.png'; // Make sure to use the correct image path

    function pixelateImage() {
        glitchCanvas.width = image.width;
        glitchCanvas.height = image.height;

        ctxGlitch.drawImage(image, 0, 0);

        let imageData = ctxGlitch.getImageData(0, 0, glitchCanvas.width, glitchCanvas.height);
        let pixels = imageData.data;

        // Apply pixelation
        for (let i = 0; i < pixels.length; i += 4) {
            if (Math.random() > 0.5) {
                let randomPixel = Math.floor(Math.random() * 255);
                pixels[i] = randomPixel; // Red
                pixels[i + 1] = randomPixel; // Green
                pixels[i + 2] = randomPixel; // Blue
            }
        }
        
        ctxGlitch.putImageData(imageData, 0, 0);

        // After 3 seconds, make it clear
        setTimeout(() => {
            ctxGlitch.clearRect(0, 0, glitchCanvas.width, glitchCanvas.height);
            ctxGlitch.drawImage(image, 0, 0);
        }, 3000);
    }

    // Start the pixelate effect when the image is loaded
    image.onload = pixelateImage;
});
