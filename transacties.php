<?php
session_start();
include 'includes/db.php';

// Check if the user is logged in at all
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Get the requested user ID from the URL securely
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// IDOR FIX: If the user is NOT an admin (beheerder), they can ONLY view their own ID
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'beheerder') {
    // Check if your project uses $_SESSION['id'] for the logged-in user's ID
    $current_user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    
    if ($id !== $current_user_id) {
        header("location: index.php");
        exit;
    }
}

// Gebruikersgegevens ophalen (Fetch user details if access is granted)
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Uitgaande transacties ophalen (Fetch outgoing transactions)
$stmt = $pdo->prepare("SELECT * FROM transaction WHERE sender = ?");
$stmt->execute([$id]);
$outgoingTransactions = $stmt->fetchAll();

// Inkomende transacties ophalen (Fetch incoming transactions)
$stmt = $pdo->prepare("SELECT * FROM transaction WHERE receiver = ?");
$stmt->execute([$id]);
$incomingTransactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $user['username'] ?> | Omanido</title>
    <!-- Voeg Tailwind CSS toe via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.15/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include 'includes/header.php'; ?>

<div class="container mx-auto mt-20 p-6 bg-white shadow-md rounded-md">
    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-1">
            <div class="flex justify-center">
                <img src="img/Omanido1.png" alt="Omanido Logo" class="mb-6 w-1/2">
            </div>
            <h2 class="text-lg text-center font-bold mb-6"><?= $user['username'] ?></h2>
            <p class="text-center mb-6">Saldo: €<?= number_format($user['balance'], 2, ',', '.') ?></p>
            <div class="flex justify-center">
                <a href="dashboard.php"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Geld overmaken</a>
            </div>
            <div class="flex justify-center mt-6">
                <a href="logout.php"
                   class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Uitloggen</a>
            </div>
        </div>
        <div class="col-span-1">
            <?php if (!empty($outgoingTransactions)): ?>
            <h2 class="text-lg text-center font-bold mb-6">Uitgaande Transacties</h2>
            <div class="bg-red-100 p-2 rounded">
                <?php foreach ($outgoingTransactions as $transaction): ?>
                    <div class="flex justify-between mb-2">
                       <p><?= htmlspecialchars($transaction['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p>€<?= number_format($transaction['amount'], 2, ',', '.') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p class="text-center text-red-500 font-bold">Er zijn geen uitgaande transacties.</p>
            <?php endif; ?>
        </div>

        <div class="col-span-1">
            <?php if (!empty($incomingTransactions)): ?>
                <h2 class="text-lg text-center font-bold mb-6">Inkomende Transacties</h2>
                <div class="bg-green-100 p-2 rounded">
                    <?php foreach ($incomingTransactions as $transaction): ?>
                        <div class="flex justify-between mb-2">
                            <p><?= htmlspecialchars($transaction['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>€<?= number_format($transaction['amount'], 2, ',', '.') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-red-500 font-bold">Er zijn geen inkomende transacties.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


</body>
</html>
