</div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
<!-- Drag & Drop Logic -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropZones = document.querySelectorAll('.drop-zone');

        dropZones.forEach(zone => {
            const input = zone.querySelector('input[type="file"]');
            const previewContainer = zone.closest('.mb-3').querySelector('.preview-container') ||
                (zone.nextElementSibling && zone.nextElementSibling.classList.contains('preview-container') ? zone.nextElementSibling : null);

            // Use DataTransfer to aggregate files
            let dt = new DataTransfer();

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            ['dragleave', 'dragend'].forEach(type => {
                zone.addEventListener(type, (e) => {
                    zone.classList.remove('dragover');
                });
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');

                if (e.dataTransfer.files.length) {
                    Array.from(e.dataTransfer.files).forEach(file => dt.items.add(file));
                    input.files = dt.files;
                    handlePreview(input, previewContainer, dt);
                }
            });

            input.addEventListener('change', () => {
                // If files were selected via browse, add them to our DataTransfer
                Array.from(input.files).forEach(file => {
                    // Check if file already exists in dt to avoid duplicates
                    let exists = false;
                    for (let i = 0; i < dt.files.length; i++) {
                        if (dt.files[i].name === file.name && dt.files[i].size === file.size) {
                            exists = true; break;
                        }
                    }
                    if (!exists) dt.items.add(file);
                });
                input.files = dt.files;
                handlePreview(input, previewContainer, dt);
            });
        });

        function handlePreview(input, container, dt) {
            if (!container) return;
            container.innerHTML = '';

            Array.from(dt.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const item = document.createElement('div');
                        item.className = 'preview-item';
                        item.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-btn" data-index="${index}">&times;</button>
                        `;

                        item.querySelector('.remove-btn').addEventListener('click', function () {
                            const idx = parseInt(this.getAttribute('data-index'));
                            const newDt = new DataTransfer();
                            Array.from(dt.files).forEach((f, i) => {
                                if (i !== idx) newDt.items.add(f);
                            });
                            // We can't re-assign dt variable here because it's in a closure
                            // But we can update the input and the source dt items if we rethink the logic
                            // For simplicity, let's just clear and rebuild
                            dt.items.clear();
                            Array.from(newDt.files).forEach(f => dt.items.add(f));
                            input.files = dt.files;
                            handlePreview(input, container, dt);
                        });

                        container.appendChild(item);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
</body>

</html>