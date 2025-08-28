<?php
include 'db.php';
 
// Fetch featured hotels (top 3 by rating)
$featured_sql = "SELECT * FROM hotels ORDER BY rating DESC LIMIT 3";
$featured_result = $conn->query($featured_sql);
 
// Fetch distinct hotel types for filters
$type_sql = "SELECT DISTINCT type FROM hotels";
$type_result = $conn->query($type_sql);
$hotel_types = [];
if ($type_result->num_rows > 0) {
    while ($row = $type_result->fetch_assoc()) {
        $hotel_types[] = $row['type'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Hotel Booking - Home</title>
    <style>
        /* Reset & base */
        body, h1, h2, h3, p, ul, li, input, select, button {
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
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        /* Header */
        header {
            margin-bottom: 30px;
            text-align: center;
        }
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #004080;
        }
        /* Search bar */
        .search-bar {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .search-bar input, .search-bar button {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-bar input[type="text"] {
            flex: 2 1 200px;
        }
        .search-bar input[type="date"] {
            flex: 1 1 140px;
        }
        .search-bar button {
            background: #004080;
            color: white;
            border: none;
            cursor: pointer;
            flex: 0 1 120px;
            transition: background 0.3s;
        }
        .search-bar button:hover {
            background: #003366;
        }
        /* Main content with filters and featured hotels */
        .main-content {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        /* Filters sidebar */
        .filters {
            flex: 1 1 250px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .filters h3 {
            margin-bottom: 15px;
            color: #004080;
        }
        .filters label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            margin-top: 15px;
            color: #222;
        }
        .filters input[type="range"] {
            width: 100%;
        }
        .filters select {
            width: 100%;
            padding: 8px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        /* Featured hotels */
        .featured-hotels {
            flex: 3 1 700px;
        }
        .featured-hotels h2 {
            margin-bottom: 20px;
            color: #004080;
        }
        .hotel-card {
            background: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        .hotel-card img {
            width: 200px;
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
            margin-bottom: 8px;
        }
        .hotel-info p {
            flex-grow: 1;
            font-size: 0.9rem;
            color: #555;
        }
        .hotel-info .price-rating {
            margin-top: 10px;
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
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        /* Responsive */
        @media (max-width: 900px) {
            .main-content {
                flex-direction: column;
            }
            .filters, .featured-hotels {
                flex: 1 1 100%;
            }
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
        <h1>Hotel Booking Platform</h1>
        <p>Find the best hotels for your stay</p>
    </header>
 
    <!-- Search Bar -->
    <form action="listing.php" method="GET" class="search-bar" id="searchForm">
        <input type="text" name="destination" placeholder="Enter destination (e.g. New York)" required />
        <input type="date" name="checkin" required min="<?php echo date('Y-m-d'); ?>" />
        <input type="date" name="checkout" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" />
        <button type="submit">Search</button>
    </form>
 
    <div class="main-content">
        <!-- Filters -->
        <aside class="filters">
            <h3>Filters</h3>
            <form action="listing.php" method="GET" id="filterForm">
                <label for="priceRange">Max Price per Night ($):</label>
                <input type="range" id="priceRange" name="max_price" min="10" max="1000" value="1000" oninput="priceValue.innerText = this.value" />
                <span id="priceValue">1000</span>
 
                <label for="minRating">Minimum Rating:</label>
                <select name="min_rating" id="minRating">
                    <option value="0">Any</option>
                    <option value="1">1+</option>
                    <option value="2">2+</option>
                    <option value="3">3+</option>
                    <option value="4">4+</option>
                    <option value="5">5</option>
                </select>
 
                <label for="hotelType">Hotel Type:</label>
                <select name="hotel_type" id="hotelType">
                    <option value="">Any</option>
                    <?php foreach($hotel_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
 
                <button type="submit" style="margin-top:15px; background:#004080; color:#fff; padding:10px; border:none; border-radius:5px; cursor:pointer;">Apply Filters</button>
            </form>
        </aside>
 
        <!-- Featured Hotels -->
        <section class="featured-hotels">
            <h2>Featured Hotels</h2>
            <?php if ($featured_result->num_rows > 0): ?>
                <?php while ($hotel = $featured_result->fetch_assoc()): ?>
                <div class="hotel-card">
                    <img src="images/<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" />
                    <div class="hotel-info">
                        <h3><a href="hotel.php?id=<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name']); ?></a></h3>
                        <p><?php echo htmlspecialchars(substr($hotel['description'],0,120)) . '...'; ?></p>
                        <div class="price-rating">
                            <span>$<?php echo number_format($hotel['price_per_night'],2); ?> / night</span>
                            <span class="rating"><?php echo number_format($hotel['rating'],1); ?> ★</span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No featured hotels available.</p>
            <?php endif; ?>
        </section>
    </div>
</div>
 
<script>
    // Ensure checkout date is always after checkin date
    const checkin = document.querySelector('input[name="checkin"]');
    const checkout = document.querySelector('input[name="checkout"]');
 
    checkin.addEventListener('change', () => {
        const minCheckout = new Date(checkin.value);
        minCheckout.setDate(minCheckout.getDate() + 1);
        checkout.min = minCheckout.toISOString().split('T')[0];
        if (checkout.value <= checkin.value) {
            checkout.value = checkout.min;
        }
    });
</script>
</body>
</html>
 
