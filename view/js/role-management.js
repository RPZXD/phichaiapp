// Role Management System with Department-based Permissions
// Author: ICT Team @Phichai School
// Date: May 27, 2025

class RoleManagementSystem {
    constructor() {
        // กำหนดข้อมูลฝ่ายงานและโมดูล
        this.departments = {
            academic: {
                name: 'งานวิชาการ',
                icon: 'fa-graduation-cap',
                color: 'academic',
                description: 'จัดการด้านการเรียนการสอนและหลักสูตร',
                modules: {
                    curriculum: { name: 'หลักสูตร', icon: 'fa-book' },
                    schedule: { name: 'ตารางเรียน', icon: 'fa-calendar-alt' },
                    assessment: { name: 'การประเมิน', icon: 'fa-chart-line' },
                    academic_report: { name: 'รายงานวิชาการ', icon: 'fa-file-alt' }
                }
            },
            budget: {
                name: 'งานงบประมาณ',
                icon: 'fa-coins',
                color: 'budget',
                description: 'จัดการงบประมาณและการเงิน',
                modules: {
                    budget_plan: { name: 'แผนงบประมาณ', icon: 'fa-chart-pie' },
                    expenses: { name: 'รายจ่าย', icon: 'fa-money-bill-wave' },
                    procurement: { name: 'จัดซื้อจัดจ้าง', icon: 'fa-shopping-cart' },
                    finance_report: { name: 'รายงานการเงิน', icon: 'fa-calculator' }
                }
            },
            personnel: {
                name: 'งานบุคคล',
                icon: 'fa-users',
                color: 'personnel',
                description: 'จัดการบุคลากรและทรัพยากรบุคคล',
                modules: {
                    staff_management: { name: 'จัดการบุคลากร', icon: 'fa-user-tie' },
                    attendance: { name: 'ลงเวลาทำงาน', icon: 'fa-clock' },
                    leave_management: { name: 'จัดการลาหยุด', icon: 'fa-calendar-times' },
                    hr_report: { name: 'รายงานบุคลากร', icon: 'fa-users-cog' }
                }
            },
            general: {
                name: 'งานทั่วไป',
                icon: 'fa-cogs',
                color: 'general',
                description: 'งานสนับสนุนและบริหารทั่วไป',
                modules: {
                    facility: { name: 'สิ่งอำนวยความสะดวก', icon: 'fa-building' },
                    maintenance: { name: 'ซ่อมบำรุง', icon: 'fa-tools' },
                    inventory: { name: 'พัสดุ', icon: 'fa-boxes' },
                    general_report: { name: 'รายงานทั่วไป', icon: 'fa-clipboard-list' }
                }
            }
        };

        // กำหนดบทบาทและระดับสิทธิ์
        this.roles = {
            admin: { name: 'Admin', level: 5, color: 'red' },
            head: { name: 'หัวหน้าฝ่าย', level: 4, color: 'orange' },
            officer: { name: 'เจ้าหน้าที่', level: 3, color: 'blue' },
            teacher: { name: 'ครู', level: 2, color: 'green' },
            viewer: { name: 'ผู้ชม', level: 1, color: 'purple' }
        };

        // กำหนดประเภท permissions
        this.permissions = ['view', 'create', 'edit', 'delete'];

        // ข้อมูลผู้ใช้และสิทธิ์
        this.users = [];
        this.userPermissions = {};
        this.currentUser = null;

        this.init();
    }

    init() {
        this.loadUsers();
        this.renderDepartments();
        this.bindEvents();
        this.setupSelect2();
    }

    // โหลดข้อมูลผู้ใช้
    async loadUsers() {
        try {
            const response = await fetch('api/UserController.php?action=getAll');
            const data = await response.json();
            if (data.success) {
                this.users = data.data.users || [];
                this.populateUserSelect();
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    // เติมข้อมูลใน select box ของผู้ใช้
    populateUserSelect() {
        const userSelect = document.getElementById('userSelect');
        userSelect.innerHTML = '<option value="">เลือกผู้ใช้งาน...</option>';
        
        this.users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.user_id;
            option.textContent = `${user.username} (${user.email})`;
            userSelect.appendChild(option);
        });
    }

    // สร้างการแสดงผลฝ่ายงาน
    renderDepartments() {
        const container = document.getElementById('departmentsGrid');
        container.innerHTML = '';

        Object.keys(this.departments).forEach(deptKey => {
            const dept = this.departments[deptKey];
            const card = this.createDepartmentCard(deptKey, dept);
            container.appendChild(card);
        });
    }

    // สร้างการ์ดฝ่ายงาน
    createDepartmentCard(deptKey, dept) {
        const card = document.createElement('div');
        card.className = `department-card bg-white dark:bg-gray-800 p-6 shadow-lg`;
        
        card.innerHTML = `
            <div class="gradient-bg-${dept.color} rounded-lg p-4 mb-4">
                <div class="flex items-center text-white">
                    <i class="fas ${dept.icon} text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-xl font-bold">${dept.name}</h3>
                        <p class="text-sm opacity-90">${dept.description}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <!-- User Role Assignment -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-800 dark:text-white">
                            <i class="fas fa-user-tag mr-2"></i>บทบาทในฝ่าย
                        </h4>
                        <button class="assign-role-btn text-blue-500 hover:text-blue-700" 
                                data-department="${deptKey}">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                    </div>
                    <div id="userRole_${deptKey}" class="text-sm text-gray-600 dark:text-gray-300">
                        เลือกผู้ใช้เพื่อกำหนดบทบาท
                    </div>
                </div>

                <!-- Modules -->
                <div class="space-y-2">
                    <h4 class="font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fas fa-puzzle-piece mr-2"></i>โมดูล
                        <button class="toggle-modules ml-auto text-gray-500 hover:text-gray-700" 
                                data-department="${deptKey}">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </h4>
                    <div id="modules_${deptKey}" class="module-content space-y-3">
                        ${this.renderModules(deptKey, dept.modules)}
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    // สร้างการแสดงผลโมดูล
    renderModules(deptKey, modules) {
        return Object.keys(modules).map(moduleKey => {
            const module = modules[moduleKey];
            return `
                <div class="bg-white dark:bg-gray-800 border rounded-lg p-3">
                    <div class="flex justify-between items-center mb-2">
                        <h5 class="font-medium text-gray-700 dark:text-gray-300">
                            <i class="fas ${module.icon} mr-2 text-gray-500"></i>
                            ${module.name}
                        </h5>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        ${this.permissions.map(permission => `
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" 
                                       class="permission-checkbox rounded" 
                                       data-department="${deptKey}"
                                       data-module="${moduleKey}"
                                       data-permission="${permission}">
                                <span class="text-gray-600 dark:text-gray-400">
                                    ${this.getPermissionLabel(permission)}
                                </span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        }).join('');
    }

    // แปลชื่อ permission
    getPermissionLabel(permission) {
        const labels = {
            view: 'ดู',
            create: 'สร้าง',
            edit: 'แก้ไข',
            delete: 'ลบ'
        };
        return labels[permission] || permission;
    }

    // ผูกเหตุการณ์
    bindEvents() {
        // เลือกผู้ใช้
        document.getElementById('userSelect').addEventListener('change', (e) => {
            this.selectUser(e.target.value);
        });

        // กรองฝ่าย
        document.getElementById('departmentFilter').addEventListener('change', (e) => {
            this.filterDepartments(e.target.value);
        });

        // Toggle โมดูล
        document.addEventListener('click', (e) => {
            if (e.target.closest('.toggle-modules')) {
                const dept = e.target.closest('.toggle-modules').dataset.department;
                this.toggleModules(dept);
            }
        });

        // กำหนดบทบาท
        document.addEventListener('click', (e) => {
            if (e.target.closest('.assign-role-btn')) {
                const dept = e.target.closest('.assign-role-btn').dataset.department;
                this.showRoleModal(dept);
            }
        });

        // Modal events
        document.getElementById('closeRoleModal').addEventListener('click', () => {
            this.hideRoleModal();
        });

        document.getElementById('cancelRole').addEventListener('click', () => {
            this.hideRoleModal();
        });

        document.getElementById('saveRole').addEventListener('click', () => {
            this.saveUserRole();
        });

        // Permission checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('permission-checkbox')) {
                this.updatePermission(e.target);
            }
        });

        // บันทึกสิทธิ์ทั้งหมด
        document.getElementById('saveAllPermissions').addEventListener('click', () => {
            this.saveAllPermissions();
        });

        // รีเฟรช
        document.getElementById('refreshPermissions').addEventListener('click', () => {
            this.refreshData();
        });
    }

    // ตั้งค่า Select2
    setupSelect2() {
        $('#userSelect').select2({
            placeholder: 'เลือกผู้ใช้งาน...',
            allowClear: true,
            width: '100%'
        });
    }

    // เลือกผู้ใช้
    selectUser(userId) {
        if (!userId) {
            this.currentUser = null;
            this.clearUserPermissions();
            return;
        }

        this.currentUser = this.users.find(u => u.user_id == userId);
        if (this.currentUser) {
            this.loadUserPermissions(userId);
        }
    }

    // โหลดสิทธิ์ของผู้ใช้
    async loadUserPermissions(userId) {
        try {
            const response = await fetch(`api/RoleController.php?action=getUserPermissions&user_id=${userId}`);
            const data = await response.json();
            if (data.success) {
                this.userPermissions = data.data || {};
                this.updatePermissionDisplay();
            }
        } catch (error) {
            console.error('Error loading user permissions:', error);
        }
    }

    // อัพเดทการแสดงผลสิทธิ์
    updatePermissionDisplay() {
        // อัพเดทบทบาทในแต่ละฝ่าย
        Object.keys(this.departments).forEach(deptKey => {
            const roleElement = document.getElementById(`userRole_${deptKey}`);
            const userRole = this.userPermissions.roles?.[deptKey];
            
            if (userRole && this.roles[userRole]) {
                const role = this.roles[userRole];
                roleElement.innerHTML = `
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium role-level-${role.level}">
                        ${role.name}
                    </span>
                `;
            } else {
                roleElement.innerHTML = '<span class="text-gray-500">ไม่มีบทบาท</span>';
            }
        });

        // อัพเดท permission checkboxes
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            const dept = checkbox.dataset.department;
            const module = checkbox.dataset.module;
            const permission = checkbox.dataset.permission;
            
            checkbox.checked = this.hasPermission(dept, module, permission);
        });
    }

    // ตรวจสอบสิทธิ์
    hasPermission(department, module, permission) {
        if (!this.currentUser || !this.userPermissions.roles) return false;

        const userRole = this.userPermissions.roles[department];
        if (!userRole) return false;

        // ตรวจสอบ explicit permission ก่อน
        const explicitPerms = this.userPermissions.permissions;
        if (explicitPerms?.[department]?.[module]?.[permission]) {
            return true;
        }

        // ตรวจสอบตาม role level
        return this.hasRolePermission(userRole, department, module, permission);
    }

    // ตรวจสอบสิทธิ์ตาม role
    hasRolePermission(role, department, module, permission) {
        const roleLevel = this.roles[role]?.level || 0;
        
        // กำหนดสิทธิ์ตาม role level
        const rolePermissions = {
            admin: { view: true, create: true, edit: true, delete: true },
            head: { view: true, create: true, edit: true, delete: false },
            officer: { view: true, create: true, edit: false, delete: false },
            teacher: { view: true, create: false, edit: false, delete: false },
            viewer: { view: true, create: false, edit: false, delete: false }
        };

        return rolePermissions[role]?.[permission] || false;
    }

    // แสดง/ซ่อนโมดูล
    toggleModules(department) {
        const moduleContent = document.getElementById(`modules_${department}`);
        const toggleBtn = document.querySelector(`[data-department="${department}"] i`);
        
        if (moduleContent.classList.contains('expanded')) {
            moduleContent.classList.remove('expanded');
            toggleBtn.className = 'fas fa-chevron-down';
        } else {
            moduleContent.classList.add('expanded');
            toggleBtn.className = 'fas fa-chevron-up';
        }
    }

    // แสดง modal กำหนดบทบาท
    showRoleModal(department) {
        if (!this.currentUser) {
            Swal.fire('กรุณาเลือกผู้ใช้ก่อน', '', 'warning');
            return;
        }

        const dept = this.departments[department];
        const currentRole = this.userPermissions.roles?.[department] || '';

        document.getElementById('modalDepartment').value = dept.name;
        document.getElementById('modalUser').value = `${this.currentUser.username} (${this.currentUser.email})`;
        document.getElementById('modalRole').value = currentRole;
        
        document.getElementById('roleModal').dataset.department = department;
        document.getElementById('roleModal').classList.remove('hidden');
    }

    // ซ่อน modal
    hideRoleModal() {
        document.getElementById('roleModal').classList.add('hidden');
    }

    // บันทึกบทบาทผู้ใช้
    async saveUserRole() {
        const department = document.getElementById('roleModal').dataset.department;
        const role = document.getElementById('modalRole').value;

        if (!this.currentUser) return;

        try {
            const response = await fetch('api/RoleController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'setUserRole',
                    user_id: this.currentUser.user_id,
                    department: department,
                    role: role
                })
            });

            const data = await response.json();
            if (data.success) {
                // อัพเดทข้อมูลท้องถิ่น
                if (!this.userPermissions.roles) this.userPermissions.roles = {};
                if (role) {
                    this.userPermissions.roles[department] = role;
                } else {
                    delete this.userPermissions.roles[department];
                }

                this.updatePermissionDisplay();
                this.hideRoleModal();
                
                Swal.fire('สำเร็จ', 'บันทึกบทบาทเรียบร้อยแล้ว', 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error saving role:', error);
            Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
        }
    }

    // อัพเดทสิทธิ์
    updatePermission(checkbox) {
        if (!this.currentUser) return;

        const { department, module, permission } = checkbox.dataset;
        const hasPermission = checkbox.checked;

        // อัพเดทข้อมูลท้องถิ่น
        if (!this.userPermissions.permissions) this.userPermissions.permissions = {};
        if (!this.userPermissions.permissions[department]) this.userPermissions.permissions[department] = {};
        if (!this.userPermissions.permissions[department][module]) this.userPermissions.permissions[department][module] = {};

        this.userPermissions.permissions[department][module][permission] = hasPermission;
    }

    // บันทึกสิทธิ์ทั้งหมด
    async saveAllPermissions() {
        if (!this.currentUser) {
            Swal.fire('กรุณาเลือกผู้ใช้ก่อน', '', 'warning');
            return;
        }

        try {
            const response = await fetch('api/RoleController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'saveUserPermissions',
                    user_id: this.currentUser.user_id,
                    permissions: this.userPermissions
                })
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire('สำเร็จ', 'บันทึกสิทธิ์ทั้งหมดเรียบร้อยแล้ว', 'success');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error saving permissions:', error);
            Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
        }
    }

    // กรองฝ่าย
    filterDepartments(filter) {
        const cards = document.querySelectorAll('.department-card');
        cards.forEach(card => {
            if (!filter || card.innerHTML.includes(filter)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // ล้างข้อมูลสิทธิ์ผู้ใช้
    clearUserPermissions() {
        this.userPermissions = {};
        this.updatePermissionDisplay();
    }

    // รีเฟรชข้อมูล
    async refreshData() {
        await this.loadUsers();
        if (this.currentUser) {
            await this.loadUserPermissions(this.currentUser.user_id);
        }
        Swal.fire('สำเร็จ', 'รีเฟรชข้อมูลเรียบร้อยแล้ว', 'success');
    }

    // ===== Utility Functions for Permission Checking =====
    
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์ในการทำงานใดงานหนึ่งหรือไม่
     * @param {number} userId - ID ของผู้ใช้
     * @param {string} department - ฝ่ายงาน (academic, budget, personnel, general)
     * @param {string} module - โมดูล
     * @param {string} action - การกระทำ (view, create, edit, delete)
     * @returns {boolean}
     */
    static async checkUserPermission(userId, department, module, action) {
        try {
            const response = await fetch(`api/RoleController.php?action=checkPermission&user_id=${userId}&department=${department}&module=${module}&action=${action}`);
            const data = await response.json();
            return data.success && data.hasPermission;
        } catch (error) {
            console.error('Error checking permission:', error);
            return false;
        }
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีบทบาทในฝ่ายใดฝ่ายหนึ่งหรือไม่
     * @param {number} userId - ID ของผู้ใช้
     * @param {string} department - ฝ่ายงาน
     * @param {string} minRole - บทบาทขั้นต่ำที่ต้องการ
     * @returns {boolean}
     */
    static async checkUserRole(userId, department, minRole = 'viewer') {
        try {
            const response = await fetch(`api/RoleController.php?action=checkRole&user_id=${userId}&department=${department}&min_role=${minRole}`);
            const data = await response.json();
            return data.success && data.hasRole;
        } catch (error) {
            console.error('Error checking role:', error);
            return false;
        }
    }
}

// เริ่มต้นระบบเมื่อ DOM โหลดเสร็จ
$(document).ready(() => {
    window.roleManager = new RoleManagementSystem();
});

// Export functions for global use
window.RoleManager = {
    checkPermission: RoleManagementSystem.checkUserPermission,
    checkRole: RoleManagementSystem.checkUserRole
};
