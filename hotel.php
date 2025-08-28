<?php
include 'db.php';
 
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$errors = [];
$success = false;
$booking_info = [];
 
if ($hotel_id <= 0) {
    die("Invalid hotel selected.");
}
 
// Fetch hotel details
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel_result = $stmt->get_result();
if ($hotel_result->num_rows == 0) {
    die("Hotel not found.");
}
$hotel = $hotel_result->fetch_assoc();
 
// Booking form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $guests = intval($_POST['guests'] ?? 1);
    $checkin_post = $_POST['checkin'] ?? '';
    $checkout_post = $_POST['checkout'] ?? '';
 
    // Validate
    if (!$user_name) {
        $errors[] = "Name is required.";
    }
    if (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (!$checkin_post || !$checkout_post || $checkin_post >= $checkout_post) {
        $errors[] = "Valid check-in and check-out dates are required.";
    }
    if ($guests < 1) {
        $errors[] = "Guests must be at least 1.";
    }
 
    // If no errors, save booking
    if (empty($errors)) {
        $date1 = new DateTime($checkin_post);
        $date2 = new DateTime($checkout_post);
        $diff = $date2->diff($date1)->days;
        if ($diff <= 0) {
            $errors[] = "Check-out must be after check-in.";
        } else {
            $total_price = $diff * $hotel['price_per_night'];
 
            $insert_stmt = $conn->prepare("INSERT INTO bookings (hotel_id, user_name, user_email, check_in, check_out, guests, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("issssid", $hotel_id, $user_name, $user_email, $checkin_post, $checkout_post, $guests, $total_price);
            if ($insert_stmt->execute()) {
                $success = true;
                $booking_info = [
                    'user_name' => htmlspecialchars($user_name),
                    'hotel_name' => htmlspecialchars($hotel['name']),
                    'checkin' => $checkin_post,
                    'checkout' => $checkout_post,
                    'guests' => $guests,
                    'total_price' => number_format($total_price, 2),
                    'booking_id' => $insert_stmt->insert_id
                ];
            } else {
                $errors[] = "Booking failed. Please try again.";
            }
        }
    }
}
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Book <?php echo htmlspecialchars($hotel['name']); ?></title>
    <style>
        body, h1, h2, p, label, input, textarea, button {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f7f7f7;
            color: #333;
            padding: 20px;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .hotel-header {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .hotel-image {
            flex: 1 1 300px;
            max-width: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        .hotel-image img {
            width: 100%;
            display: block;
            object-fit: cover;
            height: 220px;
        }
        .hotel-details {
            flex: 2 1 400px;
        }
        .hotel-details h1 {
            margin-bottom: 8px;
            color: #004080;
        }
        .hotel-details .rating {
            background: #004080;
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 12px;
        }
        .hotel-details p.description {
            margin-bottom: 15px;
            font-size: 1rem;
            line-height: 1.4;
        }
        .hotel-details .amenities {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 20px;
        }
        .price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #004080;
            margin-bottom: 25px;
        }
        /* Booking form */
        form {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
        }
        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            margin-top: 15px;
        }
        form input[type="text"],
        form input[type="email"],
        form input[type="number"],
        form input[type="date"] {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form button {
            margin-top: 20px;
            background: #004080;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        form button:hover {
            background: #003366;
        }
        .errors {
            background: #ffdddd;
            border: 1px solid #ff5c5c;
            color: #900;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .success {
            background: #ddffdd;
            border: 1px solid #5cba5c;
            color: #060;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        /* Responsive */
        @media (max-width: 700px) {
            .hotel-header {
                flex-direction: column;
            }
            .hotel-image, .hotel-details {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php">&larr; Back to Home</a>
    <div class="hotel-header" style="margin-top: 15px;">
        <div class="hotel-image">
            <img src="images/<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" />
        </div>
        <div class="hotel-details">
            <h1><?php echo htmlspecialchars($hotel['name']); ?></h1>
            <div class="rating"><?php echo number_format($hotel['rating'], 1); ?> ★</div>
            <p class="description"><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>
            <div class="amenities"><strong>Amenities:</strong> <?php echo htmlspecialchars($hotel['amenities']); ?></div>
            <div class="price">Price: $<?php echo number_format($hotel['price_per_night'], 2); ?> per night</div>
        </div>
    </div>
 
    <?php if ($success): ?>
        <div class="success">
            <h2>Booking Confirmed!</h2>
            <p>Thank you, <?php echo $booking_info['user_name']; ?>. Your booking for <strong><?php echo $booking_info['hotel_name']; ?></strong> is confirmed.</p>
            <p><strong>Booking ID:</strong> <?php echo $booking_info['booking_id']; ?></p>
            <p><strong>Check-in:</strong> <?php echo $booking_info['checkin']; ?></p>
            <p><strong>Check-out:</strong> <?php echo $booking_info['checkout']; ?></p>
            <p><strong>Guests:</strong> <?php echo $booking_info['guests']; ?></p>
            <p><strong>Total Price:</strong> $<?php echo $booking_info['total_price']; ?></p>
            <p>We look forward to your stay!</p>
        </div>
    <?php else: ?>
        <?php if ($errors): ?>
            <div class="errors">
                <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
 
        <form method="POST" action="hotel.php?id=<?php echo $hotel_id; ?>">
            <label for="user_name">Your Name *</label>
            <input type="text" name="user_name" id="user_name" value="<?php echo isset($_POST['user_name']) ? htmlspecialchars($_POST['user_name']) : ''; ?>" required />
 
            <label for="user_email">Your Email *</label>
            <input type="email" name="user_email" id="user_email" value="<?php echo isset($_POST['user_email']) ? htmlspecialchars($_POST['user_email']) : ''; ?>" required />
 
            <label for="guests">Number of Guests *</label>
            <input type="number" name="guests" id="guests" min="1" value="<?php echo isset($_POST['guests']) ? intval($_POST['guests']) : 1; ?>" required />
 
            <label for="checkin">Check-in Date *</label>
            <input type="date" name="checkin" id="checkin" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($checkin); ?>" required />
 
            <label for="checkout">Check-out Date *</label>
            <input type="date" name="checkout" id="checkout" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo htmlspecialchars($checkout); ?>" required />
 
            <button type="submit">Book Now</button>
        </form>
    <?php endif; ?>
</div>
 
<script>
    // Date validation to keep checkout after checkin
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
 
    checkinInput.addEventListener('change', () => {
        const minCheckout = new Date(checkinInput.value);
        minCheckout.setDate(minCheckout.getDate() + 1);
        checkoutInput.min = minCheckout.toISOString().split('T')[0];
        if (checkoutInput.value <= checkinInput.value) {
            checkoutInput.value = checkoutInput.min;
        }
    });
</script>
</body>
</html>
 
