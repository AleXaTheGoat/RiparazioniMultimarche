<?php
// Configurazione database
$host = 'localhost'; // Host del server MySQL
$dbname = 'riparazioni multimarche'; // Nome del database
$user = 'root'; // Username MySQL
$password = ''; // Password MySQL

try {
    // Connessione al database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gestione del modulo di inserimento
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
       
        $nome = ($_POST['nome']);
        $marca = ($_POST['marca']);
        $prezzo = $_POST['prezzo'];
        $tipologia = ($_POST['tipologia']);

        try {
            // Inserisci l'articolo nel database
            $stmt = $pdo->prepare("INSERT INTO articoli (nome, marca, prezzo, tipologia) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $marca, $prezzo, $tipologia]);

            // Ricarica gli articoli
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            die("Errore durante l'inserimento dell'articolo: " . $e->getMessage());
        }
    } else {
        // Query per ottenere gli articoli
        $stmt = $pdo->query("SELECT * FROM articoli");
        $articoli = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Errore di connessione: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="it">
<head>

    <script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.min.js"></script>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listino articoli</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            text-align: center;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        #searchBar {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        #buttons {
            position: relative;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .actionButton {
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .actionButton:hover {
            background-color: #0056b3;
        }
        .addButton {
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-top: 10px;
        }
        .addButton:hover {
            background-color: #218838;
        }
        #productList {
            width: 100%;
            border-collapse: collapse;
            display: none;
        }
        #productList th, #productList td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        #productList th {
            background-color: #f4f4f4;
        }
        #addFormContainer {
            margin-top: 20px;
            text-align: left;
        }
        #addFormContainer input, #addFormContainer select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Listino articoli</h1>
    <input type="text" id="searchBar" placeholder="Cerca per Nome o Marca..." oninput="filterProducts()">

    <div id="buttons">
        <button class="actionButton" id="showButton" onclick="showTable()">Mostra Tabella</button>
        <button class="actionButton" id="hideButton" onclick="hideTable()">Nascondi Tabella</button>
    </div>

    <button class="addButton" id="addButton" onclick="showAddForm()">Aggiungi Articolo</button>

    <div id="addFormContainer" style="display: none;">
        <h2>Aggiungi Nuovo Articolo</h2>
        <form id="addForm" method="POST" action="">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
            <label for="marca">Marca:</label>
            <select id="marca" name="marca" required>
                <option value="AEG">AEG</option>
                <option value="Electrolux">Electrolux</option>
                <option value="Bosch">Bosch</option>
                <option value="Neff">Neff</option>
                <option value="Siemens">Siemens</option>
                <option value="Ariston">Ariston</option>
                <option value="Hotpoint Ariston">Hotpoint Ariston</option>
                <option value="Indesit">Indesit</option>
                <option value="Whirlpool">Whirlpool</option>
                <option value="Candy">Candy</option>
                <option value="Hoover">Hoover</option>
                <option value="Haier">Haier</option>
                <option value="Ignis">Ignis</option>
                <option value="Franke">Franke</option>
                <option value="Smeg">Smeg</option>
                <option value="LG">LG</option>
                <option value="Samsung">Samsung</option>
                <option value="Gaggenau">Gaggenau</option>
                <option value="General/Liebher">General/Liebher</option>
                <option value="Miele">Miele</option>
                <option value="BAUCKNECHT">BAUCKNECHT</option>
                <option value="Beko">Beko</option>
                <option value="San Giorgio">San Giorgio</option>
            </select>
            <label for="prezzo">Prezzo (€):</label>
            <input type="number" id="prezzo" name="prezzo" step="0.01" required>
            <label for="tipologia">Tipologia:</label>
            <select id="tipologia" name="tipologia" required>
                <option value="Lavatrice">Lavatrice</option>
                <option value="Lavastoviglie">Lavastoviglie</option>
                <option value="Forno">Forno</option>
                <option value="Induzione">Induzione</option>
                <option value="Frigorifero">Frigorifero</option>
                <option value="Asciugatrice">Asciugatrice</option>
                <option value="Microonde">Microonde</option>
                <option value="PC Gas">PC Gas</option>
            </select>
            <button type="submit" class="addButton">Aggiungi Articolo</button>
            <button type="button" class="actionButton" onclick="hideAddForm()">Annulla</button>
        </form>
    </div>

    <table id="productList">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Marca</th>
            <th>Tipologia</th>
            <th>Prezzo (€)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($articoli as $prodotto): ?>
            <tr>
                <td><?= ($prodotto['id']) ?></td>
                <td><?= ($prodotto['nome']) ?></td>
                <td><?= ($prodotto['marca']) ?></td>
                <td><?= ($prodotto['tipologia']) ?></td>
                <td><?= ($prodotto['prezzo']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    const articoli = <?= json_encode($articoli) ?>;

    function filterProducts() {
        const searchValue = document.getElementById("searchBar").value.trim();
        const rows = document.querySelectorAll("#productList tbody tr");

        rows.forEach(row => {
            const nome = row.children[1].textContent.toLowerCase();
            const marca = row.children[2].textContent.toLowerCase();
            const match = nome.includes(searchValue.toLowerCase()) || marca.includes(searchValue.toLowerCase());
            row.style.display = match ? "" : "none";
        });
        document.getElementById("productList").style.display = rows.length > 0 ? "table" : "none";
    }

    function showTable() {
        document.getElementById("productList").style.display = "table";
        document.getElementById("searchBar").value = "";
    }

    function hideTable() {
        document.getElementById("productList").style.display = "none";
        document.getElementById("searchBar").value = "";
    }

    function showAddForm() {
        document.getElementById("addFormContainer").style.display = "block";
    }

    function hideAddForm() {
        document.getElementById("addFormContainer").style.display = "none";
    }

    // Configura Fuse.js con i campi da cercare
const fuseOptions = {
    keys: ['nome', 'marca'], // Campi da includere nella ricerca
    threshold: 0.4,         // Precisione della ricerca (0 = esatto, 1 = molto permissivo)
    includeScore: false,    // Includi il punteggio nei risultati
};

const fuse = new Fuse(articoli, fuseOptions);

function filterProducts() {
    const searchValue = document.getElementById("searchBar").value.trim();

    if (searchValue === "") {
        resetTable(); // Mostra tutti i risultati
        return;
    }

    const results = fuse.search(searchValue); // Esegui la ricerca con Fuse.js
    const filteredArticoli = results.map(result => result.item);

    updateTable(filteredArticoli);
}

function resetTable() {
    updateTable(articoli); // Ripristina la tabella con tutti gli articoli
}

function updateTable(filteredArticoli) {
    const tbody = document.querySelector("#productList tbody");
    tbody.innerHTML = ""; // Svuota la tabella

    if (filteredArticoli.length === 0) {
        document.getElementById("productList").style.display = "none";
        return;
    }

    filteredArticoli.forEach(prodotto => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${prodotto.id}</td>
            <td>${prodotto.nome}</td>
            <td>${prodotto.marca}</td>
            <td>${prodotto.tipologia}</td>
            <td>${prodotto.prezzo}</td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById("productList").style.display = "table";
}

</script>
</body>
</html>