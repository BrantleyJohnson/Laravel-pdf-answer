<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="chatApp()" x-init="loadTheme()">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
      
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        rel="stylesheet"
      />
      <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <style>
            * {
              box-sizing: border-box;
              margin: 0;
              padding: 0;
              font-family: "Arial", sans-serif;
            }
      
            body {
              transition: background-color 0.3s, color 0.3s;
            }
      
            .dark {
              background-color: #121212;
              color: #fff;
            }
      
            .chat-container {
              display: flex;
               height: calc( 100vh - 4rem );;
              overflow: hidden;
            }
      
            .sidebar {
              width: 250px;
              background-color: #f0f0f0;
              padding: 20px;
              display: flex;
              flex-direction: column;
              justify-content: space-between;
              transition: background-color 0.3s;
            }
      
            .dark .sidebar {
              background-color: #333;
            }
      
            .top-controls select,
            .top-controls button {
              margin-bottom: 10px;
            }
      
            .user-info {
              text-align: left;
            }
      
            .username {
              margin-bottom: 10px;
            }
      
            .chat-main {
              flex-grow: 1;
              display: flex;
              flex-direction: column;
              background-color: #fff;
              transition: background-color 0.3s;
            }
      
            .dark .chat-main {
              background-color: #1e1e1e;
            }
      
            .chat-history {
              flex-grow: 1;
              overflow-y: auto;
              padding: 10px 20px;
              display: flex;
              flex-direction: column;
            }
      
            .chat-message {
              max-width: 70%;
              position: relative;
            }
      
            .message-master {
              align-self: flex-start;
            }
            .outgoing {
              background-color: #007aff;
              color: white;
            }
      
            .incoming {
              background-color: #e5e5ea;
              color: black;
            }
      
            .dark .incoming {
              background-color: #2b2b2b;
            }
      
            .chat-form {
              display: flex;
              padding: 10px;
              background-color: #f0f0f0;
              transition: background-color 0.3s;
            }
      
            .dark .chat-form {
              background-color: #252525;
            }
      
            .chat-form input {
              flex-grow: 1;
              margin-right: 10px;
              padding: 10px;
              border: none;
              border-radius: 20px;
              outline: none;
            }
      
            .chat-form button {
              padding: 10px 20px;
              border: none;
              border-radius: 20px;
              background-color: #007aff;
              color: white;
              cursor: pointer;
            }

            .relative {
                position: relative;
            }
      
            .rounded-lg {
                border-radius: .5rem;
            }

            items-center {
            align-items: center;
            }
            .whitespace-nowrap {
                white-space: nowrap;
            }
            .grow {
               flex-grow: 1;
            }
            a {
                color: inherit;
                text-decoration: inherit;
            }
            .chat-history-href:hover {
                background-color: #f0f0f0; /* Change background color on hover */
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Add shadow on hover */
            }
            .overflow-hidden {
                overflow: hidden;
            }
            @media (max-width: 768px) {
              .chat-container {
                flex-direction: column;
              }
      
              .sidebar {
                width: 100%;
                height: 150px;
              }
            }

            .mobile-only {
              display: none;
            }

            @media only screen and (max-width: 768px) {
              .mobile-only {
                display: block;
              }

              .mobile-avoid {
                display: none;
              }
            }

          </style>
    </head>
    <body class="font-sans antialiased" :class="{ 'dark': darkMode }">
        <div>
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

    </body>
</html>
