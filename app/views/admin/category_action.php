<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: categories.php?error=Invalid request");
    exit;
}

$action = $_POST['action'] ?? "";

if ($action === "add") {
    $name = trim($_POST['name'] ?? "");
    $description = trim($_POST['description'] ?? "");

    if (empty($name)) {
        header("Location: categories.php?error=Category name is required");
        exit;
    }

    $result = $admin->addCategory($name, $description);

    if ($result === "exists") {
        header("Location: categories.php?error=Category name already exists");
        exit;
    }

    if ($result) {
        header("Location: categories.php?message=Category added successfully");
        exit;
    }

    header("Location: categories.php?error=Failed to add category");
    exit;
}

if ($action === "update") {
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = trim($_POST['name'] ?? "");
    $description = trim($_POST['description'] ?? "");

    if ($categoryId <= 0) {
        header("Location: categories.php?error=Invalid category ID");
        exit;
    }

    if (empty($name)) {
        header("Location: category_edit.php?id=$categoryId&error=Category name is required");
        exit;
    }

    $result = $admin->updateCategory($categoryId, $name, $description);

    if ($result === "exists") {
        header("Location: category_edit.php?id=$categoryId&error=Category name already exists");
        exit;
    }

    if ($result) {
        header("Location: categories.php?message=Category updated successfully");
        exit;
    }

    header("Location: category_edit.php?id=$categoryId&error=Failed to update category");
    exit;
}

if ($action === "delete") {
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

    if ($categoryId <= 0) {
        header("Location: categories.php?error=Invalid category ID");
        exit;
    }

    $result = $admin->deleteCategory($categoryId);

    if ($result === "has_active_jobs") {
        header("Location: categories.php?error=Cannot delete category because active jobs exist under this category");
        exit;
    }

    if ($result) {
        header("Location: categories.php?message=Category deleted successfully");
        exit;
    }

    header("Location: categories.php?error=Failed to delete category");
    exit;
}

header("Location: categories.php?error=Unknown action");
exit;