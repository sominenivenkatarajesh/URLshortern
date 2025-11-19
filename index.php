<?php
$conn = new mysqli("localhost", "root", "", "urlshortener");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$resultMessage = "";
$recent = [];

function is_real_url($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    if (!str_contains($url, ".")) return false;
    return true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["name"] ?? "");
    $longUrl = trim($_POST["longUrl"] ?? "");

    if ($title === "") {
        $resultMessage = "<span style='color:#ff6b6b;'>❌ Name is required.</span>";
    } elseif (!is_real_url($longUrl)) {
        $resultMessage = "<span style='color:#ff6b6b;'>❌ Enter a valid real URL.</span>";
    } else {
        $chk = $conn->prepare("SELECT id FROM urls WHERE title = ?");
        $chk->bind_param("s", $title);
        $chk->execute();
        $dup = $chk->get_result();

        if ($dup->num_rows > 0) {
            $resultMessage = "<span style='color:#ff6b6b;'>❌ This name already exists.</span>";
        } else {
            $alias = substr(md5(uniqid(mt_rand(), true)), 0, 6);

            do {
                $check = $conn->prepare("SELECT id FROM urls WHERE short_code = ?");
                $check->bind_param("s", $alias);
                $check->execute();
                $res = $check->get_result();
                if ($res->num_rows > 0) {
                    $alias = substr(md5(uniqid(mt_rand(), true)), 0, 6);
                }
            } while ($res->num_rows > 0);

            $stmt = $conn->prepare("INSERT INTO urls (short_code, title, long_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $alias, $title, $longUrl);

            if ($stmt->execute()) {
                $resultMessage = "<span style='color:#6ee7b7;'>✅ Saved: $title</span>";
            } else {
                $resultMessage = "<span style='color:#ff6b6b;'>❌ Database error.</span>";
            }
        }
        $chk->close();
    }
}

$historyQ = $conn->query("SELECT id, short_code, title, long_url FROM urls ORDER BY id DESC LIMIT 4");
if ($historyQ) {
    while ($r = $historyQ->fetch_assoc()) {
        $recent[] = $r;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Smart URL Shortener</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    font-family: Arial;
    background: linear-gradient(120deg,#3a0ca3,#b5179e,#ff6a88,#ff9e00);
    background-size: 300% 300%;
    animation: gradientMove 8s ease infinite;
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    color:#fff;
}
@keyframes gradientMove {
    0% {background-position:0% 50%;}
    50% {background-position:100% 50%;}
    100% {background-position:0% 50%;}
}

.container {
    background:#2a1a3aee;
    width:480px;
    padding:35px;
    border-radius:22px;
    box-shadow:0 10px 40px rgba(0,0,0,.5);
}
input {
    width:100%;
    padding:15px;
    border-radius:10px;
    border:none;
    font-size:16px;
    margin:12px 0;
    box-sizing:border-box;
}

button {
    width:100%;
    padding:16px;
    border-radius:10px;
    border:none;
    background:#ffd84d;
    font-weight:700;
    font-size:18px;
    cursor:pointer;
    margin-top:5px;
}

.history {
    background:#1d1329;
    margin-top:25px;
    padding:18px;
    border-radius:14px;
}

.history li {
    list-style:none;
    padding:12px;
    background:#2a1d3a;
    margin-bottom:12px;
    border-radius:10px;
}

.link-title {
    color:#ffdd57;
    font-size:17px;
    font-weight:bold;
}
.link-url {
    color:#9bc3ff;
    font-size:13px;
    word-break:break-all;
}
</style>
</head>

<body>
<div class="container">

    <h1 style="text-align:center;color:#ffe066;margin-bottom:20px;">Smart URL Shortener</h1>

    <form method="POST">
        <input type="text" name="name" placeholder="Name for this link" required>
        <input type="text" name="longUrl" placeholder="Enter long URL" required>
        <button type="submit">Shorten URL</button>
    </form>

    <div style="margin-top:12px;"><?php echo $resultMessage; ?></div>

    <div class="history">
        <h3 style="color:#ffe066;">Recent Links</h3>
        <ul>
        <?php if (count($recent) === 0): ?>
            <li>No links yet.</li>
        <?php else: foreach ($recent as $row): ?>
            <li>
                <div class="link-title"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="link-url"><?php echo htmlspecialchars($row['long_url']); ?></div>
            </li>
        <?php endforeach; endif; ?>
        </ul>

        <div style="text-align:center;margin-top:10px;">
            <a href="history.php" style="color:#ffd43b;font-weight:bold;">📜 Full History</a>
        </div>
    </div>
</div>
</body>
</html>
