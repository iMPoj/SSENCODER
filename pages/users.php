<div id="usersPage" class="hidden drill-enter">
    <div class="max-w-5xl mx-auto space-y-6 pb-12">

        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#E42278] via-[#c91d68] to-[#9e1350] shadow-xl shadow-[#E42278]/25 p-6 md:p-8">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-pink-100 text-xs font-bold uppercase tracking-widest mb-0.5">Administration</p>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight leading-none">User Management</h1>
                        <p class="text-pink-100 text-sm mt-1">Manage system users, roles, and access.</p>
                    </div>
                </div>
                <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                <button id="addUserBtn" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-[#E42278] font-bold text-sm rounded-xl shadow-lg hover:bg-pink-50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Add New User
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Users Grid -->
        <div id="usersList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="glass-card p-8 col-span-2 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Loading users...
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 id="userModalTitle" class="text-xl font-black text-[#0D111A]">Add New User</h3>
            <button onclick="document.getElementById('userModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="userForm" class="space-y-4">
            <input type="hidden" id="userId">
            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Username</label>
                <input type="text" id="userUsername" class="glass-input !w-full" required placeholder="e.g. john_doe">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Display Name</label>
                <input type="text" id="userDisplayName" class="glass-input !w-full" placeholder="e.g. John Doe">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Password <span id="passwordNote" class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
                <input type="password" id="userPassword" class="glass-input !w-full" placeholder="••••••••" autocomplete="new-password">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Role</label>
                <select id="userRole" class="glass-input !w-full">
                    <option value="encoder">Encoder</option>
                    <option value="admin">Admin</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>
            <!-- Load Cropper.js Library -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

            <div>
                <label class="block text-xs font-bold text-[#6B7280] uppercase tracking-widest mb-1">Profile Photo <span class="text-gray-400 font-normal text-[10px]">(optional)</span></label>
                <input type="file" id="userAvatar" accept="image/*" class="glass-input !w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#E42278]/10 file:text-[#E42278] hover:file:bg-[#E42278]/20 cursor-pointer">
                
                <!-- New Interactive Crop Box -->
                <div id="cropContainer" class="hidden mt-3">
                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2 text-center bg-gray-50 p-1 rounded">Drag & Scroll to Zoom and Center</p>
                    <div class="w-full h-48 bg-gray-100 rounded-xl overflow-hidden relative shadow-inner border border-gray-200">
                        <img id="avatarPreview" src="" class="block max-w-full">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('userModal').classList.add('hidden')" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" id="userSubmitBtn" class="btn-primary !py-2 px-6 !bg-gradient-to-r !from-[#E42278] !to-[#ED7BAB]">Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var isAdmin = <?php echo (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") ? "true" : "false"; ?>;
    
    // 1. Ensure Modal is at body level so it doesn't get trapped behind invisible layers
    const uModal = document.getElementById('userModal');
    if (uModal && uModal.parentElement !== document.body) {
        document.body.appendChild(uModal);
    }

    async function fetchData(action) {
        const r = await fetch('api.php?action=' + action);
        return r.json();
    }
    async function postData(action, data) {
        const fd = new FormData();
        fd.append('action', action);
        for (const k in data) fd.append(k, data[k]);
        const r = await fetch('api.php', { method: 'POST', body: fd });
        return r.json();
    }

    const roleColors = {
        admin: 'bg-[#E42278]/10 text-[#E42278] border-[#E42278]/20',
        encoder: 'bg-blue-50 text-blue-700 border-blue-200',
        viewer: 'bg-gray-100 text-gray-600 border-gray-200'
    };

    async function loadUsers() {
        const list = document.getElementById('usersList');
        if (!list) return;
        try {
            const result = await fetchData('get_users');
            if (result.success && result.data.length > 0) {
                list.innerHTML = result.data.map(u => {
                    const initials = (u.display_name || u.username).substring(0, 2).toUpperCase();
                    const roleClass = roleColors[u.role] || roleColors.viewer;
                    const isCurrentUser = (u.username === '<?php echo htmlspecialchars($_SESSION["username"] ?? ""); ?>');
                    
                    // Safely escape strings for onclick functions
                    const safeUser = (u.username || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
                    const safeName = (u.display_name || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");

                    return `
                        <div class="glass-card p-5 flex items-start gap-4 group hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#E42278] to-[#ED7BAB] flex items-center justify-center text-white font-black text-lg flex-shrink-0 shadow-md shadow-[#E42278]/20">
                                ${u.avatar_url ? `<img src="${u.avatar_url}" class="w-12 h-12 rounded-2xl object-cover">` : initials}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-bold text-[#0D111A] text-sm">${u.display_name || u.username}</p>
                                    ${isCurrentUser ? '<span class="text-[9px] font-black bg-[#E42278]/10 text-[#E42278] px-1.5 py-0.5 rounded uppercase tracking-wider">You</span>' : ''}
                                </div>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">@${u.username}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider ${roleClass}">${u.role}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity" style="${isAdmin ? "" : "display:none"}">
                                <button onclick="openUserModal('${u.id}', '${safeUser}', '${safeName}', '${u.role}')" 
                                    class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                ${!isCurrentUser ? `<button onclick="deleteUser(${u.id}, '${safeUser}')" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            } else if (result.success) {
                list.innerHTML = '<div class="glass-card p-8 col-span-2 text-center text-gray-400">No users found.</div>';
            } else {
                list.innerHTML = `<div class="glass-card p-8 col-span-2 text-center text-red-500 font-bold">${result.message || 'Error: Could not load users.'}</div>`;
            }
        } catch(e) {
            list.innerHTML = `<div class="glass-card p-8 col-span-2 text-center text-red-500 font-bold">Error: ${e.message}</div>`;
        }
    }

    let cropperInstance = null; // Holds the cropper tool

    window.openUserModal = function(id = '', username = '', displayName = '', role = 'encoder') {
        document.getElementById('userId').value = id;
        document.getElementById('userUsername').value = username;
        document.getElementById('userDisplayName').value = displayName;
        document.getElementById('userRole').value = role;
        document.getElementById('userPassword').value = '';
        
        // Reset file input and completely destroy previous crop box
        const avatarInput = document.getElementById('userAvatar');
        if(avatarInput) avatarInput.value = ''; 
        if(cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
        document.getElementById('cropContainer').classList.add('hidden');
        
        document.getElementById('userModalTitle').textContent = id ? 'Edit User' : 'Add New User';
        document.getElementById('userUsername').disabled = !!id;
        document.getElementById('passwordNote').style.display = id ? 'inline' : 'none';
        document.getElementById('userModal').classList.remove('hidden');
    };

    window.deleteUser = async function(id, username) {
        if (!confirm('Delete user "' + username + '"? This cannot be undone.')) return;
        const result = await postData('delete_user', { id });
        if (result.success) { loadUsers(); }
        else { alert(result.message || 'Error deleting user.'); }
    };

    // 2. Event Delegation for the Add User Button (Bulletproof clicking)
    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#addUserBtn');
        if (addBtn) {
            e.preventDefault();
            openUserModal();
        }
    });

    // Initialize the Crop box when a file is selected
    document.getElementById('userAvatar')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById('avatarPreview');
                img.src = event.target.result;
                document.getElementById('cropContainer').classList.remove('hidden');

                if (cropperInstance) cropperInstance.destroy();
                
                // Set the crop box to a perfect 1:1 Square
                cropperInstance = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    guides: false,
                    highlight: false,
                    background: false
                });
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('cropContainer').classList.add('hidden');
            if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
        }
    });

    // Helper to safely convert the crop box into an actual image file
    function getCroppedImageFile(cropper) {
        return new Promise(resolve => {
            if (!cropper) return resolve(null);
            // Export it as a clean 300x300 high quality JPEG
            cropper.getCroppedCanvas({ width: 300, height: 300 }).toBlob(blob => {
                resolve(blob ? new File([blob], "avatar.jpg", { type: "image/jpeg" }) : null);
            }, 'image/jpeg', 0.9);
        });
    }

    // 3. Form Submit 
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('userId').value;
            const data = {
                id,
                username: document.getElementById('userUsername').value,
                display_name: document.getElementById('userDisplayName').value,
                password: document.getElementById('userPassword').value,
                role: document.getElementById('userRole').value
            };
            
            // Grab the perfectly cropped photo!
            if (cropperInstance) {
                const croppedAvatar = await getCroppedImageFile(cropperInstance);
                if (croppedAvatar) data.avatar = croppedAvatar;
            }

            const btn = document.getElementById('userSubmitBtn');
            btn.disabled = true; btn.textContent = 'Saving...';
            try {
                const result = await postData(id ? 'update_user' : 'add_user', data);
                if (result.success) {
                    document.getElementById('userModal').classList.add('hidden');
                    loadUsers();
                } else { alert(result.message || 'Error saving user.'); }
            } finally { btn.disabled = false; btn.textContent = 'Save User'; }
        };
    }

    // 4. Observer and initial load
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'usersPage' && !m.target.classList.contains('hidden')) {
                loadUsers();
            }
        });
    });
    const usersPage = document.getElementById('usersPage');
    if (usersPage) {
        observer.observe(usersPage, { attributes: true, attributeFilter: ['class'] });
        if (!usersPage.classList.contains('hidden')) {
            loadUsers();
        }
    }
})();
</script>