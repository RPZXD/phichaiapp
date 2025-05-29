<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'admin') {
    header('Location: ../login.php');
    exit();
}
require_once('header.php');
require_once('wrapper.php');
?>

<!-- Tailwind CSS CDN with plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        200: '#bfdbfe',
                        300: '#93c5fd',
                        400: '#60a5fa',
                        500: '#3b82f6',
                        600: '#2563eb',
                        700: '#1d4ed8',
                        800: '#1e40af',
                        900: '#1e3a8a'
                    },
                    secondary: {
                        50: '#f8fafc',
                        100: '#f1f5f9',
                        200: '#e2e8f0',
                        300: '#cbd5e1',
                        400: '#94a3b8',
                        500: '#64748b',
                        600: '#475569',
                        700: '#334155',
                        800: '#1e293b',
                        900: '#0f172a'
                    }
                },
                animation: {
                    'fade-in': 'fadeIn 0.5s ease-in-out',
                    'slide-up': 'slideUp 0.3s ease-out',
                    'bounce-in': 'bounceIn 0.6s ease-out',
                    'pulse-slow': 'pulse 3s infinite',
                    'float': 'float 3s ease-in-out infinite',
                    'glow': 'glow 2s ease-in-out infinite alternate',
                    'shimmer': 'shimmer 2s infinite linear',
                    'scale-up': 'scaleUp 0.2s ease-out',
                    'wobble': 'wobble 1s ease-in-out infinite'
                },
                keyframes: {
                    fadeIn: {
                        '0%': { opacity: '0', transform: 'translateY(10px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' }
                    },
                    slideUp: {
                        '0%': { opacity: '0', transform: 'translateY(20px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' }
                    },
                    bounceIn: {
                        '0%': { opacity: '0', transform: 'scale(0.3)' },
                        '50%': { opacity: '1', transform: 'scale(1.05)' },
                        '70%': { transform: 'scale(0.9)' },
                        '100%': { opacity: '1', transform: 'scale(1)' }
                    },
                    float: {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%': { transform: 'translateY(-10px)' }
                    },
                    glow: {
                        '0%': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.5)' },
                        '100%': { boxShadow: '0 0 30px rgba(59, 130, 246, 0.8)' }
                    },
                    shimmer: {
                        '0%': { backgroundPosition: '-200% 0' },
                        '100%': { backgroundPosition: '200% 0' }
                    },
                    scaleUp: {
                        '0%': { transform: 'scale(1)' },
                        '100%': { transform: 'scale(1.05)' }
                    },
                    wobble: {
                        '0%, 100%': { transform: 'rotate(0deg)' },
                        '25%': { transform: 'rotate(3deg)' },
                        '75%': { transform: 'rotate(-3deg)' }
                    }
                },
                backgroundImage: {
                    'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                }
            }
        }
    }
</script>

<!-- Custom Styles -->
<style>

    

    
    /* Enhanced gradient backgrounds */
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .gradient-bg-alt {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .gradient-bg-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    /* Glass morphism effect */
    .glass-effect {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    .dark .glass-effect {
        background: rgba(31, 41, 55, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(75, 85, 99, 0.18);
    }
    
    /* Hover effects */
    .card-hover {
        transition: all 0.3s ease;
    }
    
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    
    .dark .card-hover:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    }
    
    /* Button effects */
    .btn-glow {
        position: relative;
        overflow: hidden;
    }
    
    .btn-glow::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-glow:hover::before {
        left: 100%;
    }
    
    /* Custom DataTables styling with Tailwind */
    .dataTables_wrapper {
        padding: 0;
    }

    .dataTables_filter input {
        border: 1px solid #d1d5db !important;
        border-radius: 0.75rem !important;
        padding: 0.75rem !important;
        margin-left: 0.5rem !important;
        transition: all 0.3s ease !important;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
    }

    .dataTables_filter input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        transform: scale(1.02);
    }

    .dataTables_length select {
        border: 1px solid #d1d5db !important;
        border-radius: 0.75rem !important;
        padding: 0.75rem !important;
        margin: 0 0.5rem !important;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
        transition: all 0.3s ease !important;
    }

    .dt-buttons {
        margin-bottom: 1rem;
    }

    .dt-button {
        border-radius: 0.75rem !important;
        margin-right: 0.5rem !important;
        padding: 0.5rem 1rem !important;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        border: none !important;
        color: white !important;
        transition: all 0.3s ease !important;
    }

    .dt-button:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4) !important;
    }

    .dataTables_info {
        color: #6b7280 !important;
        font-weight: 500 !important;
    }

    .dataTables_paginate .paginate_button {
        border-radius: 0.5rem !important;
        margin: 0 0.125rem !important;
        transition: all 0.3s ease !important;
    }

    .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        color: white !important;
        transform: translateY(-1px) !important;
    }

    /* Dark mode enhancements */
    .dark .dataTables_wrapper {
        color: #e5e7eb;
    }

    .dark .dataTables_filter input {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%) !important;
        border-color: #4b5563 !important;
        color: #e5e7eb !important;
    }

    .dark .dataTables_length select {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%) !important;
        border-color: #4b5563 !important;
        color: #e5e7eb !important;
    }

    .dark .dataTables_info {
        color: #9ca3af !important;
    }

    .dark table.dataTable thead th {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%) !important;
        color: #e5e7eb !important;
    }

    .dark table.dataTable tbody tr {
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
        transition: all 0.3s ease !important;
    }

    .dark table.dataTable tbody tr:nth-child(even) {
        background-color: #111827 !important;
    }

    .dark table.dataTable tbody tr:hover {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%) !important;
        transform: scale(1.01) !important;
    }

    /* Modal enhancements */
    .modal-backdrop {
        backdrop-filter: blur(4px);
    }

    /* Role badge animations */
    .role-badge {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .role-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s;
    }

    .role-badge:hover::before {
        left: 100%;
    }    .role-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
      /* Reset Password Modal Enhancements */
    .radio-option input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-color: #3b82f6 !important;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    }
    
    .dark .radio-option input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
        border-color: #60a5fa !important;
    }
    
    /* Radio dot animation */
    .radio-option input[type="radio"]:checked + .radio-container .radio-dot {
        opacity: 1 !important;
        transform: scale(1);
        animation: radioSelect 0.3s ease-out;
    }
    
    .radio-option input[type="radio"]:not(:checked) + .radio-container .radio-dot {
        opacity: 0 !important;
        transform: scale(0.7);
    }
    
    .radio-option input[type="radio"]:checked + .radio-container .radio-circle {
        border-color: currentColor;
    }
    
    /* Force Change option colors */
    .radio-option[data-value="force_change"] input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #fed7aa 0%, #fde68a 100%);
        border-color: #f59e0b !important;
    }
    
    .dark .radio-option[data-value="force_change"] input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
        border-color: #fb923c !important;
    }
    
    /* Set Default option colors */
    .radio-option[data-value="set_default"] input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-color: #3b82f6 !important;
    }
    
    .dark .radio-option[data-value="set_default"] input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
        border-color: #60a5fa !important;
    }
    
    /* Custom option colors */
    .radio-option[data-value="custom"] input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border-color: #16a34a !important;
    }
    
    .dark .radio-option[data-value="custom"] input[type="radio"]:checked + .radio-container {
        background: linear-gradient(135deg, #166534 0%, #15803d 100%);
        border-color: #4ade80 !important;
    }
    
    @keyframes radioSelect {
        0% {
            opacity: 0;
            transform: scale(0.5);
        }
        50% {
            opacity: 0.8;
            transform: scale(1.2);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    #customPasswordField {
        transition: all 0.3s ease;
    }
    
    #customPasswordField.show {
        display: block !important;
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<div class="content-wrapper min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Animated Header Section -->
    <section class="content">
        <div class="relative overflow-hidden bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-700 rounded-none mb-0 p-8">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div class="animate-fade-in">
                        <h1 class="text-4xl font-bold text-white mb-2 drop-shadow-lg flex items-center">
                            <span class="text-5xl mr-4 animate-wobble">👥</span>
                            จัดการข้อมูลผู้ใช้งาน
                        </h1>
                        <p class="text-lg text-blue-100 opacity-90">
                            ระบบจัดการผู้ใช้งาน เพิ่ม แก้ไข และกำหนดบทบาทของผู้ใช้
                        </p>
                    </div>
                    <div class="animate-float">
                        <i class="fas fa-users-cog text-6xl text-white opacity-30"></i>
                    </div>
                </div>
            </div>
            <!-- Animated background elements -->
            <div class="absolute top-4 right-20 w-32 h-32 bg-white opacity-5 rounded-full animate-pulse"></div>
            <div class="absolute bottom-4 left-20 w-24 h-24 bg-blue-300 opacity-10 rounded-full animate-bounce"></div>
        </div>

        <div class="container mx-auto px-8 py-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Users Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 card-hover border-l-4 border-blue-500 transform transition-all duration-300 hover:scale-105 animate-slide-up">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium mb-1">ผู้ใช้ทั้งหมด</p>
                            <p class="text-3xl font-bold text-gray-800 count-number" id="totalUsers">0</p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i>
                                เพิ่มขึ้น
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-4 rounded-full shadow-lg animate-glow">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Active Users Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 card-hover border-l-4 border-green-500 transform transition-all duration-300 hover:scale-105 animate-slide-up" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium mb-1">ผู้ใช้ที่ใช้งาน</p>
                            <p class="text-3xl font-bold text-gray-800 count-number" id="activeUsers">0</p>
                            <p class="text-xs text-blue-600 mt-1">
                                <i class="fas fa-signal mr-1"></i>
                                30 วันที่ผ่านมา
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-green-400 to-green-600 p-4 rounded-full shadow-lg animate-glow">
                            <i class="fas fa-user-check text-white text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Admin Count Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 card-hover border-l-4 border-red-500 transform transition-all duration-300 hover:scale-105 animate-slide-up" style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium mb-1">ผู้ดูแลระบบ</p>
                            <p class="text-3xl font-bold text-gray-800 count-number" id="adminCount">0</p>
                            <p class="text-xs text-red-600 mt-1">
                                <i class="fas fa-shield-alt mr-1"></i>
                                ระดับสูงสุด
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-red-400 to-red-600 p-4 rounded-full shadow-lg animate-glow">
                            <i class="fas fa-user-shield text-white text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- New Users Today Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 card-hover border-l-4 border-purple-500 transform transition-all duration-300 hover:scale-105 animate-slide-up" style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium mb-1">ผู้ใช้ใหม่วันนี้</p>
                            <p class="text-3xl font-bold text-gray-800 count-number" id="newUsersToday">0</p>
                            <p class="text-xs text-purple-600 mt-1">
                                <i class="fas fa-user-plus mr-1"></i>
                                เพิ่มใหม่
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-4 rounded-full shadow-lg animate-glow">
                            <i class="fas fa-user-plus text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200 animate-fade-in">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                    <div class="flex justify-between items-center flex-wrap gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-white mb-1">รายการผู้ใช้งานทั้งหมด</h2>
                            <p class="text-indigo-100">จัดการข้อมูลผู้ใช้และกำหนดสิทธิ์การเข้าถึง</p>
                        </div>
                        <div class="flex gap-3 flex-wrap">
                            <select id="roleFilter" class="px-4 py-3 border-0 rounded-xl bg-white/20 backdrop-blur-sm text-white placeholder-white/70 focus:ring-2 focus:ring-white/50 transition-all duration-300">
                                <option value="" class="text-gray-800">ทุกบทบาท</option>
                                <option value="student" class="text-gray-800">🎓 นักเรียน</option>
                                <option value="teacher" class="text-gray-800">👨‍🏫 ครู</option>
                                <option value="admin" class="text-gray-800">🛡️ ผู้ดูแลระบบ</option>
                                <option value="officer" class="text-gray-800">👔 เจ้าหน้าที่</option>
                                <option value="director" class="text-gray-800">🎯 ผู้อำนวยการ</option>
                                <option value="vp" class="text-gray-800">⭐ รองผู้อำนวยการ</option>
                                <option value="hod" class="text-gray-800">📊 หัวหน้าแผนก</option>
                                <option value="parent" class="text-gray-800">👨‍👩‍👧‍👦 ผู้ปกครอง</option>
                            </select>
                            <button id="refreshBtn" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all duration-300 flex items-center btn-glow">
                                <i class="fas fa-sync-alt mr-2 animate-spin"></i>รีเฟรช
                            </button>
                            <button id="addUserBtn" class="bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all duration-300 flex items-center btn-glow transform hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>เพิ่มผู้ใช้งาน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="p-6">
                    <div class="overflow-x-auto bg-gray-50 rounded-xl p-4">
                        <table id="usersTable" class="w-full table-auto border-collapse display responsive nowrap bg-white rounded-xl overflow-hidden shadow-sm" style="width:100%">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-100 to-gray-200">
                                    <th class="border-0 px-6 py-4 text-left font-semibold text-gray-700">
                                        <i class="fas fa-hashtag mr-2 text-blue-500"></i>ID
                                    </th>
                                    <th class="border-0 px-6 py-4 text-left font-semibold text-gray-700">
                                        <i class="fas fa-user mr-2 text-green-500"></i>ชื่อผู้ใช้
                                    </th>
                                    <th class="border-0 px-6 py-4 text-left font-semibold text-gray-700">
                                        <i class="fas fa-envelope mr-2 text-purple-500"></i>อีเมล
                                    </th>
                                    <th class="border-0 px-6 py-4 text-left font-semibold text-gray-700">
                                        <i class="fas fa-user-tag mr-2 text-orange-500"></i>บทบาท
                                    </th>
                                    <th class="border-0 px-6 py-4 text-left font-semibold text-gray-700">
                                        <i class="fas fa-phone mr-2 text-teal-500"></i>เบอร์โทรศัพท์
                                    </th>
                                    <th class="border-0 px-6 py-4 text-left font-semibold text-gray-700">
                                        <i class="fas fa-calendar mr-2 text-indigo-500"></i>วันที่สร้าง
                                    </th>
                                    <th class="border-0 px-6 py-4 text-center font-semibold text-gray-700">
                                        <i class="fas fa-cogs mr-2 text-red-500"></i>การจัดการ
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- User Modal -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900 dark:text-white">เพิ่มผู้ใช้งานใหม่</h3>
                <button type="button" onclick="userManager.hideUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="ปิด">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="userForm" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        ชื่อผู้ใช้งาน <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="กรอกชื่อผู้ใช้งาน">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        อีเมล <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="กรอกอีเมล">
                </div>
                <div id="passwordField">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        รหัสผ่าน <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="กรอกรหัสผ่าน">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        บทบาท <span class="text-red-500">*</span>
                    </label>
                    <select id="role" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- เลือกบทบาท --</option>
                        <option value="student">นักเรียน</option>
                        <option value="teacher">ครู</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                        <option value="officer">เจ้าหน้าที่</option>
                        <option value="director">ผู้อำนวยการ</option>
                        <option value="vp">รองผู้อำนวยการ</option>
                        <option value="hod">หัวหน้าแผนก</option>
                        <option value="parent">ผู้ปกครอง</option>
                    </select>
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">เบอร์โทรศัพท์</label>
                    <input type="tel" id="phone_number" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="กรอกเบอร์โทรศัพท์">
                </div>
            </form>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="userManager.hideUserModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>ยกเลิก
                </button>
                <button id="saveUserBtn" class="bg-gradient-to-r from-primary to-secondary text-white px-4 py-2 rounded-lg font-bold transition">
                    <i class="fas fa-save mr-2"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-600 bg-opacity-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-lg mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-key mr-3 text-blue-200"></i>
                            รีเซ็ตรหัสผ่าน
                        </h3>
                        <button type="button" 
                                onclick="userManager.hideResetPasswordModal()" 
                                class="text-blue-200 hover:text-white transition-colors duration-200 p-1 rounded-lg hover:bg-white/20">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- User Info -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <i class="fas fa-user-circle text-blue-500 text-lg mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                                    ผู้ใช้งาน
                                </p>
                                <p class="text-lg font-bold text-blue-800 dark:text-blue-500" id="resetUsername">
                                    <!-- Username will be inserted here -->
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-500 mb-3 flex items-center">
                            <i class="fas fa-cog text-gray-700 mr-2"></i>
                            เลือกวิธีการรีเซ็ตรหัสผ่าน
                        </h4>
                    </div>                    <!-- Reset Options -->
                    <div class="space-y-4 mb-6" id="resetOptions">
                        <!-- Force Change Password Option -->
                        <label class="block cursor-pointer radio-option" data-value="force_change">
                            <input type="radio" name="resetType" value="force_change" class="sr-only" checked>
                            <div class="radio-container flex items-start p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl transition-all duration-200 hover:border-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:shadow-md">
                                <div class="flex-shrink-0 w-5 h-5 mt-1 mr-4 border-2 border-orange-400 rounded-full flex items-center justify-center radio-circle">
                                    <div class="w-2.5 h-2.5 bg-orange-500 rounded-full radio-dot opacity-100"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-user-edit text-orange-500 mr-2"></i>
                                        <span class="font-semibold text-orange-900 dark:text-orange-500">
                                            บังคับให้เปลี่ยนรหัสผ่านเมื่อเข้าสู่ระบบครั้งถัดไป
                                        </span>
                                    </div>
                                    <p class="text-sm text-orange-600 dark:text-gray-400 leading-relaxed">
                                        ผู้ใช้จะถูกบังคับให้เปลี่ยนรหัสผ่านทันทีหลังจากเข้าสู่ระบบ (แนะนำ)
                                    </p>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Set Default Password Option -->
                        <label class="block cursor-pointer radio-option" data-value="set_default">
                            <input type="radio" name="resetType" value="set_default" class="sr-only">
                            <div class="radio-container flex items-start p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:shadow-md">
                                <div class="flex-shrink-0 w-5 h-5 mt-1 mr-4 border-2 border-gray-300 rounded-full flex items-center justify-center radio-circle">
                                    <div class="w-2.5 h-2.5 bg-blue-500 rounded-full radio-dot opacity-0"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-key text-blue-500 mr-2"></i>
                                        <span class="font-semibold text-gray-900 dark:text-blue-500">
                                            ตั้งรหัสผ่านใหม่เป็นค่าเริ่มต้น
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                        ตั้งรหัสผ่านเป็น <code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded text-xs">"123456"</code> (ควรแจ้งให้ผู้ใช้เปลี่ยนใหม่)
                                    </p>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Custom Password Option -->
                        <label class="block cursor-pointer radio-option" data-value="custom">
                            <input type="radio" name="resetType" value="custom" class="sr-only">
                            <div class="radio-container flex items-start p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl transition-all duration-200 hover:border-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 hover:shadow-md">
                                <div class="flex-shrink-0 w-5 h-5 mt-1 mr-4 border-2 border-gray-300 rounded-full flex items-center justify-center radio-circle">
                                    <div class="w-2.5 h-2.5 bg-green-500 rounded-full radio-dot opacity-0"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-lock text-green-500 mr-2"></i>
                                        <span class="font-semibold text-gray-900 dark:text-green-500">
                                            กำหนดรหัสผ่านใหม่เอง
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                                        กำหนดรหัสผ่านใหม่สำหรับผู้ใช้นี้
                                    </p>
                                    
                                    <!-- Custom Password Field -->
                                    <div id="customPasswordField" class="hidden space-y-3">
                                        <div class="relative">
                                            <input type="password" 
                                                   id="customPassword" 
                                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-200" 
                                                   placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)" 
                                                   minlength="6">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <i class="fas fa-shield-alt text-green-500"></i>
                                            </div>
                                        </div>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   id="showCustomPassword" 
                                                   class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                                                แสดงรหัสผ่าน
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                <!-- Warning -->
                <div class="mb-6 p-4 bg-gray-500 dark:from-yellow-900/30 dark:to-orange-900/20 rounded-xl border border-orange-300 dark:border-yellow-700">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-1">
                            <i class="fas fa-exclamation-triangle text-orange-600 dark:text-yellow-400 text-2xl drop-shadow"></i>
                        </div>
                        <div class="ml-4">
                            <h5 class="text-base font-bold text-orange-800 dark:text-yellow-200 mb-1 drop-shadow-sm">
                                คำเตือนสำคัญ
                            </h5>
                            <p class="text-sm text-orange-900 dark:text-yellow-200 leading-relaxed font-medium">
                                การรีเซ็ตรหัสผ่านจะมีผลทันที<br>
                                <span class="font-semibold underline decoration-wavy decoration-orange-400 dark:decoration-yellow-300">ควรแจ้งให้ผู้ใช้ทราบเพื่อความปลอดภัย</span>
                            </p>
                        </div>
                    </div>
                </div>

                
                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" 
                            onclick="userManager.hideResetPasswordModal()" 
                            class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        ยกเลิก
                    </button>
                    <button id="confirmResetBtn" 
                            onclick="userManager.confirmResetPassword()" 
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-key mr-2"></i>
                        รีเซ็ตรหัสผ่าน
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permission Management Quick Modal -->
<div id="permissionQuickModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-600 bg-opacity-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 rounded-full p-2 mr-3">
                                <i class="fas fa-user-shield text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">จัดการสิทธิ์ผู้ใช้</h3>
                                <p class="text-purple-100 text-sm">กำหนดสิทธิ์การเข้าถึงระบบ</p>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="userManager.hidePermissionModal()" 
                                class="text-white hover:text-purple-200 transition-colors duration-200 p-2 hover:bg-white hover:bg-opacity-20 rounded-full">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- User Info -->
                    <div class="bg-purple-50 dark:bg-purple-900 dark:bg-opacity-30 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-2">ข้อมูลผู้ใช้</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">ชื่อผู้ใช้:</span>
                                <span id="permModalUsername" class="font-medium text-gray-800 dark:text-white"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">บทบาท:</span>
                                <span id="permModalRole" class="font-medium text-gray-800 dark:text-white"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Permission Summary -->
                    <div id="permissionSummary" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 dark:text-white mb-2">สรุปสิทธิ์ปัจจุบัน</h4>
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                            <p class="text-gray-500 mt-2">กำลังโหลดข้อมูลสิทธิ์...</p>
                        </div>
                    </div>

                    <!-- Action Info -->
                    <div class="bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                <p class="font-medium mb-1">หมายเหตุ:</p>
                                <p>การจัดการสิทธิ์แบบละเอียดจะเปิดในหน้าจัดการสิทธิ์แยกต่างหาก ซึ่งมีเครื่องมือครบครันสำหรับการกำหนดสิทธิ์ตามโมดูลและฝ่ายงาน</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
                    <div class="flex gap-3">
                        <button type="button" 
                                onclick="userManager.hidePermissionModal()" 
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>ปิด
                        </button>
                        <button type="button" 
                                onclick="userManager.goToPermissionManagement()" 
                                class="flex-1 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white px-4 py-2.5 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-user-shield mr-2"></i>จัดการสิทธิ์
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('../footer.php'); ?>
<?php require_once('script.php'); ?>
<script src="js/users.js" defer></script>
</body>
</html>
