let prevDate = new Date("2000-02-04 22:34:05");
setInterval(updateSerial, 3000);
async function updateSerial() {
    fetch('https://api.noworri.com/api/getmessages')
        .then((response) => response.json())
        .then((json) => {
            const dataContainer = document.getElementById("data");
            for (let i = 0; i < json.length; i++) {
                let actuDate = new Date(json[i].updated_at);
                if (actuDate > prevDate) {
                    prevDate = actuDate
                    let div = document.createElement("p");
                    div.innerHTML = json[i].message;
                    dataContainer.appendChild(div);
                }
            };
        });
}

var form = document.getElementById("myForm");

postSerial = async function (event) {
    event.preventDefault();
    let msg = document.querySelector('#serial_message').value;
    fetch('https://api.noworri.com/api/sendmessage', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: "1",
            message: msg
        }),
    })
        .then(response => response.json())
        .then(data => {
            document.querySelector('input').value = "";
            console.log('Success:', data);
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}
form.addEventListener('submit', postSerial);

var updateForm = document.getElementById("upload");
postFirmware = async function (event) {
    event.preventDefault();
    let file = await document.querySelector('[type=file]').files[0];
    // let content = await file.blob();
    let formData = new FormData();
    formData.append('file', file);
    var xhr = new XMLHttpRequest();

    // Add any event handlers here... 
    xhr.responseType = 'json';
    xhr.onload = (e) => {
        // https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Sending_and_Receiving_Binary_Data
        const data = xhr.response;
        msg = "update: " + data["path"];
        fetch('https://api.noworri.com/api/sendmessage', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: "1",
                message: msg,
            }),
        })
            .then(response => response.json())
            .then(data => {
                document.querySelector('input').value = "";
                console.log('Success:', data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }
    xhr.open('POST', 'https://api.noworri.com/api/sendfile', true);
    xhr.send(formData);

    // To avoid actual submission of the form 
    return false;
}
updateForm.addEventListener('submit', postFirmware);

// var form = document.getElementById("myForm");
// getFirmware = function (event) {
//     event.preventDefault();
//     fetch('https://api.noworri.com/api/downloadfile?url=https://noworri.com/api/public/uploads/module/phpJfO81P/phpYk2G4G')
//         .then((response) => console.log(response));

//     // To avoid actual submission of the form 
//     return false;
// }
// form.addEventListener('submit', getFirmware);

cleanSerial = async () => {
    document.querySelector('input').value = "";
    fetch('https://api.noworri.com/api/deletemessages', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
    })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
        })
        .then(
            initializeJson()

        )
        .catch((error) => {
            console.error('Error:', error);
        });

    // setTimeout(() => {
    // }, 1000);
}

initializeJson = () => {
    fetch('https://api.noworri.com/api/sendmessage', {
        method: 'PUT',
        mode: "cors",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: "1",
            message: "ABC"
        }),
    })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

// setInterval(() => {
//     updateSerial
// }, 1000);
