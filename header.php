<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="header_styles.css">
</head>
<body>
    <div class="custom-header">
        <!-- Header -->
        <div class="header">
            <h1>GRN / Purchasing / Suppliers / Item Registration</h1>
        </div>

        <!-- Header Section -->
        <div class="header-container">
            <!-- Left Section: Inventory Image -->
            <div class="inventory-image">
                <img src="cart.png" alt="Inventory">
            </div>
            
            <!-- Middle Section: Company Name -->
            <div class="company-name">
                <h2>එග්ලන්ඩ් සුපර්</h2>
                <h1>Eggland Super</h1>
                <p>හොඳම දේ අඩුම මිලට</p>
            </div>
            
            <!-- Right Section: Date, Time, User, and Store Switcher -->
            <div class="date-time-container">
                <div class="date">
                    <p><strong id="current-date">November 21, 2024</strong></p>
                </div>
                <div class="time">
                    <h2 id="current-time">7:28:50 AM</h2>
                </div>
                <div class="user-info">
                    <label>User: </label>
                    <span id="user">Admin</span>
                    <span id="welcome">Good Morning Guest!</span>
                </div>
                <div class="store-switcher">
                    <select>
                        <option>Main Store</option>
                        <option>Branch Store</option>
                        <option>Kurunegala Store</option>
                    </select>
                    <button>Switch</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateDateTime() {
            // Get current date and time
            const now = new Date();
            
            // Update time
            const timeElement = document.getElementById('current-time');
            timeElement.textContent = now.toLocaleTimeString('en-US', { 
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            
            // Update date
            const dateElement = document.getElementById('current-date');
            dateElement.textContent = now.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            
            // Update greeting based on time of day
            const hour = now.getHours();
            const welcomeElement = document.getElementById('welcome');
            let greeting = '';
            
            if (hour >= 5 && hour < 12) {
                greeting = 'Good Morning';
            } else if (hour >= 12 && hour < 17) {
                greeting = 'Good Afternoon';
            } else if (hour >= 17 && hour < 22) {
                greeting = 'Good Evening';
            } else {
                greeting = 'Good Night';
            }
            
            welcomeElement.textContent = `${greeting} Guest!`;
        }

        // Ensure the script runs after the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Update immediately when page loads
            updateDateTime();

            // Update every second
            setInterval(updateDateTime, 1000);
        });
    </script>
</body>
</html>
