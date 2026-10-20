
<?php
require_once 'db.php';

// Get the search term, if one was submitted.
$search = trim($_GET['search'] ?? '');

// Fetch properties from the database.
if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM properties
         WHERE title LIKE ? OR location LIKE ? OR description LIKE ?
         ORDER BY created_at DESC"
    );

    $term = '%' . $search . '%';
    $stmt->bind_param("sss", $term, $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT * FROM properties ORDER BY created_at DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentStay | Find Your Student Home</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #263449;
            background: #f7f9fc;
            line-height: 1.6;
        }

        header {
            background: #ffffff;
            border-bottom: 1px solid #e5eaf2;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            color: #2457b2;
            font-size: 26px;
            font-weight: bold;
            text-decoration: none;
        }

        nav {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        nav a {
            color: #475569;
            text-decoration: none;
            font-weight: 500;
        }

        nav a:hover {
            color: #2457b2;
        }

        .hero {
            text-align: center;
            padding: 85px 20px 75px;
            background: linear-gradient(135deg, #eaf2ff, #f8fbff);
        }

        .hero h1 {
            font-size: clamp(30px, 5vw, 46px);
            line-height: 1.2;
            margin: 0 0 18px;
        }

        .hero p {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            max-width: 650px;
            margin: 0 auto;
            gap: 10px;
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            padding: 15px;
            border: 1px solid #d5dce8;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            background: #2457b2;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #19448f;
        }

        .listings {
            max-width: 1150px;
            margin: 0 auto;
            padding: 55px 25px 75px;
        }

        .section-heading {
            margin-bottom: 28px;
        }

        .section-heading h2 {
            font-size: 30px;
            margin: 0 0 8px;
        }

        .section-heading p {
            color: #64748b;
            margin: 0;
        }

        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .property-card {
            background: white;
            border: 1px solid #e5eaf2;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(30, 50, 80, 0.06);
        }

        .property-image {
            height: 180px;
            background: #e8eef8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            overflow: hidden;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-details {
            padding: 20px;
        }

        .property-details h3 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        .location {
            color: #64748b;
            margin: 0 0 14px;
        }

        .price {
            color: #2457b2;
            font-size: 23px;
            font-weight: bold;
            margin: 12px 0;
        }

        .price small {
            color: #64748b;
            font-size: 13px;
            font-weight: normal;
        }

        .description {
            color: #526176;
            font-size: 14px;
        }

        .empty-state {
            background: white;
            border: 1px solid #e5eaf2;
            padding: 35px;
            border-radius: 12px;
            text-align: center;
        }

        footer {
            text-align: center;
            background: #ffffff;
            border-top: 1px solid #e5eaf2;
            padding: 25px 15px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            header {
                padding: 18px 5%;
            }

            nav {
                gap: 15px;
            }

            .hero {
                padding: 60px 18px;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form button {
                width: 100%;
            }

            .listings {
                padding: 40px 18px 55px;
            }
        }
    </style>
</head>

<body>

<header>
    <a class="logo" href="index.php">StudentStay</a>

    <nav>
        <a href="index.php">Home</a>
        <a href="#properties">Find a Home</a>
        <a href="#about">About</a>
    </nav>
</header>

<main>
    <section class="hero">
        <h1>Find your place to call home.</h1>

        <p>
            Discover student accommodation that fits your life and budget.
        </p>

        <form class="search-form" method="GET" action="index.php">
            <input
                type="text"
                name="search"
                placeholder="Enter a city or university"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            >

            <button type="submit">Search</button>
        </form>
    </section>

    <section class="listings" id="properties">
        <div class="section-heading">
            <h2>Find your next student home</h2>
            <p>Explore accommodation options for your next academic year.</p>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="property-grid">

                <?php while ($property = $result->fetch_assoc()): ?>

                    <article class="property-card">

                        <div class="property-image">
                            <?php
                            $image = trim($property['image'] ?? '');
                            $safeImage = basename($image);
                            $imagePath = __DIR__ . '/images/' . $safeImage;
                            ?>

                            <?php if (
                                $safeImage !== '' &&
                                is_file($imagePath)
                            ): ?>
                                <img
                                    src="<?= 'images/' . rawurlencode($safeImage) ?>"
                                    alt="<?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                            <?php else: ?>
                                <span>Student accommodation</span>
                            <?php endif; ?>
                        </div>

                        <div class="property-details">

                            <h3>
                                <?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <p class="location">
                                &#128205;
                                <?= htmlspecialchars($property['location'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p class="price">
                                &euro;<?= number_format((float)$property['price'], 2) ?>
                                <small>/ month</small>
                            </p>

                            <p class="description">
                                <?= htmlspecialchars($property['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                        </div>
                    </article>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <h3>No properties found</h3>
                <p>
                    <?php if ($search !== ''): ?>
                        Try another city or search term.
                    <?php else: ?>
                        New accommodation listings will appear here soon.
                    <?php endif; ?>
                </p>
            </div>

        <?php endif; ?>
    </section>

    <section class="listings" id="about">
        <div class="section-heading">
            <h2>About StudentStay</h2>
            <p>
                Helping students discover accommodation that suits their needs
                and budget.
            </p>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentStay. Find your place to belong.</p>
</footer>

</body>
</html>
