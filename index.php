<?php
// Handling the backend processing for the chatbot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    // Read the input JSON data
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if the input data is properly decoded and the 'message' key exists
    if (is_null($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input: JSON data not decoded']);
        exit;
    }

    if (!isset($input['message'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input: message key missing']);
        exit;
    }

    $userMessage = $input['message'];

    // Escape the message to avoid shell injection
    $escapedMessage = escapeshellarg($userMessage);

    // Call the Python script and pass the user message
    $command = escapeshellcmd("python chat.py $escapedMessage");
    $output = shell_exec($command);

    if ($output === null) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to generate response']);
        exit;
    }

    echo json_encode(['response' => trim($output)]);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skytrade Bot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            background-image: url('bg.jpg');
            background-size: cover;
            background-position: center;
        }

        .drone {
            position: absolute;
            width: 100px;
            height: 100px;
            background-image: url('drone.png');
            background-size: contain;
            background-repeat: no-repeat;
        }

        .chat-container {
            width: 53vw;
            height: 80vh;
            border: 1px solid #ccc;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
            z-index: 1;
        }

        .chat-header {
            background: #ffffff;
            text-align: center;
            padding: 10px;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .chat-header img {
            width: 200px;
            height: 200px;
            
        }

        .chat-box {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: hsl(182, 100%, 95%);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .input-box {
            display: flex;
            border-top: 1px solid #ccc;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .input-box input {
            flex: 1;
            padding: 10px;
            border: none;
            outline: none;
            height: 50px;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }

        .input-box button {
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            width: 100px;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .input-box button:hover {
            background: #0056b3;
        }

        .message {
            padding: 10px;
            border-radius: 15px;
            max-width: 75%;
            word-wrap: break-word;
            display: inline-block;
        }

        .message.user {
            background: #007bff;
            color: white;
            align-self: flex-end;
        }

        .message.bot {
            background: #ffffff;
            color:#007bff;
            align-self: flex-start;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <img src="logo.png" class="imgC" alt="Skytrade Logo" />
        </div>
        <div class="chat-box" id="chat-box">
            <!-- Messages will be appended here -->
        </div>
        <div class="input-box">
            <input type="text" id="user-input" placeholder="Type a message..." />
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        const drones = [];
        const chatContainer = document.querySelector('.chat-container');

        // Initialize drones
        for (let i = 0; i < 3; i++) {
            const drone = document.createElement('div');
            drone.classList.add('drone');
            document.body.appendChild(drone);
            drones.push(drone);

            const startX = Math.random() * (window.innerWidth - 100);
            const startY = Math.random() * (window.innerHeight - 100);
            const dx = (Math.random() - 0.5) * 4;
            const dy = (Math.random() - 0.5) * 4;

            setupDrone(drone, startX, startY, dx, dy);
        }

        function setupDrone(drone, x, y, dx, dy) {
            drone.style.left = `${x}px`;
            drone.style.top = `${y}px`;

            function moveDrone() {
                x += dx;
                y += dy;

                if (x + 100 > window.innerWidth || x < 0) {
                    dx = -dx;
                }

                if (y + 100 > window.innerHeight || y < 0) {
                    dy = -dy;
                }

                drone.style.left = `${x}px`;
                drone.style.top = `${y}px`;

                requestAnimationFrame(moveDrone);
            }

            moveDrone();
        }

        async function sendMessage() {
            const userInput = document.getElementById("user-input").value;
            if (!userInput) return;

            const chatBox = document.getElementById("chat-box");

            // Display user message
            const userMessage = document.createElement("div");
            userMessage.classList.add("message", "user");
            userMessage.innerText = userInput;
            chatBox.appendChild(userMessage);

            document.getElementById("user-input").value = "";

            try {
                // Send user message to backend
                const response = await fetch("", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ message: userInput }),
                });

                const result = await response.json();

                if (response.ok) {
                    // Display bot response
                    const botMessage = document.createElement("div");
                    botMessage.classList.add("message", "bot");
                    botMessage.innerText = result.response;
                    chatBox.appendChild(botMessage);
                } else {
                    console.error('Error:', result.error);
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }

            // Scroll to the bottom of the chat
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Add event listener for 'Enter' key press
        document.getElementById("user-input").addEventListener("keypress", function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
    </script>
</body>
</html>
