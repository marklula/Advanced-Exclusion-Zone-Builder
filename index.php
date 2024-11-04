<!DOCTYPE html>
<html>
<head>
    <title>Dot Placement and Polygon Drawer</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Changed to flex-start to position header and banner at the top */
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif; /* Added a font for better appearance */
        }
        h1 {
            margin-top: 20px; /* Space above the header */
        }
        #banner {
            background-color: #e0e7ff; /* Light blue background for banner */
            border: 1px solid #ccc; /* Light border around the banner */
            padding: 15px; /* Space inside the banner */
            text-align: center; /* Centered text */
            margin: 10px 0; /* Space above and below the banner */
            width: 90%; /* Adjusted width to fit nicely */
        }
        #container {
            position: relative;
            width: 850px;
            height: 520px; /* Adjusted height to fit the y-range */
            background-color: #f0f0f0;
            border: 1px solid #000;
            margin-bottom: 20px;
        }
        .dot {
            position: absolute;
            width: 5px;
            height: 5px;
            background-color: red;
            border-radius: 50%;
        }
        #canvas {
            position: absolute;
            top: 0;
            left: 0;
        }
        #crosshair {
            position: absolute;
            top: 0;
            left: 0;
            width: 850px;
            height: 520px; /* Adjusted height to fit the y-range */
            pointer-events: none;
        }
        #crosshair .horizontal, #crosshair .vertical {
            position: absolute;
            background-color: black;
            opacity: 0.5;
        }
        #crosshair .horizontal {
            top: 260px; /* Centered horizontally within the new height */
            left: 0;
            width: 850px; /* Full width of the drawing panel */
            height: 1px;
        }
        #crosshair .vertical {
            top: 0;
            left: 425px; /* Centered vertically */
            width: 1px;
            height: 520px; /* Full height of the drawing panel */
        }
        #coordinates {
            width: 850px;
            height: 50px;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #fff;
            overflow-x: auto;
            white-space: nowrap;
        }
        #copyButton, #clearButton {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px; /* Space between buttons */
        }
        #buttonContainer {
            display: flex;
            justify-content: center;
            margin-bottom: 10px; /* Space below buttons */
        }
        #uploadButton {
            margin-top: 10px;
        }
        #imageInput {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Advanced Exclusion Zone Builder</h1>
    <div id="banner">
        Exclusion zones provide a way to prevent the automated Speaker Tracking functionality within a particular zone from framing in non-desirable ways. This tool was developed to generate coordinates for the Cisco Quad Camera.
    </div>
    <input type="file" id="imageInput" accept="image/*" onchange="loadImage(event)" />
    <div id="container" onclick="placeDot(event)">
        <canvas id="canvas" width="850" height="520"></canvas> <!-- Adjusted canvas height -->
        <div id="crosshair">
            <div class="horizontal"></div>
            <div class="vertical"></div>
        </div>
    </div>
    <div id="coordinates"></div>
    <div id="buttonContainer">
        <button id="copyButton" onclick="copyCoordinates()">Copy Coordinates</button>
        <button id="clearButton" onclick="clearCoordinates()">Clear Coordinates</button>
    </div>

    <script>
        const container = document.getElementById('container');
        const canvas = document.getElementById('canvas');
        const coordinatesBox = document.getElementById('coordinates');
        const ctx = canvas.getContext('2d');
        const dots = [];
        const centerX = 425;
        const centerY = 260; // Adjusted to center within the new height
        let backgroundImage = null; // Variable to store the background image

        function loadImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                backgroundImage = new Image(); // Create a new Image object
                backgroundImage.src = e.target.result; // Set the image source
                backgroundImage.onload = function() {
                    // Draw the uploaded image as the background
                    ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas before drawing
                    ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
                    redrawPolygon(); // Redraw the polygon after loading the image
                }
            }
            if (file) {
                reader.readAsDataURL(file);
            }
        }

        function placeDot(event) {
            const rect = container.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            const adjustedX = Math.round(x - centerX); // Original x-coordinates
            const adjustedY = Math.round(centerY - y); // Reverse the y-coordinate and round

            dots.push({ x: adjustedX, y: adjustedY });
            drawDot(x, y);

            redrawPolygon(); // Redraw the polygon every time a dot is added

            updateCoordinates();
        }

        function drawDot(x, y) {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            dot.style.left = `${x - 2.5}px`;
            dot.style.top = `${y - 2.5}px`;
            container.appendChild(dot);
        }

        function redrawPolygon() {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas before redrawing
            if (backgroundImage) {
                ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height); // Redraw the background image
            }
            if (dots.length > 0) {
                ctx.beginPath();
                ctx.moveTo(dots[0].x + centerX, centerY - dots[0].y); // Adjust y-coordinate

                for (let i = 1; i < dots.length; i++) {
                    ctx.lineTo(dots[i].x + centerX, centerY - dots[i].y); // Adjust y-coordinate
                }
                ctx.closePath();

                // Set fill color to red with 15% transparency
                ctx.fillStyle = 'rgba(255, 0, 0, 0.15)';
                ctx.fill();

                // Draw the outline of the polygon
                ctx.stroke();
            }
        }

        function updateCoordinates() {
            // Flip x-coordinates for display only
            coordinatesBox.innerHTML = dots.map(dot => `${-dot.x}, ${dot.y}`).join(', ');
        }

        function copyCoordinates() {
            const coordinatesText = coordinatesBox.innerText;
            navigator.clipboard.writeText(coordinatesText).then(() => {
                alert('Coordinates copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy coordinates: ', err);
            });
        }

        function clearCoordinates() {
            // Clear the dots array and coordinates display
            dots.length = 0;
            coordinatesBox.innerHTML = '';
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear the canvas

            // Redraw the background image
            if (backgroundImage) {
                ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            }

            // Remove all dot elements from the container
            const existingDots = document.querySelectorAll('.dot');
            existingDots.forEach(dot => dot.remove());
        }
    </script>
</body>
</html>