export function exportToExcel(title: string, headers: string[], rows: any[][], fileName: string) {
    let html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">`;
    html += `<head><meta charset="utf-8" /><style>table { border-collapse: collapse; } th { background-color: #EA580C; color: white; font-weight: bold; } th, td { border: 1px solid #CBD5E1; padding: 8px; font-family: sans-serif; }</style></head>`;
    html += `<body><h2>${title}</h2><table><thead><tr>`;
    headers.forEach(h => {
        html += `<th>${h}</th>`;
    });
    html += `</tr></thead><tbody>`;
    rows.forEach(r => {
        html += `<tr>`;
        r.forEach(c => {
            html += `<td>${c === null || c === undefined ? '' : c}</td>`;
        });
        html += `</tr>`;
    });
    html += `</tbody></table></body></html>`;

    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${fileName}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

export function exportToDocs(title: string, headers: string[], rows: any[][], fileName: string) {
    let html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">`;
    html += `<head><meta charset="utf-8" /><style>table { width: 100%; border-collapse: collapse; margin-top: 20px; } th { background-color: #EA580C; color: white; font-weight: bold; } th, td { border: 1px solid #CBD5E1; padding: 10px; font-family: sans-serif; text-align: left; }</style></head>`;
    html += `<body><h1>${title}</h1><table><thead><tr>`;
    headers.forEach(h => {
        html += `<th>${h}</th>`;
    });
    html += `</tr></thead><tbody>`;
    rows.forEach(r => {
        html += `<tr>`;
        r.forEach(c => {
            html += `<td>${c === null || c === undefined ? '' : c}</td>`;
        });
        html += `</tr>`;
    });
    html += `</tbody></table></body></html>`;

    const blob = new Blob([html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${fileName}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

export function exportToPDF() {
    window.print();
}
