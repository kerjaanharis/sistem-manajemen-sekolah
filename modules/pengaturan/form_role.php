<?php
// form_role.php

// 1. Dapatkan role yang sedang dipilih (Default ke Role #1 jika kosong)
$selected_role_id = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 1;

// 2. Fetch Module List
$stmt_modules = $pdo->query("SELECT * FROM sys_modules ORDER BY id_modul ASC");
$modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch specific permissions for the selected role
$stmt_perms = $pdo->prepare("SELECT * FROM role_permissions WHERE id_role = ?");
$stmt_perms->execute([$selected_role_id]);
$raw_perms = $stmt_perms->fetchAll(PDO::FETCH_ASSOC);

// Format array untuk kemudahan mapping di HTML: $perms[modul_id]['can_view']
$perms = [];
foreach ($raw_perms as $rp) {
    $perms[$rp['id_modul']] = $rp;
}
?>

<div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
    <h4 class="text-lg font-bold text-slate-800 flex items-center">
        <i class="fas fa-user-shield text-primary mr-2"></i> Pengaturan Role & Hak Akses
    </h4>
    
    <!-- Role Selector Dropdown -->
    <div class="flex items-center space-x-3">
        <label for="role_selector" class="text-sm font-medium text-slate-600">Pilih Role User:</label>
        <select id="role_selector" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block p-2"
                onchange="window.location.href='index.php?tab=role&role_id=' + this.value;">
            <?php foreach ($data_roles as $role): ?>
                <option value="<?= $role['id_role'] ?>" <?= $selected_role_id === $role['id_role'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($role['nama_role']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
    <div class="bg-yellow-50 border-b border-yellow-100 p-4 flex items-start space-x-3">
        <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
        <div class="text-sm text-yellow-800">
            <p class="font-bold mb-1">Panduan Pengaturan Hak Akses</p>
            <p>Konfigurasi di bawah ini menentukan apa saja yang bisa dilihat dan diubah oleh role yang sedang Anda pilih. 
               Centang kotak untuk memberikan izin, atau hilangkan centang untuk memblokir akses ke fitur tersebut.</p>
        </div>
    </div>

    <form action="proses_role.php" method="POST">
        <input type="hidden" name="id_role" value="<?= $selected_role_id ?>">
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold border-r border-slate-100">Nama Modul Sistem</th>
                        <th scope="col" class="px-6 py-4 font-bold text-center border-r border-slate-100">
                            <i class="fas fa-eye text-blue-500 mr-1"></i> Tampilkan
                            <p class="text-[10px] text-slate-400 font-normal normal-case mt-0.5">Bisa melihat halaman</p>
                        </th>
                        <th scope="col" class="px-6 py-4 font-bold text-center border-r border-slate-100">
                            <i class="fas fa-plus-circle text-emerald-500 mr-1"></i> Tambah Data
                            <p class="text-[10px] text-slate-400 font-normal normal-case mt-0.5">Bisa input data baru</p>
                        </th>
                        <th scope="col" class="px-6 py-4 font-bold text-center border-r border-slate-100">
                            <i class="fas fa-edit text-amber-500 mr-1"></i> Edit Data
                            <p class="text-[10px] text-slate-400 font-normal normal-case mt-0.5">Bisa mengubah data</p>
                        </th>
                        <th scope="col" class="px-6 py-4 font-bold text-center">
                            <i class="fas fa-trash-alt text-red-500 mr-1"></i> Hapus Data
                            <p class="text-[10px] text-slate-400 font-normal normal-case mt-0.5">Bisa delete/hapus</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($modules)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada modul yang terdaftar di sistem.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($modules as $m): 
                            $m_id = $m['id_modul'];
                            // Default value 0 if not set
                            $c_v = isset($perms[$m_id]['can_view']) ? $perms[$m_id]['can_view'] : 0;
                            $c_a = isset($perms[$m_id]['can_add']) ? $perms[$m_id]['can_add'] : 0;
                            $c_e = isset($perms[$m_id]['can_edit']) ? $perms[$m_id]['can_edit'] : 0;
                            $c_d = isset($perms[$m_id]['can_delete']) ? $perms[$m_id]['can_delete'] : 0;
                            
                            // If role is Administrator (ID: 1), lock the checkboxes to checked to prevent locking themselves out
                            $is_admin = ($selected_role_id == 1);
                        ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 border-r border-slate-50">
                                <div class="font-bold text-slate-800"><?= htmlspecialchars($m['nama_modul']) ?></div>
                                <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($m['deskripsi']) ?></div>
                            </td>
                            
                            <!-- VIEW CHECKBOX -->
                            <td class="px-6 py-4 text-center border-r border-slate-50">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="permissions[<?= $m_id ?>][can_view]" value="1" 
                                           class="sr-only peer view-cb-<?= $m_id ?>" 
                                           <?= $c_v ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 <?= $is_admin ? 'opacity-50 cursor-not-allowed' : '' ?>"></div>
                                </label>
                            </td>

                            <!-- ADD CHECKBOX -->
                            <td class="px-6 py-4 text-center border-r border-slate-50">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="permissions[<?= $m_id ?>][can_add]" value="1" 
                                           class="sr-only peer add-cb-<?= $m_id ?>" 
                                           <?= $c_a ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-emerald-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 <?= $is_admin ? 'opacity-50 cursor-not-allowed' : '' ?>"></div>
                                </label>
                            </td>

                            <!-- EDIT CHECKBOX -->
                            <td class="px-6 py-4 text-center border-r border-slate-50">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="permissions[<?= $m_id ?>][can_edit]" value="1" 
                                           class="sr-only peer edit-cb-<?= $m_id ?>" 
                                           <?= $c_e ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-amber-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500 <?= $is_admin ? 'opacity-50 cursor-not-allowed' : '' ?>"></div>
                                </label>
                            </td>

                            <!-- DELETE CHECKBOX -->
                            <td class="px-6 py-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="permissions[<?= $m_id ?>][can_delete]" value="1" 
                                           class="sr-only peer del-cb-<?= $m_id ?>" 
                                           <?= $c_d ? 'checked' : '' ?> <?= $is_admin ? 'disabled' : '' ?>>
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-red-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500 <?= $is_admin ? 'opacity-50 cursor-not-allowed' : '' ?>"></div>
                                </label>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
            <?php if($selected_role_id == 1): ?>
                <div class="text-sm font-medium text-slate-500 bg-slate-200 py-2.5 px-6 rounded-lg cursor-not-allowed inline-flex items-center">
                    <i class="fas fa-lock mr-2"></i> Hak Akses Administrator Tidak Dapat Diubah
                </div>
            <?php else: ?>
                <button type="submit" class="text-white bg-primary hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-8 py-2.5 flex items-center shadow-sm transition-colors">
                    <i class="fas fa-save mr-2"></i> Simpan Hak Akses Role
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>
