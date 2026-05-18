<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVSU-OC INC Form Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-maroon { background-color: #800000; }
        .text-maroon { color: #800000; }
        .border-maroon { border-color: #800000; }
        .hover-gold:hover { background-color: #FFD700; color: #222222; }
    </style>
</head>
<body class="bg-[#FBFBFB] min-h-screen flex flex-col justify-center items-center font-sans px-4">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-maroon uppercase tracking-wide">EVSU - Ormoc Campus</h1>
        <p class="text-gray-500 mt-2 text-sm tracking-widest">INC APPLICATION & RESOLUTION WORKFLOW SYSTEM</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 w-full max-w-4xl">
        <a href="login.php?portal=student" class="bg-white border border-gray-100 rounded-lg p-8 text-center shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between items-center group">
            <div class="p-4 bg-gray-50 rounded-full mb-4 group-hover:bg-red-50 transition">
                <svg class="w-12 h-12 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Student Portal</h2>
            <p class="text-gray-400 text-sm mb-6">File digital INC submissions, manage payments, and view grades real-time.</p>
            <span class="w-full py-2.5 rounded text-white bg-maroon font-semibold hover-gold text-center transition">Access Student Login</span>
        </a>

        <a href="login.php?portal=employee" class="bg-white border border-gray-100 rounded-lg p-8 text-center shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between items-center group">
            <div class="p-4 bg-gray-50 rounded-full mb-4 group-hover:bg-red-50 transition">
                <svg class="w-12 h-12 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Employee Portal</h2>
            <p class="text-gray-400 text-sm mb-6">For Registrar, Department Heads, and Instructors processing clearance tracks.</p>
            <span class="w-full py-2.5 rounded text-white bg-maroon font-semibold hover-gold text-center transition">Access Employee Login</span>
        </a>
    </div>
</body>
</html>