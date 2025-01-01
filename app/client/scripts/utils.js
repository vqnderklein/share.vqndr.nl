export function dateFormatter(date) {
    const monthMap = [
        'Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni',
        'Juli', 'Augstus', 'September', 'Oktober', 'November', 'December'
    ];

    let dateParts = date.split('-');
    let year = dateParts[0];
    let month = monthMap[parseInt(dateParts[1]) - 1];
    let day = parseInt(dateParts[2]);

    let targetDate = new Date(year, parseInt(dateParts[1]) - 1, day);
    let currentDate = new Date();
    let timeDiff = targetDate - currentDate;
    let daysDifference = Math.floor(timeDiff / (1000 * 3600 * 24));

    if (daysDifference === 0) {
        return "Verloopt vandaag";
    } else if (daysDifference === 1) {
        return "Verloopt over 1 dag";
    } else if (daysDifference === 2) {
        return "Verloopt over 2 dagen";
    } else if (daysDifference === 3) {
        return "Verloopt over 3 dagen";
    } else if (daysDifference > 3) {
        return `Verloopt op ${day} ${month} ${year}`;
    } else {
        return `Verlopen op ${day} ${month} ${year}`;
    }
}

export function totalSize(data) {
    let sum = 0;

    data.forEach(item => {
        sum += item.size;
    });

    return formatBytes(sum);
}

export function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';

    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
}

export function returnFilesInHTML(array) {
    const fragment = document.createDocumentFragment();

    array.forEach(file => {
        let html = `
            <span>
                <p class="title">${file.name}</p>
                <p class="descr"><span class="size">${formatBytes(file.size)}</span>&ensp;&bull;&ensp; <span class="ext">${file.name.split(".").pop()}</span></p>
            </span>
            <a file-name="${file.name}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32">
                    <path fill="currentColor" d="M16 3a.5.5 0 0 1 .5.5v18.293l6.646-6.647a.5.5 0 0 1 .708.708l-7.5 7.5a.5.5 0 0 1-.708 0l-7.5-7.5a.5.5 0 0 1 .708-.708l6.646 6.647V3.5A.5.5 0 0 1 16 3M6.5 28.5a.5.5 0 0 1 0-1h19a.5.5 0 0 1 0 1z"/>
                </svg>
            </a>
        `;

        const li = document.createElement('li');
        li.innerHTML = html;
        fragment.appendChild(li);
    });

    return fragment;
}

export function fetchServerContent(url) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.open('GET', url, true);

        xhr.onload = function() {
            if (xhr.status === 200) {
                resolve(xhr.responseText);
            } else {
                reject(`Error: Failed to load file. Status: ${xhr.status}`);
            }
        };

        xhr.onerror = function() {
            reject('Error: Unable to fetch the file.');
        };

        xhr.send();
    });
}