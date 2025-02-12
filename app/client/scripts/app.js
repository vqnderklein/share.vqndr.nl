const fileUpload = document.querySelector("#fileUpload");
const dirUpload = document.querySelector("#dirUpload");
const submitButton = document.querySelector(".submit");
const fileListContainer = document.querySelector("#fileList");

let filesToUpload = [];

fileUpload.addEventListener("change", handleFileSelection);
dirUpload.addEventListener("change", handleFileSelection);
submitButton.addEventListener("click", handleFormSubmit);

function handleFileSelection(event) {
    const files = event.target.files;

    Array.from(files).forEach(file => {
        filesToUpload.push(file);
    });

    updateFileList();
}

function updateFileList() {
    fileListContainer.innerHTML = "";

    filesToUpload.forEach((file, index) => {
        const fileDiv = document.createElement("div");
        fileDiv.classList.add("file-item");

        const fileName = document.createElement("span");
        fileName.classList.add("fileItem");

        const fileExtension = file.name.split('.').pop();
        fileIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="currentColor"><path d="M5.5 5.9c0-.84 0-1.26.163-1.581a1.5 1.5 0 0 1 .656-.656c.32-.163.74-.163 1.581-.163h4.606c.367 0 .55 0 .723.041q.232.056.433.18c.152.093.281.223.54.482l3.595 3.594c.26.26.39.39.482.54q.124.204.18.434c.041.173.041.356.041.723V18.1c0 .84 0 1.26-.163 1.581a1.5 1.5 0 0 1-.656.656c-.32.163-.74.163-1.581.163H7.9c-.84 0-1.26 0-1.581-.163a1.5 1.5 0 0 1-.656-.656c-.163-.32-.163-.74-.163-1.581z"/><path d="M12.5 3.5v3.6c0 .84 0 1.26.164 1.581a1.5 1.5 0 0 0 .655.656c.32.163.74.163 1.581.163h3.6"/></g></svg>`; // File icon
        fileName.innerHTML = `
                <div>
                    ${fileIcon}
                    <div>
                        <b>${file.name}</b>
                        <span>${formatBytes(file.size)} &bull; ${fileExtension}</span>
                    </div>
                </div>
            `;


        const removeButton = document.createElement("button");
        removeButton.innerHTML = `  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12z"/></svg>`;
        removeButton.classList.add("remove-file");
        removeButton.addEventListener("click", () => removeFile(index));

        fileName.appendChild(removeButton);

        fileListContainer.appendChild(fileName);
    });
}

function removeFile(index) {
    filesToUpload.splice(index, 1);
    updateFileList();
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function changeUI(state) {
    const uploadForm = document.querySelector('form');
    const formHeight = uploadForm.offsetHeight;
    uploadForm.style.height = formHeight + 'px';
    uploadForm.classList.add('transfering');

    switch (state) {
        case "uploading":
            fetchServerContent("/app/client/assets/html/upload.txt")
                .then(function(content) {
                    console.log(content);
                    uploadForm.innerHTML = content;
                })
                .catch(function(error) {
                    console.log(error);
                });
            break;
        case "processing":

            setTimeout(function() {}, 1200);

            document.querySelector('.circle').setAttribute('stroke-dasharray', `95, 100`)

            document.querySelector('.single-chart').classList.add('rotating');
            document.querySelector('form h2').textContent = 'Verwerken'

            break;
        case "sending":
            fetchServerContent("/app/client/assets/html/truck.txt")
                .then(function(content) {
                    console.log(content);
                    uploadForm.innerHTML = content;
                })
                .catch(function(error) {
                    console.log(error);
                });

            break;
        default:
            break;
    }
}

function fetchServerContent(url) {
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


function handleFormSubmit(event) {
    event.preventDefault();

    const email_sender = document.querySelector("#email_sender").value.trim();
    const email_retriever = document.querySelector("#email").value.trim();
    const subject = document.querySelector("#subject").value.trim();
    const message = document.querySelector("#message").value.trim();

    if (!email_sender || !email_retriever || !subject || !message || filesToUpload.length === 0) {
        alert("Please fill in all the fields and select at least one file.");
        return;
    }

    const formData = new FormData();
    changeUI("uploading");
    setTimeout(() => {

    }, (700));

    filesToUpload.forEach(file => {
        formData.append("files[]", file);
    });

    formData.append("email_sender", email_sender);
    formData.append("email_retriever", email_retriever);
    formData.append("subject", subject);
    formData.append("message", message);


    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/app/server/api/upload", true);

    xhr.upload.addEventListener("progress", event => {
        if (event.lengthComputable) {
            const percentComplete = (event.loaded / event.total) * 100;
            document.querySelector('.percentage').textContent = Math.floor(percentComplete) + "%";
            document.querySelector('.circle').setAttribute('stroke-dasharray', `${percentComplete}, 100`);

            if (percentComplete == 100)
                changeUI("processing");
        }
    });

    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log(xhr.responseText);

            changeUI("sending");

            setTimeout(() => {
                window.location.reload();
            }, 5500);


        } else {
            console.error("Server returned error:", xhr.statusText, xhr.responseText);
        }
    };

    xhr.onerror = function() {
        console.error("XHR error occurred.");
    };

    xhr.send(formData);
}