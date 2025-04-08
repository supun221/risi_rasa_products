<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RisiRasa POS - Branch Sales</title>
    <link href="https://fonts.googleapis.com/css?family=Cardo:400i|Rubik:400,700&display=swap" rel="stylesheet">
    <style>
        /* Same CSS as before with modifications for .date-selection and card layout */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            /* Uncomment if needed later */
            /* display: flex; flex-direction: column; align-items: center; background-color: #f0f0f0; */
        }

        .page-content {
            display: grid;
            grid-gap: 1.5rem;
            padding: 1rem;
            max-width: 1200px;
            margin: 20px auto;
            font-family: 'Rubik', sans-serif;
        }

        @media (min-width: 600px) {
            .page-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 900px) {
            .page-content {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            /* Changed to column to stack content vertically */
            align-items: center;
            overflow: hidden;
            /* Changed to visible or scroll if needed */
            padding: 1.5rem;
            width: 100%;
            min-width: 300px;
            text-align: center;
            color: whitesmoke;
            background-color: whitesmoke;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1),
                0 2px 2px rgba(0, 0, 0, 0.1),
                0 4px 4px rgba(0, 0, 0, 0.1),
                0 8px 8px rgba(0, 0, 0, 0.1),
                0 16px 16px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            min-height: 450px;
            /* Ensure enough height for content */
        }

        @media (min-width: 600px) {
            .card {
                min-height: 450px;
                /* Use min-height instead of fixed height */
            }
        }

        .card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 110%;
            background-size: cover;
            background-position: center;
            transition: transform 1.05s cubic-bezier(0.19, 1, 0.22, 1);
            pointer-events: none;
            background-color: #8b0000;
        }

        .card:after {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 200%;
            pointer-events: none;
            background-image: linear-gradient(to bottom,
                    hsla(0, 0%, 0%, 0) 0%,
                    hsla(0, 0%, 0%, 0.2) 40%,
                    hsla(0, 0%, 0%, 0.8) 100%);
            transform: translateY(-50%);
            transition: transform 1.4s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .content {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 1.5rem;
            transition: transform 0.7s cubic-bezier(0.19, 1, 0.22, 1);
            z-index: 1;
        }

        .content>*+* {
            margin-top: 1rem;
        }

        .title {
            font-size: 1.5rem;
            font-weight: bold;
            line-height: 1.2;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .copy {
            font-family: 'Cardo', serif;
            font-size: 1.125rem;
            font-style: italic;
            line-height: 1.35;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .sales {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff4500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .loading {
            font-size: 1.2rem;
            color: #ffd700;
            font-style: italic;
        }

        .initial-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .initial-content>* {
            opacity: 1;
            transform: translateY(0);
            transition: none;
        }

        .card:hover .initial-content,
        .card:focus-within .initial-content {
            display: none;
        }

        .card:hover .content,
        .card:focus-within .content {
            transform: translateY(0);
        }

        .card:hover .content>*:not(.title),
        .card:focus-within .content>*:not(.title) {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.0875s;
        }

        /* Updated Date Selection Styling */
        .date-selection {
            margin-top: 1.5rem;
            padding: 1rem;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: none;
            /* Ensure it starts hidden */
        }

        .date-selection form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .date-selection label {
            color: #333;
            font-size: 0.9rem;
            font-weight: bold;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }

        .date-selection input[type="date"] {
            padding: 0.5rem;
            font-size: 0.9rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            color: #333;
            width: 100%;
            max-width: 200px;
            transition: border-color 0.3s ease;
        }

        .date-selection input[type="date"]:focus {
            border-color: #ff4500;
            outline: none;
            box-shadow: 0 0 4px rgba(255, 69, 0, 0.5);
        }

        .date-selection button {
            padding: 0.6rem 1.2rem;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            background-color: #8b0000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .date-selection button:hover {
            background-color: #a30000;
        }

        .date-selection p {
            margin-top: 0.75rem;
            font-size: 1rem;
            font-weight: bold;
            color: #ff4500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .btn {
            cursor: pointer;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            font-weight: bold;
            letter-spacing: 0.025rem;
            text-transform: uppercase;
            color: white;
            background-color: #8b0000;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #a30000;
        }

        .btn:focus {
            outline: 1px dashed #ff4500;
            outline-offset: 3px;
        }

        @media (hover: hover) and (min-width: 600px) {
            .card:after {
                transform: translateY(0);
            }

            .content {
                transform: translateY(calc(100% - 4.5rem));
            }

            .content>*:not(.title) {
                opacity: 0;
                transform: translateY(1rem);
                transition: transform 0.7s cubic-bezier(0.19, 1, 0.22, 1),
                    opacity 0.7s cubic-bezier(0.19, 1, 0.22, 1);
            }

            .card:hover,
            .card:focus-within {
                align-items: center;
            }

            .card:hover:before,
            .card:focus-within:before {
                transform: translateY(-4%);
            }

            .card:hover:after,
            .card:focus-within:after {
                transform: translateY(-50%);
            }

            .card:hover .content,
            .card:focus-within .content {
                transform: translateY(0);
            }

            .card:hover .content>*:not(.title),
            .card:focus-within .content>*:not(.title) {
                opacity: 1;
                transform: translateY(0);
                transition-delay: 0.0875s;
            }

            .card:focus-within:before,
            .card:focus-within:after,
            .card:focus-within .content,
            .card:focus-within .content>*:not(.title) {
                transition-duration: 0s;
            }

        }

        .sales {
            font-size: 1.8rem;
            /* Increased from 1.5rem to 1.8rem, adjust as needed */
            font-weight: bold;
          
            /* Changed from #ff4500 (orange) to #000000 (black) */
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>
    <?php
    // Include database configuration
    require_once '../../../config/databade.php'; // Correct to 'database.php' in your setup

    // Check database connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get today's date in YYYY-MM-DD format
    $today = date('Y-m-d');

    // Query to fetch branches and their total sales for today
    $sql = "SELECT b.branch_id, b.branch_name, b.address, b.phone,
        COALESCE(SUM(br.gross_amount), 0) as today_sales
        FROM branch b
        LEFT JOIN bill_records br ON b.branch_name = br.branch
        AND DATE(br.bill_date) = ?
        GROUP BY b.branch_id, b.branch_name, b.address, b.phone";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Array to store branch data
    $branches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $branches[] = [
            'id' => $row['branch_id'],
            'name' => $row['branch_name'],
            'address' => $row['address'],
            'phone' => $row['phone'],
            'sales' => $row['today_sales'], // Renamed to today_sales for clarity
            'image' => match ($row['branch_id'] % 3) {
                0 => 'https://images.unsplash.com/photo-1603893696564-8fbba929f000?ixlib=rb-4.0.3&fit=crop&w=400&q=80',
                1 => 'https://images.unsplash.com/photo-1615485290232-9b8bc7f33909?ixlib=rb-4.0.3&fit=crop&w=400&q=80',
                2 => 'https://images.unsplash.com/photo-1621955961411-7b1f2fbedd00?ixlib=rb-4.0.3&fit=crop&w=400&q=80',
            }
        ];
    }

    // Free result set and close statement
    mysqli_stmt_free_result($stmt);
    mysqli_stmt_close($stmt);
    ?>

    <?php require_once '../../../header1.php'; ?>
    <main class="page-content">
        <?php if (empty($branches)): ?>
            <p>No branches found.</p>
        <?php else: ?>
            <?php foreach ($branches as $branch): ?>
                <div class="card" style="background-image: url('<?php echo htmlspecialchars($branch['image']); ?>');" data-branch-id="<?php echo $branch['id']; ?>">
                    <div class="initial-content">
                        <p class="sales">Today's Sales: Rs.<?php echo number_format($branch['sales'], 2); ?></p>
                    </div>
                    <div class="content">
                        <h2 class="title"><?php echo htmlspecialchars($branch['name']); ?></h2>
                        <p class="copy"><?php echo htmlspecialchars($branch['address']); ?></p>
                        <p class="copy"><?php echo htmlspecialchars($branch['phone']); ?></p>
                        <p class="sales">Today's Sales: Rs.<?php echo number_format($branch['sales'], 2); ?></p>
                        <button class="btn" onclick="toggleDateSelection(<?php echo $branch['id']; ?>)">View Details</button>
                        <div id="date-selection-<?php echo $branch['id']; ?>" class="date-selection" style="display: none;">
                            <form onsubmit="fetchSalesByDate(event, <?php echo $branch['id']; ?>)">
                                <label for="start-date-<?php echo $branch['id']; ?>">Start Date:</label>
                                <input type="date" id="start-date-<?php echo $branch['id']; ?>" name="start-date" required>
                                <label for="end-date-<?php echo $branch['id']; ?>">End Date:</label>
                                <input type="date" id="end-date-<?php echo $branch['id']; ?>" name="end-date" required>
                                <button type="submit">Get Sales</button>
                            </form>
                            <p id="sales-result-<?php echo $branch['id']; ?>"></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script>
        // JavaScript fallback for image loading errors
        document.querySelectorAll('.card').forEach(card => {
            const img = new Image();
            img.src = card.style.backgroundImage.slice(5, -2);
            img.onerror = () => {
                console.log(`Failed to load image for ${card.querySelector('.title').textContent}`);
                card.style.backgroundImage = 'url("https://via.placeholder.com/400x450?text=Image+Not+Found")';
            };
        });

        function toggleDateSelection(branchId) {
            const card = document.querySelector(`.card[data-branch-id="${branchId}"]`);
            const dateSelection = document.getElementById(`date-selection-${branchId}`);

            if (dateSelection.style.display === 'none') {
                dateSelection.style.display = 'block';
                card.classList.add('expanded');
            } else {
                dateSelection.style.display = 'none';
                card.classList.remove('expanded');
            }
        }

        function fetchSalesByDate(event, branchId) {
            event.preventDefault();
            const startDate = document.getElementById(`start-date-${branchId}`).value;
            const endDate = document.getElementById(`end-date-${branchId}`).value;

            fetch(`get_sales_by_date.php?branch_id=${branchId}&start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById(`sales-result-${branchId}`).textContent = `Total Sales: Rs.${data.total_sales}`;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>