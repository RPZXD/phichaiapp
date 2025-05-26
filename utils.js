// SweetAlert2 popup
function showAlert({title = '', text = '', icon = 'info', timer = 2000, showConfirmButton = false} = {}) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title,
            text,
            icon,
            timer,
            showConfirmButton
        });
    }
}

// แปลงวันที่เป็นวันเดือนปีไทย
function thaiDate(dateStr) {
    if (!dateStr) return '';
    const months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    const d = new Date(dateStr.replace(' ', 'T'));
    if (isNaN(d)) return dateStr;
    const day = d.getDate();
    const month = d.getMonth() + 1;
    const year = d.getFullYear() + 543;
    const hour = d.getHours().toString().padStart(2, '0');
    const min = d.getMinutes().toString().padStart(2, '0');
    return `${day} ${months[month]} ${year} ${hour}:${min} น.`;
}
