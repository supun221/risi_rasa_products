<?php
require_once '../header1.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/user_styles.css">

    <title>Employee Salary List</title>

</head>
<style>
    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 60%;
        margin: 20px auto;
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
        min-width: 150px;
    }

    .filter-container button {
        background-color: hsl(229, 98%, 81%);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .filter-container button:hover {
        background-color: hsl(229, 80%, 70%);
    }
</style>

<body>

    <h1>Employee Salary List</h1>
    <div class="filter-container">
        <input type="text" id="empId" placeholder="Employee ID">
        <input type="month" id="startMonth" placeholder="Start Month">
        <input type="month" id="endMonth" placeholder="End Month">
        <button onclick="getSalaryList()">Get Salary List</button>
    </div>

    <div class="table-container">
        <div id="salaryList"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("startMonth").value = new Date().toISOString().slice(0, 7);
            document.getElementById("endMonth").value = new Date().toISOString().slice(0, 7);
            getSalaryList();
        });

        function getSalaryList() {
            const empId = document.getElementById('empId').value;
            const startMonth = document.getElementById('startMonth').value;
            const endMonth = document.getElementById('endMonth').value;

            fetch('../../controllers/salary_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get_salary_list',
                    emp_id: empId,
                    start_month: startMonth,
                    end_month: endMonth
                }),
            })
            .then(response => response.json())
            .then(data => {
                let html = '<table border="1"><tr><th>Emp ID</th><th>Month</th><th>Basic Salary</th><th>Epf (%8)</th><th>Total Additions</th><th>Total Deductions</th><th>EPF(%12)</th><th>ETF(%3)</th><th>Net Salary</th></tr>';
                
                if (Array.isArray(data)) {
                    data.forEach(record => {
                        html += `<tr>
                            <td>${record.emp_id}</td>
                            <td>${record.month_year}</td>
                            <td>${record.basic_salary}</td>
                            <td>${record.provident_fund}</td>
                            <td>${record.total_addition}</td>
                            <td>${record.total_deductions}</td>
                            <td>${record.employer_pf}</td>
                            <td>${record.trust_fund}</td>
                            <td>${record.net_salary}</td>
                        </tr>`;
                    });
                } else {
                    html += `<tr><td colspan="6" style="text-align:center;">No records found</td></tr>`;
                }

                html += '</table>';
                document.getElementById('salaryList').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('salaryList').innerHTML = '<p style="color:red;">Failed to fetch salary data.</p>';
            });
        }
    </script>

</body>

</html>
