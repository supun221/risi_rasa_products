<!DOCTYPE html>
<html>

<head>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .employee-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .employee-details h2 {
            grid-column: span 2;
            color: rgb(245, 245, 245);
            margin-bottom: 1rem;
            background: hsl(237, 78.90%, 73.90%);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .employee-details .input-group {
            margin-bottom: 0;
        }


        .salary-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 1001;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 6px;
        }

        .section h2 {
            color: rgb(245, 245, 245);
            margin-bottom: 1rem;
            background: hsl(237, 78.90%, 73.90%);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;

            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .input-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }

        .input-group input:disabled {
            background: #e9ecef;
            cursor: not-allowed;
        }

        .total-field {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px dashed #ced4da;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: rgb(0, 62, 128);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1.5rem;
        }

        .submit-btn:hover {
            background: rgb(1, 31, 63);
        }

        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
            }
        }

        .barcode-scanner {
            margin: 20px 0;
            text-align: center;
        }

        #barcodeInputsal {
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 2px solid #007bff;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="modal-overlay">
        <div class="salary-modal">
            <div class="modal-header">
                <h1>Salary Calculator</h1>
                <button class="close-btn">&times;</button>
            </div>

            <form id="salaryForm">
                <div class="barcode-scanner">
                    <input type="text" id="barcodeInputsal" placeholder="Scan Employee Barcode" autofocus>
                </div>

                <!-- Employee Details Section -->
                <div class="employee-details">
                    <h2>Employee Details</h2>
                    <div class="input-group">
                        <label>Employee ID</label>
                        <input type="text" id="employeeId" name="employeeId" required readonly>
                    </div>
                    <div class="input-group">
                        <label>Employee Name</label>
                        <input type="text" id="employeeName" name="employeeName" required readonly>
                    </div>
                </div>

                <div class="form-container">
                    <!-- Earnings Section -->
                    <div class="section">
                        <h2>Earnings</h2>
                        <div class="input-group">
                            <label>Basic Salary</label>
                            <input type="number" name="basicSalary" required>
                        </div>
                        <div class="input-group">
                            <label>Budgetary Relief Allowance</label>
                            <input type="number" name="reliefAllowance"value=3500 readonly>
                        </div>
                        <div class="input-group">
                            <label>Performance Incentive</label>
                            <input type="number" name="performanceIncentive">
                        </div>
                        <div class="input-group">
                            <label>Over Time</label>
                            <input type="number" name="overTime" readonly>
                        </div>
                        <div class="input-group">
                            <label>Over Time (Double Pay)</label>
                            <input type="number" name="overTimeDouble">
                        </div>
                        <div class="input-group">
                            <label>Night Allowance</label>
                            <input type="number" name="nightAllowance">
                        </div>
                        <div class="input-group">
                            <label>Arrears</label>
                            <input type="number" name="arrears">
                        </div>
                        <div class="input-group">
                            <label>Target Achieve Incentive</label>
                            <input type="number" name="targetIncentive">
                        </div>
                        <div class="input-group">
                            <label>Attendance Incentive For New Year</label>
                            <input type="number" name="attendanceIncentive">
                        </div>
                        <div class="input-group">
                            <label>Bonus</label>
                            <input type="number" name="bonus">
                        </div>
                        <div class="input-group total-field">
                            <label>Total Addition</label>
                            <input type="number" name="totalAddition" disabled>
                        </div>
                    </div>

                    <!-- Deductions Section -->
                    <div class="section">
                        <h2>Deductions</h2>
                        <div class="input-group">
                            <label>Provident Fund (Employee 8%)</label>
                            <input type="number" name="providentFund">
                        </div>
                        <div class="input-group">
                            <label>No Pay Days Deductions</label>
                            <input type="number" name="noPayDeductions">
                        </div>
                        <div class="input-group">
                            <label>Welfare Society Fee</label>
                            <input type="number" name="welfareFee">
                        </div>
                        <div class="input-group total-field">
                            <label>Total Deductions</label>
                            <input type="number" name="totalDeductions" disabled>
                        </div>

                        <h2>Employer Contributions</h2>
                        <div class="input-group">
                            <label>Provident Fund (Employer 12%)</label>
                            <input type="number" name="employerPF" disabled>
                        </div>
                        <div class="input-group">
                            <label>Trust Fund (Employer 3%)</label>
                            <input type="number" name="trustFund" disabled>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Calculate Salary</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Barcode input handling
            const barcodeInput = document.getElementById('barcodeInputsal');
            const employeeIdInput = document.querySelector('input[name="employeeId"]');
            const employeeNameInput = document.querySelector('input[name="employeeName"]');

            barcodeInput.addEventListener('keypress', async function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const empId = this.value.trim();

                    if (empId.length === 0) {
                        Swal.fire({
                            title: 'warning!',
                            text: 'Please Enter Vaild Barcode.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });

                        return;
                    }

                    try {
                        const response = await fetch(`get_employees.php?emp_id=${encodeURIComponent(empId)}`);
                        const data = await response.json();

                        if (data.error) {
                            Swal.fire({
                                title: 'Alert!',
                                text: data.error,
                                icon: 'info',
                                confirmButtonText: 'OK'
                            });

                            this.value = '';
                            employeeIdInput.value = '';
                            employeeNameInput.value = '';
                            this.focus();
                        } else {
                            employeeIdInput.value = data.emp_id;
                            employeeNameInput.value = data.name;
                            // Store overtime hours and set up calculation
                            const overtimeHours = parseFloat(data.overtime_hours) || 0;
                            const basicSalaryInput = document.querySelector('input[name="basicSalary"]');
                            const overTimeInput = document.querySelector('input[name="overTime"]');

                            // Function to update Over Time
                            const updateOvertime = () => {
                                const basicSalary = parseFloat(basicSalaryInput.value) || 0;
                                overTimeInput.value = (basicSalary / 240 * overtimeHours).toFixed(2);
                                calculateTotals(); // Recalculate totals
                            };

                            // Update when basic salary changes
                            basicSalaryInput.addEventListener('input', updateOvertime);


                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Failed to fetch employee data');
                    }
                }
            });

            // Auto-calculate totals
            const form = document.getElementById('salaryForm');

            const calculateTotals = () => {
                const basicSalary = parseFloat(form.elements.basicSalary.value) || 0;
                const reliefAllowance = parseFloat(form.elements.reliefAllowance.value) || 0;
                const calculationBase = basicSalary + reliefAllowance;

                // Calculate total additions
                const earnings = Array.from(form.querySelectorAll('.section:first-child input[type="number"]:not([disabled])'));
                const totalAddition = earnings.reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
                form.elements.totalAddition.value = totalAddition.toFixed(2);

                // Calculate total deductions
                const deductions = Array.from(form.querySelectorAll('.section:last-child input[type="number"]:not([disabled])'));
                const totalDeductions = deductions.reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
                form.elements.totalDeductions.value = totalDeductions.toFixed(2);

                // Calculate employer contributions
                form.elements.employerPF.value = (calculationBase * 0.12).toFixed(2);
                form.elements.trustFund.value = (calculationBase * 0.03).toFixed(2);
                form.elements.providentFund.value = (calculationBase * 0.08).toFixed(2);
            };

            // Add event listeners for live updates
            form.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', calculateTotals);
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault(); // Prevent default form submission
                calculateTotals(); // Ensure totals are calculated

                // Prepare data for submission
                const formData = new FormData(form);

                // Submit the form data using Fetch API
                try {
                    const response = await fetch('calculate_salary.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Salary Added successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });;
                        // Additional success handling
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'error!',
                        text: 'Salary added Fail.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to show modal
            window.showSalaryModal = function() {
                document.querySelector('.modal-overlay').style.display = 'block';
            };

            // Close modal
            document.querySelector('.close-btn').addEventListener('click', function() {
                document.querySelector('.modal-overlay').style.display = 'none';
            });

            // Close modal when clicking outside
            document.querySelector('.modal-overlay').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });

            // Form calculations
            const form = document.getElementById('salaryForm');

            function calculateTotals() {
                const basicSalary = parseFloat(form['basicSalary'].value) || 0;
                const reliefAllowance = parseFloat(form['reliefAllowance'].value) || 0;
                const calculationBase = basicSalary + reliefAllowance;

                // Calculate total additions
                const additionInputs = [
                    'basicSalary', 'reliefAllowance', 'performanceIncentive',
                    'overTime', 'overTimeDouble', 'nightAllowance', 'arrears',
                    'targetIncentive', 'attendanceIncentive', 'bonus'
                ];

                const totalAddition = additionInputs.reduce((sum, inputName) => {
                    const value = parseFloat(form[inputName].value) || 0;
                    return sum + value;
                }, 0);

                form['totalAddition'].value = totalAddition;

                // Calculate total deductions
                const deductionInputs = ['providentFund', 'noPayDeductions', 'welfareFee'];

                const totalDeductions = deductionInputs.reduce((sum, inputName) => {
                    const value = parseFloat(form[inputName].value) || 0;
                    return sum + value;
                }, 0);

                form['totalDeductions'].value = totalDeductions;

                // Calculate employer contributions

                // Calculate contributions (modified section)
                form['employerPF'].value = (calculationBase * 0.12).toFixed(2);
                form['trustFund'].value = (calculationBase * 0.03).toFixed(2);
                form['providentFund'].value = (calculationBase * 0.08).toFixed(2);;
            }

            // Add input event listeners to all number inputs
            form.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', calculateTotals);
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                calculateTotals();
                // Add your form submission logic here
            });
        });
    </script>

</body>

</html>