<?php
include 'db.php';
 
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
 
if ($booking_id <= 0) {
    die("Invalid booking ID.");
}
 
// Fetch booking and related hotel info
$stmt = $conn->prepare("
    SELECT b.*, h.name AS hotel_name
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
 
if ($result->num_rows == 0) {
    die("Booking not found.");
}
 
$booking = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #004080;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1rem;
            margin: 8px 0;
        }
        strong {
            color: #004080;
        }
        a {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            background: #004080;
            color: #fff;
            padding: 12px 25px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        a:hover {
            background: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Confirmed!</h1>
        <p>Thank you, <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong> for booking with us.</p>
        <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?></p>
        <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['check_in']); ?></p>
        <p><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['check_out']); ?></p>
        <p><strong>Guests:</strong> <?php echo htmlspecialchars($booking['guests']); ?></p>
        <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
 
        <a href="index.php">Back to Home</a>
    </div>
</body>
</html>
 
