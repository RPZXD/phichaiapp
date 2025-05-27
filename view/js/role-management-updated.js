// Role Management System with 4-Department Structure
// Author: ICT Team @Phichai School
// Date: December 2024

class RoleManagementSystem {
    constructor() {
        // กำหนดข้อมูลฝ่ายงาน 4 ฝ่าย
        this.departments = {
            academic: {
                name: 'งานวิชาการ',
                icon: 'fa-graduation-cap',
                color: 'academic',
                description: 'จัดการด้านการเรียนการสอนและหลักสูตร',
                modules: {
                    curriculum: { name: 'หลักสูตร', icon: 'fa-book', permissions: ['view', 'create', 'edit', 'delete'] },
                    schedule: { name: 'ตารางเรียน', icon: 'fa-calendar-alt', permissions: ['view', 'create', 'edit', 'delete'] },
                    assessment: { name: 'การประเมิน', icon: 'fa-chart-line', permissions: ['view', 'create', 'edit', 'delete'] },
                    student_report: { name: 'รายงานนักเรียน', icon: 'fa-file-alt', permissions: ['view', 'create', 'edit'] },
                    teaching_materials: { name: 'สื่อการเรียน', icon: 'fa-laptop', permissions: ['view', 'create', 'edit', 'delete'] },
                    examination: { name: 'การสอบ', icon: 'fa-clipboard-check', permissions: ['view', 'create', 'edit', 'delete'] }
                }
            },
            budget: {
                name: 'งานงบประมาณ',
                icon: 'fa-chart-line',
                color: 'budget',
                description: 'จัดการงบประมาณและการเงิน',
                modules: {
                    budget_plan: { name: 'แผนงบประมาณ', icon: 'fa-chart-pie', permissions: ['view', 'create', 'edit', 'delete'] },
                    expenses: { name: 'รายจ่าย', icon: 'fa-money-bill-wave', permissions: ['view', 'create', 'edit', 'delete'] },
                    procurement: { name: 'จัดซื้อจัดจ้าง', icon: 'fa-shopping-cart', permissions: ['view', 'create', 'edit', 'delete'] },
                    finance_report: { name: 'รายงานการเงิน', icon: 'fa-calculator', permissions: ['view', 'create'] },
                    invoice: { name: 'ใบแจ้งหนี้', icon: 'fa-file-invoice', permissions: ['view', 'create', 'edit'] },
                    payment: { name: 'การจ่ายเงิน', icon: 'fa-credit-card', permissions: ['view', 'create', 'edit'] }
                }
            },
            personnel: {
                name: 'งานบุคคล',
                icon: 'fa-user-friends',
                color: 'personnel',
                description: 'จัดการบุคลากรและทรัพยากรบุคคล',
                modules: {
                    staff_management: { name: 'จัดการบุคลากร', icon: 'fa-user-tie', permissions: ['view', 'create', 'edit', 'delete'] },
                    attendance: { name: 'ลงเวลาทำงาน', icon: 'fa-clock', permissions: ['view', 'create', 'edit'] },
                    leave_management: { name: 'จัดการลาหยุด', icon: 'fa-calendar-times', permissions: ['view', 'create', 'edit', 'delete'] },
                    payroll: { name: 'เงินเดือน', icon: 'fa-dollar-sign', permissions: ['view', 'create', 'edit'] },
                    performance: { name: 'ประเมินผลงาน', icon: 'fa-star', permissions: ['view', 'create', 'edit'] },
                    hr_report: { name: 'รายงานบุคลากร', icon: 'fa-users-cog', permissions: ['view', 'create'] }
                }
            },
            general: {
                name: 'งานทั่วไป',
                icon: 'fa-cogs',
                color: 'general',
                description: 'งานสนับสนุนและบริหารทั่วไป',
                modules: {
                    facility: { name: 'สิ่งอำนวยความสะดวก', icon: 'fa-building', permissions: ['view', 'create', 'edit', 'delete'] },
                    maintenance: { name: 'ซ่อมบำรุง', icon: 'fa-tools', permissions: ['view', 'create', 'edit', 'delete'] },
                    inventory: { name: 'พัสดุ', icon: 'fa-boxes', permissions: ['view', 'create', 'edit', 'delete'] },
                    document: { name: 'เอกสาร', icon: 'fa-folder-open', permissions: ['view', 'create', 'edit', 'delete'] },
                    communication: { name: 'ประชาสัมพันธ์', icon: 'fa-bullhorn', permissions: ['view', 'create', 'edit'] },
                    general_report: { name: 'รายงานทั่วไป', icon: 'fa-clipboard-list', permissions: ['view', 'create'] }
                }
            }
        };

        // กำหนดบทบาทและระดับสิทธิ์ (ระดับสูงกว่าเข้าถึงระดับต่ำกว่าได้)
        this.roles = {
            admin: { name: 'Admin', level: 5, color: 'purple', icon: 'fa-crown' },
            head: { name: 'หัวหน้าฝ่าย', level: 4, color: 'green', icon: 'fa-user-tie' },
            officer: { name: 'เจ้าหน้าที่', level: 3, color: 'blue', icon: 'fa-user-cog' },
            teacher: { name: 'ครู', level: 2, color: 'orange', icon: 'fa-chalkboard-teacher' },
            viewer: { name: 'ผู้ชม', level: 1, color: 'red', icon: 'fa-eye' }
        };

        // ข้อมูลผู้ใช้และสิทธิ์
        this.users = [];
        this.userPermissions = {};
        this.currentUser = null;
        this.selectedUserId = null;

        this.init();
    }

    init() {
        this.loadUsers();
        this.renderDepartments();
        this.bindEvents();
        this.updateStatistics();
    }

    // โหลดข้อมูลผู้ใช้
    async loadUsers() {
        try {
            // สร้างข้อมูลตัวอย่างสำหรับการทดสอบ
            this.users = [
                { id: 1, name: 'ผู้ดูแลระบบ', email: 'admin@phichai.ac.th', status: 'active' },
                { id: 2, name: 'อาจารย์สมชาย', email: 'somchai@phichai.ac.th', status: 'active' },
                { id: 3, name: 'อาจารย์สมหญิง', email: 'somying@phichai.ac.th', status: 'active' },
                { id: 4, name: 'นางสาวจิรา', email: 'jira@phichai.ac.th', status: 'active' },
                { id: 5, name: 'นายสุชาติ', email: 'suchat@phichai.ac.th', status: 'active' }
            ];

            // สร้างข้อมูลสิทธิ์ตัวอย่าง
            this.userPermissions = {
                1: { // Admin
                    academic: { role: 'admin', permissions: {} },
                    budget: { role: 'admin', permissions: {} },
                    personnel: { role: 'admin', permissions: {} },
                    general: { role: 'admin', permissions: {} }
                },
                2: { // Teacher
                    academic: { role: 'teacher', permissions: {} },
                    general: { role: 'viewer', permissions: {} }
                },
                3: { // Head
                    academic: { role: 'head', permissions: {} },
                    personnel: { role: 'officer', permissions: {} }
                }
            };

            this.populateUserSelect();
            this.updateStatistics();
        } catch (error) {
            console.error('Error loading users:', error);
            this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้', 'error');
        }
    }

    // เติมข้อมูลใน select box ของผู้ใช้
    populateUserSelect() {
        const userSelect = document.getElementById('userSelect');
        userSelect.innerHTML = '<option value="">เลือกผู้ใช้งาน...</option>';

        this.users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = `${user.name} (${user.email})`;
            userSelect.appendChild(option);
        });
    }

    // แสดงแผนก
    renderDepartments() {
        const grid = document.getElementById('departmentsGrid');
        grid.innerHTML = '';

        Object.keys(this.departments).forEach(deptKey => {
            const dept = this.departments[deptKey];
            const deptCard = this.createDepartmentCard(deptKey, dept);
            grid.appendChild(deptCard);
        });
    }

    // สร้างการ์ดของแผนก
    createDepartmentCard(deptKey, dept) {
        const card = document.createElement('div');
        card.className = `department-card bg-white dark:bg-gray-800 shadow-xl rounded-xl overflow-hidden card-hover animate-fade-in`;
        
        card.innerHTML = `
            <div class="gradient-bg-${dept.color} p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">
                            <i class="fas ${dept.icon} mr-2"></i>${dept.name}
                        </h3>
                        <p class="text-sm opacity-90">${dept.description}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold" id="${deptKey}UserCount">0</div>
                        <div class="text-sm opacity-75">ผู้ใช้งาน</div>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white">
                        <i class="fas fa-puzzle-piece text-blue-500 mr-2"></i>โมดูลงาน
                    </h4>
                    <span class="text-sm text-gray-500">${Object.keys(dept.modules).length} โมดูล</span>
                </div>
                
                <div class="grid grid-cols-1 gap-3" id="${deptKey}Modules">
                    ${this.renderModules(deptKey, dept.modules)}
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm text-gray-600">บทบาทในฝ่าย:</span>
                            <div id="${deptKey}UserRole" class="font-medium text-gray-800 dark:text-white">
                                ไม่ได้กำหนด
                            </div>
                        </div>
                        <button 
                            onclick="roleManager.openRoleModal('${deptKey}')"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors text-sm"
                            ${!this.selectedUserId ? 'disabled' : ''}
                        >
                            <i class="fas fa-edit mr-1"></i>กำหนดบทบาท
                        </button>
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    // แสดงโมดูลในแต่ละฝ่าย
    renderModules(deptKey, modules) {
        return Object.keys(modules).map(moduleKey => {
            const module = modules[moduleKey];
            return `
                <div class="module-item p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas ${module.icon} text-gray-600 mr-2"></i>
                            <span class="font-medium text-gray-800 dark:text-white">${module.name}</span>
                        </div>
                        <button 
                            onclick="roleManager.openPermissionModal('${deptKey}', '${moduleKey}')"
                            class="text-blue-500 hover:text-blue-700 text-sm"
                            ${!this.selectedUserId ? 'disabled' : ''}
                        >
                            <i class="fas fa-cog mr-1"></i>จัดการสิทธิ์
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    // เปิด modal สำหรับกำหนดบทบาท
    openRoleModal(deptKey) {
        if (!this.selectedUserId) {
            this.showNotification('กรุณาเลือกผู้ใช้งานก่อน', 'warning');
            return;
        }

        const user = this.users.find(u => u.id == this.selectedUserId);
        const dept = this.departments[deptKey];
        
        document.getElementById('modalDepartment').value = dept.name;
        document.getElementById('modalUser').value = user.name;
        
        // ดึงบทบาทปัจจุบัน
        const currentRole = this.userPermissions[this.selectedUserId]?.[deptKey]?.role || '';
        document.getElementById('modalRole').value = currentRole;
        
        // เก็บข้อมูลสำหรับการบันทึก
        document.getElementById('roleModal').dataset.deptKey = deptKey;
        document.getElementById('roleModal').dataset.userId = this.selectedUserId;
        
        document.getElementById('roleModal').classList.remove('hidden');
    }

    // เปิด modal สำหรับจัดการสิทธิ์เฉพาะ
    openPermissionModal(deptKey, moduleKey) {
        if (!this.selectedUserId) {
            this.showNotification('กรุณาเลือกผู้ใช้งานก่อน', 'warning');
            return;
        }

        const user = this.users.find(u => u.id == this.selectedUserId);
        const dept = this.departments[deptKey];
        const module = dept.modules[moduleKey];
        
        document.getElementById('modalTitle').textContent = `จัดการสิทธิ์ - ${dept.name} > ${module.name}`;
        
        // สร้างเนื้อหาสำหรับจัดการสิทธิ์
        const content = document.getElementById('permissionContent');
        content.innerHTML = this.renderPermissionContent(deptKey, moduleKey, module);
        
        // เก็บข้อมูลสำหรับการบันทึก
        document.getElementById('permissionModal').dataset.deptKey = deptKey;
        document.getElementById('permissionModal').dataset.moduleKey = moduleKey;
        document.getElementById('permissionModal').dataset.userId = this.selectedUserId;
        
        document.getElementById('permissionModal').classList.remove('hidden');
    }

    // สร้างเนื้อหาสำหรับจัดการสิทธิ์
    renderPermissionContent(deptKey, moduleKey, module) {
        const userPermissions = this.userPermissions[this.selectedUserId]?.[deptKey]?.permissions?.[moduleKey] || {};
        
        return `
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <h4 class="font-semibold text-gray-800 mb-2">สิทธิ์ที่สามารถกำหนดได้:</h4>
                <div class="grid grid-cols-2 gap-4">
                    ${module.permissions.map(permission => `
                        <label class="flex items-center space-x-2">
                            <input 
                                type="checkbox" 
                                class="permission-checkbox" 
                                data-permission="${permission}"
                                ${userPermissions[permission] ? 'checked' : ''}
                            >
                            <span class="text-sm font-medium">${this.getPermissionLabel(permission)}</span>
                        </label>
                    `).join('')}
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">หมายเหตุ:</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• การเปลี่ยนแปลงสิทธิ์จะมีผลทันทีหลังจากบันทึก</li>
                    <li>• ผู้ที่มีบทบาทสูงกว่าจะได้รับสิทธิ์ของบทบาทต่ำกว่าอัตโนมัติ</li>
                    <li>• สิทธิ์เฉพาะนี้จะเพิ่มเติมจากสิทธิ์ตามบทบาท</li>
                </ul>
            </div>
        `;
    }

    // แปลงชื่อสิทธิ์เป็นภาษาไทย
    getPermissionLabel(permission) {
        const labels = {
            'view': 'ดูข้อมูล',
            'create': 'สร้างข้อมูล',
            'edit': 'แก้ไขข้อมูล',
            'delete': 'ลบข้อมูล'
        };
        return labels[permission] || permission;
    }

    // อัพเดทสถิติ
    updateStatistics() {
        // อัพเดทจำนวนผู้ใช้ทั้งหมด
        document.getElementById('totalUsers').textContent = this.users.length;
        
        // นับจำนวนผู้ใช้ในแต่ละฝ่าย
        const departmentCounts = {};
        
        Object.keys(this.departments).forEach(deptKey => {
            departmentCounts[deptKey] = 0;
        });

        Object.values(this.userPermissions).forEach(userPerms => {
            Object.keys(userPerms).forEach(deptKey => {
                if (departmentCounts.hasOwnProperty(deptKey)) {
                    departmentCounts[deptKey]++;
                }
            });
        });

        // อัพเดทตัวเลขในสถิติ
        document.getElementById('academicCount').textContent = departmentCounts.academic || 0;
        document.getElementById('budgetCount').textContent = departmentCounts.budget || 0;
        document.getElementById('personnelCount').textContent = departmentCounts.personnel || 0;
        document.getElementById('generalCount').textContent = departmentCounts.general || 0;

        // อัพเดทตัวเลขในการ์ดฝ่าย
        Object.keys(this.departments).forEach(deptKey => {
            const countElement = document.getElementById(`${deptKey}UserCount`);
            if (countElement) {
                countElement.textContent = departmentCounts[deptKey] || 0;
            }
        });
    }

    // อัพเดทข้อมูลผู้ใช้ที่เลือก
    updateSelectedUserInfo(userId) {
        this.selectedUserId = userId;
        
        if (!userId) {
            document.getElementById('selectedUserInfo').classList.add('hidden');
            this.clearUserRoleDisplay();
            return;
        }

        const user = this.users.find(u => u.id == userId);
        if (!user) return;

        // แสดงข้อมูลผู้ใช้
        document.getElementById('selectedUserInfo').classList.remove('hidden');
        document.getElementById('userDisplayName').textContent = user.name;
        
        // แสดงบทบาทปัจจุบัน
        const userPerms = this.userPermissions[userId] || {};
        const roles = Object.keys(userPerms).map(deptKey => {
            const dept = this.departments[deptKey];
            const role = this.roles[userPerms[deptKey].role];
            return `${dept.name}: ${role ? role.name : 'ไม่กำหนด'}`;
        });
        
        document.getElementById('userCurrentRoles').textContent = 
            roles.length > 0 ? roles.join(', ') : 'ยังไม่มีบทบาท';

        // อัพเดทการแสดงบทบาทในแต่ละฝ่าย
        this.updateUserRoleDisplay(userId);
    }

    // อัพเดทการแสดงบทบาทในแต่ละฝ่าย
    updateUserRoleDisplay(userId) {
        const userPerms = this.userPermissions[userId] || {};
        
        Object.keys(this.departments).forEach(deptKey => {
            const roleElement = document.getElementById(`${deptKey}UserRole`);
            if (roleElement) {
                const deptRole = userPerms[deptKey]?.role;
                if (deptRole && this.roles[deptRole]) {
                    const role = this.roles[deptRole];
                    roleElement.innerHTML = `
                        <span class="user-role-badge role-level-${role.level}">
                            <i class="fas ${role.icon} mr-1"></i>${role.name}
                        </span>
                    `;
                } else {
                    roleElement.textContent = 'ไม่ได้กำหนด';
                }
            }
        });
    }

    // ล้างการแสดงบทบาท
    clearUserRoleDisplay() {
        Object.keys(this.departments).forEach(deptKey => {
            const roleElement = document.getElementById(`${deptKey}UserRole`);
            if (roleElement) {
                roleElement.textContent = 'ไม่ได้กำหนด';
            }
        });
    }

    // บันทึกบทบาท
    async saveRole() {
        const modal = document.getElementById('roleModal');
        const deptKey = modal.dataset.deptKey;
        const userId = modal.dataset.userId;
        const role = document.getElementById('modalRole').value;

        if (!this.userPermissions[userId]) {
            this.userPermissions[userId] = {};
        }

        if (role) {
            this.userPermissions[userId][deptKey] = {
                role: role,
                permissions: this.userPermissions[userId][deptKey]?.permissions || {}
            };
        } else {
            delete this.userPermissions[userId][deptKey];
        }

        // อัพเดทการแสดงผล
        this.updateSelectedUserInfo(userId);
        this.updateStatistics();
        
        modal.classList.add('hidden');
        this.showNotification('บันทึกบทบาทเรียบร้อยแล้ว', 'success');
    }

    // บันทึกสิทธิ์เฉพาะ
    async savePermissions() {
        const modal = document.getElementById('permissionModal');
        const deptKey = modal.dataset.deptKey;
        const moduleKey = modal.dataset.moduleKey;
        const userId = modal.dataset.userId;

        if (!this.userPermissions[userId]) {
            this.userPermissions[userId] = {};
        }
        if (!this.userPermissions[userId][deptKey]) {
            this.userPermissions[userId][deptKey] = { role: '', permissions: {} };
        }
        if (!this.userPermissions[userId][deptKey].permissions) {
            this.userPermissions[userId][deptKey].permissions = {};
        }

        // รวบรวมสิทธิ์ที่เลือก
        const checkboxes = modal.querySelectorAll('.permission-checkbox');
        const permissions = {};
        
        checkboxes.forEach(checkbox => {
            permissions[checkbox.dataset.permission] = checkbox.checked;
        });

        this.userPermissions[userId][deptKey].permissions[moduleKey] = permissions;
        
        modal.classList.add('hidden');
        this.showNotification('บันทึกสิทธิ์เรียบร้อยแล้ว', 'success');
    }

    // บันทึกสิทธิ์ทั้งหมด
    async saveAllPermissions() {
        try {
            // ส่งข้อมูลไปยัง API
            const response = await fetch('api/RoleController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'saveUserPermissions',
                    permissions: this.userPermissions
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('บันทึกสิทธิ์ทั้งหมดเรียบร้อยแล้ว', 'success');
            } else {
                this.showNotification('เกิดข้อผิดพลาดในการบันทึก', 'error');
            }
        } catch (error) {
            console.error('Error saving permissions:', error);
            this.showNotification('เกิดข้อผิดพลาดในการบันทึก', 'error');
        }
    }

    // รีเฟรชข้อมูล
    async refreshPermissions() {
        this.showNotification('กำลังรีเฟรชข้อมูล...', 'info');
        await this.loadUsers();
        this.renderDepartments();
        this.updateStatistics();
        this.showNotification('รีเฟรชข้อมูลเรียบร้อยแล้ว', 'success');
    }

    // แสดงการแจ้งเตือน
    showNotification(message, type = 'info') {
        // สร้าง notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        
        notification.className += ` ${colors[type]}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info'}-circle mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // แสดง notification
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // ซ่อน notification หลัง 3 วินาที
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // ผูก event listeners
    bindEvents() {
        // เลือกผู้ใช้
        document.getElementById('userSelect').addEventListener('change', (e) => {
            this.updateSelectedUserInfo(e.target.value);
        });

        // กรองตามฝ่าย
        document.getElementById('departmentFilter').addEventListener('change', (e) => {
            this.filterByDepartment(e.target.value);
        });

        // บันทึกสิทธิ์ทั้งหมด
        document.getElementById('saveAllPermissions').addEventListener('click', () => {
            this.saveAllPermissions();
        });

        // รีเฟรช
        document.getElementById('refreshPermissions').addEventListener('click', () => {
            this.refreshPermissions();
        });

        // Modal events
        document.getElementById('closeRoleModal').addEventListener('click', () => {
            document.getElementById('roleModal').classList.add('hidden');
        });

        document.getElementById('closePermissionModal').addEventListener('click', () => {
            document.getElementById('permissionModal').classList.add('hidden');
        });

        document.getElementById('saveRole').addEventListener('click', () => {
            this.saveRole();
        });

        document.getElementById('cancelRole').addEventListener('click', () => {
            document.getElementById('roleModal').classList.add('hidden');
        });

        document.getElementById('savePermissions').addEventListener('click', () => {
            this.savePermissions();
        });

        document.getElementById('cancelPermissions').addEventListener('click', () => {
            document.getElementById('permissionModal').classList.add('hidden');
        });

        // ปิด modal เมื่อคลิกนอก modal
        document.getElementById('roleModal').addEventListener('click', (e) => {
            if (e.target.id === 'roleModal') {
                document.getElementById('roleModal').classList.add('hidden');
            }
        });

        document.getElementById('permissionModal').addEventListener('click', (e) => {
            if (e.target.id === 'permissionModal') {
                document.getElementById('permissionModal').classList.add('hidden');
            }
        });
    }

    // กรองผู้ใช้ตามฝ่าย
    filterByDepartment(deptKey) {
        if (!deptKey) {
            this.populateUserSelect();
            return;
        }

        const userSelect = document.getElementById('userSelect');
        userSelect.innerHTML = '<option value="">เลือกผู้ใช้งาน...</option>';

        this.users.forEach(user => {
            const userPerms = this.userPermissions[user.id];
            if (userPerms && userPerms[deptKey]) {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                userSelect.appendChild(option);
            }
        });
    }

    // ฟังก์ชัน utility สำหรับตรวจสอบสิทธิ์
    static checkPermission(userId, department, module, permission) {
        // Implementation for permission checking
        return true; // placeholder
    }

    static checkRole(userId, department, minLevel) {
        // Implementation for role checking
        return true; // placeholder
    }
}

// สร้าง instance และเริ่มต้นระบบ
let roleManager;
document.addEventListener('DOMContentLoaded', function() {
    roleManager = new RoleManagementSystem();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RoleManagementSystem;
}
