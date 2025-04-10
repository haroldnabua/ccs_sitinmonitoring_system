<?php
session_start();
include("connection.php");

if (!isset($_SESSION['idno'])) {
    echo "ERROR";
    exit;
}

$idno = $_SESSION['idno'];
$role = $_SESSION['role'];

$query = "SELECT *, CONCAT(lastName, ' ', midName, ' ', firstName) AS fullname, CONCAT(firstName, ' ', lastName) AS shortname FROM accounts WHERE idno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>alert('No Users Found.')</script>";
    exit();
}

$sitinquery = "SELECT * FROM sit_in WHERE time_out IS NOT NULL ORDER BY time_out DESC";
$resultSitin = mysqli_query($conn, $sitinquery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring System - Sit-in History</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        :root {
            --primary-color: #2a2aad;
            --primary-dark: #1e1e7a;
            --white: #ffffff;
            --light-gray: #f5f5f7;
            --border-color: #e0e0e0;
            --text-primary: #333333;
            --text-secondary: #666666;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f7;
            color: var(--text-primary);
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
        }

        .action-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .action-button:hover {
            background-color: var(--primary-dark);
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 15px;
            background-color: var(--light-gray);
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            background-color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .pagination button.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-completed {
            background-color: #e6f7e6;
            color: var(--success);
        }

        .status-ongoing {
            background-color: #fff3e0;
            color: var(--warning);
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>CCS Sit-in Monitoring System</h2>
        </div>
        <div class="user-profile">
            <div class="profile-img">
                <img src="lofi.jpg" alt="Avatar">
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user['shortname']) ?></div>
            <div class="user-role">Admin</div>
        </div>

        <div class="sidebar-menu">
            <div class="menu-item" onclick="window.location.href='admindashboard.php'">Dashboard</div>
            <div class="menu-item" onclick="window.location.href='adminannouncements.php'">Announcements</div>
            <div class="menu-item">View Feedback</div>
            <div class="menu-item active" onclick="window.location.href='sitinhistoryadmin.php'">Sit-in History</div>
            <div class="menu-item" onclick="window.location.href='studentlist.php'">Students List</div>
            <div class="menu-item">Settings</div>
            <div class="menu-item" onclick="window.location.href='logout.php'">Logout</div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Sit-in History</h1>
            <select name="format" id="formatType">
                <option value="PDF">PDF</option>
                <option value="CSV">CSV</option>
                <option value="Excel">Excel</option>
                <option value="print">Print</option>
            </select>
            <button class="action-button">Export Data</button>
        </div>
        <script>
            document.querySelector('.action-button').addEventListener('click', () => {
                const format = document.getElementById('formatType').value;
                const table = document.querySelector('table');
                console.log(table);

                // Convert table data into an array or string (depending on format)
                let exportData = '';
                if (format === 'CSV' || format === 'Excel') {
                    exportData = Array.from(table.rows).map(row =>
                        Array.from(row.cells).map(cell => cell.textContent).join(',')
                    ).join('\n');
                } else if (format === 'PDF') {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const pdf = new jsPDF();
                    // Add Header
                    pdf.setFontSize(14);
                    pdf.text("University Of Cebu Main", 20, 10);
                    pdf.text("College of Computer Studies", 20, 20);
                    pdf.text("Computer Laboratory Sitin Monitoring System Report", 20, 30);
                    pdf.autoTable({
                        html: table,
                        startY: 40
                    });
                    pdf.save('sit-in-records.pdf');
                    return;
                } else if (format === 'print') {
                    const win = window.open();
                    win.document.write(`
                        <div>
                            <h3>University Of Cebu Main</h3>
                            <h4>College of Computer Studies</h4>
                            <p>Computer Laboratory Sitin Monitoring System Report</p>
                        </div>
                    `);
                    win.document.write(table.outerHTML);
                    win.print();
                    win.close();
                    return;
                }

                if (format === 'CSV') {
                    const header = "University Of Cebu Main\nCollege of Computer Studies\nComputer Laboratory Sitin Monitoring System Report\n\n";
                    const tableData = Array.from(table.rows).map(row =>
                        Array.from(row.cells).map(cell => cell.textContent).join(',')
                    ).join('\n');
                    const exportData = header + tableData;
                    const blob = new Blob([exportData], {
                        type: 'text/csv'
                    });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'sit-in-records.csv';
                    link.click();
                } else if (format === 'Excel') {
                    const header = "University Of Cebu Main\nCollege of Computer Studies\nComputer Laboratory Sitin Monitoring System Report\n\n";
                    const tableData = Array.from(table.rows).map(row =>
                        Array.from(row.cells).map(cell => cell.textContent).join('\t')
                    ).join('\n');
                    const exportData = header + tableData;

                    const link = document.createElement('a');
                    link.href = `data:application/vnd.ms-excel,${encodeURIComponent(exportData)}`;
                    link.download = 'sit-in-records.xls';
                    link.click();
                }
            });
        </script>
        <div class="card">
        </div>

        <div class="card">
            <div class="card-header">
                Sit-in Records
                <span>April 9, 2025</span>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Purpose</th>
                            <th>Laboratory Room</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultSitin) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($resultSitin)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['idno']) ?></td>
                                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lab']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date']) ?></td>
                                    <td><?php echo htmlspecialchars($row['time_in']) ?></td>
                                    <td><?php echo htmlspecialchars($row['time_out']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No sit-in history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <button>Previous</button>
                    <button class="active">1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>Next</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>