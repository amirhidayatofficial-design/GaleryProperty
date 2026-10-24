/**
 * Konfirmasi sebelum meninggalkan halaman yang form-nya sudah diisi tetapi belum disimpan.
 *
 * Form ikut dijaga cukup dengan menambahkan atribut `data-confirm-leave`.
 * - Klik link di dalam aplikasi (menu, Batal, Keluar, dll.) → modal konfirmasi.
 * - Tutup tab, reload, atau tombol Back browser → dialog bawaan browser.
 */
export default () => {
    // Disimpan di luar data Alpine supaya elemen DOM tidak dibungkus Proxy reaktif
    const initial = new Map();
    const submitted = new Set();
    let pendingLink = null;
    let allowLeave = false;

    const snapshot = (form) => JSON.stringify(
        [...new FormData(form)]
            .filter(([name]) => name !== '_token' && name !== '_method')
            .map(([name, value]) => [name, value instanceof File ? value.name : value])
    );

    const isDirty = () => {
        if (allowLeave) return false;

        for (const [form, value] of initial) {
            if (!submitted.has(form) && document.contains(form) && snapshot(form) !== value) {
                return true;
            }
        }

        return false;
    };

    return {
        open: false,

        init() {
            // Ambil kondisi awal setelah Alpine selesai mengisi nilai form
            this.$nextTick(() => {
                document.querySelectorAll('form[data-confirm-leave]').forEach((form) => {
                    initial.set(form, snapshot(form));
                });
            });

            document.addEventListener('submit', (event) => submitted.add(event.target));
            document.addEventListener('click', (event) => this.interceptLink(event), true);
            window.addEventListener('beforeunload', (event) => {
                if (isDirty()) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
        },

        interceptLink(event) {
            const link = event.target.closest('a[href]');

            if (
                !link ||
                event.button !== 0 ||
                event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
                link.target === '_blank' ||
                link.hasAttribute('download') ||
                link.getAttribute('href').startsWith('#') ||
                !isDirty()
            ) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            pendingLink = link;
            this.open = true;
        },

        stay() {
            this.open = false;
            pendingLink = null;
        },

        leave() {
            allowLeave = true;
            this.open = false;
            // Klik ulang link aslinya supaya handler bawaannya (mis. form logout) tetap berjalan
            pendingLink?.click();
        },
    };
};
