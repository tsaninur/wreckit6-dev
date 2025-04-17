// Fetch data from backend API
async function fetchData() {
    try {
        const response = await fetch('http://localhost:5000/api/data');
        const data = await response.json();
        console.log('Data from database:', data);
        // Process and display data in your UI
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

// Sidebar toggle functionality
function toggleSidebar() {
    console.log('Toggle sidebar called');
    const sidebar = document.getElementById("sidebar");
    const closeBtn = document.querySelector('.close-btn');
    
    if (!sidebar) {
        console.error('Sidebar element not found');
        return;
    }

    const isOpen = sidebar.classList.contains("open");
    console.log('Current state:', isOpen ? 'open' : 'closed');

    if (isOpen) {
        sidebar.style.boxShadow = "none";
        sidebar.classList.remove("open");
        console.log('Sidebar closed');
    } else {
        sidebar.style.boxShadow = "2px 0 20px rgba(255, 255, 0, 0.5)";
        sidebar.classList.add("open");
        console.log('Sidebar opened');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Fetch data when page loads
    fetchData();

    // Initialize sidebar functionality
    const menuBtn = document.querySelector('.menu-btn');
    const closeBtn = document.querySelector('.close-btn');

    if (menuBtn) {
        menuBtn.addEventListener('click', toggleSidebar);
        console.log('Menu button event listener added');
    } else {
        console.error('Menu button not found');
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', toggleSidebar);
        console.log('Close button event listener added');
    } else {
        console.error('Close button not found');
    }

    // Initialize matrix effect if canvas exists
    const matrixCanvas = document.getElementById('matrixCanvas');
    if (matrixCanvas) {
        const ctxMatrix = matrixCanvas.getContext('2d');

        function resizeCanvas() {
            matrixCanvas.width = window.innerWidth;
            matrixCanvas.height = window.innerHeight;
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        const hexChars = '0123456789ABCDEF';
        const fontSize = 18;
        const columns = matrixCanvas.width / fontSize;
        const drops = Array.from({ length: columns }).fill(1);

        function drawMatrixEffect() {
            ctxMatrix.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctxMatrix.fillRect(0, 0, matrixCanvas.width, matrixCanvas.height);
            ctxMatrix.fillStyle = '#FFC100';
            ctxMatrix.font = `${fontSize}px monospace`;

            for (let i = 0; i < drops.length; i++) {
                const hexValue = hexChars[Math.floor(Math.random() * hexChars.length)];
                ctxMatrix.fillText(hexValue, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > matrixCanvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }

        setInterval(drawMatrixEffect, 33);
    }
});
