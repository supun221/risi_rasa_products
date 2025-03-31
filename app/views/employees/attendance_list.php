<?php
require_once '../header1.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/user_styles.css">

    <title>Employee Attendance</title>

</head>
<style>
    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50%;
        /* Match table width */
        margin: 20px auto;
        /* Center align */
        gap: 10px;
    }

    .filter-container input,
    .filter-container button {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 16px;
    }

    .filter-container input {
        flex: 1;
        /* Make inputs take equal space */
        min-width: 150px;
    }

    .filter-container input:focus {
        outline: none;
        border-color: hsl(229, 98%, 81%);
        box-shadow: 0 0 5px hsl(229, 98%, 81%);
    }

    .filter-container button {
        padding: 10px 15px;
        background-color: hsl(229, 98%, 81%);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease;
    }

    .filter-container button:hover {
        background-color: hsl(229, 80%, 70%);
    }

   
</style>

<body>

    <h1>Employee Attendance</h1>
    <div class="filter-container">
        <input type="text" id="empId" placeholder="Employee ID">
        <input type="date" id="startDate" placeholder="Start Date">
        <input type="date" id="endDate" placeholder="End Date">
        <input type="month" id="month" placeholder="Month">
        <button onclick="getAttendance()">Get Attendance</button>
    </div>


    <div class="table-container">
        <div id="attendanceList"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("month").value = new Date().toISOString().slice(0, 7); // Auto-set current month
            getAttendance(); // Auto-fetch attendance on page load
        });

        function getAttendance() {
            const empId = document.getElementById('empId').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const month = document.getElementById('month').value;

            fetch('../../controllers/attendance_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'get_attendance',
                        emp_id: empId,
                        start_date: startDate,
                        end_date: endDate,
                        month: month
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    let html = '<table border="1"><tr><th>Emp ID</th><th>Name</th><th>Date</th><th>In Time</th><th>Out Time</th><th>Total Hours</th></tr>';

                    if (Array.isArray(data)) {
                        data.forEach(record => {
                            html += `<tr>
                            <td>${record.emp_id}</td>
                            <td>${record.name}</td>
                            <td>${record.date}</td>
                            <td>${record.in_time}</td>
                            <td>${record.out_time || 'N/A'}</td>
                            <td>${record.total_hours}</td>
                        </tr>`;
                        });
                    } else {
                        html += `<tr><td colspan="6" style="text-align:center;">No records found</td></tr>`;
                    }

                    html += '</table>';
                    document.getElementById('attendanceList').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('attendanceList').innerHTML = '<p style="color:red;">Failed to fetch attendance data.</p>';
                });
        }
        document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });

    </script>

</body>

</html>