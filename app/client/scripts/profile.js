import { totalSize, dateFormatter, formatBytes } from './utils.js';

const allProfileAnchorLinks = document.querySelectorAll('nav>span>ul:nth-child(2) li:not(:last-child) a');
let profileData;

window.addEventListener('DOMContentLoaded', () => {
    event.preventDefault();

    const startLocation = window.location.href.split("/")[4];

    document.querySelectorAll("nav li:not(:last-child) a").forEach(a => {
        if (a.getAttribute("href") === "/app/" + startLocation)
            a.classList.add("active");
    });

    fetchServerContent("/app/api/report").then(result => {
        CreateTransferReport(result);
        profileData = result;
        console.log("Profile data successfully assigned:", profileData);

        if (startLocation !== "home")
            addOverlay(startLocation);

    }).catch(error => {
        console.error("Error fetching report information:", error);
    });


});

document.querySelector("#uploadSection").addEventListener('click', () => {
    window.history.pushState("", "", "/app/home");
    document.querySelector('.subPageContent').classList.remove('zoomIN');

    removeOverlay();

});

allProfileAnchorLinks.forEach(anchorLink => {

    anchorLink.addEventListener('click', () => {
        event.preventDefault();

        const destination = anchorLink.getAttribute("href");
        window.history.pushState("", "", destination);

        addOverlay(destination.split("/")[2]);

        document.querySelectorAll("nav a.active").forEach(a => {

            a.classList.remove("active");

        });

        anchorLink.classList.add("active");

    });

});

function addOverlay(s) {
    document.querySelector('.subPageContent').innerHTML = "";
    document.querySelector(".subPageContent").classList.remove('zoomIN');

    const state = s.split('?')[0];
    const url = new URL(window.location.href);

    const htmlBox = document.querySelector(".subPageContent");
    const constructionPages = [
        "services", "contacts"
    ]

    document.querySelector("body").classList.add("sideOpen");

    if (state == constructionPages[0] || state == constructionPages[1]) {
        htmlBox.classList.add('flexBoxGrid');
        htmlBox.innerHTML = `<img src=/app/client/assets/img/construction.svg><h2>Komt binnenkort...`;
        document.querySelector(".subPageTitle").textContent = state.split("?")[0];

    } else if (state == "transfers") {

        console.log(url.searchParams.get("id"))

        if (url.searchParams.get("id"))
            addOverlay("detail-transfer")
        else {
            document.querySelector(".subPageTitle").textContent = state.split("?")[0];
            htmlBox.classList.remove("flexBoxGrid");
            CreateTransferReport(profileData)
        }
    } else if (state == "detail-transfer") {
        document.querySelector("nav>span>ul:nth-child(2)>li:nth-child(3) a").classList.add('active');
        createZoomedInReport(profileData, url.searchParams.get("id"));
    }
}

function removeOverlay() {
    document.querySelector('.subPageContent').innerHTML = "";

    document.querySelector("body").classList.remove("sideOpen");
    document.querySelector(".subPageContent").classList.remove('zoomIN');

}

function createZoomedInReport(a, id) {
    const array = JSON.parse(a);
    const transferMap = new Map(array.map(item => [item.transfer_id, item]));
    const record = transferMap.get(id);



    console.log(record);

    document.querySelector(".subPageTitle").textContent = record.transfer_title;
    document.querySelector(".subPageContent").classList.add('zoomIN');

    fetchServerContent("/app/client/assets/html/format.txt")
        .then(function(content) {

            console.log(content);

            const amountOfFiles = (JSON.parse(record.transfer_files).length > 1 ? JSON.parse(record.transfer_files).length + " bestanden" : JSON.parse(record.transfer_files).length + " bestand")

            let formattedContent = content.replace('{TOTAL_SIZE}', totalSize(JSON.parse(record.transfer_files)));
            formattedContent = formattedContent.replace('{FILES}', amountOfFiles);
            formattedContent = formattedContent.replace('{EXPIRE_DATE}', dateFormatter(record.expire_date));
            formattedContent = formattedContent.replace('{DOWNLOAD_URI}', `https://share.vqndr.nl/download/${record.transfer_id}`);
            formattedContent = formattedContent.replace('{DOWNLOAD_URI}', `https://share.vqndr.nl/app/server/api/download?m=zip&i=${record.transfer_id}`);
            formattedContent = formattedContent.replace('{VIEW_URI}', `https://share.vqndr.nl/download/${record.transfer_id}`);
            formattedContent = formattedContent.replace('{EXPIRE_DATE}', dateFormatter(record.expire_date));
            formattedContent = formattedContent.replace('{TRANSFER_MESSAGE}', (record.transfer_message) ? record.transfer_message : "Geen bericht meegestuurd")
            formattedContent = formattedContent.replace('{AMOUNT-OF-FILES}', amountOfFiles);
            formattedContent = formattedContent.replace('{LIST-OF-FILES}', returnHTML(record));
            formattedContent = formattedContent.replace('{TRANSFER_DOWNLOADS}', (record.downloads > 0) ? record.downloads : "Nog geen downloads");

            document.querySelector('.subPageContent').innerHTML = formattedContent;

            document.querySelector("label[for='download-uri']").addEventListener("click", () => {
                copyToClipboard();
            });
        })
        .catch(function(error) {
            console.log(error);
        });


}

function returnHTML(a) {

    let html = "";
    const array = JSON.parse(a.transfer_files);

    array.forEach(file => {
        let h = `
        <li>
            <span>
                <header>${file.name}</header>
                <p>${formatBytes(file.size)} &bull; ${file.name.split(".")[file.name.split(".").length - 1]}</p>
            </span>
            <a href="https://share.vqndr.nl/app/server/api/download?m=file&i=${a.transfer_id}&f=${file.name}" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="currentColor" d="m12 15.577l-3.539-3.538l.708-.72L11.5 13.65V5h1v8.65l2.33-2.33l.709.719zM6.616 19q-.691 0-1.153-.462T5 17.384v-2.423h1v2.423q0 .231.192.424t.423.192h10.77q.23 0 .423-.192t.192-.424v-2.423h1v2.423q0 .691-.462 1.153T17.384 19z"/></svg>
            </a>
        </li>`

        html += h;
    })
    return html;
}

function copyToClipboard() {

    const copyData = document.querySelector("#download-uri").value;

    navigator.clipboard.writeText(copyData)
        .then(() => {
            document.querySelector("label[for='download-uri']").textContent = "gekopieerd!"

            setTimeout(() => {
                document.querySelector("label[for='download-uri']").textContent = "kopieer link!"
            }, 5000)
        })
        .catch(err => {
            console.error('Error copying text: ', err);
        });
}

function CreateTransferReport(i) {
    const data = JSON.parse(i);

    const ul = document.createElement("ul");

    for (let i = 0; i < data.length; i++) {
        const record = data[i];
        const li = document.createElement('li');
        const header = document.createElement('header');
        header.textContent = record.transfer_title;

        let arrayOfFiles = JSON.parse(record.transfer_files);
        let numberOfFiles = (arrayOfFiles.length > 1) ?
            `${arrayOfFiles.length} bestanden` :
            `${arrayOfFiles.length} bestand`;
        let numberOfDownloads = (record.downloads > 0) ?
            `${record.downloads} keer gedownload` :
            `Nog geen downloads`;

        const span = document.createElement('span');
        span.innerHTML = `<p id="size">${totalSize(arrayOfFiles)} (${numberOfFiles})</p>
                          <p class="dot">.</p>
                          <p id="downloads">${numberOfDownloads}</p>
                          <p class="dot">.</p>
                          <p id="expire">${dateFormatter(record.expire_date)}</p>`;

        li.appendChild(header);
        li.appendChild(span);
        li.addEventListener('click', () => {
            window.history.pushState("", "", "/app/transfers?id=" + record.transfer_id);
            addOverlay("detail-transfer");
        });
        ul.appendChild(li);
    }

    document.querySelector('.subPageContent').appendChild(ul);
}