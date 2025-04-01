<!DOCTYPE html>
<html>

<head>
    <title>Employee Management | Risi Rasa Products</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        /* General Layout & Typography */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Buttons */
        .action-btn {
            background-color: #4361ee;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        .action-btn:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 5px 8px rgba(67, 97, 238, 0.3);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .action-btn i {
            font-size: 16px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-info {
            background-color: #17a2b8;
            box-shadow: 0 3px 6px rgba(23, 162, 184, 0.2);
        }

        .btn-info:hover {
            background-color: #138496;
            box-shadow: 0 5px 8px rgba(23, 162, 184, 0.3);
        }

        .btn-warning {
            background-color: #fca311;
            box-shadow: 0 3px 6px rgba(252, 163, 17, 0.2);
        }

        .btn-warning:hover {
            background-color: #e09010;
            box-shadow: 0 5px 8px rgba(252, 163, 17, 0.3);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            font-size: 14px;
        }

        table th {
            background-color: #f8f9fa;
            color: #516173;
            font-weight: 600;
            padding: 14px 12px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #3a3f51;
            vertical-align: middle;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:hover {
            background-color: #f8faff;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-buttons a, 
        .action-buttons button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
        }

        .action-btn-edit {
            background-color: #4361ee;
        }

        .action-btn-edit:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        .action-btn-delete {
            background-color: #ef476f;
        }

        .action-btn-delete:hover {
            background-color: #d64161;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(239, 71, 111, 0.2);
        }

        /* Barcode Scanner Styles */
        .barcode-scanner {
            margin: 20px 0;
            text-align: center;
            display: flex;
            justify-content: center;
        }

        #barcodeInput {
            padding: 14px;
            width: 400px;
            font-size: 16px;
            border: 2px solid #4361ee;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
        }

        #barcodeInput:focus {
            outline: none;
            border-color: #3249c2;
            box-shadow: 0 5px 8px rgba(67, 97, 238, 0.2);
        }

        /* Popup styles remain the same */
        .popup-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .popup h2 {
            margin-top: 0;
        }

        .popup form {
            display: flex;
            flex-direction: column;
        }

        .popup form input {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .popup form button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <?php
    require_once '../header1.php';
    require_once '../../../config/databade.php';

    $employees = [];
    $query = "SELECT emp_id, name, telephone, nic, address FROM employees";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $employees[] = $row;
        }
    } else {
        echo "<p>Error fetching employees: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
    }

    include 'update_employee.php';
    require_once 'add_employee.php';
    require_once 'Salary_form.php';
    ?>

    <div class="content-wrapper">
        <div class="page-header">
            <h1>Employee Management</h1>
            <div class="btn-group">
                <button type="button" class="action-btn" data-toggle="modal" data-target="#addEmployeeModal">
                    <i class="fas fa-user-plus"></i> Add Employee
                </button>
                <button type="button" class="action-btn btn-warning" onclick="showSalaryModal()">
                    <i class="fas fa-money-bill-wave"></i> Add Salary
                </button>
            </div>
        </div>

        <div class="barcode-scanner">
            <input type="text" id="barcodeInput" placeholder="Scan Employee Barcode" autofocus>
        </div>

        <div class="btn-group" style="justify-content: center; margin-bottom: 20px;">
            <button type="button" class="action-btn btn-info" onclick="location.href='attendance_list.php'">
                <i class="fas fa-clipboard-list"></i> Attendance List
            </button>
            <button type="button" class="action-btn btn-info" onclick="location.href='salary_list.php'">
                <i class="fas fa-money-check-alt"></i> Salary List
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Employee ID</th>
                        <th width="20%">Employee Name</th>
                        <th width="15%">Telephone</th>
                        <th width="15%">NIC</th>
                        <th width="15%">Address</th>
                        <th width="10%">Barcode</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($employees)) {
                        foreach ($employees as $index => $employee) {
                            echo '<tr id="employee-row-' . htmlspecialchars($employee['emp_id']) . '">';
                            echo '<td>' . ($index + 1) . '</td>';
                            echo '<td>' . htmlspecialchars($employee['emp_id']) . '</td>';
                            echo '<td><strong>' . htmlspecialchars($employee['name']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($employee['telephone']) . '</td>';
                            echo '<td>' . htmlspecialchars($employee['nic']) . '</td>';
                            echo '<td>' . htmlspecialchars($employee['address']) . '</td>';
                            echo '<td><svg class="barcode" data-empid="' . htmlspecialchars($employee['emp_id']) . '"></svg></td>';
                            echo '<td class="action-buttons">';
                            echo '<a href="javascript:void(0);" onclick="loadEmployeeDetails(\'' . htmlspecialchars($employee['emp_id']) . '\')" class="action-btn-edit" title="Edit Employee"><i class="fas fa-pencil-alt"></i></a>';
                            echo '<button onclick="confirmDelete(\'' . htmlspecialchars($employee['emp_id']) . '\')" class="action-btn-delete" title="Delete Employee"><i class="fas fa-trash-alt"></i></button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8" style="text-align:center;padding:20px;">No employees found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".barcode").forEach(function(element) {
                const empId = element.getAttribute("data-empid");
                if (empId) {
                    JsBarcode(element, empId, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 40,
                        displayValue: true
                    });
                }
            });
        });

        function confirmDelete(empId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../../controllers/employee_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                emp_id: empId,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success');
                                const row = document.getElementById('employee-row-' + empId);
                                if (row) row.remove();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch((error) => {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                }
            });
        }

        function loadEmployeeDetails(empId) {
            fetch('../../controllers/employee_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'get',
                        emp_id: empId,
                    }),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success && data.data) {
                        const employee = data.data;
                        document.getElementById('update-employee-id').value = employee.emp_id;
                        document.getElementById('update-name').value = employee.name;
                        document.getElementById('update-telephone').value = employee.telephone;
                        document.getElementById('update-nic').value = employee.nic;
                        document.getElementById('update-address').value = employee.address;
                        $('#updateEmployeeModal').modal('show');
                    } else {
                        Swal.fire('Error!', data.error || 'Failed to load employee details.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error!', 'Failed to fetch employee details.', 'error');
                });
        }

        document.getElementById('barcodeInput').addEventListener('change', function() {
            const empId = this.value.trim();
            if (!empId) return;

            // Fetch employee details first
            fetch('../../controllers/employee_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'get',
                        emp_id: empId
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Confirm Employee',
                            html: `Employee ID: ${data.data.emp_id}<br>Name: ${data.data.name}`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'OK',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Record attendance
                                fetch('../../controllers/attendance_controller.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            action: 'record',
                                            emp_id: empId
                                        }),
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: data.message,
                                                showConfirmButton: false,
                                                timer: 1500
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: data.message,
                                            });
                                        }
                                        this.value = ''; // Clear input
                                        this.focus(); // Refocus for next scan
                                    })
                                    .catch(error => {
                                        Swal.fire('Error', 'An error occurred.', 'error');
                                        this.value = '';
                                        this.focus();
                                    });
                            } else {
                                this.value = ''; // Clear input
                                this.focus(); // Refocus for next scan
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Employee not found',
                        });
                        this.value = ''; // Clear input
                        this.focus(); // Refocus for next scan
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'An error occurred.', 'error');
                    this.value = '';
                    this.focus();
                });
        });

        document.addEventListener("keydown", function(event) {
            if (event.code === "Home") {
                window.location.href = "../dashboard/index.php";
            }
        });
    </script>
</body>

</html>