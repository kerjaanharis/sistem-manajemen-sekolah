<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 border-b border-slate-100 pb-2 gap-3">
    <h4 class="text-lg font-bold text-slate-800 flex items-center">
        <i class="fas fa-school text-primary mr-2"></i> Pengaturan Data Sekolah
    </h4>
    <button type="button" onclick="toggleEditSekolah()" id="btn-edit-sekolah"
        class="text-sm font-medium text-amber-600 bg-amber-50 px-3 py-1.5 rounded-md hover:bg-amber-100 transition-colors flex items-center shrink-0 w-fit">
        <i class="fas fa-edit mr-1.5"></i> Edit Data Identitas
    </button>
</div>

<!-- Progress Bar for Uploads -->
<div id="upload-progress-container" class="hidden mb-6">
    <div class="flex justify-between items-center mb-1">
        <span class="text-sm font-medium text-blue-700">Menyimpan Perubahan...</span>
        <span class="text-sm font-medium text-blue-700" id="upload-percent">0%</span>
    </div>
    <div class="w-full bg-blue-100 rounded-full h-2.5">
        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" id="upload-progress-bar"
            style="width: 0%"></div>
    </div>
</div>

<form action="proses_sekolah.php" method="POST" enctype="multipart/form-data" id="form-sekolah"
    onsubmit="return handleSekolahSubmit(event)">

    <!-- Sub-Tab Navigation -->
    <div class="border-b border-slate-100 mb-6 flex overflow-x-auto scrollbar-hide space-x-2">
        <button type="button" onclick="switchSubTabSekolah('identitas', this, 'Data Identitas')"
            class="subtab-btn border-primary text-primary bg-blue-50/50 border-b-2 font-bold px-4 py-3 text-sm whitespace-nowrap transition-colors rounded-t-lg">A.
            IDENTITAS</button>
        <button type="button" onclick="switchSubTabSekolah('alamat', this, 'Data Alamat')"
            class="subtab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium border-b-2 px-4 py-3 text-sm whitespace-nowrap transition-colors rounded-t-lg">B.
            ALAMAT</button>
        <button type="button" onclick="switchSubTabSekolah('kontak', this, 'Data Kontak')"
            class="subtab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium border-b-2 px-4 py-3 text-sm whitespace-nowrap transition-colors rounded-t-lg">C.
            KONTAK</button>
        <button type="button" onclick="switchSubTabSekolah('upload', this, 'Kelengkapan')"
            class="subtab-btn border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium border-b-2 px-4 py-3 text-sm whitespace-nowrap transition-colors rounded-t-lg">D.
            UPLOAD KELENGKAPAN</button>
    </div>

    <!-- SECTION A: IDENTITAS SEKOLAH -->
    <div id="subtab-identitas" class="subtab-content block animate-fadeIn">
        <fieldset disabled id="fs-identitas" class="group disabled:opacity-85 transition-opacity">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Sekolah <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_sekolah"
                        value="<?= htmlspecialchars($sekolah['nama_sekolah'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NPSN (8 digit angka)</label>
                    <input type="text" name="npsn" pattern="[0-9]{8}" maxlength="8"
                        value="<?= htmlspecialchars($sekolah['npsn'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NSS</label>
                    <input type="text" name="nss" value="<?= htmlspecialchars($sekolah['nss'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jenjang</label>
                    <select name="jenjang"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        <?php
                        $jenjang_opsi = ['SD', 'SMP', 'SMA', 'SMK', 'Lainnya'];
                        $jenjang_aktif = $sekolah['jenjang'] ?? 'SMA';
                        foreach ($jenjang_opsi as $j) {
                            $sel = ($j == $jenjang_aktif) ? 'selected' : '';
                            echo "<option value='$j' $sel>$j</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status Sekolah</label>
                    <select name="status_sekolah"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        <?php
                        $status_opsi = ['Negeri', 'Swasta'];
                        $status_aktif = $sekolah['status_sekolah'] ?? 'Negeri';
                        foreach ($status_opsi as $s) {
                            $sel = ($s == $status_aktif) ? 'selected' : '';
                            echo "<option value='$s' $sel>$s</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kepala Sekolah</label>
                    <input type="text" name="nama_kepala_sekolah"
                        value="<?= htmlspecialchars($sekolah['nama_kepala_sekolah'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIP Kepala Sekolah</label>
                    <input type="text" name="nip_kepala_sekolah"
                        value="<?= htmlspecialchars($sekolah['nip_kepala_sekolah'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                </div>
            </div>
        </fieldset>
    </div>

    <!-- SECTION B: ALAMAT SEKOLAH -->
    <div id="subtab-alamat" class="subtab-content hidden animate-fadeIn">
        <fieldset disabled id="fs-alamat" class="group disabled:opacity-85 transition-opacity">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat_lengkap" rows="3"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"><?= htmlspecialchars($sekolah['alamat_lengkap'] ?? '') ?></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RT</label>
                            <input type="text" name="rt" value="<?= htmlspecialchars($sekolah['rt'] ?? '') ?>"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RW</label>
                            <input type="text" name="rw" value="<?= htmlspecialchars($sekolah['rw'] ?? '') ?>"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Dusun / Lingkungan</label>
                        <input type="text" name="dusun" value="<?= htmlspecialchars($sekolah['dusun'] ?? '') ?>"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Desa / Kelurahan</label>
                            <input type="text" name="desa_kelurahan"
                                value="<?= htmlspecialchars($sekolah['desa_kelurahan'] ?? '') ?>"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan</label>
                            <input type="text" name="kecamatan"
                                value="<?= htmlspecialchars($sekolah['kecamatan'] ?? '') ?>"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kota / Kabupaten</label>
                            <input type="text" name="kota_kabupaten"
                                value="<?= htmlspecialchars($sekolah['kota_kabupaten'] ?? '') ?>"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Provinsi</label>
                            <input type="text" name="provinsi"
                                value="<?= htmlspecialchars($sekolah['provinsi'] ?? '') ?>"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode Pos</label>
                        <input type="text" name="kode_pos" value="<?= htmlspecialchars($sekolah['kode_pos'] ?? '') ?>"
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-1/2 p-2.5">
                    </div>
                </div>

                <!-- Peta Map -->
                <div class="space-y-4">
                    <div class="bg-slate-50 p-4 border border-slate-200 rounded-xl relative z-10 w-full">
                        <h6 class="text-sm font-bold text-slate-800 mb-3 flex items-center">
                            <i class="fas fa-location-crosshairs text-blue-500 mr-2"></i> Peta Lokasi Sekolah
                        </h6>

                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

                        <div id="leafletMapSekolah"
                            style="min-height: 256px; width: 100%; position: relative; z-index: 1;"
                            class="rounded-lg border border-slate-300 mb-4 z-0"></div>

                        <style>
                            .leaflet-container {
                                z-index: 1 !important;
                            }

                            .leaflet-pane {
                                z-index: 1 !important;
                            }

                            .leaflet-top,
                            .leaflet-bottom {
                                z-index: 2 !important;
                            }
                        </style>

                        <p class="text-[11px] text-slate-500 mb-4"><i class="fas fa-info-circle mr-1"></i> Geser penanda
                            merah untuk menentukan koordinat secara akurat.</p>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Latitude</label>
                                <input type="text" name="lintang" id="inputLintangSek"
                                    value="<?= htmlspecialchars($sekolah['lintang'] ?? '') ?>"
                                    onchange="updateMarkerSekolahFromInput()"
                                    class="bg-white border text-slate-800 border-slate-300 text-sm rounded-lg block w-full p-2 font-mono"
                                    placeholder="-7.xxx">
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Longitude</label>
                                <input type="text" name="bujur" id="inputBujurSek"
                                    value="<?= htmlspecialchars($sekolah['bujur'] ?? '') ?>"
                                    onchange="updateMarkerSekolahFromInput()"
                                    class="bg-white border text-slate-800 border-slate-300 text-sm rounded-lg block w-full p-2 font-mono"
                                    placeholder="112.xxx">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- SECTION C: KONTAK -->
    <div id="subtab-kontak" class="subtab-content hidden animate-fadeIn">
        <fieldset disabled id="fs-kontak" class="group disabled:opacity-85 transition-opacity">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fas fa-phone mr-1.5 text-slate-400"></i>Nomor Telepon</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($sekolah['telepon'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fab fa-whatsapp mr-1.5 text-emerald-500"></i>No HP / WhatsApp</label>
                    <input type="text" name="hp" value="<?= htmlspecialchars($sekolah['hp'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fas fa-envelope mr-1.5 text-slate-400"></i>Email Utama</label>
                    <input type="email" name="email_utama"
                        value="<?= htmlspecialchars($sekolah['email_utama'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="far fa-envelope mr-1.5 text-slate-400"></i>Email Alternatif</label>
                    <input type="email" name="email_alternatif"
                        value="<?= htmlspecialchars($sekolah['email_alternatif'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fas fa-globe mr-1.5 text-blue-500"></i>Website Utama</label>
                    <input type="url" name="website" value="<?= htmlspecialchars($sekolah['website'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5"
                        placeholder="https://">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fab fa-instagram mr-1.5 text-pink-500"></i>Instagram</label>
                    <input type="text" name="instagram" value="<?= htmlspecialchars($sekolah['instagram'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5"
                        placeholder="@username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fab fa-facebook mr-1.5 text-blue-600"></i>Facebook</label>
                    <input type="text" name="facebook" value="<?= htmlspecialchars($sekolah['facebook'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fab fa-tiktok mr-1.5 text-slate-800"></i>Tiktok</label>
                    <input type="text" name="tiktok" value="<?= htmlspecialchars($sekolah['tiktok'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5"
                        placeholder="@username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1"><i
                            class="fab fa-youtube mr-1.5 text-red-500"></i>YouTube Channel</label>
                    <input type="text" name="youtube" value="<?= htmlspecialchars($sekolah['youtube'] ?? '') ?>"
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg block w-full p-2.5">
                </div>
            </div>
        </fieldset>
    </div>

    <!-- SECTION D: UPLOAD KELENGKAPAN -->
    <div id="subtab-upload" class="subtab-content hidden animate-fadeIn">
        <fieldset disabled id="fs-upload" class="group disabled:opacity-85 transition-opacity">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Helper Render Function for Form Upload Fields -->
                <?php
                function renderUploadField($key, $label, $acceptedTypes, $currentFile)
                {
                    $preview = $currentFile ? base_url("assets/uploads/sekolah/$currentFile") : '';
                    ?>
                    <div
                        class="border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center text-center relative group">
                        <label class="block text-sm font-bold text-slate-700 mb-3 w-full text-left">
                            <?= $label ?>
                        </label>

                        <div
                            class="w-full h-32 bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center relative overflow-hidden mb-3">
                            <?php if ($currentFile): ?>
                                <img src="<?= $preview ?>" alt="Preview" class="h-full object-contain p-2"
                                    id="preview-<?= $key ?>">
                            <?php else: ?>
                                <i class="fas fa-image text-3xl text-slate-300 mb-2" id="icon-<?= $key ?>"></i>
                                <img src="" alt="Preview" class="h-full object-contain p-2 hidden" id="preview-<?= $key ?>">
                                <span class="text-xs text-slate-500" id="text-<?= $key ?>">Belum ada file</span>
                            <?php endif; ?>

                            <!-- Overlay Upload Button (Hidden when disabled via CSS) -->
                            <div
                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity file-overlay">
                                <label for="file-<?= $key ?>"
                                    class="cursor-pointer bg-white text-slate-800 text-xs font-bold py-1.5 px-3 rounded-md shadow-sm">
                                    Pilih File
                                </label>
                                <input type="file" id="file-<?= $key ?>" name="<?= $key ?>" class="hidden"
                                    accept="<?= $acceptedTypes ?>" onchange="previewImage(this, '<?= $key ?>')">
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-500 w-full text-left">Format:
                            <?= str_replace('.', '', $acceptedTypes) ?>
                        </p>
                    </div>
                    <?php
                }

                renderUploadField('logo', 'Logo Sekolah', '.png,.jpg,.jpeg', $sekolah['logo'] ?? null);
                renderUploadField('kop', 'KOP Surat (Header)', '.png,.jpg,.jpeg', $sekolah['kop'] ?? null);
                renderUploadField('favicon', 'Favicon (Icon Tab)', '.ico,.png', $sekolah['favicon'] ?? null);
                renderUploadField('stempel', 'Stempel Sekolah', '.png', $sekolah['stempel'] ?? null);
                renderUploadField('ttd_kepsek', 'TTD Kepala Sekolah', '.png', $sekolah['ttd_kepsek'] ?? null);
                renderUploadField('foto_kepsek', 'Foto Kepala Sekolah', '.png,.jpg,.jpeg', $sekolah['foto_kepsek'] ?? null);
                ?>
            </div>
            <style>
                fieldset[disabled] .file-overlay {
                    display: none !important;
                }
            </style>
        </fieldset>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end space-x-3 mt-8 pt-4 border-t border-slate-100 hidden" id="action-sekolah">
        <button type="button" onclick="cancelEditSekolah()"
            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm">
            Batal
        </button>
        <button type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm flex items-center text-sm">
            <i class="fas fa-save mr-2"></i> Simpan Pengaturan
        </button>
    </div>
</form>

<script>
    let currentEditTabSekolah = null;

    function switchSubTabSekolah(tabId, el, labelName = 'Data Sekolah') {
        const editBtn = document.getElementById('btn-edit-sekolah');
        const actions = document.getElementById('action-sekolah');

        // If currently editing, warn and cancel
        if (currentEditTabSekolah) {
            cancelEditSekolah();
        }

        // Update Button Info
        editBtn.innerHTML = '<i class="fas fa-edit mr-1.5"></i> Edit ' + labelName;

        document.querySelectorAll('.subtab-btn').forEach(btn => {
            btn.classList.remove('border-primary', 'text-primary', 'font-bold', 'bg-blue-50/50');
            btn.classList.add('border-transparent', 'text-slate-500', 'font-medium');
        });
        el.classList.add('border-primary', 'text-primary', 'font-bold', 'bg-blue-50/50');
        el.classList.remove('border-transparent', 'text-slate-500', 'font-medium');

        document.querySelectorAll('.subtab-content').forEach(content => {
            content.classList.add('hidden');
            content.classList.remove('block');
        });
        document.getElementById('subtab-' + tabId).classList.remove('hidden');
        document.getElementById('subtab-' + tabId).classList.add('block');

        if (tabId === 'alamat') {
            setTimeout(() => {
                initMapSekolah();
                if (TheMapSekolah) TheMapSekolah.invalidateSize();
            }, 100);
        }
    }

    function toggleEditSekolah() {
        const activeContent = document.querySelector('.subtab-content.block').id;
        const tabId = activeContent.replace('subtab-', '');
        currentEditTabSekolah = tabId;

        document.getElementById('fs-' + tabId).removeAttribute('disabled');
        document.getElementById('btn-edit-sekolah').classList.add('hidden');
        document.getElementById('action-sekolah').classList.remove('hidden');

        // Disable tab buttons to prevent switching while editing
        document.querySelectorAll('.subtab-btn').forEach(b => b.setAttribute('disabled', 'disabled'));
    }

    function cancelEditSekolah() {
        if (currentEditTabSekolah) {
            document.getElementById('fs-' + currentEditTabSekolah).setAttribute('disabled', 'disabled');
        }
        document.getElementById('btn-edit-sekolah').classList.remove('hidden');
        document.getElementById('action-sekolah').classList.add('hidden');

        // Let's reset the whole form to clear previous edits
        document.getElementById('form-sekolah').reset();
        currentEditTabSekolah = null;

        // Re-enable tabs
        document.querySelectorAll('.subtab-btn').forEach(b => b.removeAttribute('disabled'));
    }

    function previewImage(input, key) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var preview = document.getElementById('preview-' + key);
                preview.src = e.target.result;
                preview.classList.remove('hidden');

                var icon = document.getElementById('icon-' + key);
                if (icon) icon.classList.add('hidden');

                var text = document.getElementById('text-' + key);
                if (text) text.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Leaflet Init
    let mapSekolahInit = false;
    let TheMapSekolah, MarkerSekolah;

    function initMapSekolah() {
        if (mapSekolahInit) return;
        let lat = document.getElementById('inputLintangSek').value || -0.789275;
        let lng = document.getElementById('inputBujurSek').value || 113.921327;
        let zoom = document.getElementById('inputLintangSek').value ? 16 : 5;

        TheMapSekolah = L.map('leafletMapSekolah').setView([lat, lng], zoom);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(TheMapSekolah);

        MarkerSekolah = L.marker([lat, lng], { draggable: true }).addTo(TheMapSekolah);

        MarkerSekolah.on('dragend', function () {
            var pos = MarkerSekolah.getLatLng();
            document.getElementById('inputLintangSek').value = pos.lat.toFixed(6);
            document.getElementById('inputBujurSek').value = pos.lng.toFixed(6);
        });

        TheMapSekolah.on('click', function (e) {
            MarkerSekolah.setLatLng(e.latlng);
            document.getElementById('inputLintangSek').value = e.latlng.lat.toFixed(6);
            document.getElementById('inputBujurSek').value = e.latlng.lng.toFixed(6);
        });

        mapSekolahInit = true;
    }

    function updateMarkerSekolahFromInput() {
        if (!TheMapSekolah || !MarkerSekolah) return;
        let lat = parseFloat(document.getElementById('inputLintangSek').value);
        let lng = parseFloat(document.getElementById('inputBujurSek').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            let newPos = new L.LatLng(lat, lng);
            MarkerSekolah.setLatLng(newPos);
            TheMapSekolah.setView(newPos, TheMapSekolah.getZoom());
        }
    }

    // Form submission with AJAX & Progress Bar
    function handleSekolahSubmit(e) {
        e.preventDefault();

        // Collect form data FIRST before disabling fields
        const formData = new FormData(document.getElementById('form-sekolah'));

        // Hide actions, show progress and disable form
        document.getElementById('action-sekolah').classList.add('hidden');
        if (currentEditTabSekolah) {
            document.getElementById('fs-' + currentEditTabSekolah).setAttribute('disabled', 'disabled');
        }
        const progContainer = document.getElementById('upload-progress-container');
        const progBar = document.getElementById('upload-progress-bar');
        const progText = document.getElementById('upload-percent');

        progContainer.classList.remove('hidden');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', document.getElementById('form-sekolah').action, true);

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                var percentComplete = (e.loaded / e.total) * 100;
                progBar.style.width = percentComplete + '%';
                progText.innerText = Math.round(percentComplete) + '%';
            }
        };

        xhr.onload = function () {
            if (xhr.status == 200) {
                progBar.style.width = '100%';
                progText.innerText = '100%';
                setTimeout(() => {
                    // Reload page to show success message
                    window.location.reload();
                }, 500);
            } else {
                alert('Terjadi kesalahan saat menyimpan data.');
                cancelEditSekolah();
            }
        };

        xhr.send(formData);
        return false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Init map if section is active/visible
        // Since it's the default active tab, init immediately with slight delay
        setTimeout(() => {
            initMapSekolah();
        }, 500);
    });

</script>