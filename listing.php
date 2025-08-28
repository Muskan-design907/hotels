<?php
include 'db.php';
 
// Get search/filter parameters safely
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
$min_rating = isset($_GET['min_rating']) ? floatval($_GET['min_rating']) : 0;
$hotel_type = isset($_GET['hotel_type']) ? trim($_GET['hotel_type']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rating_desc';
 
// Validate dates (basic)
if (!$destination || !$checkin || !$checkout) {
    die("Please provide destination, check-in and check-out dates.");
}
if ($checkin >= $checkout) {
    die("Check-out date must be after check-in date.");
}
 
// Build SQL query with filters
$sql = "SELECT * FROM hotels WHERE location LIKE ? AND price_per_night <= ? AND rating >= ? ";
$params = ["%$destination%", $max_price, $min_rating];
$types = "sdd";
 
if ($hotel_type) {
    $sql .= " AND type = ? ";
    $params[] = $hotel_type;
    $types .= "s";
}
 
// Sorting
switch ($sort) {
    case "price_asc":
        $sql .= " ORDER BY price_per_night ASC ";
        break;
    case "price_desc":
        $sql .= " ORDER BY price_per_night DESC ";
        break;
    case "rating_asc":
        $sql .= " ORDER BY rating ASC ";
        break;
    default: // rating_desc
        $sql .= " ORDER BY rating DESC ";
        break;
}
 
// Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
 
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Hotel Listings - Search results</title>
    <style>
        body, h1, h2, p, ul, li, a {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: Arial, sans-serif;
            color: #333;
        }
        body {
            background: #f7f7f7;
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
            max-width: 1100px;
            margin: 0 auto;
        }
        header {
            margin-bottom: 20px;
            text-align: center;
        }
        header h1 {
            color: #004080;
            font-size: 2rem;
            margin-bottom: 8px;
        }
        .filters-sort {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .filters-sort form, .filters-sort select {
            font-size: 1rem;
        }
        .hotel-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .hotel-card {
            background: #fff;
            border-radius: 8px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .hotel-card img {
            width: 220px;
            object-fit: cover;
        }
        .hotel-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .hotel-info h3 {
            margin-bottom: 10px;
        }
        .hotel-info p {
            flex-grow: 1;
            font-size: 0.9rem;
            color: #555;
        }
        .hotel-info .price-rating {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 1.1rem;
            color: #004080;
        }
        .hotel-info .rating {
            background: #004080;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .amenities {
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
        }
        /* Responsive */
        @media (max-width: 900px) {
            .hotel-card {
                flex-direction: column;
            }
            .hotel-card img {
                width: 100%;
                height: 180px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Hotels in "<?php echo htmlspecialchars($destination); ?>"</h1>
        <p>Check-in: <?php echo htmlspecialchars($checkin); ?> | Check-out: <?php echo htmlspecialchars($checkout); ?></p>
    </header>
 
    <div class="filters-sort">
        <form method="GET" action="listing.php" style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">
            <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>" />
            <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>" />
            <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>" />
 
            <label for="sort">Sort by:</label>
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="rating_desc" <?php if ($sort == 'rating_desc') echo 'selected'; ?>>Best Rated</option>
                <option value="price_asc" <?php if ($sort == 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
                <option value="price_desc" <?php if ($sort == 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
                <option value="rating_asc" <?php if ($sort == 'rating_asc') echo 'selected'; ?>>Rating: Low to High</option>
            </select>
        </form>
    </div>
 
    <div class="hotel-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($hotel = $result->fetch_assoc()): ?>
                <div class="hotel-card">
                    <img src="images/<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" />
                    <div class="hotel-info">
                        <h3><a href="hotel.php?id=<?php echo $hotel['id']; ?>&checkin=<?php echo urlencode($checkin); ?>&checkout=<?php echo urlencode($checkout); ?>"><?php echo htmlspecialchars($hotel['name']); ?></a></h3>
                        <p><?php echo htmlspecialchars(substr($hotel['description'],0,140)) . '...'; ?></p>
                        <div class="amenities"><strong>Amenities:</strong> <?php echo htmlspecialchars($hotel['amenities']); ?></div>
                        <div class="price-rating">
                            <span>$<?php echo number_format($hotel['price_per_night'],2); ?> / night</span>
                            <span class="rating"><?php echo number_format($hotel['rating'],1); ?> ★</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hotels found matching your criteria.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
 
