<?php
// Connessione al database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "riparazioni multimarche";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Variabili per i messaggi
$banner_message = "";
$banner_type = "success"; // success o error

// Funzione per eliminare un prodotto
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM MagazzinoPrincipale WHERE ID = ?");
    $stmt->bind_param("s", $delete_id);

    if ($stmt->execute()) {
        $banner_message = "Prodotto eliminato con successo!";
    } else {
        $banner_message = "Errore durante l'eliminazione: " . $conn->error;
        $banner_type = "error";
    }
    $stmt->close();
}

// Funzione per modificare un prodotto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $edit_id = $_POST['edit_id'];
    $nome = $_POST['nome'];
    $categoria = $_POST['categoria'];
    $marchio = $_POST['marchio'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE MagazzinoPrincipale SET Nome=?, CategoriaProdotto=?, Marchio=?, StockCorrente=? WHERE ID=?");
    $stmt->bind_param("sssds", $nome, $categoria, $marchio, $stock, $edit_id);

    if ($stmt->execute()) {
        $banner_message = "Prodotto aggiornato con successo!";
    } else {
        $banner_message = "Errore durante l'aggiornamento: " . $conn->error;
        $banner_type = "error";
    }
    $stmt->close();
}

// Funzione per aggiungere un nuovo prodotto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $categoria = $_POST['categoria'];
    $marchio = $_POST['marchio'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("SELECT ID FROM MagazzinoPrincipale WHERE ID = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $banner_message = "Errore: L'ID prodotto esiste già. Scegli un ID diverso.";
        $banner_type = "error";
    } else {
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO MagazzinoPrincipale (ID, Nome, CategoriaProdotto, Marchio, StockCorrente) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $id, $nome, $categoria, $marchio, $stock);

        if ($stmt->execute()) {
            $banner_message = "Prodotto aggiunto con successo!";
        } else {
            $banner_message = "Errore durante l'aggiunta del prodotto: " . $conn->error;
            $banner_type = "error";
        }
    }
    $stmt->close();
}

// Logica di ricerca
$search_query = "";
if (isset($_GET['search_id'])) {
    $search_query = $_GET['search_id'];
}

// Query per ottenere i dati esistenti (con filtro opzionale)
$sql = "SELECT ID, Nome, CategoriaProdotto, Marchio, StockCorrente FROM MagazzinoPrincipale";
if (!empty($search_query)) {
    $sql .= " WHERE ID LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Magazzino</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
        }

        h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            flex: 1;
        }

        form label {
            font-size: 1rem;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 12px 20px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f1f1f1;
            color: #333;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        button {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0b7dda;
        }

        button:active {
            background-color: #0a6bbd;
        }

        button:focus {
            outline: none;
        }

        .search-bar {
            width: 30%;
            display: flex;
            justify-content: space-between;
        }

        .search-bar input[type="text"] {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        .search-bar input[type="submit"] {
            padding: 5px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 4px;
        }

        .search-bar input[type="submit"]:hover {
            background-color: #45a049;
        }

        .banner {
            display: block;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            transition: opacity 0.5s ease;
        }

        .banner.success {
            background-color: #4CAF50;
            color: white;
        }

        .banner.error {
            background-color: #f44336;
            color: white;
        }

        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 0;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            transition: width 0.3s;
            overflow-x: hidden;
            padding-top: 20px;
            z-index: 1000;
        }

        .drawer form {
            width: 300px;
            margin: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .drawer span {
            font-size: 2rem;
            color: #fff;
            position: absolute;
            top: 10px;
            left: 15px;
            cursor: pointer;
        }

        .drawer input[type="submit"] {
            background-color: #2196F3;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 12px 20px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .drawer input[type="submit"]:hover {
            background-color: #0b7dda;
        }

        @media (max-width: 768px) {
            form {
                width: 100%;
            }

            .drawer form {
                width: 80%;
            }

            .search-bar {
                flex-direction: column;
            }

            .search-bar input[type="text"] {
                width: 100%;
                margin-right: 0;
            }

            .search-bar input[type="submit"] {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<?php if ($banner_message): ?>
    <div class="banner <?php echo $banner_type; ?>">
        <?php echo $banner_message; ?>
    </div>
<?php endif; ?>

<h2>Aggiungi un Nuovo Prodotto</h2>
<div style="display: flex; justify-content: space-between;">
    <div class="search-bar">
        <form method="get" action="">
            <input type="text" name="search_id" placeholder="Cerca per ID" value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="submit" value="Cerca">
        </form>
    </div>
    <form method="post" action="">
        <label for="id">ID:</label>
        <input type="text" id="id" name="id" required>

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="categoria">Categoria:</label>
        <select id="categoria" name="categoria" required>
            <option value="Lavastoviglie">Lavastoviglie</option>
            <option value="Lavatrice">Lavatrice</option>
            <option value="Forno">Forno</option>
        </select>

        <label for="marchio">Marchio:</label>
        <select id="marchio" name="marchio" required>
            <option value="Samsung">Samsung</option>
            <option value="Bosch">Bosch</option>
            <option value="Whirlpool">Whirlpool</option>
        </select>

        <label for="stock">Stock Corrente:</label>
        <input type="number" id="stock" name="stock" required>

        <input type="submit" name="add_product" value="Aggiungi Prodotto">
    </form>
</div>

<h2>Gestione Magazzino</h2>

<div class="table-container">
    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Marchio</th>
            <th>Stock Corrente</th>
            <th>Azioni</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Nome']); ?></td>
                    <td><?php echo htmlspecialchars($row['CategoriaProdotto']); ?></td>
                    <td><?php echo htmlspecialchars($row['Marchio']); ?></td>
                    <td><?php echo htmlspecialchars($row['StockCorrente']); ?></td>
                    <td>
                        <button onclick="openDrawer('<?php echo $row['ID']; ?>', '<?php echo htmlspecialchars($row['Nome']); ?>', '<?php echo htmlspecialchars($row['CategoriaProdotto']); ?>', '<?php echo htmlspecialchars($row['Marchio']); ?>', '<?php echo $row['StockCorrente']; ?>')">Modifica</button>
                        <button onclick="if(confirm('Sei sicuro di voler eliminare questo prodotto?')) { window.location.href='?delete_id=<?php echo $row['ID']; ?>'; }">Elimina</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Nessun prodotto trovato.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div id="drawer" class="drawer">
    <span onclick="closeDrawer()">&times;</span>
    <form method="post" action="">
        <h3>Modifica Prodotto</h3>
        <input type="hidden" id="edit_id" name="edit_id">

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="categoria">Categoria:</label>
        <select id="categoria" name="categoria" required>
            <option value="Lavastoviglie">Lavastoviglie</option>
            <option value="Lavatrice">Lavatrice</option>
            <option value="Forno">Forno</option>
        </select>

        <label for="marchio">Marchio:</label>
        <select id="marchio" name="marchio" required>
            <option value="Samsung">Samsung</option>
            <option value="Bosch">Bosch</option>
            <option value="Whirlpool">Whirlpool</option>
        </select>

        <label for="stock">Stock Corrente:</label>
        <input type="number" id="stock" name="stock" required>

        <input type="submit" name="edit_product" value="Salva Modifiche">
    </form>
</div>

<script>
function openDrawer(id, nome, categoria, marchio, stock) {
    document.getElementById('drawer').style.width = '400px';
    document.getElementById('edit_id').value = id;
    document.getElementById('nome').value = nome;
    document.getElementById('categoria').value = categoria;
    document.getElementById('marchio').value = marchio;
    document.getElementById('stock').value = stock;
}
function closeDrawer() {
    document.getElementById('drawer').style.width = '0';
}
</script>

</body>
</html>

<?php
$conn->close();
?>
