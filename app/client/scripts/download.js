import { createXHRRequest } from './server.js';
import { dateFormatter, totalSize, returnFilesInHTML, fetchServerContent } from './utils.js';

const _downloadID = window.location.href.split("?id=")[1]; //Local
const downloadID = window.location.href.split("/")[4]; //Production
const uploadForm = document.querySelector("#download section");

function confirmDownload(downloadID) {

    createXHRRequest({
            method: 'GET',
            url: '/app/api/retrieve?id=' + downloadID,
            data: { id: downloadID },
        })
        .then(response => {
            const responseData = response[0];

            let arrayData = JSON.parse(responseData.transfer_files);
            let headerText = `${totalSize(arrayData)} &ensp;&bull;&ensp; ${dateFormatter(responseData.expire_date)}`

            document.querySelector(".subTitle>a").textContent = responseData.user;
            document.querySelector(".subTitle>a").setAttribute("href", `mailto:${responseData.user}`)
            document.querySelector(".subTitle").childNodes[0].nodeValue = responseData.transfer_title;
            document.querySelector('.information').innerHTML = headerText;

            let dataCollection = returnFilesInHTML(arrayData);
            document.querySelector('.fileListDownload').appendChild(dataCollection);

            loadDownloadButton();
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadDownloadButton() {
    const allDownloadsBttns = document.querySelectorAll(".fileListDownload a");

    allDownloadsBttns.forEach(button => {
        const fileNameDownload = button.getAttribute("file-name");
        button.setAttribute("href", `/app/api/download?m=file&i=${downloadID}&f=${fileNameDownload}`);

        button.addEventListener("click", (event) => {
            event.stopPropagation(); // Prevent the event from propagating to parent elements

            button.innerHTML = `<svg class="spinning" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 1 0 9 9"/></svg>`;

            window.addEventListener('beforeunload', () => {
                setTimeout(() => {
                    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 2048 2048"><path fill="currentColor" d="M1024 0q141 0 272 36t244 104t207 160t161 207t103 245t37 272q0 141-36 272t-104 244t-160 207t-207 161t-245 103t-272 37q-141 0-272-36t-244-104t-207-160t-161-207t-103-245t-37-272q0-141 36-272t104-244t160-207t207-161T752 37t272-37m603 685l-136-136l-659 659l-275-275l-136 136l411 411z"/></svg>`;
                    button.querySelector("svg").style.padding = "2px";
                }, 300);
            });
        });
    });

    const submitButton = document.querySelector(".submit");
    submitButton.setAttribute("href", `/app/api/download?m=zip&i=${downloadID}`);

    submitButton.addEventListener("click", function(event) {
        event.stopPropagation(); // Prevent issues with nested click handlers

        fetchServerContent("/app/client/assets/html/upload.txt")
            .then(function(content) {
                console.log(content);
                uploadForm.innerHTML = content;
                uploadForm.querySelector(".percentage").style.display = "none";
                uploadForm.querySelector("h2").textContent = "Verwerken";
                uploadForm.querySelector(".single-chart").classList.add("rotating");
            })
            .catch(function(error) {
                console.log(error);
            });

        window.addEventListener('beforeunload', () => {
            setTimeout(() => {
                fetchServerContent("/app/client/assets/html/download.txt")
                    .then(function(content) {
                        uploadForm.innerHTML = content;
                        uploadForm.querySelector("h1").style.fontWeight = "bold";

                        document.querySelector(".back").addEventListener("click", () => {
                            window.location.reload();
                        });
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
            }, 300);
        });
    });
}

//Load frontend
confirmDownload(downloadID);