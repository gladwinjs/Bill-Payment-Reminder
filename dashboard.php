<?php
include('db.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the user's name for greeting
$user_name_query = "SELECT name FROM users WHERE id = $user_id";
$user_name_result = $conn->query($user_name_query);
$user_name = $user_name_result->fetch_assoc()['name'];



// Query to fetch bills and perform calculations
$bills_sql = "SELECT * FROM bills WHERE user_id = $user_id";
$bills_result = $conn->query($bills_sql);

// Initialize counters and totals
$total_bills = 0;
$unpaid_bills =0;
$total_amount_due = 0;
$upcoming_bills = 0;

while ($bill = $bills_result->fetch_assoc()) {
    $unpaid_bills++;
    if ($bill['status'] == 'Pending') {
        $total_bills++;
        $total_amount_due += $bill['amount'];
    }
    if ($bill['due_date'] >= date('Y-m-d') && $bill['due_date'] <= date('Y-m-d', strtotime('+3 days'))) {
        $upcoming_bills++;
    }
}

// Fetch the list of all bills for the table
$result = $conn->query($bills_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Your Bills</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Global Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header Section */
        .header {
            background-color: #007bff;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 24px;
        }

        .header .greeting {
            font-size: 20px;
            margin-top: 10px;
            color: #f7f7f7;
        }

        /* Dashboard Container */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .summary-card {
            flex: 1;
            margin: 10px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .summary-card h3 {
            font-size: 18px;
            color: #555;
        }

        .summary-card p {
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
        }

        tr:hover {
            background-color: #e6f7ff;
            cursor: pointer;
        }

        /* Form Styles */
        form {
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        form input, form button {
            margin: 10px 0;
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        form input:focus {
            border-color: #007BFF;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        form button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        form button:hover {
            background-color: #218838;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .summary-section {
                flex-direction: column;
            }

            th, td {
                padding: 10px;
            }

            form {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                padding: 8px;
            }

            form input, form button {
                padding: 10px;
            }
        }

    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <h1>Bill Payment Dashboard</h1>
        <div class="greeting">
            <?php echo "Hey! " . htmlspecialchars($user_name); ?>
        </div>
    </div>

    <div class="dashboard-container">

        <!-- Bill Summary Section -->
        <div class="summary-section">
            <div class="summary-card">
                <h3>Total Bills</h3>
                <p><?php echo $total_bills; ?></p>
            </div>
            <div class="summary-card">
                <h3>Total Amount Due</h3>
                <p>$<?php echo number_format($total_amount_due, 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Upcoming Bills</h3>
                <p><?php echo $upcoming_bills; ?></p>
            </div>
        </div>

        <!-- Table of Bills -->
        <h2>Your Bills</h2>
        <table>
            <thead>
                <tr>
                    <th>Bill Name</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['bill_name']) . "</td>
                                <td>$" . number_format($row['amount'], 2) . "</td>
                                <td>" . htmlspecialchars($row['due_date']) . "</td>
                                <td>" . htmlspecialchars($row['status']) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No bills found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Add New Bill Form -->
        <h2>Add a New Bill</h2>
        <form action="add_bill.php" method="POST">
            <label for="bill_name">Bill Name:</label>
            <input type="text" name="bill_name" id="bill_name" required>

            <label for="amount">Amount:</label>
            <input type="number" step="0.01" name="amount" id="amount" required>

            <label for="due_date">Due Date:</label>
            <input type="date" name="due_date" id="due_date" required>

            <button type="submit">Add Bill</button>
        </form>

        <!-- Send Reminder Email -->
        <form action="send_reminder.php" method="POST">
            <button type="submit" class="send-reminder-btn">Send Reminder Email for Upcoming Bills</button>
        </form>
    </div>

</body>
</html>
