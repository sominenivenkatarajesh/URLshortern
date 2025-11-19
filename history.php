<?php
$conn = new mysqli("localhost", "root", "", "urlshortener");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;
$stmt = $conn->prepare("
    SELECT SQL_CALC_FOUND_ROWS id, short_code, title 
    FROM urls 
    ORDER BY id DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();

$totalRes = $conn->query("SELECT FOUND_ROWS() AS total");
$total = ($totalRes) ? (int)$totalRes->fetch_assoc()['total'] : 0;
$totalPages = (int)ceil($total / $perPage);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Full History</title>

<style>
body {
    font-family: "Poppins", Arial;
    background: linear-gradient(135deg,#3a1c71,#d76d77,#ffaf7b);
    color:#fff;
    padding:30px;
}

.card {
    max-width: 900px;
    margin: auto;
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
}

.card h2 {
    color: #ffe066;
    font-size: 28px;
    margin-bottom: 20px;
    text-align: center;
}

/* List items */
ul { padding: 0; }
li {
    background: rgba(0,0,0,0.35);
    padding: 14px;
    margin-bottom: 10px;
    border-radius: 12px;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: .2s;
}
li:hover {
    background: rgba(0,0,0,0.55);
}

a.title {
    color: #ffe066;
    font-size: 17px;
    font-weight: bold;
    text-decoration: none;
}

.delete-btn {
    color: #ff6b6b;
    font-size: 15px;
    font-weight: bold;
    text-decoration: none;
}
.delete-btn:hover {
    color: #ff4747;
}

.pagination {
    text-align: center;
    margin-top: 18px;
    font-size: 16px;
}
.pagination a {
    margin: 0 10px;
    color: #ffe066;
    text-decoration: none;
    font-weight: bold;
}
.pagination a:hover {
    text-decoration: underline;
}

.back-btn {
    display: block;
    margin-top: 20px;
    text-align: center;
    color: #ffe066;
    font-size: 17px;
    text-decoration: none;
}
</style>

</head>
<body>

<div class="card">
    <h2>📜 Full History</h2>

    <ul>
        <?php if ($res && $res->num_rows > 0): ?>
            <?php while ($r = $res->fetch_assoc()): ?>
                <li>
                   
                    <a class="title"
                        href="redirect.php?c=<?php echo urlencode($r['short_code']); ?>"
                        target="_blank">
                        <?php echo htmlspecialchars($r['title']); ?>
                    </a>

                    <a class="delete-btn"
                       href="delete.php?id=<?php echo $r['id']; ?>"
                       onclick="return confirm('Delete this link?');">
                       ✖ Delete
                    </a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>No history.</li>
        <?php endif; ?>
    </ul>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>">&laquo; Prev</a>
        <?php endif; ?>

        Page <?php echo $page; ?> of <?php echo $totalPages ?: 1; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page+1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

    <a class="back-btn" href="index.php">⬅ Back</a>
</div>

</body>
</html>
