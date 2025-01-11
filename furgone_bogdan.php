<?php
// Configurazione connessione al database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "riparazioni multimarche";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Creazione tabella furgone_bogdan
$conn->query("CREATE TABLE IF NOT EXISTS furgone_bogdan (
    ID VARCHAR(50) PRIMARY KEY,
    Nome VARCHAR(255) NOT NULL,
    CategoriaProdotto VARCHAR(255) NOT NULL,
    Marchio VARCHAR(255) NOT NULL,
    StockCorrente INT NOT NULL,
    FOREIGN KEY (ID) REFERENCES MagazzinoPrincipale(ID) ON DELETE CASCADE
)");

function redirectWithMessage($message) {
    echo "<script>alert('$message'); window.location.href = window.location.href;</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodotto_id = $conn->real_escape_string($_POST['prodotto_id'] ?? '');
    $quantita = (int)($_POST['quantita'] ?? 0);

    if (isset($_POST['add_to_furgone'])) {
        $result = $conn->query("SELECT Nome, CategoriaProdotto, Marchio, StockCorrente FROM MagazzinoPrincipale WHERE ID = '$prodotto_id'");

        if ($result && $result->num_rows > 0) {
            $prodotto = $result->fetch_assoc();
            if ((int)$prodotto['StockCorrente'] >= $quantita) {
                $new_stock = $prodotto['StockCorrente'] - $quantita;
                $conn->query("UPDATE MagazzinoPrincipale SET StockCorrente = $new_stock WHERE ID = '$prodotto_id'");

                $check_result = $conn->query("SELECT StockCorrente FROM furgone_bogdan WHERE ID = '$prodotto_id'");

                if ($check_result && $check_result->num_rows > 0) {
                    $existing = $check_result->fetch_assoc();
                    $new_quantity = $existing['StockCorrente'] + $quantita;
                    $conn->query("UPDATE furgone_bogdan SET StockCorrente = $new_quantity WHERE ID = '$prodotto_id'");
                } else {
                    $conn->query("INSERT INTO furgone_bogdan (ID, Nome, CategoriaProdotto, Marchio, StockCorrente) VALUES ('$prodotto_id', '{$prodotto['Nome']}', '{$prodotto['CategoriaProdotto']}', '{$prodotto['Marchio']}', $quantita)");
                }

                redirectWithMessage('Prodotto aggiunto o aggiornato nel furgone con successo!');
            } else {
                echo "<p>Stock insufficiente nel magazzino.</p>";
            }
        } else {
            echo "<p>Prodotto non trovato.</p>";
        }
    } elseif (isset($_POST['delete_product'])) {
        $delete_id = $conn->real_escape_string($_POST['delete_id']);
        if ($conn->query("DELETE FROM furgone_bogdan WHERE ID = '$delete_id'")) {
            redirectWithMessage('Prodotto eliminato dal furgone con successo!');
        } else {
            echo "<p>Errore durante l'eliminazione del prodotto: " . $conn->error . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furgone Bogdan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input[type="text"], input[type="number"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px;
            font-size: 16px;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Furgone Bogdan</h1>

        <h2>Aggiungi Prodotto</h2>
        <form method="POST">
            <input type="text" name="prodotto_id" placeholder="ID Prodotto" required>
            <input type="number" name="quantita" placeholder="Quantità" required>
            <input type="submit" name="add_to_furgone" value="Aggiungi al Furgone">
        </form>

        <?php
        $result = $conn->query("SELECT * FROM furgone_bogdan");
        if ($result && $result->num_rows > 0) {
            echo "<h2>Prodotti nel Furgone</h2><table><tr><th>ID</th><th>Nome</th><th>Categoria</th><th>Marchio</th><th>Stock</th><th>Azione</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['ID']}</td>
                    <td>{$row['Nome']}</td>
                    <td>{$row['CategoriaProdotto']}</td>
                    <td>{$row['Marchio']}</td>
                    <td>{$row['StockCorrente']}</td>
                    <td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='delete_id' value='{$row['ID']}'>
                            <input type='submit' name='delete_product' value='Elimina' onclick=\"return confirm('Eliminare questo prodotto?');\">
                        </form>
                    </td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nessun prodotto nel furgone.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
