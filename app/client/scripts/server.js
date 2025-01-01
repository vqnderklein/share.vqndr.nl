export function createXHRRequest({ method, url, data = null, headers = {} }) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.open(method, url, true);

        for (const key in headers) {
            xhr.setRequestHeader(key, headers[key]);
        }

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (error) {
                    reject({ error: 'Failed to parse JSON', details: error });
                }
            } else {
                reject({ error: 'Request failed', status: xhr.status, statusText: xhr.statusText });
            }
        };

        xhr.onerror = function() {
            reject({ error: 'Network error', status: xhr.status, statusText: xhr.statusText });
        };

        xhr.ontimeout = function() {
            reject({ error: 'Request timed out', status: xhr.status });
        };

        if (method.toUpperCase() === 'POST' && data) {
            if (typeof data === 'object') {
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify(data));
            } else {
                xhr.send(data);
            }
        } else {
            xhr.send();
        }
    });
}