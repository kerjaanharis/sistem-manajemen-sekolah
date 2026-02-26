<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($page_title) ? $page_title . ' - ' : '' ?>
        <?= APP_NAME ?>
    </title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tailwind CSS (via CDN for local dev without NPM) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1e40af', // Blue 800
                        secondary: '#475569', // Slate 600
                        success: '#16a34a',
                        danger: '#dc2626',
                    }
                }
            }
        }
    </script>
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Transisi sidebar dll jika perlu */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">
    <div class="flex h-screen overflow-hidden">