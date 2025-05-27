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
    
    .gradient-bg-academic {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .gradient-bg-budget {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    }
    
    .gradient-bg-personnel {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }
    
    .gradient-bg-general {
        background: linear-gradient(135deg, #d299c2 0%, #fef9d7 100%);
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
    
    /* Role level badges */
    .role-level-1 { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; }
    .role-level-2 { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; }
    .role-level-3 { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; }
    .role-level-4 { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #166534; }
    .role-level-5 { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #6b21a8; }
    
    /* Permission toggle styles */
    .permission-toggle {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }
    
    .permission-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .permission-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 30px;
    }
    
    .permission-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .permission-slider {
        background-color: #2196F3;
    }
    
    input:checked + .permission-slider:before {
        transform: translateX(30px);
    }
    
    /* Department card styling */
    .department-card {
        border-radius: 1rem;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .department-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    /* Module expansion animation */
    .module-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    
    .module-content.expanded {
        max-height: 1000px;
    }
</style>

<div class="content-wrapper min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Header Section -->
    <section class="content-header py-6">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-2 animate-fade-in">
                        <i class="fas fa-user-shield text-blue-600"></i>
                        จัดการบทบาทและสิทธิ์
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300 animate-slide-up">
                        กำหนดสิทธิ์การเข้าถึงตามฝ่ายงานและโมดูลต่างๆ
                    </p>
                </div>
                <div class="flex gap-3">
                    <button id="saveAllPermissions" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-save mr-2"></i>บันทึกสิทธิ์ทั้งหมด
                    </button>
                    <button id="refreshPermissions" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- User Selection Section -->
    <section class="content px-4">
        <div class="container mx-auto">
            <!-- User Selection Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 mb-6 card-hover">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-user text-purple-500 mr-2"></i>เลือกผู้ใช้งาน
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="userSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ผู้ใช้งาน</label>
                        <select id="userSelect" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">เลือกผู้ใช้งาน...</option>
                        </select>
                    </div>
                    <div>
                        <label for="departmentFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">กรองตามฝ่าย</label>
                        <select id="departmentFilter" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">ทุกฝ่าย</option>
                            <option value="academic">งานวิชาการ</option>
                            <option value="budget">งานงบประมาณ</option>
                            <option value="personnel">งานบุคคล</option>
                            <option value="general">งานทั่วไป</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Role Hierarchy Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 mb-6 card-hover">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
                    <i class="fas fa-layer-group text-indigo-500 mr-2"></i>ลำดับชั้นของบทบาท
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="text-center">
                        <div class="role-level-1 px-4 py-2 rounded-lg font-medium text-sm">
                            <i class="fas fa-crown mr-1"></i>Admin (5)
                        </div>
                        <p class="text-xs text-gray-500 mt-1">สิทธิ์สูงสุด</p>
                    </div>
                    <div class="text-center">
                        <div class="role-level-2 px-4 py-2 rounded-lg font-medium text-sm">
                            <i class="fas fa-user-tie mr-1"></i>หัวหน้าฝ่าย (4)
                        </div>
                        <p class="text-xs text-gray-500 mt-1">จัดการฝ่าย</p>
                    </div>
                    <div class="text-center">
                        <div class="role-level-3 px-4 py-2 rounded-lg font-medium text-sm">
                            <i class="fas fa-user-cog mr-1"></i>เจ้าหน้าที่ (3)
                        </div>
                        <p class="text-xs text-gray-500 mt-1">ดำเนินงาน</p>
                    </div>
                    <div class="text-center">
                        <div class="role-level-4 px-4 py-2 rounded-lg font-medium text-sm">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>ครู (2)
                        </div>
                        <p class="text-xs text-gray-500 mt-1">สอนและดูแล</p>
                    </div>
                    <div class="text-center">
                        <div class="role-level-5 px-4 py-2 rounded-lg font-medium text-sm">
                            <i class="fas fa-eye mr-1"></i>ผู้ชม (1)
                        </div>
                        <p class="text-xs text-gray-500 mt-1">ดูข้อมูลเท่านั้น</p>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>หมายเหตุ:</strong> ผู้ที่มีสิทธิ์สูงกว่าสามารถเข้าถึงเมนูและฟังก์ชันของระดับต่ำกว่าได้
                    </p>
                </div>
            </div>

            <!-- Departments Grid -->
            <div id="departmentsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Department cards will be dynamically generated here -->
            </div>
        </div>
    </section>
</div>

<!-- Role Assignment Modal -->
<div id="roleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">กำหนดบทบาท</h3>
            <button id="closeRoleModal" class="text-gray-500 hover:text-gray-700 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ฝ่าย</label>
                <input type="text" id="modalDepartment" class="w-full p-3 border rounded-lg bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ผู้ใช้</label>
                <input type="text" id="modalUser" class="w-full p-3 border rounded-lg bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">บทบาท</label>
                <select id="modalRole" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">ไม่มีบทบาท</option>
                    <option value="admin">Admin</option>
                    <option value="head">หัวหน้าฝ่าย</option>
                    <option value="officer">เจ้าหน้าที่</option>
                    <option value="teacher">ครู</option>
                    <option value="viewer">ผู้ชม</option>
                </select>
            </div>
            <div class="flex gap-3 mt-6">
                <button id="saveRole" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    บันทึก
                </button>
                <button id="cancelRole" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ยกเลิก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript -->
<script src="js/role-management.js"></script>

<?php require_once('../footer.php'); ?>
<?php require_once('script.php'); ?>

</body>
</html>
