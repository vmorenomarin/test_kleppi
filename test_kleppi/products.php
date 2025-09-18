<?php
session_start();

// Verifica si el usuario no ha iniciado sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verifica el rol del usuario para determinar qué enlaces mostrar
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            color: #333;
            padding: 2rem;
        }
        .container {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            margin-bottom: 2rem;
        }
        h1, h2 {
            color: #007bff;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        p {
            color: #555;
            font-size: 1.1rem;
        }
        .links-container {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
        }
        .links-container a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .links-container a:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Lista de Productos</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>. Aquí puedes ver todos los productos disponibles.</p>

    <div class="links-container">
        <?php if ($is_admin): ?>
            <a href="admin.php">Panel de Administración</a>
        <?php else: ?>
            <a href="logout.php">Cerrar Sesión</a>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody id="productsTableBody">
            <!-- Los productos se cargarán aquí con JavaScript -->
        </tbody>
    </table>
</div>

<script>
    const productsTableBody = document.getElementById('productsTableBody');

    async function fetchProducts() {
        try {
            const response = await fetch('product_controller.php');
            const result = await response.json();
            if (result.success) {
                renderProducts(result.products);
            } else {
                // Mensaje de error si la petición falla
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="3">${result.message}</td>`;
                productsTableBody.appendChild(row);
            }
        } catch (error) {
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="3">Error de conexión al servidor.</td>`;
            productsTableBody.appendChild(row);
        }
    }

    function renderProducts(products) {
        productsTableBody.innerHTML = '';
        if (products.length === 0) {
            productsTableBody.innerHTML = '<tr><td colspan="3">No hay productos disponibles.</td></tr>';
            return;
        }
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.name}</td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.description}</td>
            `;
            productsTableBody.appendChild(row);
        });
    }

    // Cargar los productos al cargar la página
    window.onload = fetchProducts;
</script>

</body>
</html>
