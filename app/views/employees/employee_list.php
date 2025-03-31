<!DOCTYPE html>
<html>
<?php
require_once '../header1.php';
?>

<head>

    <title>Employee List</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
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

        table {
            width: 98%;
            border-collapse: collapse;
            align-items: center;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .action-buttons a {
            margin-right: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .action-buttons a:hover {
            text-decoration: underline;
        }

        .barcode-scanner {
            margin: 20px 0;
            text-align: center;
        }

        #barcodeInput {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 2px solid #007bff;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h1>Employee List</h1>
    <div class="barcode-scanner">
        <input type="text" id="barcodeInput" placeholder="Scan Employee Barcode" autofocus>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <button type="button" class="add-customer" data-toggle="modal" data-target="#addEmployeeModal">Add Employee</button>
        <button type="button" class="add-customer" onclick=showSalaryModal()>Add salary</button>
        <button type="button" class="add-customer" onclick="location.href='attendance_list.php'">Attendance List</button>
        <button type="button" class="add-customer" onclick="location.href='salary_list.php'">Salary List</button>
    </div>

    <?php
    require_once 'add_employee.php';
    require_once 'Salary_form.php';
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
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Telephone</th>
                    <th>NIC</th>
                    <th>Address</th>
                    <th>Barcode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($employees)) {
                    foreach ($employees as $index => $employee) {
                        echo '<tr id="employee-row-' . htmlspecialchars($employee['emp_id']) . '">';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td>' . htmlspecialchars($employee['emp_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($employee['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($employee['telephone']) . '</td>';
                        echo '<td>' . htmlspecialchars($employee['nic']) . '</td>';
                        echo '<td>' . htmlspecialchars($employee['address']) . '</td>';
                        echo '<td><svg class="barcode" data-empid="' . htmlspecialchars($employee['emp_id']) . '"></svg></td>';
                        echo '<td class="action-buttons">';
                        echo '<a href="javascript:void(0);" onclick="loadEmployeeDetails(\'' . htmlspecialchars($employee['emp_id']) . '\')" class="edit">Edit</a>';
                        echo '<button onclick="confirmDelete(\'' . htmlspecialchars($employee['emp_id']) . '\')" class="delete">Delete</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">No employees found.</td></tr>';
                }
                ?>

            </tbody>
        </table>
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




        // Add this script after your existing SweetAlert script
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