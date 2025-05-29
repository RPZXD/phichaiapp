// User Management JavaScript with Enhanced Multi-Role Support
class UserManager {
    constructor() {
        this.table = null;
        this.editingUserId = null;
        this.currentChart = null;
        this.statisticsData = {};
        this.selectedBulkUsers = new Set();
        this.departments = [];
        this.roles = [];
        this.currentUserStatus = true;
        this.init();
    }

    init() {
        this.loadInitialData();
        this.initDataTable();
        this.bindEvents();
        this.loadUsers();
        this.loadStatistics();
        this.loadUserRoleChart();
        this.initAnimations();
    }

    // Load initial data (departments, roles)
    async loadInitialData() {
        try {
            // Load departments
            const deptResponse = await fetch('api/UserController.php?action=getDepartments');
            const deptResult = await deptResponse.json();
            if (deptResult.success) {
                this.departments = deptResult.data;
                this.populateDepartmentFilters();
            }

            // Load roles
            const rolesResponse = await fetch('api/UserController.php?action=getRoles');
            const rolesResult = await rolesResponse.json();
            if (rolesResult.success) {
                this.roles = rolesResult.data;
                this.populateRoleFilters();
                this.populateRoleCheckboxes();
            }
        } catch (error) {
            console.error('Error loading initial data:', error);
        }
    }

    // Populate department filters
    populateDepartmentFilters() {
        const departmentFilter = $('#departmentFilter');
        const departmentSelect = $('#department');
        
        // Clear existing options (except first)
        departmentFilter.find('option:not(:first)').remove();
        departmentSelect.find('option:not(:first)').remove();
        
        this.departments.forEach(dept => {
            const option = `<option value="${dept.id}">${dept.name}</option>`;
            departmentFilter.append(option);
            departmentSelect.append(option);
        });
    }

    // Populate role filters
    populateRoleFilters() {
        const roleFilter = $('#roleFilter');
        
        // Clear existing options (except first)
        roleFilter.find('option:not(:first)').remove();
        
        this.roles.forEach(role => {
            const option = `<option value="${role.id}">${role.display_name}</option>`;
            roleFilter.append(option);
        });
    }

    // Populate role checkboxes in form
    populateRoleCheckboxes() {
        const rolesContainer = $('#rolesContainer');
        rolesContainer.empty();
        
        this.roles.forEach(role => {
            const roleItem = `
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors role-label">
                    <input type="checkbox" value="${role.id}" name="userRoles" class="role-checkbox mr-3 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white">${role.display_name}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">${role.description}</div>
                    </div>
                </label>
            `;
            rolesContainer.append(roleItem);
        });
    }

    // Toggle user status in form
    toggleStatus() {
        const toggleSwitch = $('.toggle-switch');
        const statusText = $('#statusText');
        const isActiveCheckbox = $('#is_active');
        
        this.currentUserStatus = !this.currentUserStatus;
        
        if (this.currentUserStatus) {
            toggleSwitch.addClass('active');
            statusText.text('ใช้งาน').removeClass('text-red-600').addClass('text-green-600');
            isActiveCheckbox.prop('checked', true);
        } else {
            toggleSwitch.removeClass('active');
            statusText.text('ไม่ใช้งาน').removeClass('text-green-600').addClass('text-red-600');
            isActiveCheckbox.prop('checked', false);
        }
    }

    // Initialize animations and UI effects
    initAnimations() {
        // Animate statistics cards on load
        const cards = document.querySelectorAll('.stats-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('animate-fade-in');
            }, index * 100);
        });

        // Add hover effects to buttons
        document.querySelectorAll('.btn-hover').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.classList.add('animate-scale-up');
            });
            btn.addEventListener('mouseleave', function() {
                this.classList.remove('animate-scale-up');
            });
        });
    }

    // Load and display statistics
    async loadStatistics() {
        try {
            const response = await fetch('api/UserController.php?action=getStatistics');
            const result = await response.json();
            
            if (result.success) {
                this.updateStatisticsCards(result.data);
                this.statisticsData = result.data;
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
            // Set default values if API fails
            this.updateStatisticsCards({
                total_users: 0,
                active_users: 0,
                admin_count: 0,
                new_users_today: 0
            });
        }
    }

    // Update statistics cards with animation
    updateStatisticsCards(data) {
        // Update each statistic with animation
        this.animateCounter(document.getElementById('totalUsers'), data.total_users || 0);
        this.animateCounter(document.getElementById('activeUsers'), data.active_users || 0);
        this.animateCounter(document.getElementById('adminCount'), data.admin_count || 0);
        this.animateCounter(document.getElementById('newUsersToday'), data.new_users_today || 0);
    }

    // Animate counter numbers
    animateCounter(element, target) {
        const duration = 1000;
        const start = 0;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(start + (target - start) * progress);
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    // Load user role distribution chart
    async loadUserRoleChart() {
        try {
            const response = await fetch('api/UserController.php?action=getRoleDistribution');
            const result = await response.json();
            
            if (result.success) {
                this.createRoleChart(result.data);
            }
        } catch (error) {
            console.error('Error loading role distribution:', error);
        }
    }

    // Create role distribution chart
    createRoleChart(data) {
        const ctx = document.getElementById('roleChart');
        if (!ctx) return;

        if (this.currentChart) {
            this.currentChart.destroy();
        }

        this.currentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.role),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgb(75, 85, 99)',
                            font: {
                                family: 'Kanit',
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    }    initDataTable() {
        this.table = $('#usersTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: 'api/UserController.php',
                type: 'GET',
                data: function(d) {
                    return {
                        action: 'getAll',
                        page: Math.floor(d.start / d.length) + 1,
                        limit: d.length,
                        search: d.search.value,
                        role: $('#roleFilter').val() || '',
                        department: $('#departmentFilter').val() || '',
                        status: $('#statusFilter').val() || ''
                    };
                },
                dataSrc: function(json) {
                    if (json.success) {
                        // Update pagination info for DataTables
                        json.recordsTotal = json.data.pagination.total_records;
                        json.recordsFiltered = json.data.pagination.total_records;
                        return json.data.users;
                    } else {
                        userManager.showError(json.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
                        return [];
                    }
                }
            },
            language: {
                "sProcessing": "กำลังดำเนินการ...",
                "sLengthMenu": "แสดง _MENU_ รายการ",
                "sZeroRecords": "ไม่พบข้อมูลที่ตรงกับเงื่อนไข",
                "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)",
                "sSearch": "ค้นหา:",
                "oPaginate": {
                    "sFirst": "หน้าแรก",
                    "sPrevious": "ก่อนหน้า",
                    "sNext": "ถัดไป",
                    "sLast": "หน้าสุดท้าย"
                }
            },
            columns: [
                { data: 'user_id', title: 'ID' },
                { data: 'username', title: 'ชื่อผู้ใช้' },
                { data: 'email', title: 'อีเมล' },
                { 
                    data: 'roles', 
                    title: 'บทบาท',
                    render: (data) => this.formatRoles(data)
                },
                { 
                    data: 'department_name', 
                    title: 'แผนก',
                    render: (data) => data || '-'
                },
                { 
                    data: 'employee_id', 
                    title: 'รหัสพนักงาน',
                    render: (data) => data || '-'
                },
                { data: 'phone_number', title: 'เบอร์โทรศัพท์' },
                { 
                    data: 'is_active', 
                    title: 'สถานะ',
                    render: (data) => this.formatStatus(data)
                },
                { 
                    data: 'created_at', 
                    title: 'วันที่สร้าง',
                    render: (data) => this.formatDate(data)
                },
                { 
                    data: null, 
                    title: 'การจัดการ',
                    orderable: false,
                    render: (data, type, row) => this.renderActions(row)
                }
            ],
            order: [[0, 'ASC']],
            ordering: true,
        });
    }    bindEvents() {
        // Add user button
        $('#addUserBtn').on('click', () => this.showAddUserModal());
        
        // Save user button
        $('#saveUserBtn').on('click', () => this.saveUser());
        
        // Refresh button
        $('#refreshBtn').on('click', () => this.loadUsers());
        
        // Filters
        $('#roleFilter').on('change', () => this.applyFilters());
        $('#departmentFilter').on('change', () => this.applyFilters());
        $('#statusFilter').on('change', () => this.applyFilters());
        
        // Form submit
        $('#userForm').on('submit', (e) => {
            e.preventDefault();
            this.saveUser();
        });

        // Close modal on outside click
        $('#userModal').on('click', (e) => {
            if (e.target === e.currentTarget) {
                this.hideUserModal();
            }
        });
    }async loadUsers() {
        // With server-side processing, DataTables handles data loading
        // We just need to trigger a reload
        if (this.table) {
            this.table.ajax.reload(null, false);
        }
    }    showAddUserModal() {
        this.editingUserId = null;
        $('#modalTitle').text('เพิ่มผู้ใช้งานใหม่');
        $('#userForm')[0].reset();
        $('#passwordField').show();
        $('#password').prop('required', true);
        $('#statusField').addClass('hidden');
        
        // Reset role checkboxes
        $('input[name="userRoles"]').prop('checked', false);
        
        // Reset toggle switch
        this.currentUserStatus = true;
        $('.toggle-switch').addClass('active');
        $('#statusText').text('ใช้งาน').removeClass('text-red-600').addClass('text-green-600');
        $('#is_active').prop('checked', true);
        
        this.showUserModal();
    }

    showEditUserModal(userId) {
        this.editingUserId = userId;
        $('#modalTitle').text('แก้ไขข้อมูลผู้ใช้งาน');
        $('#passwordField').hide();
        $('#password').prop('required', false);
        $('#statusField').removeClass('hidden');
        
        // Load user data
        this.loadUserData(userId);
        this.showUserModal();
    }

    async loadUserData(userId) {
        try {
            const response = await fetch(`api/UserController.php?action=get&id=${userId}`);
            const result = await response.json();
            
            if (result.success) {
                const user = result.data;
                $('#username').val(user.username);
                $('#email').val(user.email);
                $('#employee_id').val(user.employee_id || '');
                $('#department').val(user.department_id || '');
                $('#phone_number').val(user.phone_number || '');
                
                // Set user status
                this.currentUserStatus = user.is_active == 1;
                if (this.currentUserStatus) {
                    $('.toggle-switch').addClass('active');
                    $('#statusText').text('ใช้งาน').removeClass('text-red-600').addClass('text-green-600');
                } else {
                    $('.toggle-switch').removeClass('active');
                    $('#statusText').text('ไม่ใช้งาน').removeClass('text-green-600').addClass('text-red-600');
                }
                $('#is_active').prop('checked', this.currentUserStatus);
                
                // Load and set user roles
                await this.loadUserRoles(userId);
                
            } else {
                this.showError(result.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูลผู้ใช้');
            }
        } catch (error) {
            console.error('Error loading user data:', error);
            this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
    }

    async loadUserRoles(userId) {
        try {
            const response = await fetch(`api/UserController.php?action=getUserRoles&user_id=${userId}`);
            const result = await response.json();
            
            if (result.success) {
                // Reset all checkboxes
                $('input[name="userRoles"]').prop('checked', false);
                
                // Check the user's roles
                result.data.forEach(roleId => {
                    $(`input[name="userRoles"][value="${roleId}"]`).prop('checked', true);
                });
            }
        } catch (error) {
            console.error('Error loading user roles:', error);
        }
    }

    showUserModal() {
        $('#userModal').removeClass('hidden');
    }

    hideUserModal() {
        $('#userModal').addClass('hidden');
        $('#userForm')[0].reset();
        this.editingUserId = null;
    }    async saveUser() {
        const form = $('#userForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Validate role selection
        const selectedRoles = $('input[name="userRoles"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedRoles.length === 0) {
            this.showError('กรุณาเลือกบทบาทอย่างน้อย 1 บทบาท');
            return;
        }

        const formData = new FormData();
        const action = this.editingUserId ? 'update' : 'create';
        
        formData.append('action', action);
        formData.append('username', $('#username').val());
        formData.append('email', $('#email').val());
        formData.append('employee_id', $('#employee_id').val());
        formData.append('department_id', $('#department').val());
        formData.append('phone_number', $('#phone_number').val());
        formData.append('roles', JSON.stringify(selectedRoles));
        
        if (action === 'create') {
            formData.append('password', $('#password').val());
        } else {
            formData.append('user_id', this.editingUserId);
            formData.append('is_active', this.currentUserStatus ? 1 : 0);
        }

        try {
            this.showLoading();
            const response = await fetch('api/UserController.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
                this.hideUserModal();
                this.loadUsers();
                this.loadStatistics(); // Reload statistics after user changes
            } else {
                this.showError(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            this.hideLoading();
        }
    }

    async deleteUser(userId, username) {
        const result = await Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: `ต้องการลบผู้ใช้งาน "${username}" หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            try {
                this.showLoading();
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('user_id', userId);

                const response = await fetch('api/UserController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const deleteResult = await response.json();
                
                if (deleteResult.success) {
                    this.showSuccess('ลบผู้ใช้งานสำเร็จ');
                    this.loadUsers();
                    this.loadStatistics(); // Reload statistics after user deletion
                } else {
                    this.showError(deleteResult.message || 'เกิดข้อผิดพลาดในการลบผู้ใช้งาน');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            } finally {
                this.hideLoading();
            }
        }
    }    // Apply all filters
    applyFilters() {
        // With server-side processing, we reload the table data
        this.table.ajax.reload();
    }

    // Format multiple roles for display
    formatRoles(roles) {
        if (!roles || roles.length === 0) {
            return '<span class="text-gray-500">ไม่มีบทบาท</span>';
        }
        
        return roles.map(role => {
            const colors = this.getRoleColor(role.name);
            return `<span class="inline-block px-2 py-1 text-xs rounded-full mr-1 mb-1 ${colors}">${role.display_name}</span>`;
        }).join('');
    }

    // Get role color for badges
    getRoleColor(roleName) {
        const colorMap = {
            'admin': 'bg-red-100 text-red-800',
            'director': 'bg-purple-100 text-purple-800',
            'vp': 'bg-indigo-100 text-indigo-800',
            'hod': 'bg-blue-100 text-blue-800',
            'teacher': 'bg-green-100 text-green-800',
            'officer': 'bg-yellow-100 text-yellow-800',
            'student': 'bg-gray-100 text-gray-800',
            'parent': 'bg-pink-100 text-pink-800'
        };
        return colorMap[roleName] || 'bg-gray-100 text-gray-800';
    }

    // Format user status
    formatStatus(isActive) {
        if (isActive == 1) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><span class="w-2 h-2 mr-1 bg-green-400 rounded-full"></span>ใช้งาน</span>';
        } else {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><span class="w-2 h-2 mr-1 bg-red-400 rounded-full"></span>ไม่ใช้งาน</span>';
        }
    }

    formatRole(role) {
        const roleMap = {
            'student': 'นักเรียน',
            'teacher': 'ครู',
            'admin': 'ผู้ดูแลระบบ',
            'officer': 'เจ้าหน้าที่',
            'director': 'ผู้อำนวยการ',
            'vp': 'รองผู้อำนวยการ',
            'hod': 'หัวหน้าแผนก',
            'parent': 'ผู้ปกครอง'
        };
        return roleMap[role] || role;
    }

    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }    renderActions(row) {
        const hasPermissionManagement = row.roles && row.roles.some(role => 
            ['teacher', 'director', 'vp', 'hod', 'officer', 'admin'].includes(role.name)
        );
        
        const statusAction = row.is_active == 1 ? 
            `<button onclick="userManager.toggleUserStatus(${row.user_id}, 0, '${row.username}')" 
                    class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded text-sm transition transform hover:scale-105"
                    title="ปิดการใช้งาน">
                <i class="fas fa-user-slash"></i> ปิด
            </button>` :
            `<button onclick="userManager.toggleUserStatus(${row.user_id}, 1, '${row.username}')" 
                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition transform hover:scale-105"
                    title="เปิดการใช้งาน">
                <i class="fas fa-user-check"></i> เปิด
            </button>`;
        
        return `
            <div class="flex gap-2 justify-center flex-wrap">
                <button onclick="userManager.showEditUserModal(${row.user_id})" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm transition transform hover:scale-105"
                        title="แก้ไขข้อมูลผู้ใช้">
                    <i class="fas fa-edit"></i> แก้ไข
                </button>
                ${hasPermissionManagement ? `
                <button onclick="userManager.showPermissionModal(${row.user_id}, '${row.username}', '${row.roles ? row.roles.map(r => r.name).join(',') : ''}')" 
                        class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition transform hover:scale-105"
                        title="จัดการสิทธิ์">
                    <i class="fas fa-user-shield"></i> สิทธิ์
                </button>
                ` : ''}
                ${statusAction}
                <button onclick="userManager.showResetPasswordModal(${row.user_id}, '${row.username}')" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition transform hover:scale-105"
                        title="รีเซ็ตรหัสผ่าน">
                    <i class="fas fa-key"></i> รีเซ็ต
                </button>
                <button onclick="userManager.deleteUser(${row.user_id}, '${row.username}')" 
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition transform hover:scale-105"
                        title="ลบผู้ใช้">
                    <i class="fas fa-trash"></i> ลบ
                </button>
            </div>
        `;
    }

    // Toggle user status (activate/deactivate)
    async toggleUserStatus(userId, status, username) {
        const action = status == 1 ? 'เปิดการใช้งาน' : 'ปิดการใช้งาน';
        const result = await Swal.fire({
            title: `${action}ผู้ใช้งาน`,
            text: `ต้องการ${action}ผู้ใช้งาน "${username}" หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status == 1 ? '#10b981' : '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: action,
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            try {
                this.showLoading();
                const formData = new FormData();
                formData.append('action', status == 1 ? 'activateUser' : 'deactivateUser');
                formData.append('user_id', userId);

                const response = await fetch('api/UserController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const toggleResult = await response.json();
                
                if (toggleResult.success) {
                    this.showSuccess(toggleResult.message);
                    this.loadUsers();
                    this.loadStatistics();
                } else {
                    this.showError(toggleResult.message || `เกิดข้อผิดพลาดในการ${action}ผู้ใช้งาน`);
                }
            } catch (error) {
                console.error('Error toggling user status:', error);
                this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            } finally {
                this.hideLoading();
            }
        }
    }

    showLoading() {
        Swal.fire({
            title: 'กำลังดำเนินการ...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    hideLoading() {
        Swal.close();
    }

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    }    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: message
        });
    }    // Reset Password Functions
    showResetPasswordModal(userId, username) {
        this.resetUserId = userId;
        $('#resetUsername').text(username);
        
        // Reset form state before showing
        $('.radio-option').removeClass('selected');
        $('input[name="resetType"][value="force_change"]').prop('checked', true);
        $('.radio-option[data-value="force_change"]').addClass('selected');
        $('#customPassword').val('');
        $('#showCustomPassword').prop('checked', false);
        $('#customPasswordField').addClass('hidden').removeClass('show');
        $('#customPassword').attr('required', false).attr('type', 'password');
        
        this.setupResetPasswordModal();
        $('#resetPasswordModal').removeClass('hidden');
    }hideResetPasswordModal() {
        $('#resetPasswordModal').addClass('hidden');
        this.resetUserId = null;
        
        // Reset form and selections
        $('.radio-option').removeClass('selected');
        $('input[name="resetType"][value="force_change"]').prop('checked', true);
        $('.radio-option[data-value="force_change"]').addClass('selected');
        $('#customPassword').val('');
        $('#showCustomPassword').prop('checked', false);
        $('#customPasswordField').addClass('hidden').removeClass('show');
        $('#customPassword').attr('required', false).attr('type', 'password');
    }setupResetPasswordModal() {
        // Handle radio button changes (both actual radio and clicking on labels)
        $('.radio-option').off('click').on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            const value = $this.data('value');
            const $radioInput = $this.find('input[type="radio"]');
            
            // Uncheck all radio buttons and remove visual selection
            $('input[name="resetType"]').prop('checked', false);
            $('.radio-option').removeClass('selected');
            
            // Check the clicked radio button and add visual selection
            $radioInput.prop('checked', true);
            $this.addClass('selected');
            
            // Handle custom password field visibility
            const customField = $('#customPasswordField');
            if (value === 'custom') {
                customField.removeClass('hidden').addClass('show');
                $('#customPassword').attr('required', true).focus();
            } else {
                customField.addClass('hidden').removeClass('show');
                $('#customPassword').attr('required', false).val('');
            }
            
            // Trigger change event for other handlers
            $radioInput.trigger('change');
        });
        
        // Handle actual radio button changes (for keyboard navigation)
        $('input[name="resetType"]').off('change').on('change', function() {
            const $this = $(this);
            const value = $this.val();
            
            // Remove selection from all options
            $('.radio-option').removeClass('selected');
            
            // Add selection to current option
            $this.closest('.radio-option').addClass('selected');
            
            // Handle custom password field visibility
            const customField = $('#customPasswordField');
            if (value === 'custom') {
                customField.removeClass('hidden').addClass('show');
                $('#customPassword').attr('required', true);
            } else {
                customField.addClass('hidden').removeClass('show');
                $('#customPassword').attr('required', false).val('');
            }
        });

        // Handle show/hide password checkbox
        $('#showCustomPassword').off('change').on('change', function() {
            const passwordField = $('#customPassword');
            if ($(this).is(':checked')) {
                passwordField.attr('type', 'text');
            } else {
                passwordField.attr('type', 'password');
            }
        });
        
        // Initialize the default selection
        const defaultOption = $('.radio-option').find('input[type="radio"]:checked').closest('.radio-option');
        if (defaultOption.length) {
            defaultOption.addClass('selected');
        }
    }

    async confirmResetPassword() {
        const resetType = $('input[name="resetType"]:checked').val();
        let newPassword = '';

        // Validate based on reset type
        if (resetType === 'custom') {
            newPassword = $('#customPassword').val().trim();
            if (!newPassword || newPassword.length < 6) {
                this.showError('กรุณากรอกรหัสผ่านอย่างน้อย 6 ตัวอักษร');
                return;
            }
        }

        // Show confirmation dialog
        const typeText = this.getResetTypeText(resetType);
        const result = await Swal.fire({
            title: 'ยืนยันการรีเซ็ตรหัสผ่าน',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>ผู้ใช้:</strong> ${$('#resetUsername').text()}</p>
                    <p class="mb-2"><strong>วิธีการรีเซ็ต:</strong> ${typeText}</p>
                    ${resetType === 'custom' ? `<p class="mb-2"><strong>รหัสผ่านใหม่:</strong> ${'•'.repeat(newPassword.length)}</p>` : ''}
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ยืนยันการรีเซ็ต',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            await this.processPasswordReset(resetType, newPassword);
        }
    }

    getResetTypeText(resetType) {
        switch (resetType) {
            case 'force_change':
                return 'บังคับให้เปลี่ยนรหัสผ่านเมื่อเข้าสู่ระบบครั้งถัดไป';
            case 'set_default':
                return 'ตั้งรหัสผ่านเป็น "123456"';
            case 'custom':
                return 'กำหนดรหัสผ่านใหม่เอง';
            default:
                return 'ไม่ระบุ';
        }
    }

    async processPasswordReset(resetType, newPassword = '') {
        this.showLoading();

        try {
            const formData = new FormData();
            formData.append('action', 'resetPassword');
            formData.append('user_id', this.resetUserId);
            formData.append('reset_type', resetType);
            if (resetType === 'custom') {
                formData.append('new_password', newPassword);
            }

            const response = await fetch('api/UserController.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'รีเซ็ตรหัสผ่านสำเร็จ',
                    text: result.message || 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว',
                    timer: 2000,
                    showConfirmButton: false
                });

                this.hideResetPasswordModal();
                this.loadUsers(); // Refresh table if needed
            } else {
                this.showError(result.message || 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน');
            }
        } catch (error) {
            console.error('Reset password error:', error);
            this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
    }

    // Show permission management modal
    showPermissionModal(userId, username, role) {
        // Show confirmation before redirecting
        Swal.fire({
            title: 'จัดการสิทธิ์ผู้ใช้',
            html: `
                <div class="text-left">
                    <p><strong>ผู้ใช้:</strong> ${username}</p>
                    <p><strong>บทบาท:</strong> ${this.getRoleDisplayName(role)}</p>
                    <p class="mt-3">คุณต้องการเปิดหน้าจัดการสิทธิ์หรือไม่?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8B5CF6',
            cancelButtonColor: '#6B7280',
            confirmButtonText: '<i class="fas fa-user-shield"></i> เปิดหน้าจัดการสิทธิ์',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                confirmButton: 'btn btn-purple',
                cancelButton: 'btn btn-gray'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Store selected user data and redirect to roles page
                sessionStorage.setItem('selectedUserForPermission', JSON.stringify({
                    userId: userId,
                    username: username,
                    role: role
                }));
                window.location.href = 'roles.php';
            }
        });
    }

    getRoleDisplayName(role) {
        const roleNames = {
            'admin': 'ผู้ดูแลระบบ',
            'director': 'ผู้อำนวยการ',
            'vp': 'รองผู้อำนวยการ',
            'hod': 'หัวหน้าฝ่าย',
            'officer': 'เจ้าหน้าที่',
            'teacher': 'ครู',
            'student': 'นักเรียน',
            'parent': 'ผู้ปกครอง'
        };
        return roleNames[role] || role;
    }
}

// Initialize when DOM is ready
$(document).ready(() => {
    window.userManager = new UserManager();
});
