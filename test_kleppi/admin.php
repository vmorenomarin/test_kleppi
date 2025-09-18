<?php
session_start();

// Verifica si el usuario no ha iniciado sesión o no es un administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CRUD</title>
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
        h2 {
            font-size: 1.5rem;
            margin-top: 2rem;
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
        form {
            text-align: left;
            margin-top: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #28a745;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
        }
        button:hover {
            background-color: #218838;
        }
        .message-box {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            color: #fff;
            display: none;
        }
        .message-box.success {
            background-color: #28a745;
        }
        .message-box.error {
            background-color: #dc3545;
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
        .actions-cell {
            white-space: nowrap;
        }
        .actions-cell button {
            width: auto;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }
        .edit-btn {
            background-color: #ffc107;
            color: #333;
        }
        .edit-btn:hover {
            background-color: #e0a800;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Panel de Administración</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>. Puedes gestionar los productos aquí.</p>

    <div class="links-container">
        <a href="products.php">Ver productos (Vista Cliente)</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>

    <h2>Agregar nuevo producto</h2>
    <form id="productForm">
        <input type="hidden" id="productId" name="id">
        <div class="form-group">
            <label for="name">Nombre del producto</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="price">Precio</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <button type="submit" id="submitBtn">Agregar Producto</button>
    </form>
    <div id="messageBox" class="message-box"></div>

    <h2>Lista de Productos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="productsTableBody">
            <!-- Los productos se cargarán aquí con JavaScript -->
        </tbody>
    </table>
</div>

<script>
    const productForm = document.getElementById('productForm');
    const productsTableBody = document.getElementById('productsTableBody');
    const messageBox = document.getElementById('messageBox');
    const submitBtn = document.getElementById('submitBtn');
    const productIdInput = document.getElementById('productId');

    async function fetchProducts() {
        try {
            const response = await fetch('product_controller.php');
            const result = await response.json();
            if (result.success) {
                renderProducts(result.products);
            } else {
                showMessage('Error al cargar productos: ' + result.message, 'error');
            }
        } catch (error) {
            showMessage('Error de conexión al servidor.', 'error');
        }
    }

    function renderProducts(products) {
        productsTableBody.innerHTML = '';
        if (products.length === 0) {
            productsTableBody.innerHTML = '<tr><td colspan="5">No hay productos registrados.</td></tr>';
            return;
        }
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>${product.description}</td>
                <td class="actions-cell">
                    <button class="edit-btn" data-id="${product.id}" data-name="${product.name}" data-price="${product.price}" data-description="${product.description}">Editar</button>
                    <button class="delete-btn" data-id="${product.id}">Borrar</button>
                </td>
            `;
            productsTableBody.appendChild(row);
        });
    }

    function showMessage(message, type) {
        messageBox.textContent = message;
        messageBox.className = `message-box show ${type}`;
        setTimeout(() => {
            messageBox.className = 'message-box';
        }, 5000);
    }

    // Manejar el formulario para agregar/editar
    productForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        const formData = new FormData(productForm);
        const data = Object.fromEntries(formData.entries());
        
        const isEditing = productIdInput.value !== '';
        let url = 'product_controller.php';
        let method = 'POST';

        if (isEditing) {
            url += `?id=${data.id}`;
            method = 'PUT';
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                showMessage(result.message, 'success');
                productForm.reset();
                submitBtn.textContent = 'Agregar Producto';
                productIdInput.value = '';
                fetchProducts();
            } else {
                showMessage('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showMessage('Error de conexión al servidor.', 'error');
        }
    });

    // Manejar clics en los botones de editar y borrar
    productsTableBody.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-btn')) {
            const btn = event.target;
            productIdInput.value = btn.dataset.id;
            document.getElementById('name').value = btn.dataset.name;
            document.getElementById('price').value = btn.dataset.price;
            document.getElementById('description').value = btn.dataset.description;
            submitBtn.textContent = 'Actualizar Producto';
        }

        if (event.target.classList.contains('delete-btn')) {
            const id = event.target.dataset.id;
            if (confirm('¿Estás seguro de que quieres borrar este producto?')) {
                deleteProduct(id);
            }
        }
    });

    async function deleteProduct(id) {
        try {
            const response = await fetch(`product_controller.php?id=${id}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            if (result.success) {
                showMessage('Producto borrado exitosamente.', 'success');
                fetchProducts();
            } else {
                showMessage('Error: ' + result.message, 'error');
            }
        } catch (error) {
            showMessage('Error de conexión al servidor.', 'error');
        }
    }

    // Cargar los productos al cargar la página
    window.onload = fetchProducts;
</script>

</body>
</html>
